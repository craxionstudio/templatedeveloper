import { router, usePage } from '@inertiajs/react';
import Breadcrumbs from '@/components/site/breadcrumbs';
import CtaSection from '@/components/site/cta-section';
import FacilityCard from '@/components/site/facility-card';
import { ContentIcon } from '@/components/site/icons';
import PageHead from '@/components/site/page-head';
import Picture, { IMAGE_SIZES } from '@/components/site/picture';
import SmartLink from '@/components/site/smart-link';
import { Eyebrow } from '@/components/site/ui';
import { cn } from '@/lib/utils';
import type {
    Crumb,
    CtaData,
    FacilityCardData,
    ImageData,
    Stat,
} from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    breadcrumbs: Crumb[];
    header: {
        eyebrow: string;
        title: string;
        description: string;
        stats: Stat[];
        images: ImageData[];
    };
    filters: {
        allLabel: string;
        categories: { slug: string; name: string; icon: string | null }[];
        activeCategory: string | null;
        kawasan: {
            label: string;
            allLabel: string;
            options: { value: string; label: string }[];
            active: string | null;
        } | null;
    };
    facilities: FacilityCardData[];
    cta: CtaData;
};

export default function FasilitasIndex({
    meta,
    breadcrumbs,
    header,
    filters,
    facilities,
    cta,
}: Props) {
    const { labels } = usePage().props.site;
    const query = (next: {
        kategori?: string | null;
        kawasan?: string | null;
    }) => {
        const params = new URLSearchParams();
        const kategori =
            next.kategori === undefined
                ? filters.activeCategory
                : next.kategori;
        const kawasan =
            next.kawasan === undefined ? filters.kawasan?.active : next.kawasan;
        if (kategori) params.set('kategori', kategori);
        if (kawasan) params.set('kawasan', kawasan);
        const qs = params.toString();

        return `/fasilitas${qs ? `?${qs}` : ''}`;
    };

    const chip = (active: boolean) =>
        cn(
            'inline-flex h-11 shrink-0 items-center gap-2 rounded-full px-5 text-[15px] no-underline',
            active
                ? 'bg-ink font-semibold text-white'
                : 'border border-line bg-white text-ink hover:border-ink',
        );

    const [main, ...side] = header.images;

    return (
        <>
            <PageHead meta={meta} />
            <section className="container-site flex flex-col gap-5 pt-4 xl:gap-8 xl:pt-6">
                <Breadcrumbs items={breadcrumbs} />
                <div className="grid gap-5 xl:grid-cols-2 xl:items-end xl:gap-20">
                    <div className="flex flex-col gap-3 xl:gap-4">
                        <Eyebrow>{header.eyebrow}</Eyebrow>
                        <h1 className="font-display text-[34px] leading-[1.1] font-medium xl:text-6xl">
                            {header.title}
                        </h1>
                    </div>
                    <div className="flex flex-col gap-5">
                        <p className="text-base leading-[1.7] text-body xl:text-[17px]">
                            {header.description}
                        </p>
                        <dl className="flex gap-7">
                            {header.stats.map((stat) => (
                                <div
                                    key={stat.label}
                                    className="flex flex-col-reverse"
                                >
                                    <dt className="text-[13px] text-caption">
                                        {stat.label}
                                    </dt>
                                    <dd className="font-display text-2xl font-semibold xl:text-[30px]">
                                        {stat.value}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    </div>
                </div>
                {main ? (
                    <div className="grid grid-cols-2 gap-3 xl:grid-cols-[2fr_1fr] xl:grid-rows-2 xl:gap-4">
                        <Picture
                            sizes={IMAGE_SIZES.half}
                            image={main}
                            priority
                            className="col-span-2 h-[240px] rounded-card xl:col-span-1 xl:row-span-2 xl:h-[440px]"
                        />
                        {side.map((image, index) => (
                            <Picture
                                sizes={IMAGE_SIZES.quarter}
                                key={index}
                                image={image}
                                className="h-[130px] rounded-card-sm xl:h-auto xl:rounded-card"
                            />
                        ))}
                    </div>
                ) : null}
            </section>

            <section className="container-site flex flex-col gap-6 pt-10 pb-14 xl:pt-14 xl:pb-[120px]">
                <div className="flex flex-col gap-4 border-b border-line pb-5 xl:flex-row xl:items-center xl:justify-between">
                    <nav
                        aria-label={filters.allLabel}
                        className="-mx-5 flex [scrollbar-width:none] gap-2 overflow-x-auto px-5 xl:mx-0 xl:flex-wrap xl:px-0"
                    >
                        <SmartLink
                            href={query({ kategori: null })}
                            className={chip(!filters.activeCategory)}
                            aria-current={
                                !filters.activeCategory ? 'page' : undefined
                            }
                        >
                            {filters.allLabel}
                        </SmartLink>
                        {filters.categories.map((category) => (
                            <SmartLink
                                key={category.slug}
                                href={query({ kategori: category.slug })}
                                className={chip(
                                    filters.activeCategory === category.slug,
                                )}
                                aria-current={
                                    filters.activeCategory === category.slug
                                        ? 'page'
                                        : undefined
                                }
                            >
                                <ContentIcon
                                    name={category.icon}
                                    className="size-4"
                                />
                                {category.name}
                            </SmartLink>
                        ))}
                    </nav>
                    {filters.kawasan ? (
                        <label className="flex items-center gap-2.5 text-sm text-caption">
                            {filters.kawasan.label}
                            <select
                                value={filters.kawasan.active ?? ''}
                                onChange={(e) =>
                                    router.get(
                                        query({
                                            kawasan: e.target.value || null,
                                        }),
                                        {},
                                        { preserveScroll: true },
                                    )
                                }
                                className="h-11 rounded-xl border-[1.5px] border-[#CFC7B6] bg-white px-3 text-[15px] text-ink"
                            >
                                <option value="">
                                    {filters.kawasan.allLabel}
                                </option>
                                {filters.kawasan.options.map((option) => (
                                    <option
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </label>
                    ) : null}
                </div>
                {/* Judul section untuk urutan heading (h1 → h2 → h3 kartu). */}
                <h2 className="sr-only">{labels.facility_list}</h2>
                <div className="grid gap-3 md:grid-cols-2 md:gap-6 xl:grid-cols-3">
                    {facilities.map((facility) => (
                        <FacilityCard key={facility.id} facility={facility} />
                    ))}
                </div>
            </section>

            <CtaSection cta={cta} />
        </>
    );
}
