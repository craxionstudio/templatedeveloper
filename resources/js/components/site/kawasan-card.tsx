import { usePage } from '@inertiajs/react';
import { Icon } from '@/components/site/icons';
import Picture, { IMAGE_SIZES } from '@/components/site/picture';
import SmartLink from '@/components/site/smart-link';
import { Badge } from '@/components/site/ui';
import type { KawasanCardData } from '@/types/content';

/**
 * Kartu kawasan (tampilan Kawasan di Produk Listing).
 */
export function KawasanCard({
    kawasan,
    eyebrow,
    viewLabel,
}: {
    kawasan: KawasanCardData;
    eyebrow: string;
    viewLabel: string;
}) {
    const { labels } = usePage().props.site;

    return (
        <SmartLink
            href={kawasan.url}
            className="group flex flex-col overflow-hidden rounded-card bg-white text-ink no-underline transition-shadow hover:shadow-[0_12px_32px_-16px_rgba(30,43,36,0.35)]"
        >
            <Picture
                sizes={IMAGE_SIZES.card3}
                image={kawasan.image}
                className="aspect-[16/10] xl:aspect-auto xl:h-[300px]"
            >
                <Badge tone="dark" className="absolute top-3.5 left-3.5 bg-ink">
                    {kawasan.clustersCount} {labels.clusters}
                </Badge>
            </Picture>
            <div className="flex flex-1 flex-col gap-3 p-5 md:p-7">
                <p className="text-[13px] font-semibold tracking-[0.06em] text-terracotta uppercase">
                    {eyebrow}
                </p>
                <h3 className="font-display text-[26px] leading-tight font-semibold group-hover:text-terracotta xl:text-[30px]">
                    {kawasan.name}
                </h3>
                {kawasan.summary ? (
                    <p className="text-[15px] leading-[1.6] text-body">
                        {kawasan.summary}
                    </p>
                ) : null}
                <ul className="flex flex-wrap gap-1.5">
                    {kawasan.clusters.map((name) => (
                        <li
                            key={name}
                            className="rounded-lg border border-[#E4DDCF] bg-ground px-2.5 py-[5px] text-[13px] font-medium"
                        >
                            {name}
                        </li>
                    ))}
                </ul>
                <div className="mt-auto flex items-end justify-between gap-3 border-t border-[#ECE6DA] pt-3.5">
                    <div className="flex flex-col gap-0.5">
                        <span className="text-xs text-caption">
                            {labels.price_from}
                        </span>
                        <span className="text-lg font-bold xl:text-xl">
                            {kawasan.priceFrom}
                        </span>
                    </div>
                    <span className="inline-flex items-center gap-1.5 text-[15px] font-semibold text-terracotta">
                        {viewLabel}
                        <Icon name="arrowRight" className="size-4" />
                    </span>
                </div>
            </div>
        </SmartLink>
    );
}

/**
 * Kartu kawasan ringkas horizontal ("Kawasan lainnya" di Detail Kawasan).
 */
export function KawasanCardCompact({ kawasan }: { kawasan: KawasanCardData }) {
    const { labels } = usePage().props.site;

    return (
        <SmartLink
            href={kawasan.url}
            className="group grid grid-cols-[120px_1fr] overflow-hidden rounded-card bg-white text-ink no-underline md:grid-cols-[220px_1fr]"
        >
            <Picture
                sizes={IMAGE_SIZES.thumb}
                image={kawasan.image}
                className="min-h-[140px] text-[11px]"
            />
            <div className="flex flex-col justify-center gap-2 p-5 md:p-6">
                <span className="text-xs font-semibold text-terracotta">
                    {kawasan.clustersCount} {labels.clusters} ·{' '}
                    {labels.price_from.toLowerCase()} {kawasan.priceFrom}
                </span>
                <h3 className="font-display text-xl leading-tight font-semibold group-hover:text-terracotta xl:text-2xl">
                    {kawasan.name}
                </h3>
                {kawasan.summary ? (
                    <p className="line-clamp-2 text-sm leading-[1.55] text-body">
                        {kawasan.summary}
                    </p>
                ) : null}
            </div>
        </SmartLink>
    );
}
