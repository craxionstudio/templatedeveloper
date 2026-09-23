import { Head } from '@inertiajs/react';
import type { PageMeta } from '@/types/site';

/**
 * Tag head per halaman; ikut ter-render di output SSR.
 * Canonical, OG, dan JSON-LD lengkap menyusul di Milestone 5.
 */
export default function PageHead({ meta }: { meta: PageMeta }) {
    return (
        <Head title={meta.title}>
            {meta.description ? (
                <meta name="description" content={meta.description} />
            ) : null}
        </Head>
    );
}
