import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import ArticleCard from '@/components/site/article-card';
import Breadcrumbs from '@/components/site/breadcrumbs';
import { Icon } from '@/components/site/icons';
import PageHead from '@/components/site/page-head';
import Pagination from '@/components/site/pagination';
import Picture from '@/components/site/picture';
import SmartLink from '@/components/site/smart-link';
import { Badge, Eyebrow } from '@/components/site/ui';
import { cn } from '@/lib/utils';
import type {
    ArticleCardData,
    Crumb,
    Pagination as PaginationData,
} from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    breadcrumbs: Crumb[];
    header: {
        eyebrow: string;
        title: string;
        description: string;
        highlightBadge: string;
    };
    highlight: ArticleCardData | null;
    readLabel: string;
    readingLabel: string;
    categories: {
        all: { label: string; url: string; active: boolean };
        items: { label: string; url: string; active: boolean }[];
    };
    search: { placeholder: string; value: string; action: string } | null;
    count: string;
    emptyText: string;
    articles: { data: ArticleCardData[]; pagination: PaginationData };
    newsletter: {
        title: string;
        description: string;
        placeholder: string;
        buttonLabel: string;
    } | null;
};

/**
 * Artikel (index) dan halaman kategori (/artikel/kategori/{slug}) — komponen yang sama.
 */
