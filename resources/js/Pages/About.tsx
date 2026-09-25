import Breadcrumbs from '@/components/site/breadcrumbs';
import CtaSection from '@/components/site/cta-section';
import { ContentIcon } from '@/components/site/icons';
import PageHead from '@/components/site/page-head';
import Picture, { IMAGE_SIZES } from '@/components/site/picture';
import RichText from '@/components/site/rich-text';
import { Eyebrow } from '@/components/site/ui';
import type { Crumb, CtaData, ImageData, Stat } from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    breadcrumbs: Crumb[];
    hero: {
        eyebrow: string;
        title: string;
        description: string | null;
        image: ImageData;
        quote: string | null;
    };
    history: { title: string; body: string | null; image: ImageData } | null;
    vision: {
        title: string;
        visionLabel: string;
        vision: string;
        missionLabel: string;
        missions: string[];
    } | null;
    stats: { title: string; items: Stat[] } | null;
    timeline: {
        title: string;
        items: { year: string; title: string; description?: string | null }[];
    } | null;
    team: {
        title: string;
        items: { name: string; role: string; photo: ImageData }[];
    } | null;
    awards: {
        title: string;
        items: {
            year?: string | null;
            title: string;
            issuer?: string | null;
        }[];
    } | null;
    cta: CtaData;
};

const h2 = 'font-display text-[30px] leading-[1.15] font-medium xl:text-[44px]';

/**
 * Tentang Kami. Tidak ada desain khusus; memakai pola section Home (Tentang, statistik, CTA).
 */
