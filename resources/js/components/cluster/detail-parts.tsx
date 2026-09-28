import { usePage } from '@inertiajs/react';
import LeadForm from '@/components/lead/lead-form';
import type { LeadPosition } from '@/components/lead/lead-form';
import { useLeadModalTrigger } from '@/components/lead/lead-modal';
import { ContentIcon, Icon } from '@/components/site/icons';
import type { IconName } from '@/components/site/icons';
import Picture, { IMAGE_SIZES } from '@/components/site/picture';
import SmartLink from '@/components/site/smart-link';
import { ButtonLink } from '@/components/site/ui';
import { cn } from '@/lib/utils';
import type { ImageData } from '@/types/content';

export type HouseTypeData = {
    id: number;
    slug: string;
    name: string;
    /** false = tipe belum punya harga/luas/kamar/denah: tidak ditampilkan. */
    hasData: boolean;
    lotSize: string | null;
    landArea: number | null;
    buildingArea: number | null;
    bedrooms: string | null;
    bathrooms: number | null;
    floors: number | null;
    carports: number | null;
    price: string | null;
    installment: string | null;
    unitsAvailable: number | null;
    floorplan: ImageData;
    whatsappUrl: string;
};

export type PricingData = {
    priceLabel: string;
    priceNote: string;
    installmentLabel: string;
    installmentNote: string;
    perMonth: string;
    bookingFeeLabel: string;
    bookingFeeNote: string;
    kprLink: { label: string; url: string };
};

/**
 * Harga mulai, cicilan, booking fee. Sel yang kosong disembunyikan; tidak dirender sama sekali
 * kalau ketiganya kosong (cluster belum punya harga).
 */
export function PriceBox({
    type,
    pricing,
    bookingFee,
}: {
    type: HouseTypeData | null;
    pricing: PricingData;
    bookingFee: string | null;
}) {
    const cells = [
        type?.price
            ? {
                  key: 'price',
                  label: pricing.priceLabel,
                  value: type.price,
                  note: pricing.priceNote,
                  main: true,
              }
            : null,
        type?.installment
            ? {
                  key: 'installment',
                  label: pricing.installmentLabel,
                  value: type.installment,
                  suffix: pricing.perMonth,
                  note: pricing.installmentNote,
              }
            : null,
        bookingFee
            ? {
                  key: 'booking',
                  label: pricing.bookingFeeLabel,
                  value: bookingFee,
                  note: pricing.bookingFeeNote,
              }
            : null,
    ].filter((cell) => cell !== null);

    if (cells.length === 0) {
        return null;
    }

    return (
        <div className="overflow-hidden rounded-card-sm bg-white">
            <div
                className={cn(
                    'grid grid-cols-2',
                    cells.length === 3 && 'md:grid-cols-3',
                    cells.length === 1 && 'grid-cols-1',
                )}
            >
                {cells.map((cell, index) => (
                    <div
                        key={cell.key}
                        className={cn(
                            'p-5 md:p-6',
                            cell.main &&
                                cells.length > 1 &&
                                'col-span-2 flex items-end justify-between gap-4 border-b border-[#ECE6DA] md:col-span-1 md:flex-col md:items-start md:justify-start md:border-r md:border-b-0',
                            !cell.main &&
                                index < cells.length - 1 &&
                                'border-r border-[#ECE6DA]',
                        )}
                    >
                        <div>
                            <p className="text-[13px] text-caption xl:text-sm">
                                {cell.label}
                            </p>
                            <p
                                className={cn(
                                    'font-display font-semibold',
                                    cell.main
                                        ? 'text-[30px] leading-tight'
                                        : 'text-xl xl:text-[26px]',
                                )}
                            >
                                {cell.value}
                                {cell.suffix ? (
                                    <span className="text-base">
                                        {cell.suffix}
                                    </span>
                                ) : null}
                            </p>
                        </div>
                        <p
                            className={cn(
                                'text-xs text-caption',
                                cell.main &&
                                    cells.length > 1 &&
                                    'text-right md:text-left',
                            )}
                        >
                            {cell.note}
                        </p>
                    </div>
                ))}
            </div>
            <SmartLink
                href={pricing.kprLink.url}
                className="flex min-h-12 items-center gap-2 border-t border-[#ECE6DA] bg-[#FAF8F3] px-5 text-sm font-semibold text-terracotta no-underline md:px-6"
            >
                {pricing.kprLink.label}
                <Icon name="arrowRight" className="size-4" />
            </SmartLink>
        </div>
    );
}

