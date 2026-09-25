import { createInertiaApp, router } from '@inertiajs/react';
import SiteLayout from '@/layouts/site-layout';
import { listenForClicks, pageView } from '@/lib/analytics';

void createInertiaApp({
    // Judul lengkap (pola "{Judul} | {Brand}") disusun di server.
    title: (title) => title,
    layout: () => SiteLayout,
    strictMode: true,
    progress: {
        color: '#A94F2A',
    },
});

if (typeof window !== 'undefined') {
    listenForClicks();
    pageView();
    // Navigasi berikutnya (bukan reload): event `navigate` Inertia.
    router.on('navigate', () => {
        pageView();
    });
}
