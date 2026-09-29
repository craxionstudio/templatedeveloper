import { ContentIcon } from '@/components/site/icons';
import SmartLink from '@/components/site/smart-link';
import { Badge } from '@/components/site/ui';

export type PromoCardData = {
    id: number;
    label: string | null;
    title: string;
    description: string | null;
    period: string | null;
    items: { icon?: string; title: string; description?: string }[];
    /** Detail Kawasan: cluster tempat promo berlaku (link ke Detail Rumah). */
    clusters: { name: string; url: string }[];
};

/**
 * Daftar promo aktif (Detail Rumah & Detail Kawasan). Tidak dirender kalau tidak ada promo.
 */
export default function PromoSection({
    title,
    eyebrow,
    promos,
    headingLevel: Heading = 'h2',
}: {
    title: string;
    eyebrow?: string;
    promos: PromoCardData[];
    headingLevel?: 'h2' | 'h3';
}) {
    if (promos.length === 0) {
        return null;
    }

    return (
        <section className="rounded-card-sm bg-[#F6E3D8] p-5 md:p-7">
            {eyebrow ? (
                <p className="text-[13px] font-semibold tracking-[0.08em] text-[#8A3A17] uppercase">
                    {eyebrow}
                </p>
            ) : null}
            <Heading className="font-display text-[22px] font-medium text-[#6E2E14] xl:text-2xl">
                {title}
            </Heading>
            <ul className="mt-5 grid gap-4 md:grid-cols-2">
                {promos.map((promo) => (
                    <li
                        key={promo.id}
                        className="flex flex-col gap-3 rounded-2xl bg-white p-4 md:p-5"
                    >
                        <div className="flex flex-wrap items-center gap-2">
                            {promo.label ? <Badge>{promo.label}</Badge> : null}
                            {promo.clusters.map((cluster) => (
                                <SmartLink
                                    key={cluster.url}
                                    href={cluster.url}
                                    className="text-[13px] font-semibold text-terracotta underline underline-offset-2 hover:text-terracotta-hover"
                                >
                                    {cluster.name}
                                </SmartLink>
                            ))}
                        </div>
                        <p className="font-semibold">{promo.title}</p>
                        {promo.description ? (
                            <p className="text-[14px] leading-snug text-body">
                                {promo.description}
                            </p>
                        ) : null}
                        {promo.items.length > 0 ? (
                            <ul className="flex flex-col gap-3">
                                {promo.items.map((item) => (
                                    <li key={item.title} className="flex gap-3">
                                        <span className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-[#F6E3D8] text-terracotta">
                                            <ContentIcon
                                                name={item.icon}
                                                className="size-[18px]"
                                            />
                                        </span>
                                        <div>
                                            <p className="text-[14px] font-semibold">
                                                {item.title}
                                            </p>
                                            {item.description ? (
                                                <p className="text-[13px] leading-snug text-body">
                                                    {item.description}
                                                </p>
                                            ) : null}
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        ) : null}
                        {promo.period ? (
                            <p className="mt-auto text-[13px] font-semibold text-[#8A3A17]">
                                {promo.period}
                            </p>
                        ) : null}
                    </li>
                ))}
            </ul>
        </section>
    );
}