export function PromoBox({
    promo,
}: {
    promo: {
        title: string;
        period: string | null;
        items: { icon?: string; title: string; description?: string }[];
    };
}) {
    return (
        <section className="rounded-card-sm bg-[#F6E3D8] p-5 md:p-7">
            <div className="flex flex-col gap-1 md:flex-row md:items-baseline md:justify-between">
                <h2 className="font-display text-[22px] font-medium text-[#6E2E14] xl:text-2xl">
                    {promo.title}
                </h2>
                {promo.period ? (
                    <p className="text-[13px] font-semibold text-[#8A3A17]">
                        {promo.period}
                    </p>
                ) : null}
            </div>
            <ul className="mt-5 grid gap-4 md:grid-cols-2 md:gap-x-6">
                {promo.items.map((item) => (
                    <li key={item.title} className="flex gap-3">
                        <span className="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-terracotta">
                            <ContentIcon
                                name={item.icon}
                                className="size-[18px]"
                            />
                        </span>
                        <div>
                            <p className="font-semibold">{item.title}</p>
                            {item.description ? (
                                <p className="text-[13px] leading-snug text-body">
                                    {item.description}
                                </p>
                            ) : null}
                        </div>
                    </li>
                ))}
            </ul>
        </section>
    );
}

export function SpecSection({
    title,
    type,
    specLabels,
    materials,
}: {
    title: string;
    type: HouseTypeData | null;
    specLabels: Record<string, string>;
    materials: { label: string; value: string }[];
}) {
    const { labels } = usePage().props.site;
    // Hanya fakta yang terisi; section tidak dirender kalau fakta & tabel material kosong.
    const facts = [
        type?.landArea
            ? {
                  icon: 'land' as IconName,
                  value: `${type.landArea} ${labels.area_unit}`,
                  label: specLabels.land_area,
              }
            : null,
        type?.buildingArea
            ? {
                  icon: 'building' as IconName,
                  value: `${type.buildingArea} ${labels.area_unit}`,
                  label: specLabels.building_area,
              }
            : null,
        type?.bedrooms
            ? {
                  icon: 'bed' as IconName,
                  value: type.bedrooms,
                  label: specLabels.bedrooms,
              }
            : null,
        type?.bathrooms
            ? {
                  icon: 'bath' as IconName,
                  value: String(type.bathrooms),
                  label: specLabels.bathrooms,
              }
            : null,
        type?.floors
            ? {
                  icon: 'stairs' as IconName,
                  value: String(type.floors),
                  label: specLabels.floors,
              }
            : null,
        type?.carports
            ? {
                  icon: 'car' as IconName,
                  value: `${type.carports} ${labels.carport_unit}`,
                  label: specLabels.carports,
              }
            : null,
    ].filter((fact) => fact !== null);

    if (facts.length === 0 && materials.length === 0) {
        return null;
    }

    return (
        <section className="flex flex-col gap-5">
            <h2 className="font-display text-[26px] font-medium xl:text-[32px]">
                {title}
            </h2>
            {facts.length > 0 ? (
                <dl className="grid grid-cols-3 gap-3 md:grid-cols-6">
                    {facts.map((fact) => (
                        <div
                            key={fact.label}
                            className="flex flex-col gap-1.5 rounded-2xl bg-white p-4"
                        >
                            <Icon
                                name={fact.icon}
                                className="size-5 text-terracotta"
                            />
                            <dd className="text-lg font-bold">{fact.value}</dd>
                            <dt className="order-last text-xs leading-snug text-caption">
                                {fact.label}
                            </dt>
                        </div>
                    ))}
                </dl>
            ) : null}
            {materials.length > 0 ? (
                <dl className="grid gap-x-8 md:grid-cols-2">
                    {materials.map((row) => (
                        <div
                            key={row.label}
                            className="flex justify-between gap-4 border-b border-line py-3 text-sm"
                        >
                            <dt className="text-caption">{row.label}</dt>
                            <dd className="text-right font-semibold">
                                {row.value}
                            </dd>
                        </div>
                    ))}
                </dl>
            ) : null}
        </section>
    );
}

