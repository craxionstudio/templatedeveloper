import { createInertiaApp, router } from '@inertiajs/react';
import SiteLayout from '@/layouts/site-layout';
import { configureTracking, listenForClicks, pageView } from '@/lib/analytics';

void createInertiaApp({
    // Judul lengkap (pola "{Judul} | {Brand}") disusun di server.
    title: (title) => title,
    layout: () => SiteLayout,
    strictMode: true,
    progress: {
        color: '#9A4524',
    },
});

if (typeof window !== 'undefined') {
    listenForClicks();
    pageView();
    // Navigasi berikutnya (bukan reload): event `navigate` Inertia.
    router.on('navigate', (event) => {
        const props = event.detail.page.props as {
            meta?: { title?: string };
            site?: { tracking?: { capi?: boolean } };
        };
        configureTracking(props.site?.tracking);
        const meta = props.meta;
        pageView(meta?.title);
    });
}
