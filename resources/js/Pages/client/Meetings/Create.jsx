import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import ClientLayout from '@/Layouts/ClientLayout';
import { ArrowLeft } from 'lucide-react';

export default function Create({ tags, segments, workspace_id }) {
    const { data, setData, post, processing, errors } = useForm({
        workspace_id: workspace_id,
        title: '',
        description: '',
        start_time: '',
        end_time: '',
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        targets: [], // [{ type: 'App\\Modules\\Shared\\Models\\ContactTag', id: 1 }]
    });

    const [selectedTargets, setSelectedTargets] = useState([]);

    const handleTargetChange = (e) => {
        const value = e.target.value;
        if (!value) return;

        const [type, id] = value.split(':');
        
        // Avoid duplicates
        if (selectedTargets.some(t => t.type === type && t.id === parseInt(id))) {
            return;
        }

        const newTarget = { type, id: parseInt(id) };
        const newTargets = [...selectedTargets, newTarget];
        
        setSelectedTargets(newTargets);
        setData('targets', newTargets);
    };

    const removeTarget = (indexToRemove) => {
        const newTargets = selectedTargets.filter((_, index) => index !== indexToRemove);
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
        post(route('client.meetings.store'));
    };

    return (
        <ClientLayout>
            <Head title="Schedule Class" />

            <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="mb-6 flex items-center gap-4">
                    <Link
                        href={route('client.meetings.index')}
                        className="rounded-full p-2 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600 dark:hover:bg-neutral-800 dark:hover:text-neutral-300"
                    >
                        <ArrowLeft className="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            Schedule a New Class
                        </h1>
                    </div>
                </div>

                <div className="rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700/50 dark:bg-neutral-800">
                    <form onSubmit={submit} className="space-y-6 p-6">
                        <div>
                            <label htmlFor="title" className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Class Title
                            </label>
                            <input
                                id="title"
                                type="text"
                                value={data.title}
                                onChange={e => setData('title', e.target.value)}
                                className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                required
                            />
                            {errors.title && <p className="mt-2 text-sm text-red-600">{errors.title}</p>}
                        </div>

                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label htmlFor="start_time" className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                    Start Time
                                </label>
                                <input
                                    id="start_time"
                                    type="datetime-local"
                                    value={data.start_time}
                                    onChange={e => setData('start_time', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                    required
                                />
                                {errors.start_time && <p className="mt-2 text-sm text-red-600">{errors.start_time}</p>}
                            </div>

                            <div>
                                <label htmlFor="end_time" className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                    End Time
                                </label>
                                <input
                                    id="end_time"
                                    type="datetime-local"
                                    value={data.end_time}
                                    onChange={e => setData('end_time', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                                    required
                                />
                                {errors.end_time && <p className="mt-2 text-sm text-red-600">{errors.end_time}</p>}
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Smart Mapping Targets (Who to invite)
                            </label>
                            <select
                                onChange={handleTargetChange}
                                defaultValue=""
                                className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                            >
                                <option value="" disabled>Select a Batch or Segment to add...</option>
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

                            {/* Selected Targets Pill List */}
                            {selectedTargets.length > 0 && (
                                <div className="mt-3 flex flex-wrap gap-2">
                                    {selectedTargets.map((target, index) => (
                                        <span key={index} className="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-sm font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                                            {getTargetName(target)}
                                            <button
                                                type="button"
                                                onClick={() => removeTarget(index)}
                                                className="ml-1 inline-flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full text-brand-400 hover:bg-brand-200 hover:text-brand-500 focus:bg-brand-500 focus:text-white focus:outline-none dark:hover:bg-brand-800"
                                            >
                                                <span className="sr-only">Remove target</span>
                                                &times;
                                            </button>
                                        </span>
                                    ))}
                                </div>
                            )}
                            {errors.targets && <p className="mt-2 text-sm text-red-600">{errors.targets}</p>}
                        </div>

                        <div>
                            <label htmlFor="description" className="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                Description (Optional)
                            </label>
                            <textarea
                                id="description"
                                rows={3}
                                value={data.description}
                                onChange={e => setData('description', e.target.value)}
                                className="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-white sm:text-sm"
                            />
                            <p className="mt-2 text-xs text-neutral-500">
                                We will automatically generate a Google Meet link and attach it to the WhatsApp message.
                            </p>
                        </div>

                        <div className="flex justify-end pt-4">
                            <button
                                type="submit"
                                disabled={processing || selectedTargets.length === 0}
                                className="inline-flex justify-center rounded-md border border-transparent bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-neutral-900"
                            >
                                {processing ? 'Scheduling...' : 'Schedule Class & Notify Students'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </ClientLayout>
    );
}
