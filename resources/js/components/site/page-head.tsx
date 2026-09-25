import { Head } from '@inertiajs/react';
import type { PageMeta } from '@/types/site';

/**
 * JSON aman di dalam <script>: cegah "</script>" dari konten.
 */
function safeJson(data: unknown): string {
    return JSON.stringify(data).replace(/</g, '\\u003c');
}

/**
 * Tag head per halaman (brief 8.2 & 8.3), disusun di server (App\Support\PageMeta) dan
 * ikut ter-render di HTML SSR: title, description, robots, canonical, Open Graph,
 * Twitter Card, dan JSON-LD.
 */
export default function PageHead({ meta }: { meta: PageMeta }) {
    const og = meta.og;

    return (
        <Head title={meta.title}>
            {meta.description ? (
                <meta name="description" content={meta.description} />
            ) : null}
            {meta.robots ? <meta name="robots" content={meta.robots} /> : null}
            {meta.canonical ? (
                <link rel="canonical" href={meta.canonical} />
            ) : null}

            {og ? <meta property="og:type" content={og.type} /> : null}
            {og ? <meta property="og:title" content={og.title} /> : null}
            {og?.description ? (
                <meta property="og:description" content={og.description} />
            ) : null}
            {og ? <meta property="og:url" content={og.url} /> : null}
            {og ? <meta property="og:site_name" content={og.siteName} /> : null}
            {og ? <meta property="og:locale" content={og.locale} /> : null}
            {og ? <meta property="og:image" content={og.image} /> : null}
            {og?.imageWidth ? (
                <meta
                    property="og:image:width"
                    content={String(og.imageWidth)}
                />
            ) : null}
            {og?.imageHeight ? (
                <meta
                    property="og:image:height"
                    content={String(og.imageHeight)}
                />
            ) : null}
            {og ? <meta property="og:image:alt" content={og.title} /> : null}
            {meta.article?.publishedTime ? (
                <meta
                    property="article:published_time"
                    content={meta.article.publishedTime}
                />
            ) : null}
            {meta.article?.modifiedTime ? (
                <meta
                    property="article:modified_time"
                    content={meta.article.modifiedTime}
                />
            ) : null}
            {meta.article?.section ? (
                <meta
                    property="article:section"
                    content={meta.article.section}
                />
            ) : null}

            {og ? (
                <meta name="twitter:card" content="summary_large_image" />
            ) : null}
            {og ? <meta name="twitter:title" content={og.title} /> : null}
            {og?.description ? (
                <meta name="twitter:description" content={og.description} />
            ) : null}
            {og ? <meta name="twitter:image" content={og.image} /> : null}

            {(meta.jsonLd ?? []).map((data, index) => (
                <script
                    key={index}
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{ __html: safeJson(data) }}
                />
            ))}
        </Head>
    );
}
