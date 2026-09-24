import ArticleCard from '@/components/site/article-card';
import ClusterCard from '@/components/site/cluster-card';
import { ContentIcon } from '@/components/site/icons';
import Picture from '@/components/site/picture';
import {
    ArrowLink,
    Badge,
    ButtonLink,
    Eyebrow,
    SectionHeading,
} from '@/components/site/ui';
import { cn } from '@/lib/utils';
import type {
    ArticleCardData,
    ClusterCardData,
    FacilityCardData,
    ImageData,
} from '@/types/content';

const carousel =
    'flex snap-x snap-mandatory scroll-px-5 gap-4 overflow-x-auto pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden -mx-5 px-5 md:mx-0 md:scroll-px-0 md:px-0';

export type ListingData = {
    eyebrow: string;
    title: string;
    items: ClusterCardData[];
    button: { label: string; url: string };
};

export function ListingSection({ listing }: { listing: ListingData }) {
    return (
        <section className="bg-sand py-14 xl:py-[120px]">
            <div className="container-site flex flex-col gap-8 xl:gap-12">
                <SectionHeading
                    eyebrow={listing.eyebrow}
                    title={listing.title}
                    action={
                        <ButtonLink
                            href={listing.button.url}
                            variant="dark"
                            size="sm"
                        >
                            {listing.button.label}
                        </ButtonLink>
                    }
                />
                {/* Mobile: carousel scroll-snap; desktop: grid 4 kolom. */}
                <div
                    className={cn(
                        carousel,
                        'xl:grid xl:grid-cols-4 xl:gap-6 xl:overflow-visible xl:pb-0',
                    )}
                >
                    {listing.items.map((cluster) => (
                        <ClusterCard
                            key={cluster.id}
                            cluster={cluster}
                            className="w-[82%] shrink-0 snap-start md:w-[46%] xl:w-auto"
                        />
                    ))}
                </div>
                <div className="md:hidden">
                    <ButtonLink
                        href={listing.button.url}
                        variant="dark"
                        size="lg"
                        className="w-full"
                    >
                        {listing.button.label}
                    </ButtonLink>
                </div>
            </div>
        </section>
    );
}

export type RegionData = {
    eyebrow: string;
    title: string;
    description: string;
    map: ImageData;
    mapEmbedUrl: string | null;
    badge: { label: string; value: string | null } | null;
    points: {
        icon?: string;
        title: string;
        description?: string;
        distance?: string;
    }[];
};

export function RegionSection({ region }: { region: RegionData }) {
    return (
        <section className="container-site grid gap-8 py-14 xl:grid-cols-2 xl:items-center xl:gap-20 xl:py-[120px]">
            <div className="flex flex-col gap-5 xl:order-2">
                <Eyebrow>{region.eyebrow}</Eyebrow>
                <h2 className="font-display text-[30px] leading-[1.15] font-medium xl:text-[44px]">
                    {region.title}
                </h2>
                <p className="text-base leading-[1.7] text-body xl:text-[17px]">
                    {region.description}
                </p>
                <ul className="mt-2 hidden flex-col xl:flex">
                    <RegionPoints points={region.points} />
                </ul>
            </div>
            <Picture
                image={region.map}
                className="aspect-[4/3] rounded-card xl:order-1 xl:aspect-auto xl:h-[520px] xl:rounded-section"
            >
                {region.badge ? (
                    <div className="absolute right-4 bottom-4 rounded-2xl bg-white px-4 py-3 text-left tracking-normal normal-case shadow-lg xl:right-6 xl:bottom-6 xl:px-6 xl:py-4">
                        <p className="text-xs font-normal text-caption">
                            {region.badge.label}
                        </p>
                        {region.badge.value ? (
                            <p className="font-display text-lg font-semibold text-ink xl:text-2xl">
                                {region.badge.value}
                            </p>
                        ) : null}
                    </div>
                ) : null}
            </Picture>
            <ul className="flex flex-col xl:hidden">
                <RegionPoints points={region.points} />
            </ul>
        </section>
    );
}

function RegionPoints({ points }: { points: RegionData['points'] }) {
    return (
        <>
            {points.map((point) => (
                <li
                    key={point.title}
                    className="flex gap-4 border-b border-line py-5 first:pt-0 xl:gap-5"
                >
                    <span className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white text-terracotta">
                        <ContentIcon name={point.icon} className="size-5" />
                    </span>
                    <div className="flex flex-col gap-1">
                        <h3 className="text-base font-semibold xl:text-lg">
                            {point.title}
                        </h3>
                        {point.description ? (
                            <p className="text-sm leading-[1.55] text-body xl:text-[15px]">
                                {point.description}
                            </p>
                        ) : null}
                    </div>
                </li>
            ))}
        </>
    );
}

export type FacilitiesData = {
    eyebrow: string;
    title: string;
    items: FacilityCardData[];
    link: { label: string; url: string };
};

