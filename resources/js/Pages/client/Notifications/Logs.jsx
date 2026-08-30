import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import ClientLayout from '@/Layouts/ClientLayout';
import {
    Activity, CheckCircle2, XCircle, Search, Filter, AlertCircle,
    Info, Phone, Calendar, Clock, RefreshCw, Layers
} from 'lucide-react';

export default function NotificationLogs({ logs, stats, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedStatus, setSelectedStatus] = useState(filters.status || '');
    const [selectedError, setSelectedError] = useState(null);

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('notification-logs.index'), {
            search,
            status: selectedStatus,
        }, { preserveState: true });
    };

    const handleStatusFilter = (status) => {
        setSelectedStatus(status);
        router.get(route('notification-logs.index'), {
            search,
            status,
        }, { preserveState: true });
    };

    const triggerLabelMap = {
        on_create: 'On Class Create',
        morning: 'Morning Reminder',
        before_15m: '15m Before Class',
        on_start: 'Class Started',
    };

    return (
        <ClientLayout title="Notification Logs">
            <Head title="Notification Logs" />

            <div className="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold text-neutral-900 dark:text-white flex items-center gap-2">
                            <Activity className="h-6 w-6 text-brand-600 dark:text-brand-400" />
                            Notification Logs
                        </h1>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
                            Real-time delivery status, trigger logs, and error diagnostics for WhatsApp notifications.
                        </p>
                    </div>
                </div>

                {/* Stats Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="bg-white dark:bg-neutral-800 p-5 rounded-xl border border-neutral-200 dark:border-neutral-700/60 shadow-sm flex items-center justify-between">
                        <div>
                            <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Total Triggers</p>
                            <p className="text-2xl font-bold text-neutral-900 dark:text-white mt-1">{stats.total ?? 0}</p>
                        </div>
                        <div className="h-10 w-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                            <Layers className="h-5 w-5" />
                        </div>
                    </div>

                    <div className="bg-white dark:bg-neutral-800 p-5 rounded-xl border border-neutral-200 dark:border-neutral-700/60 shadow-sm flex items-center justify-between">
                        <div>
                            <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Successfully Sent</p>
                            <p className="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{stats.sent ?? 0}</p>
                        </div>
                        <div className="h-10 w-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <CheckCircle2 className="h-5 w-5" />
                        </div>
                    </div>

                    <div className="bg-white dark:bg-neutral-800 p-5 rounded-xl border border-neutral-200 dark:border-neutral-700/60 shadow-sm flex items-center justify-between">
                        <div>
                            <p className="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Failed Delivery</p>
                            <p className="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{stats.failed ?? 0}</p>
                        </div>
                        <div className="h-10 w-10 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center">
                            <XCircle className="h-5 w-5" />
                        </div>
                    </div>
                </div>

                {/* Controls & Filters */}
                <div className="bg-white dark:bg-neutral-800 p-4 rounded-xl border border-neutral-200 dark:border-neutral-700/60 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div className="flex items-center gap-2">
                        {['', 'sent', 'failed'].map((st) => (
                            <button
                                key={st}
                                onClick={() => handleStatusFilter(st)}
                                className={`px-3 py-1.5 rounded-lg text-xs font-semibold capitalize transition-colors ${
                                    selectedStatus === st
                                        ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900'
                                        : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-700/50 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-neutral-700'
                                }`}
                            >
                                {st === '' ? 'All Logs' : st}
                            </button>
                        ))}
                    </div>

                    <form onSubmit={handleSearch} className="flex items-center gap-2">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400" />
                            <input
                                type="text"
                                placeholder="Search phone, trigger..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="pl-9 pr-4 py-1.5 text-xs bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-700 rounded-lg focus:ring-2 focus:ring-brand-500 text-neutral-900 dark:text-white"
                            />
                        </div>
                        <button
                            type="submit"
                            className="px-3 py-1.5 bg-neutral-900 text-white dark:bg-neutral-100 dark:text-neutral-900 rounded-lg text-xs font-medium hover:bg-neutral-800 transition-colors"
                        >
                            Filter
                        </button>
                    </form>
                </div>

                {/* Logs Table */}
                <div className="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700/60 shadow-sm overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left border-collapse">
                            <thead>
                                <tr className="bg-neutral-50 dark:bg-neutral-900/50 border-b border-neutral-200 dark:border-neutral-700/60 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    <th className="px-4 py-3">Timestamp</th>
                                    <th className="px-4 py-3">Recipient</th>
                                    <th className="px-4 py-3">Trigger / Class</th>
                                    <th className="px-4 py-3">Provider</th>
                                    <th className="px-4 py-3">Status</th>
                                    <th className="px-4 py-3 text-right">Details</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-100 dark:divide-neutral-700/50 text-xs text-neutral-700 dark:text-neutral-300">
                                {logs.data && logs.data.length > 0 ? (
                                    logs.data.map((log) => (
                                        <tr key={log.id} className="hover:bg-neutral-50 dark:hover:bg-neutral-700/30 transition-colors">
                                            <td className="px-4 py-3 whitespace-nowrap text-neutral-500 dark:text-neutral-400">
                                                {log.sent_at}
                                            </td>
                                            <td className="px-4 py-3 font-medium text-neutral-900 dark:text-white whitespace-nowrap">
                                                <div>{log.contact_name ?? 'Contact'}</div>
                                                <div className="text-[11px] text-neutral-400 font-mono">{log.phone}</div>
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="font-medium text-neutral-800 dark:text-neutral-200">
                                                    {triggerLabelMap[log.trigger] ?? log.trigger}
                                                </div>
                                                {log.meeting_title && (
                                                    <div className="text-[11px] text-neutral-400 line-clamp-1">{log.meeting_title}</div>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 whitespace-nowrap">
                                                <span className="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-neutral-100 text-neutral-700 dark:bg-neutral-700 dark:text-neutral-300 uppercase">
                                                    {log.provider === 'meta' ? 'Meta Cloud API' : 'Web WA (QR)'}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 whitespace-nowrap">
                                                {log.status === 'sent' || log.status === 'delivered' ? (
                                                    <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                        <CheckCircle2 className="h-3 w-3" /> SENT
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                                        <XCircle className="h-3 w-3" /> FAILED
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-right whitespace-nowrap">
                                                {log.error_message ? (
                                                    <button
                                                        onClick={() => setSelectedError(log)}
                                                        className="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-700 dark:text-red-400 underline"
                                                    >
                                                        <AlertCircle className="h-3.5 w-3.5" /> View Error
                                                    </button>
                                                ) : (
                                                    <span className="text-neutral-400 text-[11px]">No issues</span>
                                                )}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="px-4 py-12 text-center text-neutral-400">
                                            No notification logs found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Error Details Modal */}
                {selectedError && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                        <div className="bg-white dark:bg-neutral-800 rounded-xl max-w-lg w-full p-6 border border-neutral-200 dark:border-neutral-700 shadow-xl space-y-4">
                            <div className="flex items-center justify-between border-b border-neutral-100 dark:border-neutral-700 pb-3">
                                <h3 className="text-lg font-bold text-red-600 dark:text-red-400 flex items-center gap-2">
                                    <AlertCircle className="h-5 w-5" /> Delivery Error Details
                                </h3>
                                <button
                                    onClick={() => setSelectedError(null)}
                                    className="text-neutral-400 hover:text-neutral-600 text-sm font-bold"
                                >
                                    ✕
                                </button>
                            </div>

                            <div className="space-y-2 text-xs text-neutral-600 dark:text-neutral-300">
                                <div><strong className="text-neutral-900 dark:text-white">Recipient:</strong> {selectedError.phone} ({selectedError.contact_name ?? 'Unknown'})</div>
                                <div><strong className="text-neutral-900 dark:text-white">Trigger:</strong> {selectedError.trigger} ({selectedError.template_name ?? 'N/A'})</div>
                                <div><strong className="text-neutral-900 dark:text-white">Provider:</strong> {selectedError.provider}</div>
                                <div><strong className="text-neutral-900 dark:text-white">Timestamp:</strong> {selectedError.sent_at}</div>

                                <div className="mt-3">
                                    <label className="block font-bold text-neutral-900 dark:text-white mb-1">Raw Error Message / Meta Response:</label>
                                    <pre className="p-3 bg-neutral-900 text-red-400 rounded-lg overflow-x-auto text-[11px] font-mono leading-relaxed max-h-48 whitespace-pre-wrap">
                                        {selectedError.error_message}
                                    </pre>
                                </div>
                            </div>

                            <div className="pt-2 flex justify-end">
                                <button
                                    onClick={() => setSelectedError(null)}
                                    className="px-4 py-2 bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-white rounded-lg text-xs font-semibold hover:bg-neutral-200 transition-colors"
                                >
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </ClientLayout>
    );
}
