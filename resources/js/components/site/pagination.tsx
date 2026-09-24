import { usePage } from '@inertiajs/react';
import { Icon } from '@/components/site/icons';
import SmartLink from '@/components/site/smart-link';
import { cn } from '@/lib/utils';
import type { Pagination as PaginationData } from '@/types/content';

/**
 * Pagination dengan link asli (bisa di-crawl).
 */
export default function Pagination({
    pagination,
    className,
}: {
    pagination: PaginationData;
    className?: string;
}) {
    const { site } = usePage().props;

    if (pagination.last <= 1) {
        return null;
    }

    const circle =
        'flex size-12 items-center justify-center rounded-full font-semibold no-underline';

    return (
        <nav
            aria-label={site.labels.pagination}
            className={cn('flex justify-center gap-2', className)}
        >
            {pagination.prev ? (
                <SmartLink
                    href={pagination.prev}
                    aria-label={site.labels.previous_page}
                    className={cn(
                        circle,
                        'border-[1.5px] border-[#CFC7B6] text-ink',
                    )}
                >
                    <Icon name="chevronLeft" className="size-5" />
                </SmartLink>
            ) : null}
            {pagination.pages.map((page) =>
                page.page === pagination.current ? (
                    <span
                        key={page.page}
                        aria-current="page"
                        className={cn(circle, 'bg-ink text-white')}
                    >
                        {page.page}
                    </span>
                ) : (
                    <SmartLink
                        key={page.page}
                        href={page.url}
                        className={cn(
                            circle,
                            'border-[1.5px] border-[#CFC7B6] text-ink hover:border-ink',
                        )}
                    >
                        {page.page}
                    </SmartLink>
                ),
            )}
            {pagination.next ? (
                <SmartLink
                    href={pagination.next}
                    aria-label={site.labels.next_page}
                    className={cn(
                        circle,
                        'border-[1.5px] border-[#CFC7B6] text-ink',
                    )}
                >
                    <Icon name="chevronRight" className="size-5" />
                </SmartLink>
            ) : null}
        </nav>
    );
}
