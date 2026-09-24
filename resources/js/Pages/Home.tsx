import { usePage } from '@inertiajs/react';
import { ChatIcon, SearchIcon } from '@/components/site/icons';
import PageHead from '@/components/site/page-head';
import SmartLink from '@/components/site/smart-link';
import type { PageMeta } from '@/types/site';

type HomeProps = {
    meta: PageMeta;
    hero: {
        eyebrow: string;
        title: string;
        description: string;
        media_label: string;
        primary_label: string;
        primary_url: string;
        secondary_label: string;
        secondary_url: string | null;
    } | null;
};

/**
 * Beranda — Milestone 1 baru berisi Hero untuk menguji layout global & token.
 * Section lain (About, Promo, Listing, dst.) dibangun di Milestone 3.
 */
export default function Home({ meta, hero }: HomeProps) {
    const { site } = usePage().props;

    // Section Hero dimatikan di admin: tetap ada satu H1 untuk SEO.
    if (!hero) {
        return (
            <>
                <PageHead meta={meta} />
                <h1 className="sr-only">{meta.title}</h1>
            </>
        );
    }

    const buttons = (
        <>
            <SmartLink
                href={hero.primary_url}
                className="flex h-[52px] items-center justify-center gap-2.5 rounded-full bg-terracotta px-[26px] text-base font-semibold text-white no-underline transition-colors hover:bg-terracotta-hover"
            >
                <SearchIcon className="size-5" />
                {hero.primary_label}
            </SmartLink>
            <SmartLink
                href={hero.secondary_url ?? site.contact.whatsappUrl}
                className="flex h-[52px] items-center justify-center gap-2.5 rounded-full border-[1.5px] border-ink px-[26px] text-base font-semibold text-ink no-underline transition-colors hover:bg-ink hover:text-ground md:border-ground md:text-ground md:hover:bg-ground md:hover:text-ink"
            >
                <ChatIcon className="size-5" />
                {hero.secondary_label}
            </SmartLink>
        </>
    );

    return (
        <>
            <PageHead meta={meta} />

            <section className="container-site pt-4 pb-8 xl:pt-6 xl:pb-20">
                <div className="grid">
                    <div className="placeholder-stripes-dark col-start-1 row-start-1 flex min-h-[540px] items-start justify-end rounded-card p-6 md:min-h-[600px] xl:min-h-[680px] xl:rounded-section xl:p-8">
                        <span className="text-[13px] font-semibold tracking-[0.08em] text-mist uppercase">
                            {hero.media_label}
                        </span>
                    </div>

                    <div className="col-start-1 row-start-1 flex flex-col gap-4 self-end p-6 md:max-w-[800px] md:p-12 xl:gap-6 xl:p-16">
                        <p className="text-[13px] font-bold tracking-[0.08em] text-peach uppercase">
                            {hero.eyebrow}
                        </p>
                        <h1 className="font-display text-[44px] leading-[1.05] font-medium tracking-[-0.01em] text-ground md:text-6xl xl:text-[76px]">
                            {hero.title}
                        </h1>
                        <p className="text-base leading-[1.6] text-[#dfe7e1] xl:max-w-[600px] xl:text-lg">
                            {hero.description}
                        </p>
                        <div className="mt-2 hidden gap-3 md:flex">
                            {buttons}
                        </div>
                    </div>
                </div>

                <div className="mt-4 flex flex-col gap-3 md:hidden">
                    {buttons}
                </div>
            </section>
        </>
    );
}
