import { Link } from '@inertiajs/react';
import type { AriaAttributes, MouseEvent, ReactNode } from 'react';
import { isExternalUrl } from '@/lib/url';

type SmartLinkProps = {
    href: string;
    newTab?: boolean;
    className?: string;
    onClick?: (event: MouseEvent<Element>) => void;
    /** Penanda event analytics (lihat resources/js/lib/analytics.ts). */
    'data-track'?: string;
    'data-cluster'?: string;
    'data-position'?: string;
    'aria-label'?: string;
    'aria-current'?: AriaAttributes['aria-current'];
    children: ReactNode;
};

/**
 * <Link> Inertia untuk URL internal, <a> biasa untuk URL eksternal/wa.me/tel:.
 * Keduanya tetap <a href> asli sehingga ter-render SSR dan bisa di-crawl.
 */
export default function SmartLink({
    href,
    newTab = false,
    children,
    ...props
}: SmartLinkProps) {
    if (newTab) {
        return (
            <a href={href} target="_blank" rel="noopener noreferrer" {...props}>
                {children}
            </a>
        );
    }

    if (isExternalUrl(href) || href.startsWith('#')) {
        return (
            <a href={href} {...props}>
                {children}
            </a>
        );
    }

    return (
        <Link href={href} {...props}>
            {children}
        </Link>
    );
}
