import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Home, ArrowLeft, FileQuestion, ServerCrash, ShieldAlert, RefreshCw, AlertTriangle } from 'lucide-react';

export default function Error({ status }) {
    const errorConfigs = {
        404: {
            title: 'Page Not Found',
            subtitle: 'The page you are looking for does not exist or has been moved.',
            icon: FileQuestion,
        },
        500: {
            title: 'Server Error',
            subtitle: 'Something went wrong on our end. Please try again in a moment.',
            icon: ServerCrash,
        },
        403: {
            title: 'Access Forbidden',
            subtitle: 'You do not have permission to access this page or resource.',
            icon: ShieldAlert,
        },
        419: {
            title: 'Session Expired',
            subtitle: 'Your session has expired. Please refresh and try again.',
            icon: RefreshCw,
        },
        503: {
            title: 'Service Maintenance',
            subtitle: 'Learmy is currently undergoing scheduled maintenance. We will be back online shortly.',
            icon: AlertTriangle,
        },
    };

    const config = errorConfigs[status] || {
        title: 'Unexpected Error',
        subtitle: 'An unexpected issue occurred. Please try again.',
        icon: AlertTriangle,
    };

    const IconComponent = config.icon;

    return (
        <div className="min-h-screen bg-neutral-50 dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 flex flex-col justify-between p-6 sm:p-10 font-sans transition-colors">
            <Head title={`${status} - ${config.title}`} />

            {/* Top Bar Header */}
            <div className="w-full max-w-7xl mx-auto flex items-center justify-between">
                <Link href={route('client.dashboard')} className="flex items-center gap-2.5">
                    <img src="/logonew.png" alt="Learmy" className="h-8 w-auto object-contain" onError={(e) => { e.target.style.display = 'none'; }} />
                    <span className="text-lg font-bold text-neutral-900 dark:text-white tracking-tight">Learmy</span>
                </Link>
            </div>

            {/* Main Error Content Card */}
            <main className="w-full max-w-md mx-auto my-auto text-center">
                <div className="rounded-2xl border border-neutral-200 bg-white p-8 sm:p-10 shadow-sm dark:border-neutral-800 dark:bg-neutral-800">
                    <div className="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400">
                        <IconComponent className="h-7 w-7" />
                    </div>

                    <p className="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 mb-1">
                        Error {status}
                    </p>

                    <h1 className="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white mb-2">
                        {config.title}
                    </h1>

                    <p className="text-xs text-neutral-500 dark:text-neutral-400 mb-8 leading-relaxed">
                        {config.subtitle}
                    </p>

                    <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
                        <Link
                            href={route('client.dashboard')}
                            className="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-700 focus:outline-none transition-colors"
                        >
                            <Home className="h-4 w-4" />
                            Go to Dashboard
                        </Link>

                        <button
                            onClick={() => window.history.back()}
                            className="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border border-neutral-300 bg-white px-4 py-2.5 text-xs font-semibold text-neutral-700 shadow-sm hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-600 transition-colors"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            Go Back
                        </button>
                    </div>
                </div>
            </main>

            {/* Footer */}
            <div className="text-center text-xs text-neutral-400 dark:text-neutral-500">
                &copy; {new Date().getFullYear()} Learmy Education. All rights reserved.
            </div>
        </div>
    );
}