export function FacilitiesSection({
    facilities,
}: {
    facilities: FacilitiesData;
}) {
    return (
        <section className="bg-forest py-14 text-ground xl:py-[120px]">
            <div className="container-site flex flex-col gap-8 xl:gap-12">
                <SectionHeading
                    tone="dark"
                    eyebrow={facilities.eyebrow}
                    title={facilities.title}
                    action={
                        <ArrowLink href={facilities.link.url} tone="dark">
                            {facilities.link.label}
                        </ArrowLink>
                    }
                />
                <div
                    className={cn(
                        carousel,
                        'xl:grid xl:grid-cols-4 xl:gap-6 xl:overflow-visible',
                    )}
                >
                    {facilities.items.map((facility) => (
                        <article
                            key={facility.id}
                            className="flex w-[72%] shrink-0 snap-start flex-col gap-4 md:w-[40%] xl:w-auto"
                        >
                            <Picture
                                image={facility.image}
                                tone="dark"
                                className="aspect-[4/3] rounded-card-sm xl:aspect-auto xl:h-[260px]"
                            />
                            <div className="flex flex-col gap-2">
                                <h3 className="text-lg font-semibold xl:text-xl">
                                    {facility.name}
                                </h3>
                                {facility.description ? (
                                    <p className="text-sm leading-[1.6] text-mist">
                                        {facility.description}
                                    </p>
                                ) : null}
                            </div>
                        </article>
                    ))}
                </div>
                <div className="md:hidden">
                    <ArrowLink href={facilities.link.url} tone="dark">
                        {facilities.link.label}
                    </ArrowLink>
                </div>
            </div>
        </section>
    );
}

export type DevelopmentsData = {
    eyebrow: string;
    title: string;
    description: string;
    disclaimer: string;
    items: {
        id: number;
        target: string;
        title: string;
        description: string | null;
        status: string;
        statusLabel: string;
        image: ImageData;
    }[];
};

const statusTone = {
    beroperasi: 'success',
    konstruksi: 'warning',
    perencanaan: 'warning',
} as const;

export function DevelopmentsSection({
    developments,
}: {
    developments: DevelopmentsData;
}) {
    return (
        <section className="container-site flex flex-col gap-8 py-14 xl:gap-14 xl:py-[120px]">
            <div className="grid gap-4 xl:grid-cols-2 xl:items-end xl:gap-20">
                <div className="flex flex-col gap-4">
                    <Eyebrow>{developments.eyebrow}</Eyebrow>
                    <h2 className="font-display text-[30px] leading-[1.15] font-medium xl:text-[44px]">
                        {developments.title}
                    </h2>
                </div>
                <p className="text-[15px] leading-[1.7] text-body xl:text-base">
                    {developments.description} {developments.disclaimer}
                </p>
            </div>
            <ol className="relative flex flex-col gap-8 border-l-2 border-line pl-7 xl:grid xl:grid-cols-4 xl:gap-8 xl:border-t-2 xl:border-l-0 xl:pt-0 xl:pl-0">
                {developments.items.map((item, index) => (
                    <li
                        key={item.id}
                        className="relative flex flex-col gap-3 xl:pt-0"
                    >
                        <div className="flex items-center gap-3 xl:-mt-[15px]">
                            <span
                                aria-hidden="true"
                                className={cn(
                                    'absolute -left-[37px] size-4 rounded-full border-[3px] border-terracotta bg-ground xl:static',
                                    index === 0 && 'bg-terracotta',
                                )}
                            />
                            <span className="font-display text-[26px] leading-none font-semibold xl:bg-ground xl:pr-3 xl:text-[28px]">
                                {item.target}
                            </span>
                            <span className="xl:hidden">
                                <Badge
                                    tone={
                                        statusTone[
                                            item.status as keyof typeof statusTone
                                        ] ?? 'muted'
                                    }
                                >
                                    {item.statusLabel}
                                </Badge>
                            </span>
                        </div>
                        <div className="hidden xl:block">
                            <Picture
                                image={item.image}
                                className="aspect-[3/2] rounded-card-sm text-[11px]"
                            />
                        </div>
                        <span className="hidden xl:block">
                            <Badge
                                tone={
                                    statusTone[
                                        item.status as keyof typeof statusTone
                                    ] ?? 'muted'
                                }
                            >
                                {item.statusLabel}
                            </Badge>
                        </span>
                        <h3 className="text-lg font-semibold">{item.title}</h3>
                        {item.description ? (
                            <p className="text-sm leading-[1.6] text-body">
                                {item.description}
                            </p>
                        ) : null}
                    </li>
                ))}
            </ol>
        </section>
    );
}

export type ArticlesData = {
    eyebrow: string;
    title: string;
    main: ArticleCardData;
    items: ArticleCardData[];
    link: { label: string; url: string };
};

export function ArticlesSection({ articles }: { articles: ArticlesData }) {
    return (
        <section className="bg-sand py-14 xl:py-[120px]">
            <div className="container-site flex flex-col gap-8 xl:gap-12">
                <SectionHeading
                    eyebrow={articles.eyebrow}
                    title={articles.title}
                    action={
                        <ArrowLink href={articles.link.url}>
                            {articles.link.label}
                        </ArrowLink>
                    }
                />
                <div className="grid gap-6 xl:grid-cols-2 xl:gap-12">
                    <ArticleCard
                        article={articles.main}
                        variant="compact"
                        className="border-b border-line pb-6 xl:border-0 xl:pb-0 [&_h3]:xl:text-[28px]"
                    />
                    <div className="flex flex-col gap-6">
                        {articles.items.map((article) => (
                            <ArticleCard
                                key={article.id}
                                article={article}
                                variant="row"
                                showReading={false}
                            />
                        ))}
                    </div>
                </div>
                <div className="md:hidden">
                    <ArrowLink href={articles.link.url}>
                        {articles.link.label}
                    </ArrowLink>
                </div>
            </div>
        </section>
    );
}
