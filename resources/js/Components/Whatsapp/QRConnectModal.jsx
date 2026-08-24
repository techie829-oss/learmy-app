import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { QrCode, CheckCircle2, AlertCircle, RefreshCw, X, LogOut, Smartphone } from 'lucide-react';

export default function QRConnectModal({ isOpen, onClose, onConnected }) {
    const [status, setStatus] = useState('disconnected');
    const [qrImage, setQrImage] = useState(null);
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        let interval = null;
        if (isOpen) {
            startSession();
            interval = setInterval(checkStatus, 2500);
        } else {
            setQrImage(null);
            setStatus('disconnected');
        }

        return () => {
            if (interval) clearInterval(interval);
        };
    }, [isOpen]);

    const startSession = async () => {
        setLoading(true);
        setError(null);
        try {
            const res = await axios.post('/app/whatsapp/qr/start');
            setStatus(res.data.status);
            setQrImage(res.data.qr);
            setUser(res.data.user);
            if (res.data.status === 'connected') {
                if (onConnected) onConnected();
            }
        } catch (err) {
            console.error('Failed to start QR session:', err);
            setError('Could not connect to WhatsApp QR service. Ensure background service is running.');
        } finally {
            setLoading(false);
        }
    };

    const checkStatus = async () => {
        try {
            const res = await axios.get('/app/whatsapp/qr/status');
            setStatus(res.data.status);
            if (res.data.qr) setQrImage(res.data.qr);
            if (res.data.user) setUser(res.data.user);

            if (res.data.status === 'connected') {
                if (onConnected) onConnected();
            }
        } catch (err) {
            console.error('Error polling QR status:', err);
        }
    };

    const handleLogout = async () => {
        if (!confirm('Are you sure you want to disconnect your WhatsApp QR session?')) return;
        setLoading(true);
        try {
            await axios.post('/app/whatsapp/qr/logout');
            setStatus('disconnected');
            setQrImage(null);
            setUser(null);
        } catch (err) {
            console.error('Logout failed:', err);
        } finally {
            setLoading(false);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div className="bg-white dark:bg-neutral-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-neutral-200 dark:border-neutral-800 relative animate-in fade-in zoom-in duration-200">
                <button
                    onClick={onClose}
                    className="absolute top-4 right-4 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 p-1.5 rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                >
                    <X className="h-5 w-5" />
                </button>

                <div className="text-center space-y-4">
                    <div className="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                        <QrCode className="h-7 w-7" />
                    </div>

                    <div>
                        <h2 className="text-xl font-bold text-neutral-900 dark:text-white">
                            Scan WhatsApp QR Code
                        </h2>
                        <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                            Link your personal or business WhatsApp in 30 seconds with zero credit card or verification barriers!
                        </p>
                    </div>

                    {error && (
                        <div className="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2 text-left">
                            <AlertCircle className="h-4 w-4 shrink-0" />
                            <span>{error}</span>
                        </div>
                    )}

                    {status === 'connected' ? (
                        <div className="p-6 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-center space-y-4">
                            <CheckCircle2 className="h-12 w-12 text-emerald-500 mx-auto" />
                            <div>
                                <h3 className="font-semibold text-emerald-900 dark:text-emerald-200 text-base">
                                    WhatsApp Successfully Connected!
                                </h3>
                                <p className="text-xs text-emerald-700 dark:text-emerald-400 mt-1">
                                    {user?.id ? `Phone: +${user.id.split(':')[0]}` : 'Active session persistence enabled'}
                                </p>
                            </div>
                            <button
                                onClick={handleLogout}
                                disabled={loading}
                                className="w-full py-2 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-medium text-xs flex items-center justify-center gap-2 transition"
                            >
                                <LogOut className="h-4 w-4" /> Disconnect Session
                            </button>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            <div className="relative mx-auto h-64 w-64 rounded-2xl border-2 border-dashed border-emerald-300 dark:border-emerald-800 bg-neutral-50 dark:bg-neutral-800/50 flex items-center justify-center overflow-hidden p-3 shadow-inner">
                                {loading && !qrImage ? (
                                    <div className="flex flex-col items-center gap-2 text-neutral-400 text-xs">
                                        <RefreshCw className="h-8 w-8 animate-spin text-emerald-600" />
                                        <span>Generating QR Code...</span>
                                    </div>
                                ) : qrImage ? (
                                    <img src={qrImage} alt="WhatsApp QR Code" className="h-full w-full object-contain rounded-xl" />
                                ) : (
                                    <div className="flex flex-col items-center gap-2 text-neutral-400 text-xs text-center p-4">
                                        <Smartphone className="h-8 w-8 text-neutral-400" />
                                        <span>Click below to generate WhatsApp QR Code</span>
                                    </div>
                                )}
                            </div>

                            <div className="text-left text-xs text-neutral-600 dark:text-neutral-400 bg-neutral-50 dark:bg-neutral-800/40 p-3 rounded-xl space-y-1">
                                <p className="font-semibold text-neutral-800 dark:text-neutral-200 mb-1">📱 Instructions:</p>
                                <p>1. Open WhatsApp on your phone.</p>
                                <p>2. Tap <strong>Menu (⋮) / Settings</strong> → <strong>Linked Devices</strong>.</p>
                                <p>3. Tap <strong>Link a Device</strong> and point your camera at this QR code.</p>
                            </div>

                            <button
                                onClick={startSession}
                                disabled={loading}
                                className="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs flex items-center justify-center gap-2 transition shadow-md"
                            >
                                <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} /> Refresh QR Code
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
