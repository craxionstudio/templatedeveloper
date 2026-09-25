import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import LeadModal from '@/components/lead/lead-modal';
import SiteFooter from '@/components/site/site-footer';
import SiteHeader from '@/components/site/site-header';

export default function SiteLayout({ children }: { children: ReactNode }) {
    const { site } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col bg-ground">
            <a
                href="#konten"
                className="sr-only z-50 rounded-full bg-ink px-5 py-3 font-semibold text-ground focus:not-sr-only focus:fixed focus:top-3 focus:left-3"
            >
                {site.labels.skip_to_content}
            </a>
            <SiteHeader />
            <main id="konten" className="flex-1">
                {children}
            </main>
            <SiteFooter />
            <LeadModal />
        </div>
    );
}
