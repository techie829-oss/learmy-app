import express from 'express';
import cors from 'cors';
import axios from 'axios';
import QRCode from 'qrcode';
import pino from 'pino';
import fs from 'fs';
import path from 'path';
import https from 'https';
import { fileURLToPath } from 'url';
import makeWASocket, {
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion,
    makeCacheableSignalKeyStore,
    Browsers
} from '@whiskeysockets/baileys';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PORT = process.env.WA_QR_PORT || 3001;
const LARAVEL_WEBHOOK_URL = process.env.LARAVEL_WEBHOOK_URL || 'https://learmy.solidrix.com/webhooks/whatsapp-qr';
const SESSIONS_DIR = path.join(__dirname, '../storage/app/whatsapp-sessions');

if (!fs.existsSync(SESSIONS_DIR)) {
    fs.mkdirSync(SESSIONS_DIR, { recursive: true });
}

const logger = pino({ level: 'silent' });
const app = express();
app.use(cors());
app.use(express.json({ limit: '50mb' }));

// In-memory active sockets & QR cache
const activeSessions = new Map(); // sessionId -> { sock, qr, status, user, info }

async function getOrCreateSession(sessionId) {
    if (activeSessions.has(sessionId)) {
        return activeSessions.get(sessionId);
    }

    const sessionPath = path.join(SESSIONS_DIR, sessionId);
    if (!fs.existsSync(sessionPath)) {
        fs.mkdirSync(sessionPath, { recursive: true });
    }

    const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
    const { version } = await fetchLatestBaileysVersion();

    const sessionData = {
        sock: null,
        qr: null,
        status: 'initializing',
        user: null,
        sessionId
    };

    const sock = makeWASocket({
        version,
        logger,
        printQRInTerminal: false,
        browser: Browsers.macOS('Chrome'), // Simulates standard macOS Google Chrome to avoid VPS IP security flags
        keepAliveIntervalMs: 30000, // Ping socket every 30s to prevent Malaysia VPS TCP drops
        markOnlineOnConnect: true,
        syncFullHistory: false, // Prevents loading thousands of old messages which triggers WhatsApp rate limits
        auth: {
            creds: state.creds,
            keys: makeCacheableSignalKeyStore(state.keys, logger)
        },
        generateHighQualityLinkPreview: true
    });

    sessionData.sock = sock;
    activeSessions.set(sessionId, sessionData);

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            sessionData.status = 'qr_ready';
            sessionData.qr = await QRCode.toDataURL(qr);
            notifyLaravel(sessionId, 'qr_updated', { qr: sessionData.qr });
        }

        if (connection === 'open') {
            sessionData.status = 'connected';
            sessionData.qr = null;
            sessionData.user = sock.user;
            notifyLaravel(sessionId, 'connected', {
                user: sock.user,
                phone: sock.user?.id ? sock.user.id.split(':')[0] : null
            });
        }

        if (connection === 'close') {
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

            sessionData.status = shouldReconnect ? 'reconnecting' : 'disconnected';
            sessionData.qr = null;

            notifyLaravel(sessionId, 'disconnected', {
                reason: statusCode,
                loggedOut: !shouldReconnect
            });

            activeSessions.delete(sessionId);

            if (!shouldReconnect) {
                // Clear session files if logged out
                try {
                    fs.rmSync(sessionPath, { recursive: true, force: true });
                } catch (err) {
                    console.error('Failed to clear session dir:', err);
                }
            } else {
                // Attempt automatic reconnect after 3 seconds
                setTimeout(() => getOrCreateSession(sessionId), 3000);
            }
        }
    });

    sock.ev.on('messages.upsert', async ({ messages, type }) => {
        if (type !== 'notify') return;

        for (const msg of messages) {
            if (!msg.message || msg.key.fromMe) continue;
            notifyLaravel(sessionId, 'inbound_message', { message: msg });
        }
    });

    return sessionData;
}

const WA_QR_SECRET = process.env.WA_QR_SECRET || 'learmy_qr_sec_99812';
const httpsAgent = new https.Agent({ rejectUnauthorized: false });

async function notifyLaravel(sessionId, event, data) {
    try {
        await axios.post(LARAVEL_WEBHOOK_URL, {
            session_id: sessionId,
            event,
            data,
            timestamp: Math.floor(Date.now() / 1000)
        }, {
            headers: {
                'Content-Type': 'application/json',
                'X-Learmy-QR-Secret': WA_QR_SECRET
            },
            httpsAgent,
            timeout: 5000
        });
    } catch (err) {
        console.error(`[Webhook Error] Failed to notify Laravel for session ${sessionId}:`, err.message);
    }
}

