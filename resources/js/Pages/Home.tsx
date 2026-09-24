import { usePage } from '@inertiajs/react';
import AboutSection from '@/components/home/about-section';
import type { AboutData } from '@/components/home/about-section';
import PromoSlider from '@/components/home/promo-slider';
import type { PromoData } from '@/components/home/promo-slider';
import {
    ArticlesSection,
    DevelopmentsSection,
    FacilitiesSection,
    ListingSection,
    RegionSection,
} from '@/components/home/sections';
import type {
    ArticlesData,
    DevelopmentsData,
    FacilitiesData,
    ListingData,
    RegionData,
} from '@/components/home/sections';
import CtaSection from '@/components/site/cta-section';
import PageHead from '@/components/site/page-head';
import Picture from '@/components/site/picture';
import { ButtonLink } from '@/components/site/ui';
import type { CtaData, ImageData } from '@/types/content';
import type { PageMeta } from '@/types/site';

type HomeProps = {
    meta: PageMeta;
    hero: {
        eyebrow: string;
        title: string;
        description: string;
        image: ImageData;
        imageMobile: ImageData | null;
        videoUrl: string | null;
        primary: { label: string; url: string };
        secondary: { label: string; url: string | null };
    } | null;
    about: AboutData | null;
    promos: PromoData | null;
    listing: ListingData | null;
    region: RegionData | null;
    facilities: FacilitiesData | null;
    developments: DevelopmentsData | null;
    articles: ArticlesData | null;
    cta: CtaData;
};

export default function Home({
    meta,
    hero,
    about,
    promos,
    listing,
    region,
    facilities,
    developments,
    articles,
    cta,
}: HomeProps) {
    const { site } = usePage().props;

    return (
        <>
            <PageHead meta={meta} />

            {hero ? (
                <section className="container-site pt-4 xl:pt-6">
                    <div className="grid">
                        <Picture
                            image={hero.image}
                            tone="dark"
                            priority
                            labelCorner
                            className="col-start-1 row-start-1 min-h-[540px] rounded-card md:min-h-[600px] xl:min-h-[680px] xl:rounded-section"
                        />
                        <div className="relative col-start-1 row-start-1 flex flex-col gap-4 self-end p-6 md:max-w-[960px] md:p-12 xl:gap-6 xl:p-16">
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
                                <ButtonLink
                                    href={hero.primary.url}
                                    icon="search"
                                    size="lg"
                                >
                                    {hero.primary.label}
                                </ButtonLink>
                                <ButtonLink
                                    href={
                                        hero.secondary.url ??
                                        site.contact.whatsappUrl
                                    }
                                    variant="outlineLight"
                                    icon="chat"
                                    size="lg"
                                >
                                    {hero.secondary.label}
                                </ButtonLink>
                            </div>
                        </div>
                    </div>
                    <div className="mt-4 flex flex-col gap-3 md:hidden">
                        <ButtonLink
                            href={hero.primary.url}
                            icon="search"
                            size="lg"
                        >
                            {hero.primary.label}
                        </ButtonLink>
                        <ButtonLink
                            href={
                                hero.secondary.url ?? site.contact.whatsappUrl
                            }
                            variant="outline"
                            icon="chat"
                            size="lg"
                        >
                            {hero.secondary.label}
                        </ButtonLink>
                    </div>
                </section>
            ) : (
                <h1 className="sr-only">{meta.title}</h1>
            )}

            {about ? (
                <AboutSection about={about} />
            ) : (
                <div className="h-14 xl:h-[120px]" />
            )}
            {promos ? <PromoSlider promos={promos} /> : null}
            {listing ? <ListingSection listing={listing} /> : null}
            {region ? <RegionSection region={region} /> : null}
            {facilities ? <FacilitiesSection facilities={facilities} /> : null}
            {developments ? (
                <DevelopmentsSection developments={developments} />
            ) : null}
            {articles ? <ArticlesSection articles={articles} /> : null}
            <div className="pt-14 xl:pt-[120px]">
                <CtaSection cta={cta} />
            </div>
        </>
    );
}
