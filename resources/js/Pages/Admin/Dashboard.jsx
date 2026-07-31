import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useTranslation } from 'react-i18next';
import {
    CreditCard,
    Users,
    Banknote,
    Building2,
    AlertTriangle,
    MessageSquare,
    Contact as ContactIcon,
    UserPlus,
    Inbox,
    TrendingUp,
    Receipt,
} from 'lucide-react';
import { LineChart, BarChart, DonutChart } from '@/Components/Charts';
import { RangeFilter, StatTile, WidgetCard, EmptyState } from '@/Components/Dashboard';

const usd = (d) => {
    const n = Number(d || 0);
    if (Math.abs(n) >= 1e6) return `$${(n / 1e6).toFixed(1)}M`;
    if (Math.abs(n) >= 1e3) return `$${(n / 1e3).toFixed(1)}K`;
    return `$${n.toFixed(2)}`;
};
const fromCents = (c) => usd((c || 0) / 100);
const spark = (arr, key) => (arr || []).map((d) => ({ v: Number(d[key] || 0) }));
const sumRow = (d) => Object.entries(d).reduce((s, [k, v]) => (k === 'date' ? s : s + Number(v || 0)), 0);

function relativeDate(iso) {
    if (!iso) return '—';
    try {
        return new Date(iso).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
    } catch {
        return iso;
    }
}

const STATUS_TONE = {
    active: 'text-emerald-600 dark:text-emerald-400',
    succeeded: 'text-emerald-600 dark:text-emerald-400',
    trialing: 'text-blue-600 dark:text-blue-400',
    pending: 'text-amber-600 dark:text-amber-400',
    failed: 'text-red-600 dark:text-red-400',
    inactive: 'text-neutral-400',
};

export default function AdminDashboard({ range = 30, stats = {}, charts = {}, tables = {}, warnings = [] }) {
    const { t } = useTranslation();
    const s = stats;

    const channelKeys = [
        ...new Set((charts.messages_by_day ?? []).flatMap((d) => Object.keys(d).filter((k) => k !== 'date'))),
    ];
    const messageTotalsSpark = (charts.messages_by_day ?? []).map((d) => ({ v: sumRow(d) }));

    const tiles = [
        {
            label: t('admin.users_count') || 'Users',
            value: s.users_count ?? 0,
            icon: Users,
            hint: `+${s.new_users ?? 0} ${t('admin.this_period') || 'this period'}`,
        },
        {
            label: t('admin.messages_period') || 'Messages',
            value: s.messages_period ?? 0,
            icon: MessageSquare,
            delta: s.messages_delta,
            sparkline: messageTotalsSpark,
        },
        {
            label: t('admin.contacts_total') || 'Contacts',
            value: s.contacts_total ?? 0,
            icon: ContactIcon,
        },
        {
            label: t('admin.conversations_total') || 'Conversations',
            value: s.conversations_total ?? 0,
            icon: Inbox,
        },
    ];

    const quickLinks = [
        { href: route('admin.ai.index'), label: t('admin.nav.ai') || 'AI' },
        { href: route('admin.queue.index'), label: t('admin.nav.queue') || 'Queue' },
        { href: route('admin.settings.index'), label: t('admin.nav.settings') || 'Settings' },
    ];

    return (
        <AdminLayout title={t('admin.dashboard')}>
            <Head title={`${t('admin.dashboard')} · ${t('head.admin')}`} />
            <div className="space-y-6">
                {warnings.length > 0 && (
                    <div className="space-y-2">
                        {warnings.map((w, i) => (
                            <div
                                key={i}
                                className="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200"
                            >
                                <AlertTriangle className="mt-0.5 h-4 w-4 flex-shrink-0" />
                                <span>{w}</span>
                            </div>
                        ))}
                    </div>
                )}

                {/* Header + range filter */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex flex-col gap-1">
                        <h2 className="text-xl font-semibold text-neutral-900 dark:text-neutral-100">{t('admin.dashboard')}</h2>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400">{t('admin.dashboard_overview')}</p>
                    </div>
                    <RangeFilter value={range} routeName="admin.dashboard" />
                </div>

                {/* KPI tiles */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    {tiles.map((tile) => (
                        <StatTile key={tile.label} {...tile} />
                    ))}
                </div>

                {/* Messages trend + mix */}
                <div className="grid gap-4 lg:grid-cols-2">
                    <WidgetCard title={t('admin.platform_messages') || 'Messages by channel'} subtitle={t('admin.last_n_days', { n: range }) || `Last ${range} days`}>
                        {channelKeys.length > 0 ? (
                            <LineChart data={charts.messages_by_day ?? []} xKey="date" yKeys={channelKeys} height={220} />
                        ) : (
                            <EmptyState>{t('admin.no_message_data') || 'No messages in this period'}</EmptyState>
                        )}
                    </WidgetCard>
                    <WidgetCard title={t('admin.channel_mix') || 'Channel mix'}>
                        {(charts.channel_mix ?? []).length > 0 ? (
                            <DonutChart data={charts.channel_mix} nameKey="name" valueKey="value" height={240} />
                        ) : (
                            <EmptyState>{t('admin.no_message_data') || 'No messages in this period'}</EmptyState>
                        )}
                    </WidgetCard>
                </div>

                {/* Top AI workspaces */}
                <WidgetCard title={t('admin.top_workspaces_ai_cost') || 'Top workspaces by AI cost'}>
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-neutral-200 dark:border-neutral-700">
                                    <th className="pb-2 text-left text-xs text-neutral-500">{t('admin.workspace')}</th>
                                    <th className="pb-2 text-right text-xs text-neutral-500">{t('admin.ai_cost')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-neutral-100 dark:divide-neutral-700/50">
                                {(charts.top_ai_workspaces ?? []).map((row) => (
                                    <tr key={row.workspace_id}>
                                        <td className="py-2 text-neutral-800 dark:text-neutral-200">{row.name}</td>
                                        <td className="py-2 text-right text-neutral-600 dark:text-neutral-400">{fromCents(row.total_cost_cents)}</td>
                                    </tr>
                                ))}
                                {(charts.top_ai_workspaces ?? []).length === 0 && (
                                    <tr><td colSpan={2} className="py-4 text-center text-neutral-400">{t('admin.no_ai_usage')}</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </WidgetCard>

                {/* Quick links */}
                <WidgetCard title={t('admin.quick_links') || 'Quick links'}>
                    <div className="flex flex-wrap gap-2">
                        {quickLinks.map((l) => (
                            <Link
                                key={l.href}
                                href={l.href}
                                className="rounded-soft bg-neutral-100 px-3 py-1.5 text-sm text-neutral-700 transition hover:bg-neutral-200 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                            >
                                {l.label}
                            </Link>
                        ))}
                    </div>
                </WidgetCard>
            </div>
        </AdminLayout>
    );
}
