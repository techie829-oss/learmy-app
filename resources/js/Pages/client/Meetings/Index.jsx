import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';

import ClientLayout from '@/Layouts/ClientLayout';
import { Calendar, dateFnsLocalizer } from 'react-big-calendar';
import { format, parse, startOfWeek, getDay } from 'date-fns';
import enUS from 'date-fns/locale/en-US';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import {
    Plus, Calendar as CalendarIcon, List, Pencil, Trash2, ExternalLink,
    Clock, Users, Copy, Check, Video, MapPin
} from 'lucide-react';

const locales = { 'en-US': enUS };
const localizer = dateFnsLocalizer({ format, parse, startOfWeek, getDay, locales });

function StatusBadge({ status }) {
    const map = {
        scheduled: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        completed:  'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
        cancelled:  'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
    };
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${map[status] ?? map.scheduled}`}>
            {status ?? 'scheduled'}
        </span>
    );
}

function MeetingListRow({ meeting, onDelete }) {
    const [copied, setCopied] = useState(false);
    const start = new Date(meeting.start_time);
    const end   = new Date(meeting.end_time);
    const dateStr = format(start, 'dd MMM yyyy');
    const timeStr = format(start, 'hh:mm a') + ' – ' + format(end, 'hh:mm a');

    const handleCopy = (url) => {
        if (!url) return;
        navigator.clipboard.writeText(url);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    const isOffline = meeting.class_type === 'offline';

    return (
        <tr className="border-b border-neutral-100 dark:border-neutral-700/50 hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
            <td className="px-4 py-3.5 min-w-[180px]">
                <div className="flex items-center gap-2">
                    <p className="font-semibold text-neutral-900 dark:text-white text-sm">{meeting.title}</p>
                    {isOffline ? (
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">
                            <MapPin className="h-3 w-3" /> Offline
                        </span>
                    ) : (
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            <Video className="h-3 w-3" /> Online
                        </span>
                    )}
                </div>
                {meeting.description && (
                    <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-1">{meeting.description}</p>
                )}
            </td>
            <td className="px-4 py-3.5 text-sm text-neutral-600 dark:text-neutral-300 whitespace-nowrap">
                <div className="flex items-center gap-1.5 font-medium text-xs sm:text-sm">
                    <CalendarIcon className="h-3.5 w-3.5 text-neutral-400 shrink-0" />
                    {dateStr}
                </div>
                <div className="flex items-center gap-1.5 mt-0.5 text-xs text-neutral-400">
                    <Clock className="h-3 w-3 shrink-0" />
                    {timeStr}
                </div>
            </td>
            <td className="px-4 py-3.5 whitespace-nowrap">
                <div className="flex items-center gap-1.5 text-xs text-neutral-500 dark:text-neutral-400">
                    <Users className="h-3.5 w-3.5 shrink-0" />
                    {meeting.targets?.length ?? 0} target(s)
                </div>
            </td>
            <td className="px-4 py-3.5 whitespace-nowrap">
                <StatusBadge status={meeting.status} />
            </td>
            <td className="px-4 py-3.5 whitespace-nowrap">
                {isOffline ? (
                    <div className="flex items-center gap-1.5 text-xs font-medium text-purple-700 dark:text-purple-300 max-w-[200px] truncate" title={meeting.location ?? 'Offline Venue'}>
                        <MapPin className="h-3.5 w-3.5 shrink-0" />
                        <span className="truncate">{meeting.location ?? 'Offline Venue'}</span>
                    </div>
                ) : meeting.meet_link ? (
                    <div className="flex items-center gap-2">
                        <a
                            href={meeting.meet_link}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 hover:bg-emerald-100 transition-colors"
                        >
                            <Video className="h-3 w-3" /> Join
                        </a>
                        <button
                            onClick={() => handleCopy(meeting.meet_link)}
                            title="Copy Meeting Link"
                            className="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors"
                        >
                            {copied ? (
                                <>
                                    <Check className="h-3 w-3 text-emerald-600" />
                                    <span className="text-emerald-600">Copied!</span>
                                </>
                            ) : (
                                <>
                                    <Copy className="h-3 w-3" />
                                    <span>Copy</span>
                                </>
                            )}
                        </button>
                    </div>
                ) : (
                    <span className="text-xs text-neutral-400">—</span>
                )}
            </td>
            <td className="px-4 py-3.5 whitespace-nowrap">
                <div className="flex items-center gap-2">
                    <Link
                        href={route('client.meetings.edit', meeting.id)}
                        className="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 transition-colors"
                    >
                        <Pencil className="h-3 w-3" /> Edit
                    </Link>
                    <button
                        onClick={() => onDelete(meeting)}
                        className="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors"
                    >
                        <Trash2 className="h-3 w-3" /> Delete
                    </button>
                </div>
            </td>
        </tr>
    );
}

export default function Index({ meetings }) {
    const [view, setView] = useState('list');
    const [confirmDelete, setConfirmDelete] = useState(null);
    const { props } = usePage();
    const flash = props.flash ?? {};

    const events = meetings.map(m => ({
        id: m.id,
        title: m.title,
        start: new Date(m.start_time),
        end:   new Date(m.end_time),
        resource: m,
    }));

    function handleDelete(meeting) {
        setConfirmDelete(meeting);
    }

    function confirmDoDelete() {
        router.delete(route('client.meetings.destroy', confirmDelete.id), {
            onFinish: () => setConfirmDelete(null),
        });
    }

    return (
        <ClientLayout>
            <Head title="Classes & Calendar" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-xl sm:text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            Classes &amp; Calendar
                        </h1>
                        <p className="mt-1 text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                            Schedule classes and auto-generate Google Meet links with 1-click copy &amp; WhatsApp reminders.
                        </p>
                    </div>
                    <div className="flex items-center justify-between sm:justify-end gap-2 sm:gap-3">
                        {/* View toggle */}
                        <div className="flex rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden bg-white dark:bg-neutral-800">
                            <button
                                onClick={() => setView('list')}
                                className={`flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-medium transition-colors ${
                                    view === 'list'
                                        ? 'bg-brand-600 text-white'
                                        : 'text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700'
                                }`}
                            >
                                <List className="h-3.5 w-3.5 sm:h-4 sm:w-4" /> List
                            </button>
                            <button
                                onClick={() => setView('calendar')}
                                className={`flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-medium transition-colors ${
                                    view === 'calendar'
                                        ? 'bg-brand-600 text-white'
                                        : 'text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700'
                                }`}
                            >
                                <CalendarIcon className="h-3.5 w-3.5 sm:h-4 sm:w-4" /> Calendar
                            </button>
                        </div>

                        <Link
                            href={route('client.meetings.create')}
                            className="inline-flex items-center gap-1.5 sm:gap-2 rounded-lg bg-brand-600 px-3 py-1.5 sm:px-4 sm:py-2 text-xs sm:text-sm font-medium text-white shadow-sm hover:bg-brand-500 transition-colors shrink-0"
                        >
                            <Plus className="h-4 w-4" /> Schedule Class
                        </Link>
                    </div>
                </div>

                {/* Flash message */}
                {flash.success && (
                    <div className="mb-4 flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-xs sm:text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-300">
                        <span>✓</span> {flash.success}
                    </div>
                )}
                {flash.error && (
                    <div className="mb-4 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs sm:text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                        <span>✕</span> {flash.error}
                    </div>
                )}

                {/* List View with Responsive Table Wrapper */}
                {view === 'list' && (
                    <div className="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700/50 dark:bg-neutral-800 overflow-hidden">
                        {meetings.length === 0 ? (
                            <div className="py-16 text-center text-neutral-400 dark:text-neutral-500">
                                <CalendarIcon className="h-10 w-10 mx-auto mb-3 opacity-40" />
                                <p className="text-sm font-medium">No classes scheduled yet.</p>
                                <Link href={route('client.meetings.create')} className="mt-3 inline-block text-xs sm:text-sm text-brand-600 hover:underline">
                                    + Schedule your first class
                                </Link>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-left border-collapse">
                                    <thead className="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/80">
                                        <tr>
                                            <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Class</th>
                                            <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Date &amp; Time</th>
                                            <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Targets</th>
                                            <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Status</th>
                                            <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Meet Link</th>
                                            <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {meetings.map(m => (
                                            <MeetingListRow key={m.id} meeting={m} onDelete={handleDelete} />
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                )}

                {/* Calendar View */}
                {view === 'calendar' && (
                    <div className="rounded-xl border border-neutral-200 bg-white p-3 sm:p-6 shadow-sm dark:border-neutral-700/50 dark:bg-neutral-800">
                        <div style={{ height: '65vh', minHeight: '400px' }}>
                            <Calendar
                                localizer={localizer}
                                events={events}
                                startAccessor="start"
                                endAccessor="end"
                                style={{ height: '100%' }}
                                onSelectEvent={event => {
                                    router.visit(route('client.meetings.edit', event.id));
                                }}
                                tooltipAccessor={e => e.title}
                            />
                        </div>
                    </div>
                )}
            </div>

            {/* Delete Confirm Modal */}
            {confirmDelete && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div className="bg-white dark:bg-neutral-800 rounded-xl shadow-xl p-5 max-w-sm w-full">
                        <h2 className="text-base font-semibold text-neutral-900 dark:text-white mb-2">Delete Class?</h2>
                        <p className="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mb-5">
                            "<strong>{confirmDelete.title}</strong>" permanently delete ho jayegi.
                        </p>
                        <div className="flex justify-end gap-3">
                            <button
                                onClick={() => setConfirmDelete(null)}
                                className="px-3.5 py-1.5 text-xs sm:text-sm rounded-lg border border-neutral-200 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700"
                            >
                                Cancel
                            </button>
                            <button
                                onClick={confirmDoDelete}
                                className="px-3.5 py-1.5 text-xs sm:text-sm rounded-lg bg-red-600 text-white hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </ClientLayout>
    );
}
