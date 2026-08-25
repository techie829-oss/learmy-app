import { Head, Link, router, usePage } from '@inertiajs/react';
import ClientLayout from '@/Layouts/ClientLayout';
import EmptyState from '@/Components/EmptyState';
import TemplatePreview from '@/Components/TemplatePreview';
import { Plus, RefreshCw, CheckCircle, XCircle, Clock, PauseCircle, FileText, Search, Phone, Pencil, Trash2, Info, BookOpen } from 'lucide-react';
import { useState, useCallback, useRef } from 'react';
import { useTranslation } from 'react-i18next';

const STATUS_CONFIG = {
    APPROVED: { color: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300', icon: <CheckCircle className="h-3 w-3" />, label: 'Approved' },
    REJECTED: { color: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',         icon: <XCircle className="h-3 w-3" />, label: 'Rejected' },
    PENDING:  { color: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300', icon: <Clock className="h-3 w-3" />, label: 'Pending' },
    PAUSED:   { color: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-300', icon: <PauseCircle className="h-3 w-3" />, label: 'Paused' },
};

export default function WhatsappTemplatesIndex({ templates = [], phoneNumbers = [], filters = {} }) {
    const { t } = useTranslation();
    const { props } = usePage();
    const flash = props?.flash ?? {};
    const pageErrors = props?.errors ?? {};

    const [showGuide, setShowGuide] = useState(true);

    const safeFilters = filters || {};
    const safeTemplates = Array.isArray(templates) ? templates : [];
    const safePhoneNumbers = Array.isArray(phoneNumbers) ? phoneNumbers : [];

    const [search, setSearch] = useState(safeFilters.search ?? '');
    const debounceTimer = useRef(null);

    const applyFilters = useCallback((patch) => {
        router.get(
            route('client.whatsapp.templates.index'),
            { ...safeFilters, ...patch },
            { preserveState: true, replace: true },
        );
    }, [safeFilters]);

    const handleSearch = (e) => {
        const value = e.target.value;
        setSearch(value);
        clearTimeout(debounceTimer.current);
        debounceTimer.current = setTimeout(() => {
            applyFilters({ search: value || undefined });
        }, 400);
    };

    const handleSync = () => router.post(route('client.whatsapp.templates.sync'), {}, { preserveScroll: true });
    const handleStatus = (status) => applyFilters({ status: status || undefined });
    const handlePhone = (e) => applyFilters({ phone_number_id: e.target.value || undefined });

    const handleDelete = (tpl) => {
        if (!window.confirm(`Are you sure you want to delete template "${tpl.name}"?`)) return;
        router.delete(route('client.whatsapp.templates.destroy', tpl.id), { preserveScroll: true });
    };

    return (
        <ClientLayout title="WhatsApp Templates">
            <Head title="WhatsApp Templates" />
            <div className="space-y-5">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                            📱 WhatsApp Message Templates
                        </h2>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400 mt-0.5">
                            Create &amp; manage rich WhatsApp templates for class reminders, broadcasts &amp; automations.
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <button
                            onClick={() => setShowGuide(!showGuide)}
                            className="flex items-center gap-1.5 rounded-lg border border-blue-300 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 text-sm font-medium text-blue-700 dark:text-blue-300 hover:bg-blue-100 transition"
                        >
                            <BookOpen className="h-4 w-4" /> {showGuide ? 'Hide Guide' : '📖 Variables Guide'}
                        </button>
                        <button
                            onClick={handleSync}
                            className="flex items-center gap-1.5 rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
                        >
                            <RefreshCw className="h-4 w-4" /> Sync / Refresh
                        </button>
                        <Link
                            href={route('client.whatsapp.templates.create')}
                            className="flex items-center gap-1.5 rounded-lg bg-green-600 px-3.5 py-2 text-sm font-medium text-white hover:bg-green-700 transition"
                        >
                            <Plus className="h-4 w-4" /> + Create Template
                        </Link>
                    </div>
                </div>

                {/* Notes / Guide Card for Template Variables */}
                {showGuide && (
                    <div className="rounded-xl border border-blue-200 bg-blue-50/80 p-4 dark:border-blue-800/40 dark:bg-blue-900/10 transition-all">
                        <div className="flex items-start gap-3">
                            <div className="rounded-lg bg-blue-100 p-2 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 flex-shrink-0 mt-0.5">
                                <Info className="h-5 w-5" />
                            </div>
                            <div className="space-y-2 text-xs text-blue-900 dark:text-blue-200">
                                <div className="flex items-center justify-between">
                                    <p className="text-sm font-bold text-blue-950 dark:text-blue-100">
                                        💡 WhatsApp Template Variables &amp; How They Work
                                    </p>
                                    <span className="text-[11px] text-blue-600 dark:text-blue-400 font-medium">Auto-replaced on send</span>
                                </div>
                                <p className="text-blue-800 dark:text-blue-300">
                                    Template likhte waqt aap text mein neeche diye gaye variables inject kar sakte hain. Student ko message jaate waqt ye values dynamic replace ho jayengi:
                                </p>
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 pt-1 font-mono">
                                    <div className="rounded bg-white dark:bg-neutral-800 p-2.5 border border-blue-200 dark:border-neutral-700 shadow-xs">
                                        <p className="font-bold text-blue-700 dark:text-blue-400 text-xs"><code>{`{{1}}`}</code> or <code>{`{{first_name}}`}</code></p>
                                        <p className="font-sans text-[11px] text-neutral-600 dark:text-neutral-400 mt-1">Student First Name (e.g. Rahul)</p>
                                    </div>
                                    <div className="rounded bg-white dark:bg-neutral-800 p-2.5 border border-blue-200 dark:border-neutral-700 shadow-xs">
                                        <p className="font-bold text-blue-700 dark:text-blue-400 text-xs"><code>{`{{2}}`}</code> or <code>{`{{class_title}}`}</code></p>
                                        <p className="font-sans text-[11px] text-neutral-600 dark:text-neutral-400 mt-1">Class Name (e.g. Physics Ch-4)</p>
                                    </div>
                                    <div className="rounded bg-white dark:bg-neutral-800 p-2.5 border border-blue-200 dark:border-neutral-700 shadow-xs">
                                        <p className="font-bold text-blue-700 dark:text-blue-400 text-xs"><code>{`{{3}}`}</code> or <code>{`{{start_time}}`}</code></p>
                                        <p className="font-sans text-[11px] text-neutral-600 dark:text-neutral-400 mt-1">Class Date &amp; Time (e.g. 26 Aug, 10:00 AM)</p>
                                    </div>
                                    <div className="rounded bg-white dark:bg-neutral-800 p-2.5 border border-blue-200 dark:border-neutral-700 shadow-xs">
                                        <p className="font-bold text-blue-700 dark:text-blue-400 text-xs"><code>{`{{4}}`}</code> or <code>{`{{meet_link}}`}</code></p>
                                        <p className="font-sans text-[11px] text-neutral-600 dark:text-neutral-400 mt-1">Google Meet URL (e.g. meet.google.com/...)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {flash.success && <div className="rounded-lg bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-4 py-3 text-sm font-medium border border-green-200">{flash.success}</div>}
                {flash.error   && <div className="rounded-lg bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-200 px-4 py-3 text-sm font-medium border border-red-200">{flash.error}</div>}
                {pageErrors.sync && <div className="rounded-lg bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-200 px-4 py-3 text-sm font-medium border border-red-200">{pageErrors.sync}</div>}

                {/* Search + Phone filter bar */}
                <div className="flex flex-wrap gap-3">
                    <div className="relative flex-1 min-w-48">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400 pointer-events-none" />
                        <input
                            type="text"
                            value={search}
                            onChange={handleSearch}
                            placeholder="Search templates by name..."
                            className="w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 pl-9 pr-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </div>

                    {safePhoneNumbers.length > 0 && (
                        <div className="relative">
                            <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400 pointer-events-none" />
                            <select
                                value={safeFilters.phone_number_id ?? ''}
                                onChange={handlePhone}
                                className="rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 pl-9 pr-8 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 appearance-none min-w-48"
                            >
                                <option value="">All Phone Numbers</option>
                                {safePhoneNumbers.map(p => (
                                    <option key={p.phone_number_id} value={p.phone_number_id}>
                                        {p.display_phone}{p.verified_name ? ` · ${p.verified_name}` : ''}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                </div>

                {/* Status filter tabs */}
                <div className="flex gap-2 flex-wrap">
                    {[null, 'APPROVED', 'PENDING', 'REJECTED', 'PAUSED'].map(s => (
                        <button
                            key={s ?? 'all'}
                            onClick={() => handleStatus(s)}
                            className={`rounded-full px-3 py-1.5 text-xs font-medium transition ${(safeFilters.status ?? null) === s ? 'bg-green-600 text-white' : 'bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700'}`}
                        >
                            {s ? (STATUS_CONFIG[s]?.label ?? s) : 'All Status'}
                        </button>
                    ))}
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {safeTemplates.map(tpl => {
                        const sc = STATUS_CONFIG[tpl.status] ?? STATUS_CONFIG.APPROVED;
                        return (
                            <div key={tpl.id} className="flex flex-col rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-5 space-y-3 shadow-sm hover:shadow-md transition">
                                <div className="flex items-start justify-between gap-2">
                                    <span className="font-mono font-bold text-sm text-neutral-900 dark:text-neutral-100 break-all">{tpl.name}</span>
                                    <span className={`flex shrink-0 items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ${sc.color}`}>
                                        {sc.icon} {sc.label}
                                    </span>
                                </div>
                                <div className="flex gap-2 text-xs font-medium text-neutral-500 dark:text-neutral-400">
                                    <span className="rounded bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5">{tpl.category}</span>
                                    <span className="rounded bg-neutral-100 dark:bg-neutral-800 px-2 py-0.5">{tpl.language}</span>
                                </div>

                                <TemplatePreview components={tpl.components ?? []} />

                                {tpl.rejection_reason && (
                                    <p className="text-xs text-red-500 dark:text-red-400">{tpl.rejection_reason}</p>
                                )}

                                <div className="flex gap-2 pt-2 mt-auto border-t border-neutral-100 dark:border-neutral-800">
                                    <Link
                                        href={route('client.whatsapp.templates.edit', tpl.id)}
                                        className="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-neutral-300 dark:border-neutral-600 px-3 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
                                    >
                                        <Pencil className="h-3.5 w-3.5" /> Edit
                                    </Link>
                                    <button
                                        onClick={() => handleDelete(tpl)}
                                        className="flex items-center justify-center gap-1.5 rounded-lg border border-red-200 dark:border-red-900/50 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                                    >
                                        <Trash2 className="h-3.5 w-3.5" /> Delete
                                    </button>
                                </div>
                            </div>
                        );
                    })}
                    {safeTemplates.length === 0 && (
                        <div className="col-span-full">
                            <EmptyState
                                icon={<FileText className="h-8 w-8" />}
                                title="No WhatsApp templates yet"
                                description="Create custom rich WhatsApp templates with text, header images/videos, variables, and CTA buttons."
                                action={{ label: "+ Create Template", href: route('client.whatsapp.templates.create') }}
                                secondaryAction={{ label: "Refresh / Sync", onClick: handleSync }}
                            />
                        </div>
                    )}
                </div>
            </div>
        </ClientLayout>
    );
}
