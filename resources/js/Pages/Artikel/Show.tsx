import { useState } from 'react';
import ArticleCard from '@/components/site/article-card';
import Breadcrumbs from '@/components/site/breadcrumbs';
import CtaSection from '@/components/site/cta-section';
import { Icon } from '@/components/site/icons';
import PageHead from '@/components/site/page-head';
import Picture from '@/components/site/picture';
import RichText from '@/components/site/rich-text';
import SmartLink from '@/components/site/smart-link';
import { ArrowLink } from '@/components/site/ui';
import type {
    ArticleCardData,
    Crumb,
    CtaData,
    ImageData,
} from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    /** Pratinjau admin (draft boleh), noindex. */
    preview?: boolean;
    breadcrumbs: Crumb[];
    article: ArticleCardData & {
        body: string | null;
        author: { name: string; photo: ImageData } | null;
        updated: string | null;
        tags: string[];
    };
    display: {
        showReadingTime: boolean;
        showShare: boolean;
        updatedLabel: string;
        readingLabel: string;
        shareLabel: string;
    };
    related: {
        title: string;
        link: { label: string; url: string };
        items: ArticleCardData[];
    } | null;
    cta: CtaData;
};

function ShareButton({ label, title }: { label: string; title: string }) {
    const [copied, setCopied] = useState(false);

    const share = async () => {
        const url = window.location.href;

        if (navigator.share) {
            try {
                await navigator.share({ title, url });
            } catch {
                // Dibatalkan pengguna.
            }

            return;
        }

        await navigator.clipboard?.writeText(url);
        setCopied(true);
        window.setTimeout(() => setCopied(false), 2000);
    };

    return (
        <button
            type="button"
            onClick={share}
            className="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-full border-[1.5px] border-[#CFC7B6] px-[18px] text-[15px] font-semibold text-ink hover:border-ink xl:h-11 xl:text-sm"
        >
            <Icon name={copied ? 'check' : 'share'} className="size-[18px]" />
            <span aria-live="polite">{copied ? `${label} ✓` : label}</span>
        </button>
    );
}

export default function ArtikelShow({
    meta,
    preview = false,
    breadcrumbs,
    article,
    display,
    related,
    cta,
}: Props) {
    const reading = display.showReadingTime
        ? `${article.readingMinutes} ${display.readingLabel}`
        : null;
    const hasFooter = article.tags.length > 0 || display.showShare;

    return (
        <>
            <PageHead meta={meta} />
            {preview ? (
                <p
                    role="status"
                    className="sticky top-0 z-40 bg-ink px-5 py-2.5 text-center text-sm font-semibold text-ground"
                >
                    Pratinjau — hanya terlihat oleh admin, tidak diindeks mesin
                    pencari.
                </p>
            ) : null}
            <article className="container-site flex flex-col gap-5 pt-4 pb-12 xl:items-center xl:gap-12 xl:pt-6 xl:pb-24">
                <div className="w-full">
                    <Breadcrumbs items={breadcrumbs} />
                </div>

                <header className="flex flex-col gap-5 xl:w-[800px] xl:items-center xl:gap-6 xl:text-center">
                    {article.category ? (
                        <SmartLink
                            href={article.category.url}
                            className="text-xs font-semibold tracking-[0.08em] text-terracotta uppercase no-underline xl:text-sm xl:tracking-[0.06em]"
                        >
                            {article.category.name}
                        </SmartLink>
                    ) : null}
                    <h1 className="font-display text-[31px] leading-[1.15] font-medium xl:text-[54px] xl:leading-[1.12] xl:tracking-[-0.01em]">
                        {article.title}
                    </h1>
                    <div className="flex items-center gap-2.5 text-[13px] text-caption xl:gap-5 xl:text-[15px]">
                        {article.author ? (
                            <Picture
                                image={article.author.photo}
                                label="Foto"
                                className="size-9 shrink-0 rounded-full p-0! text-[8px] xl:size-10 xl:text-[9px]"
                            />
                        ) : null}
                        <div className="flex flex-col xl:flex-row xl:items-center xl:gap-5">
                            {article.author ? (
                                <strong className="text-sm text-ink xl:text-[15px]">
                                    {article.author.name}
                                </strong>
                            ) : null}
                            <span className="xl:flex xl:gap-5">
                                {article.dateIso ? (
                                    <time dateTime={article.dateIso}>
                                        {article.date}
                                    </time>
                                ) : null}
                                {reading ? (
                                    <>
                                        <span
                                            aria-hidden="true"
                                            className="xl:hidden"
                                        >
                                            {' '}
                                            ·{' '}
                                        </span>
                                        <span>{reading}</span>
                                    </>
                                ) : null}
                            </span>
                        </div>
                    </div>
                    {article.updated ? (
                        <p className="-mt-2 text-[13px] text-caption xl:-mt-3 xl:text-sm">
                            {display.updatedLabel} {article.updated}
                        </p>
                    ) : null}
                </header>

                <Picture
                    image={article.image}
                    priority
                    label={article.image.url ? undefined : article.image.alt}
                    className="h-[230px] w-full rounded-[20px] text-[11px] md:h-[400px] xl:h-[560px] xl:rounded-section xl:text-[13px]"
                />

                <div className="flex w-full flex-col xl:w-[760px]">
                    <RichText html={article.body} size="article" />

                    {hasFooter ? (
                        <div className="mt-8 flex flex-col gap-3.5 border-y border-line py-5 xl:mt-10 xl:flex-row xl:items-center xl:justify-between xl:gap-6 xl:py-6">
                            {article.tags.length > 0 ? (
                                <ul className="flex flex-wrap gap-2">
                                    {article.tags.map((tag) => (
                                        <li
                                            key={tag}
                                            className="rounded-full bg-sand px-3 py-2 text-[13px] xl:px-3.5 xl:text-sm"
                                        >
                                            #{tag}
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <span />
                            )}
                            {display.showShare ? (
                                <ShareButton
                                    label={display.shareLabel}
                                    title={article.title}
                                />
                            ) : null}
                        </div>
                    ) : null}
                </div>
            </article>

            {related ? (
                <section className="bg-sand py-12 xl:pt-24 xl:pb-[120px]">
                    <div className="container-site flex flex-col gap-6 xl:gap-12">
                        <div className="flex items-end justify-between gap-6">
                            <h2 className="font-display text-[30px] leading-[1.2] font-medium tracking-[-0.01em] xl:text-[40px]">
                                {related.title}
                            </h2>
                            <div className="hidden md:block">
                                <ArrowLink href={related.link.url}>
                                    {related.link.label}
                                </ArrowLink>
                            </div>
                        </div>
                        <div className="flex flex-col gap-5 md:hidden">
                            {related.items.map((item) => (
                                <ArticleCard
                                    key={item.id}
                                    article={item}
                                    variant="row"
                                    showReading={false}
                                />
                            ))}
                        </div>
                        <div className="hidden gap-6 md:grid md:grid-cols-2 xl:grid-cols-3">
                            {related.items.map((item) => (
                                <ArticleCard
                                    key={item.id}
                                    article={item}
                                    showExcerpt={false}
                                    showReading={false}
                                />
                            ))}
                        </div>
                        <div className="md:hidden">
                            <ArrowLink href={related.link.url}>
                                {related.link.label}
                            </ArrowLink>
                        </div>
                    </div>
                </section>
            ) : null}

            <div className="pt-14 xl:pt-[120px]">
                <CtaSection cta={cta} />
            </div>
        </>
    );
}
