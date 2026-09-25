import { usePage } from '@inertiajs/react';
import SmartLink from '@/components/site/smart-link';
import { cn } from '@/lib/utils';
import type { Crumb } from '@/types/content';

/**
 * compact = versi ringkas satu baris di mobile (font kecil, scroll horizontal kalau panjang);
 * mulai tablet tampil normal.
 */
export default function Breadcrumbs({
    items,
    compact = false,
}: {
    items: Crumb[];
    compact?: boolean;
}) {
    const { site } = usePage().props;

    if (items.length === 0) {
        return null;
    }

    return (
        <nav
            aria-label={site.labels.breadcrumb}
            className={compact ? '-mx-5 md:mx-0' : undefined}
        >
            <ol
                className={cn(
                    'flex items-center gap-x-2.5 gap-y-1',
                    compact
                        ? '[scrollbar-width:none] overflow-x-auto px-5 py-1 text-[13px] whitespace-nowrap md:flex-wrap md:px-0 md:py-0 md:text-sm md:whitespace-normal [&::-webkit-scrollbar]:hidden'
                        : 'flex-wrap text-sm',
                )}
            >
                {items.map((item, index) => {
                    const last = index === items.length - 1;

                    return (
                        <li
                            key={`${item.label}-${index}`}
                            className="flex items-center gap-2.5"
                        >
                            {index > 0 ? (
                                <span
                                    aria-hidden="true"
                                    className="text-[#A39C8E]"
                                >
                                    /
                                </span>
                            ) : null}
                            {item.url && !last ? (
                                <SmartLink
                                    href={item.url}
                                    className="text-caption no-underline hover:text-ink"
                                >
                                    {item.label}
                                </SmartLink>
                            ) : (
                                <span
                                    aria-current={last ? 'page' : undefined}
                                    className="font-semibold text-ink"
                                >
                                    {item.label}
                                </span>
                            )}
                        </li>
                    );
                })}
            </ol>
        </nav>
    );
}
