import { ListingHeader } from '@/components/listing/listing-header';
import type { ListingHeaderData } from '@/components/listing/listing-header';
import ClusterNameChips from '@/components/site/cluster-name-chips';
import CtaSection from '@/components/site/cta-section';
import PageHead from '@/components/site/page-head';
import type { Crumb, CtaData } from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    breadcrumbs: Crumb[];
    header: ListingHeaderData;
    groups: { id: string; title: string; clusters: string[] }[];
    cta: CtaData;
};

/**
 * /properti/cluster-lainnya: cluster tanpa halaman sendiri, nama saja, dikelompokkan per kawasan.
 * Layout sama dengan /properti (header, breadcrumb, container). Anchor grup = slug kawasan.
 */
export default function PropertiLainnya({
    meta,
    breadcrumbs,
    header,
    groups,
    cta,
}: Props) {
    return (
        <>
            <PageHead meta={meta} />
            <ListingHeader header={header} breadcrumbs={breadcrumbs} />

            <section className="container-site flex flex-col gap-4 pt-8 pb-14 md:gap-6 xl:pt-12 xl:pb-[120px]">
                {groups.map((group) => (
                    <div
                        key={group.id}
                        id={group.id}
                        className="flex scroll-mt-6 flex-col gap-4 rounded-card bg-white p-6 xl:gap-5 xl:p-8"
                    >
                        <h2 className="font-display text-2xl leading-tight font-semibold xl:text-[28px]">
                            {group.title}
                        </h2>
                        <ClusterNameChips names={group.clusters} />
                    </div>
                ))}
            </section>

            <CtaSection cta={cta} />
        </>
    );
}
