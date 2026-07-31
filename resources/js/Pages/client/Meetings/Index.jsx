import React from 'react';
import { Head, Link } from '@inertiajs/react';
import ClientLayout from '@/Layouts/ClientLayout';
import { Calendar, dateFnsLocalizer } from 'react-big-calendar';
import { format, parse, startOfWeek, getDay } from 'date-fns';
import enUS from 'date-fns/locale/en-US';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import { Plus } from 'lucide-react';
import { useTranslation } from 'react-i18next';

const locales = {
    'en-US': enUS,
};

const localizer = dateFnsLocalizer({
    format,
    parse,
    startOfWeek,
    getDay,
    locales,
});

export default function Index({ meetings }) {
    const { t } = useTranslation();

    // Transform meetings to react-big-calendar event format
    const events = meetings.map(meeting => ({
        id: meeting.id,
        title: meeting.title,
        start: new Date(meeting.start_time),
        end: new Date(meeting.end_time),
        desc: meeting.description,
        meet_link: meeting.meet_link,
    }));

    return (
        <ClientLayout>
            <Head title="Classes & Calendar" />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="mb-6 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            Classes & Calendar
                        </h1>
                        <p className="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                            Schedule and view upcoming classes, automatically syncing with Google Meet and WhatsApp.
                        </p>
                    </div>
                    <Link
                        href={route('client.meetings.create')}
                        className="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900"
                    >
                        <Plus className="h-4 w-4" />
                        Schedule Class
                    </Link>
                </div>

                <div className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700/50 dark:bg-neutral-800">
                    <div style={{ height: '70vh' }}>
                        <Calendar
                            localizer={localizer}
                            events={events}
                            startAccessor="start"
                            endAccessor="end"
                            style={{ height: '100%' }}
                            onSelectEvent={event => {
                                if (event.meet_link) {
                                    window.open(event.meet_link, '_blank');
                                } else {
                                    alert('Meeting details: ' + event.title);
                                }
                            }}
                            tooltipAccessor={e => e.title + (e.desc ? '\n' + e.desc : '')}
                        />
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
