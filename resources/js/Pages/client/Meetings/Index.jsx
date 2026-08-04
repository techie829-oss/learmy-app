import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import ClientLayout from '@/Layouts/ClientLayout';
import { Calendar, dateFnsLocalizer } from 'react-big-calendar';
import { format, parse, startOfWeek, getDay } from 'date-fns';
import enUS from 'date-fns/locale/en-US';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import { Plus, Calendar as CalendarIcon, List, Pencil, Trash2, ExternalLink, Clock, Users } from 'lucide-react';

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
    const start = new Date(meeting.start_time);
    const end   = new Date(meeting.end_time);
    const dateStr = format(start, 'dd MMM yyyy');
    const timeStr = format(start, 'hh:mm a') + ' – ' + format(end, 'hh:mm a');

    return (
        <tr className="border-b border-neutral-100 dark:border-neutral-700/50 hover:bg-neutral-50 dark:hover:bg-neutral-800/40 transition-colors">
            <td className="px-4 py-3">
                <p className="font-medium text-neutral-900 dark:text-white text-sm">{meeting.title}</p>
                {meeting.description && (
                    <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-1">{meeting.description}</p>
                )}
            </td>
            <td className="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-300 whitespace-nowrap">
                <div className="flex items-center gap-1.5">
                    <CalendarIcon className="h-3.5 w-3.5 text-neutral-400" />
                    {dateStr}
                </div>
                <div className="flex items-center gap-1.5 mt-0.5 text-xs text-neutral-400">
                    <Clock className="h-3 w-3" />
                    {timeStr}
                </div>
            </td>
            <td className="px-4 py-3">
                <div className="flex items-center gap-1.5 text-xs text-neutral-500 dark:text-neutral-400">
                    <Users className="h-3.5 w-3.5" />
                    {meeting.targets?.length ?? 0} target(s)
                </div>
            </td>
            <td className="px-4 py-3">
                <StatusBadge status={meeting.status} />
            </td>
            <td className="px-4 py-3">
                {meeting.meet_link ? (
                    <a href={meeting.meet_link} target="_blank" rel="noopener noreferrer"
                        className="inline-flex items-center gap-1 text-xs text-brand-600 dark:text-brand-400 hover:underline">
                        <ExternalLink className="h-3 w-3" /> Join
                    </a>
                ) : (
                    <span className="text-xs text-neutral-400">—</span>
                )}
            </td>
            <td className="px-4 py-3">
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
    const [view, setView] = useState('list'); // 'list' | 'calendar'
    const [confirmDelete, setConfirmDelete] = useState(null);

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

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {/* Header */}
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            Classes &amp; Calendar
                        </h1>
                        <p className="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            Schedule and view upcoming classes. Google Meet links auto-generated.
                        </p>
                    </div>
                    <div className="flex items-center gap-3">
                        {/* View toggle */}
                        <div className="flex rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden">
                            <button
                                onClick={() => setView('list')}
                                className={`flex items-center gap-1.5 px-3 py-1.5 text-sm transition-colors ${
                                    view === 'list'
                                        ? 'bg-brand-600 text-white'
                                        : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700'
                                }`}
                            >
                                <List className="h-4 w-4" /> List
                            </button>
                            <button
                                onClick={() => setView('calendar')}
                                className={`flex items-center gap-1.5 px-3 py-1.5 text-sm transition-colors ${
                                    view === 'calendar'
                                        ? 'bg-brand-600 text-white'
                                        : 'bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700'
                                }`}
                            >
                                <CalendarIcon className="h-4 w-4" /> Calendar
                            </button>
                        </div>

                        <Link
                            href={route('client.meetings.create')}
                            className="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-500 transition-colors"
                        >
                            <Plus className="h-4 w-4" /> Schedule Class
                        </Link>
                    </div>
                </div>

                {/* List View */}
                {view === 'list' && (
                    <div className="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700/50 dark:bg-neutral-800 overflow-hidden">
                        {meetings.length === 0 ? (
                            <div className="py-20 text-center text-neutral-400 dark:text-neutral-500">
                                <CalendarIcon className="h-10 w-10 mx-auto mb-3 opacity-40" />
                                <p className="text-sm">No classes scheduled yet.</p>
                                <Link href={route('client.meetings.create')} className="mt-3 inline-block text-sm text-brand-600 hover:underline">
                                    + Schedule your first class
                                </Link>
                            </div>
                        ) : (
                            <table className="w-full text-left">
                                <thead className="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/80">
                                    <tr>
                                        <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Class</th>
                                        <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Date &amp; Time</th>
                                        <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Targets</th>
                                        <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Status</th>
                                        <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Meet</th>
                                        <th className="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {meetings.map(m => (
                                        <MeetingListRow key={m.id} meeting={m} onDelete={handleDelete} />
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>
                )}

                {/* Calendar View */}
                {view === 'calendar' && (
                    <div className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700/50 dark:bg-neutral-800">
                        <div style={{ height: '70vh' }}>
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
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div className="bg-white dark:bg-neutral-800 rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
                        <h2 className="text-base font-semibold text-neutral-900 dark:text-white mb-2">Delete Class?</h2>
                        <p className="text-sm text-neutral-500 dark:text-neutral-400 mb-5">
                            "<strong>{confirmDelete.title}</strong>" permanently delete ho jayegi. Kya aap sure hain?
                        </p>
                        <div className="flex justify-end gap-3">
                            <button
                                onClick={() => setConfirmDelete(null)}
                                className="px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-600 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700"
                            >
                                Cancel
                            </button>
                            <button
                                onClick={confirmDoDelete}
                                className="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700"
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