export function TypeTabs({
    title,
    types,
    selected,
    onSelect,
    specLabels,
}: {
    title: string;
    types: HouseTypeData[];
    selected: HouseTypeData;
    onSelect: (slug: string) => void;
    specLabels: Record<string, string>;
}) {
    const { labels } = usePage().props.site;

    return (
        <section className="flex flex-col gap-5">
            <h2 className="font-display text-[26px] font-medium xl:text-[32px]">
                {title}
            </h2>
            {/* Jumlah tab mengikuti jumlah tipe; satu tipe = tanpa tab. */}
            {types.length > 1 ? (
                <div
                    role="tablist"
                    aria-label={title}
                    className="flex [scrollbar-width:none] gap-2 overflow-x-auto"
                >
                    {types.map((type) => (
                        <button
                            key={type.slug}
                            type="button"
                            role="tab"
                            id={`tab-${type.slug}`}
                            aria-selected={type.slug === selected.slug}
                            aria-controls="tipe-panel"
                            onClick={() => onSelect(type.slug)}
                            className={cn(
                                'inline-flex h-11 shrink-0 items-center rounded-full px-5 text-[15px]',
                                type.slug === selected.slug
                                    ? 'bg-ink font-semibold text-white'
                                    : 'border border-line bg-white text-ink',
                            )}
                        >
                            {labels.type_prefix} {type.name}
                        </button>
                    ))}
                </div>
            ) : null}
            <div
                id="tipe-panel"
                role={types.length > 1 ? 'tabpanel' : undefined}
                aria-labelledby={
                    types.length > 1 ? `tab-${selected.slug}` : undefined
                }
                className="grid gap-5 rounded-card-sm bg-white p-4 md:grid-cols-2 md:p-5"
            >
                <Picture
                    sizes={IMAGE_SIZES.half}
                    image={selected.floorplan}
                    className="aspect-square rounded-2xl md:aspect-auto md:min-h-[300px]"
                />
                <div className="flex flex-col py-1">
                    <h3 className="font-display text-2xl font-semibold">
                        {labels.type_prefix} {selected.name}
                    </h3>
                    {selected.lotSize ? (
                        <p className="text-sm text-body">
                            {specLabels.lot}{' '}
                            {selected.lotSize.replace('×', ' × ')}
                        </p>
                    ) : null}
                    <dl className="mt-4 flex flex-col text-sm">
                        {/* Baris yang datanya kosong tidak ditampilkan. */}
                        {[
                            selected.landArea || selected.buildingArea
                                ? [
                                      `${specLabels.land_area} / ${specLabels.building_area.toLowerCase()}`,
                                      `${selected.landArea ?? '–'} / ${selected.buildingArea ?? '–'} ${labels.area_unit}`,
                                  ]
                                : null,
                            selected.bedrooms || selected.bathrooms
                                ? [
                                      `${specLabels.bedrooms} / ${specLabels.bathrooms.toLowerCase()}`,
                                      `${selected.bedrooms ?? '–'} / ${selected.bathrooms ?? '–'}`,
                                  ]
                                : null,
                            selected.floors
                                ? [
                                      specLabels.floors,
                                      `${selected.floors} ${labels.floors_unit}`,
                                  ]
                                : null,
                            selected.price
                                ? [labels.price_from, selected.price]
                                : null,
                        ]
                            .filter((row) => row !== null)
                            .map(([label, value]) => (
                                <div
                                    key={label}
                                    className="flex justify-between gap-4 border-b border-line py-3"
                                >
                                    <dt className="text-body">{label}</dt>
                                    <dd className="font-bold">{value}</dd>
                                </div>
                            ))}
                    </dl>
                    {selected.unitsAvailable !== null ? (
                        <p className="mt-3 text-[13px] text-body">
                            {specLabels.units_available}:{' '}
                            <strong className="text-terracotta">
                                {selected.unitsAvailable} {labels.unit}
                            </strong>
                        </p>
                    ) : null}
                </div>
            </div>
        </section>
    );
}

