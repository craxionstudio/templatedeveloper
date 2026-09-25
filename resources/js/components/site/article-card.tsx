import { usePage } from '@inertiajs/react';
import Picture, { IMAGE_SIZES } from '@/components/site/picture';
import SmartLink from '@/components/site/smart-link';
import { cn } from '@/lib/utils';
import type { ArticleCardData } from '@/types/content';

type Variant = 'grid' | 'row' | 'compact';

/**
 * Kartu artikel. grid = kartu vertikal (index, terkait), row = foto kecil di kiri (Beranda,
 * mobile index), compact = tanpa ringkasan.
 */
export default function ArticleCard({
    article,
    variant = 'grid',
    showExcerpt = true,
    showReading = true,
    className,
}: {
    article: ArticleCardData;
    variant?: Variant;
    showExcerpt?: boolean;
    showReading?: boolean;
    className?: string;
}) {
    const { labels } = usePage().props.site;
    const meta = [
        article.date,
        showReading ? `${article.readingMinutes} ${labels.minutes}` : null,
    ]
        .filter(Boolean)
        .join(' · ');

    if (variant === 'row') {
        return (
            <article
                className={cn(
                    'group relative grid grid-cols-[104px_1fr] items-start gap-4 md:grid-cols-[146px_1fr] md:gap-5',
                    className,
                )}
            >
                <Picture
                    sizes={IMAGE_SIZES.thumb}
                    image={article.image}
                    label={labels.gallery_photo}
                    className="aspect-square rounded-[14px] text-[11px] md:aspect-[4/3]"
                />
                <div className="flex flex-col gap-1.5">
                    {article.category ? (
                        <span className="text-xs font-semibold text-terracotta xl:text-[13px]">
                            {article.category.name}
                        </span>
                    ) : null}
                    <h3 className="font-display text-[17px] leading-snug font-semibold xl:text-lg">
                        <SmartLink
                            href={article.url}
                            className="text-ink no-underline group-hover:text-terracotta after:absolute after:inset-0"
                        >
                            {article.title}
                        </SmartLink>
                    </h3>
                    {meta ? (
                        <span className="text-[13px] text-caption">{meta}</span>
                    ) : null}
                </div>
            </article>
        );
    }

    return (
        <article
            className={cn('group relative flex flex-col gap-3', className)}
        >
            <Picture
                sizes={IMAGE_SIZES.card3}
                image={article.image}
                className="aspect-[16/10] rounded-[20px]"
            />
            <div className="flex flex-col gap-2">
                {article.category ? (
                    <span className="text-xs font-semibold text-terracotta xl:text-[13px]">
                        {article.category.name}
                    </span>
                ) : null}
                <h3 className="font-display text-[19px] leading-snug font-semibold xl:text-xl">
                    <SmartLink
                        href={article.url}
                        className="text-ink no-underline group-hover:text-terracotta after:absolute after:inset-0"
                    >
                        {article.title}
                    </SmartLink>
                </h3>
                {showExcerpt && variant === 'grid' && article.excerpt ? (
                    <p className="line-clamp-2 text-sm leading-[1.55] text-body">
                        {article.excerpt}
                    </p>
                ) : null}
                {meta ? (
                    <span className="text-[13px] text-caption">{meta}</span>
                ) : null}
            </div>
        </article>
    );
}
