import { usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { ContentIcon, Icon } from '@/components/site/icons';
import type { IconName } from '@/components/site/icons';
import Picture from '@/components/site/picture';
import SmartLink from '@/components/site/smart-link';
import { ButtonLink } from '@/components/site/ui';
import { cn } from '@/lib/utils';
import type { ImageData } from '@/types/content';

export type HouseTypeData = {
    slug: string;
    name: string;
    lotSize: string | null;
    landArea: number | null;
    buildingArea: number | null;
    bedrooms: string;
    bathrooms: number;
    floors: number;
    carports: number;
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

export function PriceBox({
    type,
    pricing,
    bookingFee,
}: {
    type: HouseTypeData;
    pricing: PricingData;
    bookingFee: string | null;
}) {
    return (
        <div className="overflow-hidden rounded-card-sm bg-white">
            <div className="grid grid-cols-2 md:grid-cols-3">
                <div className="col-span-2 flex items-end justify-between gap-4 border-b border-[#ECE6DA] p-5 md:col-span-1 md:flex-col md:items-start md:justify-start md:border-r md:border-b-0 md:p-6">
                    <div>
                        <p className="text-[13px] text-caption xl:text-sm">
                            {pricing.priceLabel}
                        </p>
                        <p className="font-display text-[30px] leading-tight font-semibold">
                            {type.price}
                        </p>
                    </div>
                    <p className="text-right text-xs text-caption md:text-left">
                        {pricing.priceNote}
                    </p>
                </div>
                <div className="border-r border-[#ECE6DA] p-5 md:p-6">
                    <p className="text-[13px] text-caption xl:text-sm">
                        {pricing.installmentLabel}
                    </p>
                    <p className="font-display text-xl font-semibold xl:text-[26px]">
                        {type.installment}
                        <span className="text-base">{pricing.perMonth}</span>
                    </p>
                    <p className="text-xs text-caption">
                        {pricing.installmentNote}
                    </p>
                </div>
                <div className="p-5 md:p-6">
                    <p className="text-[13px] text-caption xl:text-sm">
                        {pricing.bookingFeeLabel}
                    </p>
                    <p className="font-display text-xl font-semibold xl:text-[26px]">
                        {bookingFee ?? '–'}
                    </p>
                    <p className="text-xs text-caption">
                        {pricing.bookingFeeNote}
                    </p>
                </div>
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
    type: HouseTypeData;
    specLabels: Record<string, string>;
    materials: { label: string; value: string }[];
}) {
    const { labels } = usePage().props.site;
    const facts: { icon: IconName; value: string; label: string }[] = [
        {
            icon: 'land',
            value: `${type.landArea ?? '–'} ${labels.area_unit}`,
            label: specLabels.land_area,
        },
        {
            icon: 'building',
            value: `${type.buildingArea ?? '–'} ${labels.area_unit}`,
            label: specLabels.building_area,
        },
        { icon: 'bed', value: type.bedrooms, label: specLabels.bedrooms },
        {
            icon: 'bath',
            value: String(type.bathrooms),
            label: specLabels.bathrooms,
        },
        {
            icon: 'stairs',
            value: String(type.floors),
            label: specLabels.floors,
        },
        {
            icon: 'car',
            value: `${type.carports} ${labels.carport_unit}`,
            label: specLabels.carports,
        },
    ];

    return (
        <section className="flex flex-col gap-5">
            <h2 className="font-display text-[26px] font-medium xl:text-[32px]">
                {title}
            </h2>
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
                        {[
                            [
                                `${specLabels.land_area} / ${specLabels.building_area.toLowerCase()}`,
                                `${selected.landArea ?? '–'} / ${selected.buildingArea ?? '–'} ${labels.area_unit}`,
                            ],
                            [
                                `${specLabels.bedrooms} / ${specLabels.bathrooms.toLowerCase()}`,
                                `${selected.bedrooms} / ${selected.bathrooms}`,
                            ],
                            [
                                specLabels.floors,
                                `${selected.floors} ${labels.floors_unit}`,
                            ],
                            [labels.price_from, selected.price ?? '–'],
                        ].map(([label, value]) => (
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
 * Kartu marketing + form lead. Penyimpanan lead, anti-spam, dan UTM di Milestone 4.
 */
export function LeadCard({
    marketing,
    form,
    whatsappUrl,
    legality,
    showButtons = true,
}: {
    marketing: MarketingData;
    form: LeadFormData;
    whatsappUrl: string;
    legality: string | null;
    showButtons?: boolean;
}) {
    const { labels } = usePage().props.site;
    const input =
        'h-12 w-full rounded-xl border-[1.5px] border-[#CFC7B6] bg-white px-3.5 text-[15px] placeholder:text-caption focus:border-ink focus:outline-none';
    const onSubmit = (e: FormEvent) => e.preventDefault();

    return (
        <div className="flex flex-col gap-3">
            <div className="flex flex-col gap-4 rounded-card-sm bg-white p-5 shadow-[0_18px_40px_-24px_rgba(30,43,36,0.35)] md:p-6">
                <div className="flex items-center gap-3">
                    <Picture
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
                <form onSubmit={onSubmit} className="flex flex-col gap-3">
                    <label className="flex flex-col gap-1.5 text-sm font-semibold">
                        {form.nameLabel}
                        <input
                            type="text"
                            name="name"
                            autoComplete="name"
                            placeholder={form.namePlaceholder}
                            className={input}
                        />
                    </label>
                    <label className="flex flex-col gap-1.5 text-sm font-semibold">
                        {form.whatsappLabel}
                        <input
                            type="tel"
                            name="whatsapp"
                            autoComplete="tel"
                            inputMode="tel"
                            placeholder={form.whatsappPlaceholder}
                            className={input}
                        />
                    </label>
                    <button
                        type="submit"
                        className="mt-1 flex h-[52px] items-center justify-center rounded-full bg-terracotta font-semibold text-white hover:bg-terracotta-hover"
                    >
                        {form.submitLabel}
                    </button>
                </form>
                {showButtons ? (
                    <div className="grid grid-cols-2 gap-2">
                        <ButtonLink
                            href={whatsappUrl}
                            variant="dark"
                            icon="chat"
                            size="sm"
                            newTab
                        >
                            {form.whatsappButtonLabel}
                        </ButtonLink>
                        <ButtonLink
                            href={form.surveyUrl}
                            variant="outline"
                            icon="calendar"
                            size="sm"
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
