import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Home, ArrowLeft, Calendar, ShieldAlert, RefreshCw, AlertTriangle, FileQuestion, ServerCrash } from 'lucide-react';

export default function Error({ status }) {
    const errorConfigs = {
        404: {
            title: 'Page Not Found',
            subtitle: 'Aap jis page ko dhoondh rahe hain wo exist nahi karta ya move ho gaya hai.',
            icon: FileQuestion,
            badgeColor: 'text-indigo-400 bg-indigo-500/10 border-indigo-500/20',
        },
        500: {
            title: 'Server Error',
            subtitle: 'Server par temporary error aayi hai. Kripya thodi der baad try karein.',
            icon: ServerCrash,
            badgeColor: 'text-rose-400 bg-rose-500/10 border-rose-500/20',
        },
        403: {
            title: 'Access Forbidden',
            subtitle: 'Aapko is section ko access karne ki permission nahi hai.',
            icon: ShieldAlert,
            badgeColor: 'text-amber-400 bg-amber-500/10 border-amber-500/20',
        },
        419: {
            title: 'Session Expired',
            subtitle: 'Aapka page session expire ho gaya hai. Kripya page reload karke dobara try karein.',
            icon: RefreshCw,
            badgeColor: 'text-blue-400 bg-blue-500/10 border-blue-500/20',
        },
        503: {
            title: 'Service Unavailable',
            subtitle: 'System maintenance ya updates chal rahe hain. Jaldi hi live wapas aayenge.',
            icon: AlertTriangle,
            badgeColor: 'text-amber-400 bg-amber-500/10 border-amber-500/20',
        },
    };

    const config = errorConfigs[status] || {
        title: 'Unexpected Error',
        subtitle: 'An unexpected issue occurred. Please try again.',
        icon: AlertTriangle,
        badgeColor: 'text-indigo-400 bg-indigo-500/10 border-indigo-500/20',
    };

    const IconComponent = config.icon;

    return (
        <div className="min-h-screen bg-slate-950 text-white flex flex-col items-center justify-center p-6 relative overflow-hidden font-sans">
            <Head title={`${status} - ${config.title}`} />

            {/* Ambient Background Glow Orbs */}
            <div className="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/25 rounded-full blur-[120px] pointer-events-none" />
            <div className="absolute -bottom-40 -right-40 w-96 h-96 bg-purple-600/20 rounded-full blur-[120px] pointer-events-none" />

            {/* Brand Header */}
            <header className="absolute top-8 left-8 z-10">
                <Link href={route('client.dashboard')} className="flex items-center gap-3 group">
                    <img src="/logonew.png" alt="Learmy" className="h-9 w-auto object-contain" onError={(e) => {
                        e.target.style.display = 'none';
                    }} />
                    <span className="text-xl font-black tracking-tight text-white group-hover:text-indigo-400 transition-colors">
                        Learmy
                    </span>
                </Link>
            </header>

            {/* Central Error Card */}
            <main className="relative z-10 max-w-lg w-full bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-8 sm:p-10 text-center shadow-2xl shadow-indigo-950/50">
                <div className="inline-flex items-center justify-center p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 mb-6">
                    <IconComponent className="h-10 w-10 text-indigo-400" />
                </div>

                <div className="text-6xl sm:text-7xl font-extrabold tracking-tight bg-gradient-to-r from-white via-slate-200 to-indigo-400 bg-clip-text text-transparent mb-2">
                    {status}
                </div>

                <h1 className="text-2xl font-bold text-white mb-3 tracking-tight">
                    {config.title}
                </h1>

                <p className="text-slate-400 text-sm leading-relaxed mb-8 max-w-sm mx-auto">
                    {config.subtitle}
                </p>

                {/* Actions */}
                <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <Link
                        href={route('client.dashboard')}
                        className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-600/30 transition-all hover:-translate-y-0.5 active:translate-y-0"
                    >
                        <Home className="h-4 w-4" />
                        Dashboard
                    </Link>

                    <Link
                        href={route('client.meetings.index')}
                        className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-sm font-semibold border border-slate-700 transition-all hover:-translate-y-0.5"
                    >
                        <Calendar className="h-4 w-4" />
                        Classes & Calendar
                    </Link>

                    <button
                        onClick={() => window.history.back()}
                        className="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-transparent hover:bg-slate-800/60 text-slate-400 hover:text-slate-200 text-sm font-medium transition-all"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Go Back
                    </button>
                </div>
            </main>

            <footer className="absolute bottom-6 text-xs text-slate-500 font-medium">
                Learmy Education Platform &copy; {new Date().getFullYear()}
            </footer>
        </div>
    );
}
