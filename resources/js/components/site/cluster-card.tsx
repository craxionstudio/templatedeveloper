import { usePage } from '@inertiajs/react';
import { Icon } from '@/components/site/icons';
import Picture from '@/components/site/picture';
import SmartLink from '@/components/site/smart-link';
import { Badge } from '@/components/site/ui';
import { cn } from '@/lib/utils';
import type { ClusterCardData } from '@/types/content';

/**
 * Kartu cluster — satu komponen untuk Listing, Detail Kawasan, Beranda, dan "Listing lainnya".
 */
export default function ClusterCard({
    cluster,
    headingLevel: Heading = 'h3',
    className,
}: {
    cluster: ClusterCardData;
    headingLevel?: 'h2' | 'h3';
    className?: string;
}) {
    const { labels } = usePage().props.site;

    return (
        <SmartLink
            href={cluster.url}
            className={cn(
                'group flex flex-col overflow-hidden rounded-card bg-white text-ink no-underline transition-shadow hover:shadow-[0_12px_32px_-16px_rgba(30,43,36,0.35)]',
                className,
            )}
        >
            <Picture
                image={cluster.image}
                className="aspect-[16/10] xl:aspect-auto xl:h-60"
            >
                {cluster.badge ? (
                    <Badge className="absolute top-3.5 left-3.5">
                        {cluster.badge}
                    </Badge>
                ) : null}
                {cluster.typesCount > 0 ? (
                    <Badge
                        tone="dark"
                        className="absolute right-3.5 bottom-3.5"
                    >
                        {cluster.typesCount} {labels.house_types}
                    </Badge>
                ) : null}
            </Picture>

            <div className="flex flex-1 flex-col gap-3 p-[18px] md:p-6">
                <span className="flex items-center gap-1.5 text-[13px] text-caption">
                    {cluster.kawasan ? (
                        <>
                            <Icon name="grid" className="size-[15px]" />
                            {labels.kawasan}{' '}
                            <strong className="font-semibold text-ink">
                                {cluster.kawasan.name}
                            </strong>
                        </>
                    ) : (
                        <>
                            <Icon name="building" className="size-[15px]" />
                            {labels.standalone_cluster}
                        </>
                    )}
                </span>

                <div className="flex flex-col gap-0.5">
                    <Heading className="font-display text-[22px] leading-tight font-semibold group-hover:text-terracotta xl:text-[25px]">
                        {labels.cluster_prefix} {cluster.name}
                    </Heading>
                    {cluster.buildingType ? (
                        <span className="text-sm text-body">
                            {cluster.buildingType}
                        </span>
                    ) : null}
                </div>

                {cluster.types.length > 0 ? (
                    <ul className="flex flex-wrap gap-1.5">
                        {cluster.types.map((type) => (
                            <li
                                key={type}
                                className="rounded-lg border border-[#E4DDCF] bg-ground px-2.5 py-[5px] text-[13px] font-semibold"
                            >
                                {type}
                            </li>
                        ))}
                    </ul>
                ) : null}

                <div className="flex flex-wrap gap-x-4 gap-y-1.5 text-sm">
                    {cluster.landArea ? (
                        <span className="inline-flex items-center gap-1.5">
                            <Icon
                                name="land"
                                className="size-[17px] text-caption"
                            />
                            {labels.land_area_short} {cluster.landArea}{' '}
                            {labels.area_unit}
                        </span>
                    ) : null}
                    {cluster.bedrooms ? (
                        <span className="inline-flex items-center gap-1.5">
                            <Icon
                                name="bed"
                                className="size-[17px] text-caption"
                            />
                            {cluster.bedrooms} {labels.bedrooms_short}
                        </span>
                    ) : null}
                </div>

                <div className="mt-auto flex items-end justify-between gap-3 border-t border-[#ECE6DA] pt-3.5">
                    <div className="flex flex-col gap-0.5">
                        <span className="text-xs text-caption">
                            {labels.price}
                        </span>
                        <span className="text-lg font-bold xl:text-xl">
                            {cluster.price}
                        </span>
                    </div>
                    {cluster.installment ? (
                        <span className="text-right text-xs text-body">
                            {labels.installment_from}
                            <br />
                            <strong className="text-[13px] text-ink">
                                {cluster.installment}
                                {labels.per_month}
                            </strong>
                        </span>
                    ) : null}
                </div>
            </div>
        </SmartLink>
    );
}
