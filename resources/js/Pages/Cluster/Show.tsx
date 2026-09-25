import { usePage } from '@inertiajs/react';
import { useState } from 'react';
import {
    LeadCard,
    PriceBox,
    PromoBox,
    SpecSection,
    TypeTabs,
} from '@/components/cluster/detail-parts';
import type {
    HouseTypeData,
    LeadFormData,
    MarketingData,
    PricingData,
} from '@/components/cluster/detail-parts';
import Gallery from '@/components/cluster/gallery';
import type { GalleryData } from '@/components/cluster/gallery';
import Breadcrumbs from '@/components/site/breadcrumbs';
import ClusterCard from '@/components/site/cluster-card';
import CtaSection from '@/components/site/cta-section';
import { Icon } from '@/components/site/icons';
import PageHead from '@/components/site/page-head';
import RichText from '@/components/site/rich-text';
import SmartLink from '@/components/site/smart-link';
import {
    ArrowLink,
    Badge,
    ButtonLink,
    SectionHeading,
} from '@/components/site/ui';
import type { ClusterCardData, Crumb, CtaData } from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    breadcrumbs: Crumb[];
    cluster: {
        name: string;
        url: string;
        kawasan: { name: string; url: string } | null;
        buildingType: string | null;
        badge: string | null;
        status: string;
        address: string | null;
        description: string | null;
        legality: string | null;
        bookingFee: string | null;
        brochure: string | null;
        pricelist: string | null;
        specifications: { label: string; value: string }[];
    };
    gallery: GalleryData;
    types: HouseTypeData[];
    selectedType: string | null;
    pricing: PricingData;
    promo: {
        title: string;
        period: string | null;
        items: { icon?: string; title: string; description?: string }[];
    } | null;
    sections: {
        specs: string | null;
        types: string | null;
        description: string | null;
    };
    specLabels: Record<string, string>;
    downloads: { brochure: string; pricelist: string };
    marketing: MarketingData;
    form: LeadFormData;
    mobileBar: {
        priceLabel: string;
        whatsappLabel: string;
        surveyLabel: string;
    };
    others: {
        eyebrow: string;
        title: string;
        link: { label: string; url: string };
        items: ClusterCardData[];
    } | null;
    cta: CtaData;
};

/**
 * Detail Rumah: satu halaman per cluster. Tab tipe mengganti harga, spesifikasi, dan denah
 * di sisi klien; URL diperbarui ke ?tipe= (canonical tetap tanpa query).
 */
