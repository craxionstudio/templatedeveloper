import type { SiteLayoutData } from '@/types/site';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            site: SiteLayoutData;
            flash: { newsletter: string | null };
            [key: string]: unknown;
        };
    }
}

declare global {
    interface Window {
        dataLayer?: unknown[];
        gtag?: (...args: unknown[]) => void;
        fbq?: (...args: unknown[]) => void;
        turnstile?: {
            render: (
                el: HTMLElement,
                options: Record<string, unknown>,
            ) => string;
            reset: (id?: string) => void;
            remove: (id?: string) => void;
        };
    }
}
