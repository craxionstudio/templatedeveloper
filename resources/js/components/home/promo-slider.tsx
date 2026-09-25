import { usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { Icon } from '@/components/site/icons';
import Picture, { IMAGE_SIZES } from '@/components/site/picture';
import { ButtonLink } from '@/components/site/ui';
import { cn } from '@/lib/utils';
import type { ImageData } from '@/types/content';

export type PromoData = {
    autoplay: boolean;
    items: {
        id: number;
        label: string | null;
        title: string;
        description: string | null;
        cta: { label: string; url: string } | null;
        image: ImageData;
        imageMobile: ImageData | null;
    }[];
};

/**
 * Banner promo: carousel CSS scroll-snap (tanpa library). Semua slide ada di HTML SSR.
 */
export default function PromoSlider({ promos }: { promos: PromoData }) {
    const { labels } = usePage().props.site;
    const track = useRef<HTMLDivElement>(null);
    const [active, setActive] = useState(0);
    const count = promos.items.length;

    const goTo = (index: number) => {
        const el = track.current;

        if (!el) {
            return;
        }

        const next = (index + count) % count;
        el.scrollTo({ left: next * el.clientWidth, behavior: 'smooth' });
    };

    useEffect(() => {
        const el = track.current;

        if (!el) {
            return;
        }

        const onScroll = () =>
            setActive(Math.round(el.scrollLeft / el.clientWidth));
        el.addEventListener('scroll', onScroll, { passive: true });

        return () => el.removeEventListener('scroll', onScroll);
    }, []);

    useEffect(() => {
        if (
            !promos.autoplay ||
            count < 2 ||
            window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            return;
        }

        const id = window.setInterval(() => goTo(active + 1), 6000);

        return () => window.clearInterval(id);
    });

    return (
        <section
            aria-roledescription="carousel"
            className="container-site pb-14 xl:pb-[120px]"
        >
            <div
                ref={track}
                className="flex snap-x snap-mandatory [scrollbar-width:none] overflow-x-auto rounded-card xl:rounded-section [&::-webkit-scrollbar]:hidden"
            >
                {promos.items.map((promo, index) => (
                    <article
                        key={promo.id}
                        aria-roledescription={labels.slide}
                        aria-label={`${index + 1} / ${count}`}
                        className="grid w-full shrink-0 snap-start overflow-hidden bg-[#1F2A24] text-ground xl:grid-cols-2"
                    >
                        <div className="order-2 flex flex-col items-start justify-center gap-4 p-6 xl:order-1 xl:gap-5 xl:p-16">
                            {promo.label ? (
                                <span className="inline-flex items-center gap-2 rounded-full bg-terracotta px-3.5 py-1.5 text-[13px] font-semibold text-white">
                                    <Icon name="gift" className="size-4" />
                                    {promo.label}
                                </span>
                            ) : null}
                            <h2 className="font-display text-[26px] leading-[1.2] font-semibold xl:text-[40px]">
                                {promo.title}
                            </h2>
                            {promo.description ? (
                                <p className="text-[15px] leading-[1.6] text-mist">
                                    {promo.description}
                                </p>
                            ) : null}
                            {promo.cta ? (
                                <ButtonLink
                                    href={promo.cta.url}
                                    className="mt-2 w-full xl:w-auto"
                                    size="lg"
                                >
                                    {promo.cta.label}
                                </ButtonLink>
                            ) : null}
                        </div>
                        <Picture
                            sizes={IMAGE_SIZES.half}
                            image={promo.image}
                            tone="dark"
                            className="order-1 min-h-[190px] xl:order-2 xl:min-h-[400px]"
                        />
                    </article>
                ))}
            </div>

            {count > 1 ? (
                <div className="mt-5 flex items-center justify-center gap-3 xl:justify-between">
                    <div className="flex items-center gap-1.5">
                        {promos.items.map((promo, index) => (
                            <button
                                key={promo.id}
                                type="button"
                                aria-label={`${labels.slide} ${index + 1}`}
                                aria-current={index === active}
                                onClick={() => goTo(index)}
                                className="flex h-11 items-center"
                            >
                                <span
                                    className={cn(
                                        'block h-1.5 rounded-full transition-all',
                                        index === active
                                            ? 'w-8 bg-terracotta'
                                            : 'w-1.5 bg-[#CFC7B6]',
                                    )}
                                />
                            </button>
                        ))}
                    </div>
                    <div className="hidden gap-2 xl:flex">
                        <button
                            type="button"
                            aria-label={labels.previous_slide}
                            onClick={() => goTo(active - 1)}
                            className="flex size-12 items-center justify-center rounded-full border-[1.5px] border-ink"
                        >
                            <Icon name="chevronLeft" className="size-5" />
                        </button>
                        <button
                            type="button"
                            aria-label={labels.next_slide}
                            onClick={() => goTo(active + 1)}
                            className="flex size-12 items-center justify-center rounded-full bg-ink text-white"
                        >
                            <Icon name="chevronRight" className="size-5" />
                        </button>
                    </div>
                </div>
            ) : null}
        </section>
    );
}
