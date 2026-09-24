import { usePage } from '@inertiajs/react';
import SmartLink from '@/components/site/smart-link';
import type { Crumb } from '@/types/content';

export default function Breadcrumbs({ items }: { items: Crumb[] }) {
    const { site } = usePage().props;

    if (items.length === 0) {
        return null;
    }

    return (
        <nav aria-label={site.labels.breadcrumb} className="text-sm">
            <ol className="flex flex-wrap items-center gap-x-2.5 gap-y-1">
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