export default function ClusterShow(props: Props) {
    const {
        meta,
        breadcrumbs,
        cluster,
        gallery,
        types,
        pricing,
        promo,
        sections,
        specLabels,
        downloads,
        marketing,
        form,
        mobileBar,
        others,
        cta,
    } = props;
    const { labels } = usePage().props.site;
    const [selectedSlug, setSelectedSlug] = useState(
        props.selectedType ?? types[0]?.slug,
    );
    const type = types.find((t) => t.slug === selectedSlug) ?? types[0];

    const selectType = (slug: string) => {
        setSelectedSlug(slug);
        const url = new URL(window.location.href);
        url.searchParams.set('tipe', slug);
        window.history.replaceState(window.history.state, '', url);
    };

    const downloadsBlock =
        cluster.brochure || cluster.pricelist ? (
            <div className="grid grid-cols-2 gap-3 md:flex">
                {cluster.brochure ? (
                    <ButtonLink
                        href={cluster.brochure}
                        variant="outline"
                        icon="download"
                        newTab
                    >
                        {downloads.brochure}
                    </ButtonLink>
                ) : null}
                {cluster.pricelist ? (
                    <ButtonLink
                        href={cluster.pricelist}
                        variant="outline"
                        icon="download"
                        newTab
                    >
                        {downloads.pricelist}
                    </ButtonLink>
                ) : null}
            </div>
        ) : null;

    return (
        <>
            <PageHead meta={meta} />

            <div className="container-site flex flex-col gap-3 pt-3 md:gap-4 md:pt-4 xl:gap-6 xl:pt-6">
                <Breadcrumbs items={breadcrumbs} compact />
                <Gallery
                    gallery={gallery}
                    floorplan={type?.floorplan ?? null}
                    backUrl={cluster.kawasan?.url ?? '/properti'}
                />
            </div>

            <div className="container-site grid gap-8 pt-6 pb-14 xl:grid-cols-[1fr_400px] xl:gap-16 xl:pt-10 xl:pb-[120px]">
                <div className="flex min-w-0 flex-col gap-8 xl:gap-12">
                    <header className="flex flex-col gap-3">
                        <div className="flex flex-wrap gap-2">
                            {cluster.badge ? (
                                <Badge>{cluster.badge}</Badge>
                            ) : null}
                            <Badge tone="success">{cluster.status}</Badge>
                        </div>
                        <p className="text-sm font-semibold text-caption">
                            {cluster.kawasan ? (
                                <>
                                    <SmartLink
                                        href={cluster.kawasan.url}
                                        className="text-caption underline underline-offset-2 hover:text-ink"
                                    >
                                        {labels.kawasan} {cluster.kawasan.name}
                                    </SmartLink>{' '}
                                    ·{' '}
                                </>
                            ) : (
                                <>{labels.standalone_cluster} · </>
                            )}
                            {labels.cluster_prefix} {cluster.name}
                        </p>
                        <h1 className="font-display text-[34px] leading-[1.1] font-medium xl:text-[52px]">
                            {cluster.name}
                            {type
                                ? `, ${labels.type_prefix} ${type.name}${type.lotSize ? ` ${type.lotSize}` : ''}`
                                : ''}
                        </h1>
                        {cluster.address ? (
                            <p className="flex items-start gap-2 text-[15px] text-body">
                                <Icon
                                    name="pin"
                                    className="mt-0.5 size-[18px] shrink-0"
                                />
                                {cluster.address}
                            </p>
                        ) : null}
                    </header>

                    {type ? (
                        <PriceBox
                            type={type}
                            pricing={pricing}
                            bookingFee={cluster.bookingFee}
                        />
                    ) : null}
                    {promo ? <PromoBox promo={promo} /> : null}
                    {sections.specs && type ? (
                        <SpecSection
                            title={sections.specs}
                            type={type}
                            specLabels={specLabels}
                            materials={cluster.specifications}
                        />
                    ) : null}
                    {sections.types && type ? (
                        <TypeTabs
                            title={sections.types}
                            types={types}
                            selected={type}
                            onSelect={selectType}
                            specLabels={specLabels}
                        />
                    ) : null}

                    {sections.description ? (
                        <section className="flex flex-col gap-4">
                            <h2 className="font-display text-[26px] font-medium xl:text-[32px]">
                                {sections.description}
                            </h2>
                            <RichText
                                html={cluster.description}
                                className="xl:text-base"
                            />
                            {downloadsBlock ? (
                                <div className="mt-2">{downloadsBlock}</div>
                            ) : null}
                        </section>
                    ) : null}

                    {/* Mobile & tablet: form lead inline setelah deskripsi. */}
                    <div className="xl:hidden">
                        <LeadCard
                            marketing={marketing}
                            form={form}
                            whatsappUrl={type?.whatsappUrl ?? '/kontak'}
                            legality={cluster.legality}
                            showButtons={false}
                        />
                    </div>
                </div>

                <aside className="hidden xl:block">
                    <div className="sticky top-6">
                        <LeadCard
                            marketing={marketing}
                            form={form}
                            whatsappUrl={type?.whatsappUrl ?? '/kontak'}
                            legality={cluster.legality}
                        />
                    </div>
                </aside>
            </div>

            {others ? (
                <section className="bg-sand py-14 xl:py-[120px]">
                    <div className="container-site flex flex-col gap-8 xl:gap-12">
                        <SectionHeading
                            eyebrow={others.eyebrow}
                            title={others.title}
                            action={
                                <ArrowLink href={others.link.url}>
                                    {others.link.label}
                                </ArrowLink>
                            }
                        />
                        <div className="-mx-5 flex snap-x snap-mandatory scroll-px-5 [scrollbar-width:none] gap-4 overflow-x-auto px-5 pb-2 md:mx-0 md:grid md:scroll-px-0 md:grid-cols-2 md:gap-6 md:overflow-visible md:px-0 xl:grid-cols-3 [&::-webkit-scrollbar]:hidden">
                            {others.items.map((item) => (
                                <ClusterCard
                                    key={item.id}
                                    cluster={item}
                                    className="w-[82%] shrink-0 snap-start md:w-auto"
                                />
                            ))}
                        </div>
                    </div>
                </section>
            ) : null}

            <div className="pt-14 xl:pt-[120px]">
                <CtaSection cta={cta} />
            </div>

            {/* Mobile: sticky bottom bar (harga mulai + WA + Jadwalkan Survey). */}
            {type ? (
                <>
                    <div className="h-[84px] md:hidden" aria-hidden="true" />
                    <div className="fixed inset-x-0 bottom-0 z-30 flex items-center gap-2.5 border-t border-line bg-white px-5 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] md:hidden">
                        <div className="mr-auto flex flex-col">
                            <span className="text-xs text-caption">
                                {mobileBar.priceLabel}
                            </span>
                            <span className="text-lg leading-tight font-bold">
                                {type.price}
                            </span>
                        </div>
                        <a
                            href={type.whatsappUrl}
                            aria-label={mobileBar.whatsappLabel}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="flex size-12 items-center justify-center rounded-full border-[1.5px] border-ink text-ink"
                        >
                            <Icon name="chat" className="size-5" />
                        </a>
                        <SmartLink
                            href={form.surveyUrl}
                            className="flex h-12 items-center rounded-full bg-terracotta px-5 font-semibold text-white no-underline"
                        >
                            {mobileBar.surveyLabel}
                        </SmartLink>
                    </div>
                </>
            ) : null}
        </>
    );
}