// REST API Endpoints

// Health Check
app.get('/health', (req, res) => {
    res.json({ status: 'ok', activeSessionsCount: activeSessions.size });
});

// Start / Connect Session
app.post('/api/sessions/start', async (req, res) => {
    const { sessionId } = req.body;
    if (!sessionId) {
        return res.status(400).json({ error: 'sessionId is required' });
    }

    try {
        const session = await getOrCreateSession(sessionId);
        res.json({
            status: session.status,
            qr: session.qr,
            user: session.user
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Get Session Status & QR
app.get('/api/sessions/status/:sessionId', async (req, res) => {
    const { sessionId } = req.params;
    const session = activeSessions.get(sessionId);

    if (!session) {
        return res.json({ status: 'disconnected', qr: null, user: null });
    }

    res.json({
        status: session.status,
        qr: session.qr,
        user: session.user
    });
});

// Send Message
app.post('/api/messages/send', async (req, res) => {
    const { sessionId, to, text, mediaUrl, mediaType, caption } = req.body;

    if (!sessionId || !to) {
        return res.status(400).json({ error: 'sessionId and to phone number are required' });
    }

    const session = activeSessions.get(sessionId);
    if (!session || session.status !== 'connected' || !session.sock) {
        return res.status(400).json({ error: 'Session is not connected' });
    }

    try {
        // Anti-ban simulation: Add randomized human-like delay (1s to 2.5s) between sends
        const randomDelay = Math.floor(Math.random() * 1500) + 1000;
        await new Promise((resolve) => setTimeout(resolve, randomDelay));

        // Format phone number to WhatsApp JID format (e.g. 919876543210@s.whatsapp.net)
        const cleanPhone = to.replace(/[^0-9]/g, '');
        const jid = `${cleanPhone}@s.whatsapp.net`;

        let result;
        if (mediaUrl) {
            const messageObject = {};
            if (mediaType === 'image') messageObject.image = { url: mediaUrl };
            else if (mediaType === 'video') messageObject.video = { url: mediaUrl };
            else messageObject.document = { url: mediaUrl, fileName: caption || 'file' };

            if (caption) messageObject.caption = caption;
            result = await session.sock.sendMessage(jid, messageObject);
        } else {
            result = await session.sock.sendMessage(jid, { text: text || '' });
        }

        res.json({ success: true, messageId: result.key.id, timestamp: result.messageTimestamp });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Delete / Logout Session
app.post('/api/sessions/logout', async (req, res) => {
    const { sessionId } = req.body;
    const session = activeSessions.get(sessionId);

    if (session && session.sock) {
        try {
            await session.sock.logout();
        } catch (e) {
            console.error('Logout error:', e);
        }
    }

    const sessionPath = path.join(SESSIONS_DIR, sessionId);
    try {
        fs.rmSync(sessionPath, { recursive: true, force: true });
    } catch (e) {}

    activeSessions.delete(sessionId);
    res.json({ success: true, status: 'logged_out' });
});

// ─────────────────────────────────────────────────────────────────────────────
// Send Rich Template Message (Header image/text + Body + Footer + Buttons)
// Supports WhatsApp-style template structure via Baileys
// ─────────────────────────────────────────────────────────────────────────────
app.post('/api/messages/send-template', async (req, res) => {
    /**
     * Request body shape:
     * {
     *   sessionId: 'workspace_3_qr',
     *   to: '+917007420572',
     *   template: {
     *     header: { type: 'text'|'image'|'video', text: '...', url: '...' },
     *     body: 'Hello *Name*! Rendered body text.',
     *     footer: 'Powered by Learmy',
     *     buttons: [
     *       { type: 'url',        text: 'Visit Website', value: 'https://...' },
     *       { type: 'quickReply', text: 'Yes, Interested' },
     *       { type: 'call',       text: 'Call Now',       value: '+91...' }
     *     ]
     *   }
     * }
     */
    const { sessionId, to, template } = req.body;

    if (!sessionId || !to || !template) {
        return res.status(400).json({ error: 'sessionId, to, and template are required' });
    }

    const session = activeSessions.get(sessionId);
    if (!session || session.status !== 'connected' || !session.sock) {
        return res.status(400).json({ error: 'Session is not connected' });
    }

    try {
        // Anti-ban delay
        const randomDelay = Math.floor(Math.random() * 1500) + 1000;
        await new Promise((resolve) => setTimeout(resolve, randomDelay));

        const cleanPhone = to.replace(/[^0-9]/g, '');
        const jid = `${cleanPhone}@s.whatsapp.net`;

        const { header, body, footer, buttons = [] } = template;

        // ── Build formatted button lines ──────────────────────────────────────
        const buttonLines = buttons.map((btn) => {
            if (btn.type === 'url')        return `🔗 ${btn.text}: ${btn.value || ''}`;
            if (btn.type === 'call')       return `📞 ${btn.text}: ${btn.value || ''}`;
            if (btn.type === 'quickReply') return `👉 ${btn.text}`;
            return `• ${btn.text}`;
        }).join('\n');

        let result;

        // ── Image header: send image first, then body+footer+buttons as caption ──
        if (header && header.type === 'image' && header.url) {
            const captionParts = [];
            if (body)        captionParts.push(body);
            if (footer)      captionParts.push(`_${footer}_`);
            if (buttonLines) captionParts.push(`\n${buttonLines}`);

            result = await session.sock.sendMessage(jid, {
                image: { url: header.url },
                caption: captionParts.join('\n\n'),
                jpegThumbnail: null
            });
        }

        // ── Video header ──────────────────────────────────────────────────────
        else if (header && header.type === 'video' && header.url) {
            const captionParts = [];
            if (body)        captionParts.push(body);
            if (footer)      captionParts.push(`_${footer}_`);
            if (buttonLines) captionParts.push(`\n${buttonLines}`);

            result = await session.sock.sendMessage(jid, {
                video: { url: header.url },
                caption: captionParts.join('\n\n')
            });
        }

        // ── Text-only / No header ─────────────────────────────────────────────
        else {
            const textParts = [];
            if (header && header.type === 'text' && header.text) {
                textParts.push(`*${header.text}*`);
            }
            if (body)        textParts.push(body);
            if (footer)      textParts.push(`_${footer}_`);
            if (buttonLines) textParts.push(`\n${buttonLines}`);

            result = await session.sock.sendMessage(jid, {
                text: textParts.join('\n\n')
            });
        }

        res.json({
            success: true,
            messageId: result.key.id,
            timestamp: result.messageTimestamp
        });

    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// WhatsApp Groups: List all groups for a session
// GET /api/groups?sessionId=workspace_3_qr
// ─────────────────────────────────────────────────────────────────────────────
app.get('/api/groups', async (req, res) => {
    const { sessionId } = req.query;
    if (!sessionId) return res.status(400).json({ error: 'sessionId is required' });

    const session = activeSessions.get(sessionId);
    if (!session || session.status !== 'connected' || !session.sock) {
        return res.status(400).json({ error: 'Session is not connected' });
    }

    try {
        // Fetch all chats and filter groups (JID ends with @g.us)
        const chats = await session.sock.groupFetchAllParticipating();
        const groups = Object.values(chats).map((g) => ({
            id:            g.id,
            name:          g.subject || 'Unnamed Group',
            description:   g.desc || '',
            participantCount: (g.participants || []).length,
            creation:      g.creation,
        }));

        res.json({ success: true, groups });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// WhatsApp Groups: Get participants of a specific group
// GET /api/groups/:groupId/participants?sessionId=workspace_3_qr
// groupId must be URL-encoded (e.g. 120363XXXX@g.us → 120363XXXX%40g.us)
// ─────────────────────────────────────────────────────────────────────────────
app.get('/api/groups/:groupId/participants', async (req, res) => {
    const { sessionId } = req.query;
    const groupId = decodeURIComponent(req.params.groupId);

    if (!sessionId) return res.status(400).json({ error: 'sessionId is required' });

    const session = activeSessions.get(sessionId);
    if (!session || session.status !== 'connected' || !session.sock) {
        return res.status(400).json({ error: 'Session is not connected' });
    }

    try {
        const metadata = await session.sock.groupMetadata(groupId);
        const participants = (metadata.participants || []).map((p) => {
            // JID format: 919876543210:4@s.whatsapp.net or 919876543210@s.whatsapp.net
            const phone = p.id.split(':')[0].split('@')[0];
            return {
                jid:   p.id,
                phone: `+${phone}`,
                admin: p.admin === 'admin' || p.admin === 'superadmin',
            };
        });

        res.json({
            success: true,
            group: {
                id:          metadata.id,
                name:        metadata.subject,
                description: metadata.desc || '',
                participants,
            },
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`🚀 Learmy WhatsApp QR Engine listening on port ${PORT}`);
});