export type MarketingData = { name: string; title: string; photo: ImageData };

export type LeadFormData = {
    nameLabel: string;
    namePlaceholder: string;
    whatsappLabel: string;
    whatsappPlaceholder: string;
    submitLabel: string;
    whatsappButtonLabel: string;
    surveyButtonLabel: string;
    surveyUrl: string;
};

/**
 * Kartu marketing + form lead (POST /lead). Tombol Survey membuka form singkat di modal.
 */
export function LeadCard({
    marketing,
    form,
    whatsappUrl,
    legality,
    clusterId,
    houseTypeId,
    clusterName,
    position,
    showButtons = true,
}: {
    marketing: MarketingData;
    form: LeadFormData;
    whatsappUrl: string;
    legality: string | null;
    clusterId: number;
    houseTypeId: number | null;
    clusterName: string;
    position: LeadPosition;
    showButtons?: boolean;
}) {
    const { labels } = usePage().props.site;
    const openSurvey = useLeadModalTrigger({
        clusterId,
        houseTypeId,
        position: 'modal',
    });

    return (
        <div className="flex flex-col gap-3">
            <div className="flex flex-col gap-4 rounded-card-sm bg-white p-5 shadow-[0_18px_40px_-24px_rgba(30,43,36,0.35)] md:p-6">
                <div className="flex items-center gap-3">
                    <Picture
                        sizes={IMAGE_SIZES.avatar}
                        image={marketing.photo}
                        label={labels.gallery_photo}
                        className="size-14 shrink-0 rounded-full p-0! text-[9px]"
                    />
                    <div>
                        <p className="font-bold">{marketing.name}</p>
                        <p className="text-[13px] text-body">
                            {marketing.title}
                        </p>
                    </div>
                </div>
                <LeadForm
                    position={position}
                    clusterId={clusterId}
                    houseTypeId={houseTypeId}
                    labels={{
                        name: form.nameLabel,
                        namePlaceholder: form.namePlaceholder,
                        whatsapp: form.whatsappLabel,
                        whatsappPlaceholder: form.whatsappPlaceholder,
                        submit: form.submitLabel,
                    }}
                />
                {showButtons ? (
                    <div className="grid grid-cols-2 gap-2">
                        <ButtonLink
                            href={whatsappUrl}
                            variant="dark"
                            icon="chat"
                            size="sm"
                            newTab
                            cluster={clusterName}
                            position={position}
                        >
                            {form.whatsappButtonLabel}
                        </ButtonLink>
                        <ButtonLink
                            href={form.surveyUrl}
                            variant="outline"
                            icon="calendar"
                            size="sm"
                            onClick={openSurvey}
                        >
                            {form.surveyButtonLabel}
                        </ButtonLink>
                    </div>
                ) : null}
                {legality && !showButtons ? (
                    <p className="flex items-center gap-2 text-[13px] text-body">
                        <Icon
                            name="shieldCheck"
                            className="size-4 shrink-0 text-terracotta"
                        />
                        {legality}
                    </p>
                ) : null}
            </div>
            {legality && showButtons ? (
                <p className="flex items-center gap-2.5 rounded-2xl bg-sand px-5 py-4 text-[13px] text-body">
                    <Icon
                        name="shieldCheck"
                        className="size-4 shrink-0 text-terracotta"
                    />
                    {legality}
                </p>
            ) : null}
        </div>
    );
}
