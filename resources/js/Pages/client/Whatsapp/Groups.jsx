import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import ClientLayout from '@/Layouts/ClientLayout';
import axios from 'axios';
import {
    ArrowLeft, Users, Tag, Download, AlertCircle, CheckCircle2,
    ChevronDown, ChevronUp, Loader2, ExternalLink
} from 'lucide-react';

export default function Groups({ groups = [], tags = [], error = null, connected = false }) {
    const { props } = usePage();
    const [expandedGroup, setExpandedGroup] = useState(null);
    const [participants, setParticipants] = useState({});
    const [loadingGroup, setLoadingGroup] = useState(null);

    // Import modal state
    const [importModal, setImportModal] = useState(null); // { groupId, groupName }
    const [importForm, setImportForm] = useState({ tag_id: '', new_tag_name: '' });
    const [importing, setImporting] = useState(false);
    const [importResult, setImportResult] = useState(null);

    const loadParticipants = async (groupId) => {
        if (participants[groupId]) {
            setExpandedGroup(expandedGroup === groupId ? null : groupId);
            return;
        }
        setLoadingGroup(groupId);
        try {
            const res = await axios.get(route('whatsapp.groups.participants', encodeURIComponent(groupId)));
            setParticipants(prev => ({ ...prev, [groupId]: res.data.group.participants }));
            setExpandedGroup(groupId);
        } catch (e) {
            alert('Failed to load participants: ' + (e?.response?.data?.error || e.message));
        } finally {
            setLoadingGroup(null);
        }
    };

    const openImportModal = (group) => {
        setImportModal({ groupId: group.id, groupName: group.name });
        setImportForm({ tag_id: '', new_tag_name: '' });
        setImportResult(null);
    };

    const handleImport = async () => {
        if (!importForm.tag_id && !importForm.new_tag_name.trim()) {
            alert('Koi tag select karo ya naya tag name dalo.');
            return;
        }
        setImporting(true);
        try {
            const res = await axios.post(route('whatsapp.groups.import'), {
                group_id:     importModal.groupId,
                group_name:   importModal.groupName,
                tag_id:       importForm.tag_id || null,
                new_tag_name: importForm.new_tag_name.trim() || null,
            });
            setImportResult(res.data);
        } catch (e) {
            alert('Import failed: ' + (e?.response?.data?.error || e.message));
        } finally {
            setImporting(false);
        }
    };

    return (
        <ClientLayout>
            <Head title="Import WhatsApp Groups" />

            <div className="max-w-4xl space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Link
                        href={route('client.meetings.create')}
                        className="rounded-full p-2 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-neutral-800"
                    >
                        <ArrowLeft className="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            📱 WhatsApp Group Import
                        </h1>
                        <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                            Apne WhatsApp groups ke members ko contacts mein import karo aur tag assign karo.
                        </p>
                    </div>
                </div>

                {/* How it works */}
                <div className="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800/40 dark:bg-blue-900/10">
                    <p className="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">📋 Kaise kaam karta hai?</p>
                    <ol className="text-xs text-blue-700 dark:text-blue-400 space-y-1 list-decimal list-inside">
                        <li>Niche list mein apna WhatsApp group choose karo</li>
                        <li>"Import" button dabao → tag assign karo (naya banao ya existing)</li>
                        <li>Contacts import ho jaayenge us tag ke saath</li>
                        <li>Meeting schedule karte waqt wahi tag choose karo → sab ko reminder!</li>
                    </ol>
                </div>

                {/* Error */}
                {error && (
                    <div className="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800/40 dark:bg-red-900/10">
                        <div className="flex items-center gap-2">
                            <AlertCircle className="h-5 w-5 text-red-500" />
                            <p className="text-sm font-medium text-red-700 dark:text-red-400">{error}</p>
                        </div>
                        {!connected && (
                            <Link
                                href={route('integrations.index')}
                                className="mt-2 inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:underline"
                            >
                                <ExternalLink className="h-3 w-3" />
                                WhatsApp connect karo → Integrations
                            </Link>
                        )}
                    </div>
                )}

                {/* Groups List */}
                {groups.length > 0 ? (
                    <div className="space-y-3">
                        <p className="text-sm font-medium text-neutral-600 dark:text-neutral-400">
                            {groups.length} WhatsApp Groups mila
                        </p>
                        {groups.map(group => (
                            <div
                                key={group.id}
                                className="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700/50 dark:bg-neutral-800 overflow-hidden"
                            >
                                <div className="flex items-center justify-between p-4">
                                    <div className="flex items-center gap-3 min-w-0">
                                        <div className="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                            <Users className="h-5 w-5 text-green-600 dark:text-green-400" />
                                        </div>
                                        <div className="min-w-0">
                                            <p className="text-sm font-semibold text-neutral-900 dark:text-white truncate">{group.name}</p>
                                            <p className="text-xs text-neutral-500 dark:text-neutral-400">
                                                {group.participantCount} members
                                            </p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2 flex-shrink-0 ml-3">
                                        <button
                                            onClick={() => openImportModal(group)}
                                            className="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700 transition-colors"
                                        >
                                            <Download className="h-3.5 w-3.5" />
                                            Import
                                        </button>
                                        <button
                                            onClick={() => loadParticipants(group.id)}
                                            className="inline-flex items-center gap-1 rounded-lg border border-neutral-200 px-2.5 py-1.5 text-xs font-medium text-neutral-600 hover:bg-neutral-50 dark:border-neutral-600 dark:text-neutral-400 dark:hover:bg-neutral-700 transition-colors"
                                        >
                                            {loadingGroup === group.id ? (
                                                <Loader2 className="h-3.5 w-3.5 animate-spin" />
                                            ) : expandedGroup === group.id ? (
                                                <ChevronUp className="h-3.5 w-3.5" />
                                            ) : (
                                                <ChevronDown className="h-3.5 w-3.5" />
                                            )}
                                            Members
                                        </button>
                                    </div>
                                </div>

                                {/* Participants list */}
                                {expandedGroup === group.id && participants[group.id] && (
                                    <div className="border-t border-neutral-100 dark:border-neutral-700 p-4">
                                        <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                            {participants[group.id].map((p, i) => (
                                                <div key={i} className="flex items-center gap-2 rounded-lg bg-neutral-50 dark:bg-neutral-700/50 px-3 py-2">
                                                    <div className="h-2 w-2 rounded-full bg-green-400 flex-shrink-0" />
                                                    <span className="text-xs text-neutral-700 dark:text-neutral-300 truncate">
                                                        {p.phone}
                                                        {p.admin && <span className="ml-1 text-amber-500">★</span>}
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                ) : !error && (
                    <div className="rounded-xl border border-neutral-200 bg-neutral-50 p-8 text-center dark:border-neutral-700 dark:bg-neutral-800/50">
                        <Users className="h-12 w-12 text-neutral-300 mx-auto mb-3" />
                        <p className="text-sm font-medium text-neutral-500">Koi WhatsApp group nahi mila</p>
                        <p className="text-xs text-neutral-400 mt-1">Pehle WhatsApp se groups join/create karo</p>
                    </div>
                )}
            </div>

            {/* Import Modal */}
            {importModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div className="w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-neutral-800">
                        <div className="p-6">
                            <div className="flex items-center gap-3 mb-4">
                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                    <Tag className="h-5 w-5 text-green-600" />
                                </div>
                                <div>
                                    <h2 className="text-base font-bold text-neutral-900 dark:text-white">
                                        Group Import karo
                                    </h2>
                                    <p className="text-xs text-neutral-500 dark:text-neutral-400 truncate max-w-[200px]">
                                        {importModal.groupName}
                                    </p>
                                </div>
                            </div>

                            {importResult ? (
                                <div className="rounded-xl bg-green-50 border border-green-200 p-4 dark:bg-green-900/20 dark:border-green-700">
                                    <div className="flex items-center gap-2 mb-2">
                                        <CheckCircle2 className="h-5 w-5 text-green-600" />
                                        <p className="font-semibold text-green-800 dark:text-green-300">Import successful!</p>
                                    </div>
                                    <p className="text-sm text-green-700 dark:text-green-400">
                                        ✅ <strong>{importResult.imported}</strong> contacts import hue
                                        {importResult.skipped > 0 && ` (${importResult.skipped} skipped)`}
                                    </p>
                                    {importResult.tag && (
                                        <p className="text-sm text-green-700 dark:text-green-400 mt-1">
                                            🏷️ Tag assigned: <strong>{importResult.tag.name}</strong>
                                        </p>
                                    )}
                                    <div className="mt-4 flex gap-2">
                                        <Link
                                            href={route('client.meetings.create')}
                                            className="flex-1 text-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                                        >
                                            Meeting Schedule karo →
                                        </Link>
                                        <button
                                            onClick={() => setImportModal(null)}
                                            className="rounded-lg border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-50"
                                        >
                                            Close
                                        </button>
                                    </div>
                                </div>
                            ) : (
                                <div className="space-y-4">
                                    <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                        Import ke baad contacts ko konsa tag assign karein?
                                    </p>

                                    {/* Existing tag */}
                                    {tags.length > 0 && (
                                        <div>
                                            <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                                Existing Tag choose karo
                                            </label>
                                            <select
                                                value={importForm.tag_id}
                                                onChange={e => setImportForm(f => ({
                                                    ...f,
                                                    tag_id: e.target.value,
                                                    new_tag_name: e.target.value ? '' : f.new_tag_name
                                                }))}
                                                className="block w-full rounded-md border-neutral-300 text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                            >
                                                <option value="">— Select existing tag —</option>
                                                {tags.map(t => (
                                                    <option key={t.id} value={t.id}>{t.name}</option>
                                                ))}
                                            </select>
                                        </div>
                                    )}

                                    {/* Divider */}
                                    <div className="flex items-center gap-3">
                                        <div className="flex-1 border-t border-neutral-200 dark:border-neutral-600" />
                                        <span className="text-xs text-neutral-400">ya</span>
                                        <div className="flex-1 border-t border-neutral-200 dark:border-neutral-600" />
                                    </div>

                                    {/* New tag */}
                                    <div>
                                        <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                            Naya Tag banao
                                        </label>
                                        <input
                                            type="text"
                                            placeholder="e.g. Digital Marketing Batch A"
                                            value={importForm.new_tag_name}
                                            onChange={e => setImportForm(f => ({
                                                ...f,
                                                new_tag_name: e.target.value,
                                                tag_id: e.target.value ? '' : f.tag_id
                                            }))}
                                            className="block w-full rounded-md border-neutral-300 text-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                        />
                                    </div>

                                    <div className="flex gap-2 pt-2">
                                        <button
                                            onClick={handleImport}
                                            disabled={importing}
                                            className="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50"
                                        >
                                            {importing ? (
                                                <><Loader2 className="h-4 w-4 animate-spin" /> Importing...</>
                                            ) : (
                                                <><Download className="h-4 w-4" /> Import karo</>
                                            )}
                                        </button>
                                        <button
                                            onClick={() => setImportModal(null)}
                                            className="rounded-lg border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-600 hover:bg-neutral-50 dark:border-neutral-600 dark:text-neutral-400"
                                        >
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </ClientLayout>
    );
}
