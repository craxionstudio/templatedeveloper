import { createInertiaApp } from '@inertiajs/react';
import SiteLayout from '@/layouts/site-layout';

void createInertiaApp({
    // Judul lengkap (pola "{Judul} | {Brand}") disusun di server.
    title: (title) => title,
    layout: () => SiteLayout,
    strictMode: true,
    progress: {
        color: '#A94F2A',
    },
});
