import { usePage } from '@inertiajs/react';

export default function ApplicationLogo({ className, style, alt }) {
    let logoUrl = null;
    try {
        // eslint-disable-next-line react-hooks/rules-of-hooks
        logoUrl = usePage().props.branding?.logo_url ?? null;
    } catch {
        logoUrl = null;
    }

    if (logoUrl) {
        return (
            <img
                src={logoUrl}
                alt={alt ?? 'App Logo'}
                className={className}
                style={style}
            />
        );
    }

    // Fallback brand mark: Learmy logo
    return (
        <img
            src="/logonew.png"
            alt={alt ?? 'App Logo'}
            className={className}
            style={style}
        />
    );
}
