import { InfiniteScroll, usePage } from '@inertiajs/react';
import { FilterBar, SortSelect } from '@/components/listing/filter-bar';
import type { FiltersData } from '@/components/listing/filter-bar';
import { ListingHeader, ViewToggle } from '@/components/listing/listing-header';
import type {
    ListingHeaderData,
    ToggleData,
} from '@/components/listing/listing-header';
import ClusterCard from '@/components/site/cluster-card';
import CtaSection from '@/components/site/cta-section';
import PageHead from '@/components/site/page-head';
import Pagination from '@/components/site/pagination';
import { ButtonLink } from '@/components/site/ui';
import { fill } from '@/lib/text';
import type { ClusterCardData, Crumb, CtaData } from '@/types/content';
import type { PageMeta } from '@/types/site';

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    path: string;
};

type Props = {
    meta: PageMeta;
    breadcrumbs: Crumb[];
    header: ListingHeaderData;
    toggle: ToggleData;
    filters: FiltersData;
    result: { clusters: number; types: number; template: string };
    clusters: Paginated<ClusterCardData>;
    emptyState: { title: string; description: string; button_label: string };
    cta: CtaData;
};

/**
 * Produk Listing — tampilan Cluster (/properti). Desktop: pagination; mobile: "Muat lagi"
 * (link pagination asli tetap ada untuk crawler).
 */
export default function PropertiIndex({
    meta,
    breadcrumbs,
    header,
    toggle,
    filters,
    result,
    clusters,
    emptyState,
    cta,
}: Props) {
    const { labels } = usePage().props.site;
    // "Menampilkan {clusters} cluster · {types} tipe rumah" → angka cluster + kata sesudahnya ditebalkan.
    const [before, rest = ''] = result.template.split('{clusters}');
    const [boldWord, ...tail] = rest.trimStart().split(' ');

    const pageUrl = (page: number) => {
        const url = new URL(clusters.path, 'http://x');
        Object.entries({
            ...filters.active,
            ...(filters.sort ? { urut: filters.sort } : {}),
            page: String(page),
        }).forEach(([k, v]) => url.searchParams.set(k, v));

        return `${url.pathname}${url.search}`;
    };

    return (
        <>
            <PageHead meta={meta} />
            <ListingHeader header={header} breadcrumbs={breadcrumbs} />

            <section className="container-site flex flex-col gap-5 pt-8 pb-14 xl:gap-6 xl:pt-12 xl:pb-[120px]">
                <ViewToggle toggle={toggle} label={labels.listing_view} />
                <FilterBar filters={filters} action="/properti" />

                <div className="flex items-center justify-between gap-4">
                    <p className="text-[15px] text-body xl:text-base">
                        {before}
                        <strong className="text-ink">
                            {result.clusters} {boldWord}
                        </strong>{' '}
                        {fill(tail.join(' '), { types: result.types })}
                    </p>
                    <SortSelect filters={filters} action="/properti" />
                </div>

                {clusters.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 rounded-card bg-white px-6 py-14 text-center">
                        <h2 className="font-display text-2xl font-semibold">
                            {emptyState.title}
                        </h2>
                        <p className="max-w-md text-body">
                            {emptyState.description}
                        </p>
                        <ButtonLink
                            href="/properti"
                            variant="outline"
                            size="sm"
                            className="mt-2"
                        >
                            {emptyState.button_label}
                        </ButtonLink>
                    </div>
                ) : (
                    <InfiniteScroll
                        data="clusters"
                        manual
                        onlyNext
                        next={({ fetch, hasMore, loading }) =>
                            hasMore ? (
                                <div className="mt-6 md:hidden">
                                    <button
                                        type="button"
                                        onClick={fetch}
                                        disabled={loading}
                                        className="flex h-[52px] w-full items-center justify-center rounded-full border-[1.5px] border-ink font-semibold text-ink disabled:opacity-60"
                                    >
                                        {labels.load_more}
                                    </button>
                                </div>
                            ) : null
                        }
                    >
                        <div className="grid gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-3">
                            {clusters.data.map((cluster) => (
                                <ClusterCard
                                    key={cluster.id}
                                    cluster={cluster}
                                    headingLevel="h2"
                                />
                            ))}
                        </div>
                    </InfiniteScroll>
                )}

                {/* Pagination asli (desktop); tetap ada di HTML mobile untuk crawler. */}
                <Pagination
                    className="mt-6 hidden md:flex"
                    pagination={{
                        current: clusters.current_page,
                        last: clusters.last_page,
                        prev:
                            clusters.current_page > 1
                                ? pageUrl(clusters.current_page - 1)
                                : null,
                        next:
                            clusters.current_page < clusters.last_page
                                ? pageUrl(clusters.current_page + 1)
                                : null,
                        pages: Array.from(
                            { length: clusters.last_page },
                            (_, i) => ({ page: i + 1, url: pageUrl(i + 1) }),
                        ),
                    }}
                />
            </section>

            <CtaSection cta={cta} />
        </>
    );
}
