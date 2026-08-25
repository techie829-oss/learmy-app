import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import ClientLayout from '@/Layouts/ClientLayout';
import { ArrowLeft, Video, CheckCircle2, MessageSquare, ExternalLink, Clock, Sun, Zap, PlayCircle } from 'lucide-react';

export default function Create({ tags = [], segments = [], waGroups = [], workspace_id, meeting, waTemplates = [], whatsappConnected = false }) {
    const isEdit = !!meeting;

    const initialTargets = isEdit
        ? (meeting.targets ?? []).map(t => ({ type: t.target_type, id: t.target_id }))
        : [];

    const defaultReminders = {
        on_create:  { enabled: true,  template: '' },
        morning:    { enabled: true,  template: '' },
        before_15m: { enabled: true,  template: '' },
        on_start:   { enabled: true,  template: '' },
    };

    const initialReminders = isEdit && meeting.reminder_settings
        ? { ...defaultReminders, ...meeting.reminder_settings }
        : defaultReminders;

    const getLocalDatetime = (offsetHours = 1) => {
        const d = new Date();
        d.setHours(d.getHours() + offsetHours, 0, 0, 0);
        const pad = (n) => String(n).padStart(2, '0');
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    };

    const { data, setData, post, put, processing, errors } = useForm({
        workspace_id:                workspace_id,
        title:                       isEdit ? meeting.title : '',
        description:                 isEdit ? (meeting.description ?? '') : '',
        start_time:                  isEdit ? meeting.start_time?.slice(0, 16) : getLocalDatetime(1),
        end_time:                    isEdit ? meeting.end_time?.slice(0, 16) : getLocalDatetime(2),
        timezone:                    isEdit ? meeting.timezone : Intl.DateTimeFormat().resolvedOptions().timeZone,
        custom_meet_link:            isEdit ? (meeting.meet_link ?? '') : '',
        targets:                     initialTargets,
        whatsapp_template:           isEdit ? (meeting.whatsapp_template ?? '') : '',
        send_whatsapp_notification:  isEdit ? (meeting.send_whatsapp_notification ?? true) : true,
        reminder_settings:           initialReminders,
    });

    const [selectedTargets, setSelectedTargets] = useState(initialTargets);

    const handleTargetChange = (e) => {
        const value = e.target.value;
        if (!value) return;
        const parts = value.split(':');
        const type  = parts[0];

        if (type === 'wa_group') {
            const groupId   = parts[1];
            const groupName = parts.slice(2).join(':');
            if (selectedTargets.some(t => t.type === 'wa_group' && t.id === groupId)) {
                e.target.value = '';
                return;
            }
            const newTargets = [...selectedTargets, { type: 'wa_group', id: groupId, name: groupName }];
            setSelectedTargets(newTargets);
            setData('targets', newTargets);
        } else {
            const parsedId = parseInt(parts[1]);
            if (selectedTargets.some(t => t.type === type && t.id === parsedId)) {
                e.target.value = '';
                return;
            }
            const newTargets = [...selectedTargets, { type, id: parsedId }];
            setSelectedTargets(newTargets);
            setData('targets', newTargets);
        }
        e.target.value = '';
    };

    const removeTarget = (indexToRemove) => {
        const newTargets = selectedTargets.filter((_, i) => i !== indexToRemove);
        setSelectedTargets(newTargets);
        setData('targets', newTargets);
    };

    const getTargetName = (target) => {
        if (target.type === 'wa_group') {
            return `💬 WA Group: ${target.name || target.id}`;
        }
        if (target.type.includes('ContactTag')) {
            const tag = tags.find(t => t.id === target.id);
            return `Batch: ${tag ? tag.name : 'Unknown'}`;
        }
        if (target.type.includes('Segment')) {
            const seg = segments.find(s => s.id === target.id);
            return `Segment: ${seg ? seg.name : 'Unknown'}`;
        }
        return 'Unknown Target';
    };

    const updateReminderSetting = (trigger, key, value) => {
        const updated = {
            ...data.reminder_settings,
            [trigger]: {
                ...data.reminder_settings[trigger],
                [key]: value,
            },
        };
        setData('reminder_settings', updated);
    };

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) {
            put(route('client.meetings.update', meeting.id), {
                onSuccess: () => router.visit(route('client.meetings.index')),
            });
        } else {
            post(route('client.meetings.store'), {
                onSuccess: () => router.visit(route('client.meetings.index')),
            });
        }
    };

    const triggerConfigs = [
        {
            key: 'on_create',
            title: '🚀 On Schedule (Instant Notification)',
            subtitle: 'Class create hotey hi immediately student ko confirmation message jaayega.',
            icon: Zap,
            iconColor: 'text-amber-500 bg-amber-50 dark:bg-amber-900/20',
        },
        {
            key: 'morning',
            title: '🌅 Morning of Class (08:00 AM)',
            subtitle: 'Class wale din subah 8:00 baje automated reminder jaayega.',
            icon: Sun,
            iconColor: 'text-amber-600 bg-amber-50 dark:bg-amber-900/20',
        },
        {
            key: 'before_15m',
            title: '⏰ 15 Minutes Before Class',
            subtitle: 'Class shuru hone se 15 min pehle countdown alert jaayega.',
            icon: Clock,
            iconColor: 'text-blue-500 bg-blue-50 dark:bg-blue-900/20',
        },
        {
            key: 'on_start',
            title: '🔴 When Class Starts (LIVE Now)',
            subtitle: 'Class start hotey hi student ko direct Join link ke saath LIVE notification jaayega.',
            icon: PlayCircle,
            iconColor: 'text-red-500 bg-red-50 dark:bg-red-900/20',
        },
    ];

    return (
        <ClientLayout>
            <Head title={isEdit ? 'Edit Class' : 'Schedule Class'} />

            <div className="max-w-4xl space-y-6">
                <div className="flex items-center gap-4">
                    <Link
                        href={route('client.meetings.index')}
                        className="rounded-full p-2 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-neutral-800 dark:hover:text-neutral-300"
                    >
                        <ArrowLeft className="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            {isEdit ? 'Edit Class' : 'Schedule a New Class'}
                        </h1>
                        <p className="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                            Set up class schedule &amp; generate Google Meet link automatically.
                        </p>
                    </div>
                </div>

                <div className="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700/50 dark:bg-neutral-800">
                    <form onSubmit={submit} className="space-y-6 p-6">
                        {/* Class Title */}
                        <div>
                            <label htmlFor="title" className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Class Title <span className="text-red-500">*</span>
                            </label>
                            <input
                                id="title"
                                type="text"
                                placeholder="e.g. Mathematics - Chapter 3 Algebra"
                                value={data.title}
                                onChange={e => setData('title', e.target.value)}
                                className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                required
                            />
                            {errors.title && <p className="mt-1.5 text-xs font-medium text-red-600">{errors.title}</p>}
                        </div>

                        {/* Date and Time */}
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label htmlFor="start_time" className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                    Start Time <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="start_time"
                                    type="datetime-local"
                                    value={data.start_time}
                                    onChange={e => setData('start_time', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                    required
                                />
                                {errors.start_time && <p className="mt-1.5 text-xs font-medium text-red-600">{errors.start_time}</p>}
                            </div>

                            <div>
                                <label htmlFor="end_time" className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                    End Time <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="end_time"
                                    type="datetime-local"
                                    value={data.end_time}
                                    onChange={e => setData('end_time', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                    required
                                />
                                {errors.end_time && <p className="mt-1.5 text-xs font-medium text-red-600">{errors.end_time}</p>}
                            </div>
                        </div>

                        {/* Meeting Link */}
                        <div>
                            <label htmlFor="custom_meet_link" className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Meeting Link (Optional Zoom / Google Meet URL)
                            </label>
                            <input
                                id="custom_meet_link"
                                type="url"
                                placeholder="Leave empty to auto-generate Google Meet link..."
                                value={data.custom_meet_link}
                                onChange={e => setData('custom_meet_link', e.target.value)}
                                className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                            />
                            {!data.custom_meet_link ? (
                                <p className="mt-1.5 flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                    <Video className="h-3.5 w-3.5" />
                                    Google Meet link will be automatically generated upon saving.
                                </p>
                            ) : (
                                <p className="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                    Custom link entered. Google Meet auto-generation skipped.
                                </p>
                            )}
                            {errors.custom_meet_link && <p className="mt-1.5 text-xs font-medium text-red-600">{errors.custom_meet_link}</p>}
                        </div>

                        {/* Smart Mapping Targets */}
                        <div>
                            <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Smart Mapping Targets (Notify Students via WhatsApp)
                            </label>
                            <select
                                onChange={handleTargetChange}
                                defaultValue=""
                                className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                            >
                                <option value="">Select a Batch, Group or Segment to add...</option>
                                {waGroups.length > 0 && (
                                    <optgroup label="💬 WhatsApp Groups (Live QR)">
                                        {waGroups.map(group => (
                                            <option key={`group-${group.id}`} value={`wa_group:${group.id}:${group.name}`}>
                                                👥 {group.name} ({group.participantCount || 0} members)
                                            </option>
                                        ))}
                                    </optgroup>
                                )}
                                <optgroup label="Batches (Tags)">
                                    {tags.map(tag => (
                                        <option key={`tag-${tag.id}`} value={`App\\Modules\\Shared\\Models\\ContactTag:${tag.id}`}>
                                            Batch: {tag.name}
                                        </option>
                                    ))}
                                </optgroup>
                                <optgroup label="Segments">
                                    {segments.map(segment => (
                                        <option key={`segment-${segment.id}`} value={`App\\Modules\\Shared\\Models\\Segment:${segment.id}`}>
                                            Segment: {segment.name}
                                        </option>
                                    ))}
                                </optgroup>
                            </select>

                            {selectedTargets.length > 0 ? (
                                <div className="mt-3 flex flex-wrap gap-2">
                                    {selectedTargets.map((target, index) => (
                                        <span key={index} className="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                                            {getTargetName(target)}
                                            <button
                                                type="button"
                                                onClick={() => removeTarget(index)}
                                                className="ml-1 inline-flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full text-brand-400 hover:bg-brand-200 hover:text-brand-500 focus:outline-none dark:hover:bg-brand-800"
                                            >
                                                &times;
                                            </button>
                                        </span>
                                    ))}
                                </div>
                            ) : (
                                <p className="mt-1 text-xs text-neutral-400">
                                    Select batches or segments to automatically send WhatsApp class reminders.
                                </p>
                            )}
                            {errors.targets && <p className="mt-1.5 text-xs font-medium text-red-600">{errors.targets}</p>}
                        </div>

                        {/* WhatsApp Notification & Trigger Settings Section */}
                        <div className="rounded-xl border border-green-200 bg-green-50/50 p-5 dark:border-green-800/40 dark:bg-green-900/10">
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex items-center gap-2.5">
                                    <div className="rounded-lg bg-green-100 p-2 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        <MessageSquare className="h-5 w-5 flex-shrink-0" />
                                    </div>
                                    <div>
                                        <p className="text-sm font-bold text-green-900 dark:text-green-200">
                                            📱 Dynamic WhatsApp Reminders & Templates
                                        </p>
                                        <p className="text-xs text-green-700 dark:text-green-400 mt-0.5">
                                            Har timing trigger ke liye alag template aur automated message choose karein.
                                        </p>
                                    </div>
                                </div>
                                <label className="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                    <input
                                        type="checkbox"
                                        className="sr-only peer"
                                        checked={data.send_whatsapp_notification}
                                        onChange={e => setData('send_whatsapp_notification', e.target.checked)}
                                    />
                                    <div className="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-green-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                            </div>

                            {data.send_whatsapp_notification && (
                                <div className="mt-5 space-y-4">
                                    {!whatsappConnected && (
                                        <div className="rounded-md bg-amber-50 border border-amber-200 p-3 dark:bg-amber-900/20 dark:border-amber-700">
                                            <p className="text-xs text-amber-700 dark:text-amber-400">
                                                ⚠️ WhatsApp connected nahi hai.{' '}
                                                <Link
                                                    href={route('integrations.index')}
                                                    className="font-semibold underline"
                                                >
                                                    Integrations mein connect karo
                                                </Link>
                                            </p>
                                        </div>
                                    )}

                                    {/* Triggers Configuration List */}
                                    <div className="space-y-3">
                                        <p className="text-xs font-bold tracking-wider uppercase text-green-800 dark:text-green-300">
                                            Reminder Timing &amp; Template Options:
                                        </p>

                                        {triggerConfigs.map(config => {
                                            const IconComponent = config.icon;
                                            const isTriggerEnabled = data.reminder_settings?.[config.key]?.enabled ?? true;
                                            const selectedTemplate = data.reminder_settings?.[config.key]?.template ?? '';

                                            return (
                                                <div
                                                    key={config.key}
                                                    className="rounded-lg border border-green-200/80 bg-white p-3.5 shadow-sm dark:border-neutral-700 dark:bg-neutral-800 transition-all"
                                                >
                                                    <div className="flex items-start justify-between gap-3">
                                                        <div className="flex items-start gap-3 min-w-0">
                                                            <div className={`p-2 rounded-lg ${config.iconColor} flex-shrink-0 mt-0.5`}>
                                                                <IconComponent className="h-4 w-4" />
                                                            </div>
                                                            <div className="min-w-0">
                                                                <p className="text-xs font-bold text-neutral-900 dark:text-white">
                                                                    {config.title}
                                                                </p>
                                                                <p className="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                                                    {config.subtitle}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        {/* Trigger toggle */}
                                                        <label className="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                                            <input
                                                                type="checkbox"
                                                                className="sr-only peer"
                                                                checked={isTriggerEnabled}
                                                                onChange={e => updateReminderSetting(config.key, 'enabled', e.target.checked)}
                                                            />
                                                            <div className="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
                                                        </label>
                                                    </div>

                                                    {/* Template selector for this trigger */}
                                                    {isTriggerEnabled && (
                                                        <div className="mt-3 pl-11">
                                                            <label className="block text-[11px] font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                                                                Choose Template for this timing:
                                                            </label>
                                                            <select
                                                                value={selectedTemplate}
                                                                onChange={e => updateReminderSetting(config.key, 'template', e.target.value)}
                                                                className="block w-full rounded-md border-neutral-300 bg-neutral-50 text-xs shadow-sm focus:border-green-500 focus:ring-green-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white"
                                                            >
                                                                <option value="">✨ Default Built-in Template (Recommended)</option>
                                                                {waTemplates.map(t => (
                                                                    <option key={t.value} value={t.value}>{t.label}</option>
                                                                ))}
                                                            </select>
                                                        </div>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </div>

                                    {/* Import Groups Link */}
                                    <div className="pt-2 border-t border-green-200/50 dark:border-green-800/30 flex items-center justify-between">
                                        <Link
                                            href={route('client.whatsapp.groups.index')}
                                            className="inline-flex items-center gap-1 text-xs font-semibold text-green-700 dark:text-green-400 hover:underline"
                                        >
                                            <ExternalLink className="h-3.5 w-3.5" />
                                            WhatsApp Groups se contacts import karo → Batch tag assign karo
                                        </Link>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Description */}
                        <div>
                            <label htmlFor="description" className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Class Description (Optional)
                            </label>
                            <textarea
                                id="description"
                                rows={3}
                                placeholder="Add instructions or topics for this class..."
                                value={data.description}
                                onChange={e => setData('description', e.target.value)}
                                className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                            />
                        </div>

                        {/* Submit button */}
                        <div className="flex items-center justify-between pt-4 border-t border-neutral-100 dark:border-neutral-700">
                            <Link
                                href={route('client.meetings.index')}
                                className="text-sm font-medium text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent bg-brand-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-neutral-900 transition-colors"
                            >
                                {processing ? (
                                    <span>{isEdit ? 'Saving...' : 'Scheduling...'}</span>
                                ) : (
                                    <>
                                        <CheckCircle2 className="h-4 w-4" />
                                        <span>{isEdit ? 'Save Changes' : 'Schedule Class'}</span>
                                    </>
                                )}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </ClientLayout>
    );
}
