import { ContentIcon } from '@/components/site/icons';
import Picture from '@/components/site/picture';
import { ArrowLink, Eyebrow } from '@/components/site/ui';
import type { ImageData, Stat } from '@/types/content';

export type AboutData = {
    eyebrow: string;
    title: string;
    description: string | null;
    history: string | null;
    quote: string | null;
    stats: Stat[];
    photo: ImageData;
    secondaryPhoto: ImageData;
    link: { label: string; url: string };
};

export default function AboutSection({ about }: { about: AboutData }) {
    const photos = (
        <div className="grid grid-cols-2 gap-3 xl:gap-4">
            <Picture
                image={about.photo}
                className="row-span-2 min-h-[240px] rounded-card-sm text-[11px] xl:min-h-[520px] xl:text-[13px]"
            />
            <Picture
                image={about.secondaryPhoto}
                className="min-h-[114px] rounded-card-sm text-[11px] xl:min-h-[252px] xl:text-[13px]"
            />
            {about.quote ? (
                <figure className="flex min-h-[114px] flex-col justify-between gap-4 rounded-card-sm bg-forest p-5 text-ground xl:min-h-[252px] xl:p-7">
                    <ContentIcon name="sprout" className="size-6 text-peach" />
                    <blockquote className="font-display text-[15px] leading-snug xl:text-xl">
                        “{about.quote}”
                    </blockquote>
                </figure>
            ) : null}
        </div>
    );

    return (
        <section
            id="tentang"
            className="container-site grid gap-8 py-14 xl:grid-cols-[1fr_1fr] xl:items-center xl:gap-20 xl:py-[120px]"
        >
            <div className="hidden xl:block">{photos}</div>
            <div className="flex flex-col gap-5 xl:gap-6">
                <Eyebrow>{about.eyebrow}</Eyebrow>
                <h2 className="font-display text-[30px] leading-[1.15] font-medium xl:text-[44px]">
                    {about.title}
                </h2>
                {about.description ? (
                    <p className="text-base leading-[1.7] text-body xl:text-[17px]">
                        {about.description}
                    </p>
                ) : null}
                {about.history ? (
                    <p className="text-base leading-[1.7] text-body xl:text-[17px]">
                        {about.history}
                    </p>
                ) : null}
                <div className="xl:hidden">{photos}</div>
                {about.stats.length > 0 ? (
                    <dl className="grid grid-cols-2 gap-3 md:grid-cols-4 xl:gap-4">
                        {about.stats.map((stat) => (
                            <div
                                key={stat.label}
                                className="flex flex-col-reverse gap-1 rounded-card-sm bg-white p-4 xl:p-5"
                            >
                                <dt className="text-[13px] leading-snug text-caption">
                                    {stat.label}
                                </dt>
                                <dd className="font-display text-[26px] leading-tight font-semibold xl:text-[30px]">
                                    {stat.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                ) : null}
                <ArrowLink href={about.link.url}>{about.link.label}</ArrowLink>
            </div>
        </section>
    );
}
