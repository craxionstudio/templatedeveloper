import { createInertiaApp, router } from '@inertiajs/react';
import SiteLayout from '@/layouts/site-layout';
import { listenForClicks, pageView } from '@/lib/analytics';

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
        const meta = event.detail.page.props.meta as
            | { title?: string }
            | undefined;
        pageView(meta?.title);
    });
}
