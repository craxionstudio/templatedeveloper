import { usePage } from '@inertiajs/react';
import { ListingHeader, ViewToggle } from '@/components/listing/listing-header';
import type {
    ListingHeaderData,
    ToggleData,
} from '@/components/listing/listing-header';
import ClusterCard from '@/components/site/cluster-card';
import CtaSection from '@/components/site/cta-section';
import { KawasanCard } from '@/components/site/kawasan-card';
import PageHead from '@/components/site/page-head';
import { SectionHeading } from '@/components/site/ui';
import type {
    ClusterCardData,
    Crumb,
    CtaData,
    KawasanCardData,
} from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    breadcrumbs: Crumb[];
    header: ListingHeaderData;
    toggle: ToggleData;
    summary: string;
    kawasanEyebrow: string;
    viewLabel: string;
    kawasans: KawasanCardData[];
    standalone: {
        eyebrow: string;
        title: string;
        description: string;
        items: ClusterCardData[];
    } | null;
    cta: CtaData;
};

/**
 * Produk Listing — tampilan Kawasan (/properti/kawasan).
 */
export default function PropertiKawasan({
    meta,
    breadcrumbs,
    header,
    toggle,
    summary,
    kawasanEyebrow,
    viewLabel,
    kawasans,
    standalone,
    cta,
}: Props) {
    const { labels } = usePage().props.site;
    const [count, ...rest] = summary.split(' ');

    return (
        <>
            <PageHead meta={meta} />
            <ListingHeader header={header} breadcrumbs={breadcrumbs} />

            <section className="container-site flex flex-col gap-5 pt-8 pb-14 xl:gap-6 xl:pt-12 xl:pb-[120px]">
                <ViewToggle toggle={toggle} label={labels.listing_view} />
                <p className="text-[15px] text-body xl:text-base">
                    <strong className="text-ink">
                        {count} {rest[0]}
                    </strong>{' '}
                    {rest.slice(1).join(' ')}
                </p>
                <h2 className="sr-only">{labels.kawasan_list}</h2>
                <div className="grid gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-3">
                    {kawasans.map((kawasan) => (
                        <KawasanCard
                            key={kawasan.id}
                            kawasan={kawasan}
                            eyebrow={kawasanEyebrow}
                            viewLabel={viewLabel}
                        />
                    ))}
                </div>

                {standalone ? (
                    <div className="mt-10 flex flex-col gap-3 xl:mt-16 xl:gap-4">
                        <SectionHeading
                            eyebrow={standalone.eyebrow}
                            title={standalone.title}
                        />
                        <p className="text-[15px] text-body xl:text-base">
                            {standalone.description}
                        </p>
                        <div className="mt-3 grid gap-4 md:grid-cols-2 md:gap-6 xl:mt-5 xl:grid-cols-3">
                            {standalone.items.map((cluster) => (
                                <ClusterCard
                                    key={cluster.id}
                                    cluster={cluster}
                                />
                            ))}
                        </div>
                    </div>
                ) : null}
            </section>

            <CtaSection cta={cta} />
        </>
    );
}
