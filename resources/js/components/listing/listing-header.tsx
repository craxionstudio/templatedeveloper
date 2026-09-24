import Breadcrumbs from '@/components/site/breadcrumbs';
import { Icon } from '@/components/site/icons';
import Picture from '@/components/site/picture';
import SmartLink from '@/components/site/smart-link';
import { cn } from '@/lib/utils';
import type { Crumb, ImageData, Stat } from '@/types/content';

export type ListingHeaderData = {
    eyebrow: string;
    title: string;
    description: string;
    image: ImageData;
    imageMobile: ImageData | null;
    stats: Stat[];
};

export type ToggleData = {
    active: 'cluster' | 'kawasan';
    items: {
        key: 'cluster' | 'kawasan';
        label: string;
        url: string;
        count: number;
    }[];
};

/**
 * Header Produk Listing (sama untuk tampilan Cluster & Kawasan) + breadcrumb.
 */
export function ListingHeader({
    header,
    breadcrumbs,
    headingLevel: Heading = 'h1',
}: {
    header: ListingHeaderData;
    breadcrumbs: Crumb[];
    headingLevel?: 'h1' | 'h2';
}) {
    const stats = (
        <dl className="flex justify-between gap-6 rounded-card-sm bg-white px-4 py-4 md:gap-10 md:px-8 md:py-6 xl:bg-ink/72 xl:text-white">
            {header.stats.map((stat) => (
                <div key={stat.label} className="flex flex-col-reverse gap-0.5">
                    <dt className="text-[13px] text-caption xl:text-sm xl:text-mist">
                        {stat.label}
                    </dt>
                    <dd className="font-display text-xl font-semibold xl:text-[30px]">
                        {stat.value}
                    </dd>
                </div>
            ))}
        </dl>
    );

    return (
        <section className="container-site flex flex-col gap-4 pt-4 xl:gap-6 xl:pt-6">
            <Breadcrumbs items={breadcrumbs} />
            <div className="grid">
                <Picture
                    image={header.image}
                    tone="dark"
                    priority
                    labelCorner
                    className="col-start-1 row-start-1 min-h-[320px] rounded-card xl:min-h-[440px] xl:rounded-section"
                />
                <div className="relative col-start-1 row-start-1 flex flex-col gap-10 self-end p-6 xl:flex-row xl:items-end xl:justify-between xl:gap-12 xl:px-16 xl:py-14">
                    <div className="flex max-w-[640px] flex-col gap-3 xl:gap-4">
                        <p className="flex items-center gap-2 text-[13px] font-semibold tracking-[0.06em] text-peach uppercase xl:text-sm">
                            <Icon name="pin" className="size-[18px]" />
                            {header.eyebrow}
                        </p>
                        <Heading className="font-display text-[34px] leading-[1.05] font-medium text-white xl:text-6xl">
                            {header.title}
                        </Heading>
                        <p className="text-[15px] leading-[1.6] text-[#DCE5DF] xl:text-[17px]">
                            {header.description}
                        </p>
                    </div>
                    <div className="hidden xl:block">{stats}</div>
                </div>
            </div>
            <div className="xl:hidden">{stats}</div>
        </section>
    );
}

/**
 * Toggle Cluster | Kawasan: dua link biasa ke dua URL (bukan tab JavaScript).
 */
export function ViewToggle({
    toggle,
    label,
}: {
    toggle: ToggleData;
    label: string;
}) {
    return (
        <nav
            aria-label={label}
            className="flex w-full gap-1 rounded-full border border-line bg-white p-1 md:w-[380px]"
        >
            {toggle.items.map((item) => {
                const active = item.key === toggle.active;

                return (
                    <SmartLink
                        key={item.key}
                        href={item.url}
                        aria-current={active ? 'page' : undefined}
                        className={cn(
                            'flex h-12 flex-1 items-center justify-center gap-2 rounded-full px-6 text-[15px] no-underline transition-colors',
                            active
                                ? 'bg-ink font-semibold text-white'
                                : 'font-medium text-ink hover:bg-ground',
                        )}
                    >
                        {item.label}
                        <span
                            className={cn(
                                'rounded-full px-2 py-0.5 text-xs',
                                active ? 'bg-white/20' : 'bg-sand',
                            )}
                        >
                            {item.count}
                        </span>
                    </SmartLink>
                );
            })}
        </nav>
    );
}
