import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import {
    ContactCard,
    PriceBox,
    SpecSection,
    TypeTabs,
    withTypePrefix,
} from '@/components/cluster/detail-parts';
import type {
    HouseTypeData,
    ContactCardData,
    MarketingData,
    PricingData,
} from '@/components/cluster/detail-parts';
import BenefitSection from '@/components/cluster/benefit-section';
import type { BenefitSectionData } from '@/components/cluster/benefit-section';
import Gallery from '@/components/cluster/gallery';
import type { GalleryData } from '@/components/cluster/gallery';
import Breadcrumbs from '@/components/site/breadcrumbs';
import ClusterCard from '@/components/site/cluster-card';
import CtaSection from '@/components/site/cta-section';
import { Icon } from '@/components/site/icons';
import PreviewBanner from '@/components/site/preview-banner';
import PageHead from '@/components/site/page-head';
import RichText from '@/components/site/rich-text';
import SmartLink from '@/components/site/smart-link';
import {
    ArrowLink,
    Badge,
    ButtonLink,
    SectionHeading,
} from '@/components/site/ui';
import { track } from '@/lib/analytics';
import type { ClusterCardData, Crumb, CtaData } from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    /** Pratinjau admin (boleh belum dipublikasikan), noindex. */
    preview?: boolean;
    breadcrumbs: Crumb[];
    cluster: {
        id: number;
        name: string;
        url: string;
        kawasan: { name: string; url: string } | null;
        buildingType: string | null;
        badge: string | null;
        /** Status penjualan opsional; null = tanpa badge. */
        status: string | null;
        /** Link WA tingkat cluster (dipakai kalau belum ada tipe). */
        whatsappUrl: string;
        address: string | null;
        description: string | null;
        legality: string | null;
        bookingFee: string | null;
        brochure: string | null;
        pricelist: string | null;
        specifications: { label: string; value: string }[];
        facilities: string[];
    };
    gallery: GalleryData;
    types: HouseTypeData[];
    selectedType: string | null;
    pricing: PricingData;
    /** Bank Benefit cluster ini; null = section tidak tampil. */
    benefits: BenefitSectionData | null;
    sections: {
        specs: string | null;
        types: string | null;
        description: string | null;
        facilities: string | null;
    };
    specLabels: Record<string, string>;
    downloads: { brochure: string; pricelist: string };
    marketing: MarketingData;
    contact: ContactCardData;
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
        benefits,
        sections,
        specLabels,
        downloads,
        marketing,
        contact,
        mobileBar,
        others,
        cta,
        preview = false,
    } = props;
    const { labels } = usePage().props.site;
    // Tipe tanpa data sama sekali (belum ada harga/luas/denah) tidak ditampilkan sebagai tab.
    const visibleTypes = types.filter((t) => t.hasData);
    const [selectedSlug, setSelectedSlug] = useState(
        props.selectedType ?? visibleTypes[0]?.slug,
    );
    const type =
        visibleTypes.find((t) => t.slug === selectedSlug) ??
        visibleTypes[0] ??
        null;
    const whatsappUrl = type?.whatsappUrl ?? cluster.whatsappUrl;
    // Tipe tanpa nama yang isinya hanya harga sudah terwakili kotak harga: tanpa section tipe.
    const showTypeTabs = visibleTypes.some(
        (t) =>
            t.name ||
            t.landArea ||
            t.buildingArea ||
            t.bedrooms ||
            t.floorplan.url,
    );

    // Event view_listing sekali per cluster.
    useEffect(() => {
        track('view_listing', {
            cluster: cluster.name,
            kawasan: cluster.kawasan?.name,
        });
    }, [cluster.id, cluster.name, cluster.kawasan?.name]);

    const selectType = (slug: string) => {
        setSelectedSlug(slug);
        track('select_house_type', {
            cluster: cluster.name,
            house_type:
                visibleTypes.find((t) => t.slug === slug)?.name ?? undefined,
        });
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
                        track="download_brochure"
                        cluster={cluster.name}
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
                        track="download_pricelist"
                        cluster={cluster.name}
                    >
                        {downloads.pricelist}
                    </ButtonLink>
                ) : null}
            </div>
        ) : null;

    return (
        <>
            <PageHead meta={meta} />
            <PreviewBanner show={preview} />

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
                        {cluster.badge || cluster.status ? (
                            <div className="flex flex-wrap gap-2">
                                {cluster.badge ? (
                                    <Badge>{cluster.badge}</Badge>
                                ) : null}
                                {cluster.status ? (
                                    <Badge tone="success">
                                        {cluster.status}
                                    </Badge>
                                ) : null}
                            </div>
                        ) : null}
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
                            {type?.name
                                ? `, ${withTypePrefix(type.name, labels.type_prefix)}${type.lotSize ? ` ${type.lotSize}` : ''}`
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

                    <PriceBox
                        type={type}
                        pricing={pricing}
                        bookingFee={cluster.bookingFee}
                    />
                    {benefits ? (
                        <BenefitSection
                            benefits={benefits}
                            clusterName={cluster.name}
                        />
                    ) : null}
                    {sections.specs ? (
                        <SpecSection
                            title={sections.specs}
                            type={type}
                            specLabels={specLabels}
                            materials={cluster.specifications}
                        />
                    ) : null}
                    {sections.types && type && showTypeTabs ? (
                        <TypeTabs
                            title={sections.types}
                            types={visibleTypes}
                            selected={type}
                            onSelect={selectType}
                            specLabels={specLabels}
                        />
                    ) : null}

                    {sections.description &&
                    (cluster.description || downloadsBlock) ? (
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

                    {sections.facilities && cluster.facilities.length > 0 ? (
                        <section className="flex flex-col gap-4">
                            <h2 className="font-display text-[26px] font-medium xl:text-[32px]">
                                {sections.facilities}
                            </h2>
                            <ul className="grid gap-x-6 gap-y-3 md:grid-cols-2">
                                {cluster.facilities.map((item) => (
                                    <li
                                        key={item}
                                        className="flex gap-3 text-[15px] leading-relaxed text-body"
                                    >
                                        <Icon
                                            name="check"
                                            className="mt-1 size-[18px] shrink-0 text-terracotta"
                                        />
                                        {item}
                                    </li>
                                ))}
                            </ul>
                        </section>
                    ) : null}

                    {/* Mobile & tablet: kartu marketing + tombol WhatsApp setelah deskripsi. */}
                    <div className="xl:hidden">
                        <ContactCard
                            marketing={marketing}
                            contact={contact}
                            whatsappUrl={whatsappUrl}
                            legality={cluster.legality}
                            clusterName={cluster.name}
                            position="inline"
                        />
                    </div>
                </div>

                <aside className="hidden xl:block">
                    {/* data-floating-avoid: tombol WhatsApp melayang mengecil kalau menabrak kartu ini. */}
                    <div className="sticky top-6" data-floating-avoid>
                        <ContactCard
                            marketing={marketing}
                            contact={contact}
                            whatsappUrl={whatsappUrl}
                            legality={cluster.legality}
                            clusterName={cluster.name}
                            position="sidebar"
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

            {/* Mobile: sticky bottom bar (harga mulai + Jadwalkan Survey via WhatsApp). Tombol chat WhatsApp = tombol melayang di atasnya. */}
            <>
                <div className="h-[84px] md:hidden" aria-hidden="true" />
                <div className="fixed inset-x-0 bottom-0 z-30 flex items-center gap-2.5 border-t border-line bg-white px-5 pt-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] md:hidden">
                    <div className="mr-auto flex flex-col">
                        {type?.price ? (
                            <>
                                <span className="text-xs text-caption">
                                    {mobileBar.priceLabel}
                                </span>
                                <span className="text-lg leading-tight font-bold">
                                    {type.price}
                                </span>
                            </>
                        ) : (
                            <span className="text-sm leading-tight font-semibold">
                                {labels.price_on_request}
                            </span>
                        )}
                    </div>
                    <SmartLink
                        href={contact.surveyUrl}
                        data-cluster={cluster.name}
                        data-position="sticky_survey"
                        className="flex h-12 items-center gap-2 rounded-full bg-terracotta px-5 font-semibold text-white no-underline"
                    >
                        <Icon name="calendar" className="size-5" />
                        {mobileBar.surveyLabel}
                    </SmartLink>
                </div>
            </>
        </>
    );
}
