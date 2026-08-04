import ClientLayout from '@/Layouts/ClientLayout';
import { Head, Link, router } from '@inertiajs/react';
import { CheckCircle2, AlertCircle, Calendar, MessageSquare, ExternalLink, LogOut, ShieldCheck } from 'lucide-react';
import { useTranslation } from 'react-i18next';

export default function ClientIntegrationsIndex({ googleConnected, googleEmail, flash }) {
    const { t } = useTranslation();

    const handleGoogleConnect = () => {
        window.location.href = route('integrations.google.redirect');
    };

    const handleGoogleDisconnect = () => {
        if (confirm('Are you sure you want to disconnect Google Calendar?')) {
            router.post(route('integrations.google.disconnect'));
        }
    };

    return (
        <ClientLayout title="Integrations & Connections">
            <Head title="Integrations" />

            <div className="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
                
                {/* Header */}
                <div>
                    <h1 className="text-2xl font-bold text-neutral-900 dark:text-white flex items-center gap-2">
                        <ShieldCheck className="h-7 w-7 text-indigo-600 dark:text-indigo-400" />
                        Integrations & App Connections
                    </h1>
                    <p className="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Manage your connected messaging channels and calendar apps to automate class scheduling & WhatsApp notifications.
                    </p>
                </div>

                {/* Flash Notifications */}
                {flash?.success && (
                    <div className="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/30 p-4 text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-3">
                        <CheckCircle2 className="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                        <span>{flash.success}</span>
                    </div>
                )}
                {flash?.error && (
                    <div className="rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-950/30 p-4 text-rose-800 dark:text-rose-200 text-sm flex items-center gap-3">
                        <AlertCircle className="h-5 w-5 text-rose-600 dark:text-rose-400 shrink-0" />
                        <span>{flash.error}</span>
                    </div>
                )}

                {/* Integration Cards Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {/* WhatsApp Business API Card */}
                    <div className="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div className="flex items-center justify-between mb-4">
                                <div className="flex items-center gap-3">
                                    <div className="h-12 w-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xl">
                                        WA
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-lg text-neutral-900 dark:text-white">
                                            WhatsApp Business API
                                        </h3>
                                        <p className="text-xs text-neutral-500 dark:text-neutral-400">
                                            Official Meta Cloud Messaging
                                        </p>
                                    </div>
                                </div>
                                <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                    <CheckCircle2 className="h-3.5 w-3.5" /> Active
                                </span>
                            </div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-300 mb-6">
                                Connect your WhatsApp Business Account to send automated class schedules, meeting links, and instant WhatsApp reminders to students.
                            </p>
                        </div>
                        <div className="pt-4 border-t border-neutral-100 dark:border-neutral-800 flex items-center justify-between">
                            <Link
                                href="/app/inbox/setup"
                                className="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 transition"
                            >
                                Manage Channel & WABA <ExternalLink className="h-4 w-4" />
                            </Link>
                        </div>
                    </div>

                    {/* Google Calendar & Meet Card */}
                    <div className="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                        <div>
                            <div className="flex items-center justify-between mb-4">
                                <div className="flex items-center gap-3">
                                    <div className="h-12 w-12 rounded-xl bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xl">
                                        <Calendar className="h-6 w-6" />
                                    </div>
                                    <div>
                                        <h3 className="font-semibold text-lg text-neutral-900 dark:text-white">
                                            Google Calendar & Meet
                                        </h3>
                                        <p className="text-xs text-neutral-500 dark:text-neutral-400">
                                            Auto Google Meet Video Links
                                        </p>
                                    </div>
                                </div>
                                {googleConnected ? (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                        <CheckCircle2 className="h-3.5 w-3.5" /> Connected
                                    </span>
                                ) : (
                                    <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400">
                                        Not Connected
                                    </span>
                                )}
                            </div>
                            <p className="text-sm text-neutral-600 dark:text-neutral-300 mb-6">
                                Connect your personal or business Google Account so that scheduled classes create events on <strong>your own Google Calendar</strong> with you as the primary Host!
                            </p>
                            {googleConnected && (
                                <div className="mb-4 p-3 rounded-xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/40 text-xs text-blue-800 dark:text-blue-300 flex items-center justify-between">
                                    <span>Connected as <strong>{googleEmail}</strong></span>
                                    <button
                                        onClick={handleGoogleDisconnect}
                                        className="text-rose-600 dark:text-rose-400 font-semibold hover:underline flex items-center gap-1"
                                    >
                                        <LogOut className="h-3.5 w-3.5" /> Disconnect
                                    </button>
                                </div>
                            )}
                        </div>
                        <div className="pt-4 border-t border-neutral-100 dark:border-neutral-800">
                            {!googleConnected ? (
                                <button
                                    onClick={handleGoogleConnect}
                                    className="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm flex items-center justify-center gap-2 transition shadow-sm"
                                >
                                    <svg className="h-4 w-4 fill-current" viewBox="0 0 24 24">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                    </svg>
                                    Sign in with Google (1-Click Connect)
                                </button>
                            ) : (
                                <p className="text-xs text-neutral-500 dark:text-neutral-400 text-center">
                                    All new scheduled meetings will automatically create Google Meet calls on your account.
                                </p>
                            )}
                        </div>
                    </div>

                </div>

            </div>
        </ClientLayout>
    );
}