export default function About({
    meta,
    breadcrumbs,
    hero,
    history,
    vision,
    stats,
    timeline,
    team,
    awards,
    cta,
}: Props) {
    return (
        <>
            <PageHead meta={meta} />
            <section className="container-site flex flex-col gap-6 pt-4 xl:gap-10 xl:pt-6">
                <Breadcrumbs items={breadcrumbs} />
                <div className="grid gap-4 xl:grid-cols-[1.4fr_1fr] xl:items-end xl:gap-16">
                    <div className="flex flex-col gap-3 xl:gap-4">
                        <Eyebrow>{hero.eyebrow}</Eyebrow>
                        <h1 className="font-display text-[34px] leading-[1.1] font-medium xl:text-[56px]">
                            {hero.title}
                        </h1>
                    </div>
                    {hero.description ? (
                        <p className="text-base leading-[1.7] text-body xl:text-[17px]">
                            {hero.description}
                        </p>
                    ) : null}
                </div>
                <Picture
                    sizes={IMAGE_SIZES.container}
                    image={hero.image}
                    priority
                    labelCorner
                    className="h-[240px] rounded-card-sm md:h-[400px] xl:h-[520px] xl:rounded-section"
                >
                    {hero.quote ? (
                        <figure className="absolute right-4 bottom-4 left-4 flex flex-col gap-3 rounded-card-sm bg-forest p-5 text-left tracking-normal text-ground normal-case md:right-auto md:max-w-[420px] xl:bottom-8 xl:left-8 xl:p-7">
                            <ContentIcon
                                name="sprout"
                                className="size-6 text-peach"
                            />
                            <blockquote className="font-display text-base leading-snug font-normal xl:text-xl">
                                “{hero.quote}”
                            </blockquote>
                        </figure>
                    ) : null}
                </Picture>
            </section>

            {history ? (
                <section className="container-site grid gap-6 py-14 xl:grid-cols-2 xl:items-center xl:gap-20 xl:py-[120px]">
                    <div className="flex flex-col gap-5">
                        <h2 className={h2}>{history.title}</h2>
                        <RichText html={history.body} />
                    </div>
                    <Picture
                        sizes={IMAGE_SIZES.half}
                        image={history.image}
                        className="h-[240px] rounded-card-sm xl:order-first xl:h-[440px] xl:rounded-card"
                    />
                </section>
            ) : null}

            {vision ? (
                <section className="bg-forest py-14 text-ground xl:py-[120px]">
                    <div className="container-site grid gap-8 xl:grid-cols-[1fr_1.2fr] xl:gap-20">
                        <div className="flex flex-col gap-4">
                            <h2 className={`${h2} text-ground`}>
                                {vision.title}
                            </h2>
                            <p className="text-[13px] font-semibold tracking-[0.06em] text-peach uppercase xl:text-sm">
                                {vision.visionLabel}
                            </p>
                            <p className="font-display text-xl leading-snug xl:text-[28px]">
                                {vision.vision}
                            </p>
                        </div>
                        {vision.missions.length > 0 ? (
                            <div className="flex flex-col gap-4">
                                <p className="text-[13px] font-semibold tracking-[0.06em] text-peach uppercase xl:text-sm">
                                    {vision.missionLabel}
                                </p>
                                <ol className="flex flex-col gap-3">
                                    {vision.missions.map((mission, index) => (
                                        <li
                                            key={index}
                                            className="flex gap-4 rounded-card-sm bg-forest-2 p-5"
                                        >
                                            <span className="font-display text-xl font-semibold text-peach">
                                                {String(index + 1).padStart(
                                                    2,
                                                    '0',
                                                )}
                                            </span>
                                            <span className="text-[15px] leading-[1.6] text-mist xl:text-base">
                                                {mission}
                                            </span>
                                        </li>
                                    ))}
                                </ol>
                            </div>
                        ) : null}
                    </div>
                </section>
            ) : null}

            {stats ? (
                <section className="container-site flex flex-col gap-6 py-14 xl:gap-10 xl:py-[120px]">
                    <h2 className={h2}>{stats.title}</h2>
                    <dl className="grid grid-cols-2 gap-3 md:grid-cols-4 xl:gap-4">
                        {stats.items.map((stat) => (
                            <div
                                key={stat.label}
                                className="flex flex-col-reverse gap-1 rounded-card-sm bg-white p-5 xl:p-7"
                            >
                                <dt className="text-[13px] leading-snug text-caption xl:text-sm">
                                    {stat.label}
                                </dt>
                                <dd className="font-display text-[30px] leading-tight font-semibold xl:text-[44px]">
                                    {stat.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </section>
            ) : null}

            {timeline ? (
                <section className="bg-sand py-14 xl:py-[120px]">
                    <div className="container-site flex flex-col gap-8 xl:gap-12">
                        <h2 className={h2}>{timeline.title}</h2>
                        <ol className="grid gap-4 md:grid-cols-2 xl:grid-cols-3 xl:gap-6">
                            {timeline.items.map((item, index) => (
                                <li
                                    key={index}
                                    className="flex flex-col gap-2 border-t-2 border-terracotta pt-5"
                                >
                                    <span className="font-display text-2xl font-semibold text-terracotta">
                                        {item.year}
                                    </span>
                                    <h3 className="text-lg font-semibold">
                                        {item.title}
                                    </h3>
                                    {item.description ? (
                                        <p className="text-[15px] leading-[1.6] text-body">
                                            {item.description}
                                        </p>
                                    ) : null}
                                </li>
                            ))}
                        </ol>
                    </div>
                </section>
            ) : null}

            {team ? (
                <section className="container-site flex flex-col gap-8 py-14 xl:gap-12 xl:py-[120px]">
                    <h2 className={h2}>{team.title}</h2>
                    <ul className="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-4 xl:gap-6">
                        {team.items.map((member) => (
                            <li
                                key={member.name}
                                className="flex flex-col gap-3"
                            >
                                <Picture
                                    sizes={IMAGE_SIZES.quarter}
                                    image={member.photo}
                                    label="Foto"
                                    className="aspect-[4/5] rounded-card-sm text-[11px]"
                                />
                                <div>
                                    <p className="font-semibold">
                                        {member.name}
                                    </p>
                                    <p className="text-sm text-body">
                                        {member.role}
                                    </p>
                                </div>
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}

            {awards ? (
                <section className="container-site flex flex-col gap-6 pb-14 xl:gap-10 xl:pb-[120px]">
                    <h2 className={h2}>{awards.title}</h2>
                    <ul className="divide-y divide-line border-y border-line">
                        {awards.items.map((award, index) => (
                            <li
                                key={index}
                                className="grid gap-1 py-4 md:grid-cols-[120px_1fr_auto] md:items-center md:gap-6"
                            >
                                <span className="font-display text-lg font-semibold text-terracotta">
                                    {award.year}
                                </span>
                                <span className="font-semibold">
                                    {award.title}
                                </span>
                                {award.issuer ? (
                                    <span className="text-sm text-body">
                                        {award.issuer}
                                    </span>
                                ) : null}
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}

            <div
                className={stats || team || awards ? '' : 'pt-14 xl:pt-[120px]'}
            >
                <CtaSection cta={cta} />
            </div>
        </>
    );
}