export default function ArtikelIndex({
    meta,
    breadcrumbs,
    header,
    highlight,
    readLabel,
    readingLabel,
    categories,
    search,
    count,
    emptyText,
    articles,
    newsletter,
}: Props) {
    const { labels } = usePage().props.site;
    const [q, setQ] = useState(search?.value ?? '');

    const onSearch = (e: FormEvent) => {
        e.preventDefault();
        router.get(search!.action, q ? { q } : {}, { preserveScroll: true });
    };

    const chip = (active: boolean) =>
        cn(
            'inline-flex h-11 shrink-0 items-center rounded-full px-5 text-[15px] no-underline',
            active
                ? 'bg-ink font-semibold text-white'
                : 'border border-line bg-white text-ink hover:border-ink',
        );

    const searchForm = search ? (
        <form
            action={search.action}
            method="get"
            onSubmit={onSearch}
            role="search"
            className="relative w-full xl:w-[320px]"
        >
            <Icon
                name="search"
                className="pointer-events-none absolute top-1/2 left-4 size-[18px] -translate-y-1/2 text-caption"
            />
            <label htmlFor="cari-artikel" className="sr-only">
                {labels.search}
            </label>
            <input
                id="cari-artikel"
                type="search"
                name="q"
                value={q}
                onChange={(e) => setQ(e.target.value)}
                placeholder={search.placeholder}
                className="h-12 w-full rounded-full border-[1.5px] border-[#CFC7B6] bg-white pr-4 pl-11 text-[15px] focus:border-ink focus:outline-none"
            />
        </form>
    ) : null;

    return (
        <>
            <PageHead meta={meta} />
            <section className="container-site flex flex-col gap-5 pt-4 xl:gap-8 xl:pt-6">
                <Breadcrumbs items={breadcrumbs} />
                <div className="grid gap-3 xl:grid-cols-[1.6fr_1fr] xl:items-end xl:gap-16">
                    <div className="flex flex-col gap-3 xl:gap-4">
                        <Eyebrow>{header.eyebrow}</Eyebrow>
                        <h1 className="font-display text-[34px] leading-[1.1] font-medium xl:text-[56px]">
                            {header.title}
                        </h1>
                    </div>
                    <p className="hidden text-base leading-[1.7] text-body xl:block">
                        {header.description}
                    </p>
                </div>

                {highlight ? (
                    <article className="group relative grid overflow-hidden rounded-card bg-white xl:grid-cols-[1.35fr_1fr]">
                        <Picture
                            image={highlight.image}
                            priority
                            className="h-[210px] xl:h-[460px]"
                        />
                        <div className="flex flex-col justify-center gap-3 p-5 xl:gap-4 xl:p-14">
                            <div className="flex items-center gap-2">
                                <Badge>{header.highlightBadge}</Badge>
                                {highlight.category ? (
                                    <span className="text-[13px] font-semibold text-terracotta">
                                        {highlight.category.name}
                                    </span>
                                ) : null}
                            </div>
                            <h2 className="font-display text-[22px] leading-snug font-semibold xl:text-[34px]">
                                <SmartLink
                                    href={highlight.url}
                                    className="text-ink no-underline group-hover:text-terracotta after:absolute after:inset-0"
                                >
                                    {highlight.title}
                                </SmartLink>
                            </h2>
                            {highlight.excerpt ? (
                                <p className="text-[15px] leading-[1.6] text-body">
                                    {highlight.excerpt}
                                </p>
                            ) : null}
                            <p className="flex flex-wrap items-center gap-4 text-[13px] text-caption">
                                <span className="inline-flex items-center gap-1.5">
                                    <Icon name="calendar" className="size-4" />
                                    {highlight.date}
                                </span>
                                <span className="inline-flex items-center gap-1.5">
                                    <Icon name="clock" className="size-4" />
                                    {highlight.readingMinutes} {readingLabel}
                                </span>
                            </p>
                            <span className="hidden items-center gap-1.5 text-[15px] font-semibold text-terracotta xl:inline-flex">
                                {readLabel}
                                <Icon name="arrowRight" className="size-4" />
                            </span>
                        </div>
                    </article>
                ) : null}
            </section>

            <section className="container-site flex flex-col gap-5 pt-8 pb-14 xl:gap-8 xl:pt-12 xl:pb-[120px]">
                <div className="flex flex-col gap-4 xl:flex-row-reverse xl:items-center xl:justify-between xl:border-b xl:border-line xl:pb-6">
                    {searchForm}
                    <nav
                        aria-label={header.eyebrow}
                        className="-mx-5 flex [scrollbar-width:none] gap-2 overflow-x-auto px-5 xl:mx-0 xl:px-0"
                    >
                        <SmartLink
                            href={categories.all.url}
                            className={chip(categories.all.active)}
                            aria-current={
                                categories.all.active ? 'page' : undefined
                            }
                        >
                            {categories.all.label}
                        </SmartLink>
                        {categories.items.map((item) => (
                            <SmartLink
                                key={item.url}
                                href={item.url}
                                className={chip(item.active)}
                                aria-current={item.active ? 'page' : undefined}
                            >
                                {item.label}
                            </SmartLink>
                        ))}
                    </nav>
                </div>
                <p className="text-[15px] text-body">{count}</p>

                {articles.data.length === 0 ? (
                    <p className="rounded-card bg-white p-10 text-center text-body">
                        {emptyText}
                    </p>
                ) : (
                    <>
                        <div className="flex flex-col gap-5 md:hidden">
                            {articles.data.map((article) => (
                                <ArticleCard
                                    key={article.id}
                                    article={article}
                                    variant="row"
                                />
                            ))}
                        </div>
                        <div className="hidden gap-6 md:grid md:grid-cols-2 xl:grid-cols-3 xl:gap-y-10">
                            {articles.data.map((article) => (
                                <ArticleCard
                                    key={article.id}
                                    article={article}
                                />
                            ))}
                        </div>
                    </>
                )}

                <Pagination pagination={articles.pagination} className="mt-4" />
            </section>

            {newsletter ? (
                <section className="container-site pb-14 xl:pb-[120px]">
                    <div className="grid gap-5 rounded-card bg-forest p-6 text-ground xl:grid-cols-2 xl:items-center xl:gap-12 xl:rounded-section xl:p-14">
                        <div className="flex flex-col gap-2">
                            <h2 className="font-display text-[26px] leading-snug font-medium xl:text-[34px]">
                                {newsletter.title}
                            </h2>
                            <p className="text-[15px] text-mist">
                                {newsletter.description}
                            </p>
                        </div>
                        {/* Penyimpanan newsletter di Milestone 4. */}
                        <form
                            onSubmit={(e) => e.preventDefault()}
                            className="flex flex-col gap-3 md:flex-row"
                        >
                            <label
                                htmlFor="newsletter-email"
                                className="sr-only"
                            >
                                {newsletter.placeholder}
                            </label>
                            <input
                                id="newsletter-email"
                                type="email"
                                name="email"
                                autoComplete="email"
                                placeholder={newsletter.placeholder}
                                className="h-[52px] flex-1 rounded-full border-0 bg-white px-5 text-[15px] text-ink"
                            />
                            <button
                                type="submit"
                                className="h-[52px] rounded-full bg-terracotta px-7 font-semibold text-white hover:bg-terracotta-hover"
                            >
                                {newsletter.buttonLabel}
                            </button>
                        </form>
                    </div>
                </section>
            ) : null}
        </>
    );
}
