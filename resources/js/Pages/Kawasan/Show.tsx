import { ListingHeader } from '@/components/listing/listing-header';
import ClusterCard from '@/components/site/cluster-card';
import CtaSection from '@/components/site/cta-section';
import { ContentIcon } from '@/components/site/icons';
import { KawasanCardCompact } from '@/components/site/kawasan-card';
import PageHead from '@/components/site/page-head';
import RichText from '@/components/site/rich-text';
import {
    ArrowLink,
    ButtonLink,
    Eyebrow,
    SectionHeading,
} from '@/components/site/ui';
import type {
    ClusterCardData,
    Crumb,
    CtaData,
    ImageData,
    KawasanCardData,
    Stat,
} from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    breadcrumbs: Crumb[];
    kawasan: { name: string; summary: string | null; image: ImageData };
    hero: { eyebrow: string; stats: Stat[] };
    about: {
        eyebrow: string;
        title: string;
        description: string | null;
        brochure: string | null;
        brochureLabel: string;
        mapUrl: string | null;
        mapLabel: string;
    } | null;
    facilities: {
        title: string;
        items: { icon?: string; title: string; description?: string }[];
    } | null;
    clusters: {
        eyebrow: string;
        title: string;
        link: { label: string; url: string };
        items: ClusterCardData[];
    } | null;
    others: {
        eyebrow: string;
        title: string;
        link: { label: string; url: string };
        items: KawasanCardData[];
    } | null;
    cta: CtaData;
};

export default function KawasanShow({
    meta,
    breadcrumbs,
    kawasan,
    hero,
    about,
    facilities,
    clusters,
    others,
    cta,
}: Props) {
    const buttons =
        about && (about.brochure || about.mapUrl) ? (
            <div className="grid grid-cols-2 gap-3 md:flex">
                {about.brochure ? (
                    <ButtonLink
                        href={about.brochure}
                        variant="outline"
                        icon="download"
                        newTab
                    >
                        {about.brochureLabel}
                    </ButtonLink>
                ) : null}
                {about.mapUrl ? (
                    <ButtonLink
                        href={about.mapUrl}
                        variant="outline"
                        icon="pin"
                        newTab
                    >
                        {about.mapLabel}
                    </ButtonLink>
                ) : null}
            </div>
        ) : null;

    return (
        <>
            <PageHead meta={meta} />
            <ListingHeader
                breadcrumbs={breadcrumbs}
                header={{
                    eyebrow: hero.eyebrow,
                    title: kawasan.name,
                    description: kawasan.summary ?? '',
                    image: kawasan.image,
                    imageMobile: null,
                    stats: hero.stats,
                }}
            />

            {about || facilities ? (
                <section className="container-site grid gap-8 py-14 xl:grid-cols-2 xl:gap-20 xl:py-[120px]">
                    {about ? (
                        <div className="flex flex-col gap-5">
                            <Eyebrow>{about.eyebrow}</Eyebrow>
                            <h2 className="font-display text-[30px] leading-[1.15] font-medium xl:text-[44px]">
                                {about.title}
                            </h2>
                            <RichText html={about.description} />
                            <div className="mt-2 hidden md:block">
                                {buttons}
                            </div>
                        </div>
                    ) : null}
                    {facilities ? (
                        <div className="flex flex-col gap-4">
                            <h3 className="text-base font-semibold xl:text-lg">
                                {facilities.title}
                            </h3>
                            <ul className="grid gap-3 md:grid-cols-2">
                                {facilities.items.map((item) => (
                                    <li
                                        key={item.title}
                                        className="flex gap-4 rounded-2xl bg-white p-4 xl:p-5"
                                    >
                                        <span className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#F8E1D4] text-terracotta">
                                            <ContentIcon
                                                name={item.icon}
                                                className="size-5"
                                            />
                                        </span>
                                        <div className="flex flex-col gap-0.5">
                                            <span className="text-[15px] font-semibold">
                                                {item.title}
                                            </span>
                                            {item.description ? (
                                                <span className="text-[13px] leading-snug text-body">
                                                    {item.description}
                                                </span>
                                            ) : null}
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ) : null}
                    <div className="md:hidden">{buttons}</div>
                </section>
            ) : null}

            {clusters ? (
                <section className="bg-sand py-14 xl:py-[120px]">
                    <div className="container-site flex flex-col gap-8 xl:gap-12">
                        <SectionHeading
                            eyebrow={clusters.eyebrow}
                            title={clusters.title}
                            action={
                                <ArrowLink href={clusters.link.url}>
                                    {clusters.link.label}
                                </ArrowLink>
                            }
                        />
                        <div className="grid gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-3">
                            {clusters.items.map((cluster) => (
                                <ClusterCard
                                    key={cluster.id}
                                    cluster={cluster}
                                />
                            ))}
                        </div>
                    </div>
                </section>
            ) : null}

            {others ? (
                <section className="container-site flex flex-col gap-8 py-14 xl:gap-10 xl:py-[120px]">
                    <SectionHeading
                        eyebrow={others.eyebrow}
                        title={others.title}
                        action={
                            <ArrowLink href={others.link.url}>
                                {others.link.label}
                            </ArrowLink>
                        }
                    />
                    <div className="grid gap-4 md:grid-cols-2 md:gap-6">
                        {others.items.map((item) => (
                            <KawasanCardCompact key={item.id} kawasan={item} />
                        ))}
                    </div>
                    <div className="md:hidden">
                        <ArrowLink href={others.link.url}>
                            {others.link.label}
                        </ArrowLink>
                    </div>
                </section>
            ) : (
                <div className="h-14 xl:h-[120px]" />
            )}

            <CtaSection cta={cta} />
        </>
    );
}
