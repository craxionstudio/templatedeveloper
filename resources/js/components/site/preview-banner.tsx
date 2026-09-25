import { usePage } from '@inertiajs/react';

/**
 * Penanda halaman pratinjau admin (konten boleh belum dipublikasikan, noindex).
 */
export default function PreviewBanner({ show }: { show?: boolean }) {
    const { labels } = usePage().props.site;

    if (!show) {
        return null;
    }

    return (
        <p
            role="status"
            className="sticky top-0 z-40 bg-ink px-5 py-2.5 text-center text-sm font-semibold text-ground"
        >
            {labels.preview_notice}
        </p>
    );
}
