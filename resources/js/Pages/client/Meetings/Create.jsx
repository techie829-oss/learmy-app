import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import ClientLayout from '@/Layouts/ClientLayout';
import { ArrowLeft, Video, CheckCircle2 } from 'lucide-react';

export default function Create({ tags = [], segments = [], workspace_id, meeting }) {
    const isEdit = !!meeting;

    const initialTargets = isEdit
        ? (meeting.targets ?? []).map(t => ({ type: t.target_type, id: t.target_id }))
        : [];

    const { data, setData, post, put, processing, errors } = useForm({
        workspace_id:     workspace_id,
        title:            isEdit ? meeting.title : '',
        description:      isEdit ? (meeting.description ?? '') : '',
        start_time:       isEdit ? meeting.start_time?.slice(0, 16) : '',
        end_time:         isEdit ? meeting.end_time?.slice(0, 16) : '',
        timezone:         isEdit ? meeting.timezone : Intl.DateTimeFormat().resolvedOptions().timeZone,
        custom_meet_link: isEdit ? (meeting.meet_link ?? '') : '',
        targets:          initialTargets,
    });

    const [selectedTargets, setSelectedTargets] = useState(initialTargets);

    const handleTargetChange = (e) => {
        const value = e.target.value;
        if (!value) return;
        const [type, id] = value.split(':');
        const parsedId = parseInt(id);
        if (selectedTargets.some(t => t.type === type && t.id === parsedId)) {
            e.target.value = '';
            return;
        }
        const newTargets = [...selectedTargets, { type, id: parsedId }];
        setSelectedTargets(newTargets);
        setData('targets', newTargets);
        e.target.value = '';
    };

    const removeTarget = (indexToRemove) => {
        const newTargets = selectedTargets.filter((_, i) => i !== indexToRemove);
        setSelectedTargets(newTargets);
        setData('targets', newTargets);
    };

    const getTargetName = (target) => {
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
                                <option value="">Select a Batch or Segment to add...</option>
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
                                    Optional: Select batches or segments to automatically send WhatsApp class reminders.
                                </p>
                            )}
                            {errors.targets && <p className="mt-1.5 text-xs font-medium text-red-600">{errors.targets}</p>}
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
