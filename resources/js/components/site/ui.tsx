import type { ReactNode } from 'react';
import { Icon } from '@/components/site/icons';
import type { IconName } from '@/components/site/icons';
import SmartLink from '@/components/site/smart-link';
import { cn } from '@/lib/utils';

export function Eyebrow({
    children,
    tone = 'light',
    className,
}: {
    children: ReactNode;
    tone?: 'light' | 'dark';
    className?: string;
}) {
    return (
        <p
            className={cn(
                'text-[13px] font-semibold tracking-[0.06em] uppercase xl:text-sm',
                tone === 'dark' ? 'text-peach' : 'text-terracotta',
                className,
            )}
        >
            {children}
        </p>
    );
}

/**
 * Eyebrow + judul section (H2) + aksi opsional di kanan (desktop).
 */
export function SectionHeading({
    eyebrow,
    title,
    action,
    tone = 'light',
    as: Heading = 'h2',
    className,
}: {
    eyebrow?: string | null;
    title: string;
    action?: ReactNode;
    tone?: 'light' | 'dark';
    as?: 'h1' | 'h2';
    className?: string;
}) {
    return (
        <div
            className={cn(
                'flex flex-col gap-3 md:flex-row md:items-end md:justify-between md:gap-8',
                className,
            )}
        >
            <div className="flex max-w-[720px] flex-col gap-3 xl:gap-4">
                {eyebrow ? <Eyebrow tone={tone}>{eyebrow}</Eyebrow> : null}
                <Heading
                    className={cn(
                        'font-display text-[30px] leading-[1.15] font-medium xl:text-[44px]',
                        tone === 'dark' ? 'text-ground' : 'text-ink',
                    )}
                >
                    {title}
                </Heading>
            </div>
            {/* Aksi di kanan judul hanya di tablet/desktop; halaman menaruh versi mobile di bawah konten. */}
            {action ? (
                <div className="hidden shrink-0 md:block">{action}</div>
            ) : null}
        </div>
    );
}

type ButtonVariant = 'primary' | 'dark' | 'outline' | 'outlineLight' | 'white';

const buttonVariants: Record<ButtonVariant, string> = {
    primary: 'bg-terracotta text-white hover:bg-terracotta-hover',
    dark: 'bg-ink text-white hover:bg-forest',
    outline:
        'border-[1.5px] border-ink text-ink hover:bg-ink hover:text-ground',
    outlineLight:
        'border-[1.5px] border-ground text-ground hover:bg-ground hover:text-ink',
    white: 'bg-white text-ink hover:bg-ground',
};

export function ButtonLink({
    href,
    children,
    variant = 'primary',
    icon,
    size = 'md',
    className,
    newTab,
}: {
    href: string;
    children: ReactNode;
    variant?: ButtonVariant;
    icon?: IconName;
    size?: 'sm' | 'md' | 'lg';
    className?: string;
    newTab?: boolean;
}) {
    return (
        <SmartLink
            href={href}
            newTab={newTab}
            className={cn(
                'inline-flex items-center justify-center gap-2.5 rounded-full font-semibold whitespace-nowrap no-underline transition-colors',
                size === 'sm' && 'h-11 px-5 text-sm',
                size === 'md' && 'h-12 px-[26px] text-base',
                size === 'lg' && 'h-[52px] px-[26px] text-base xl:h-[60px]',
                buttonVariants[variant],
                className,
            )}
        >
            {icon ? <Icon name={icon} className="size-5" /> : null}
            {children}
        </SmartLink>
    );
}

/**
 * Link teks dengan panah (mis. "Lihat semua fasilitas →").
 */
export function ArrowLink({
    href,
    children,
    tone = 'light',
    className,
}: {
    href: string;
    children: ReactNode;
    tone?: 'light' | 'dark';
    className?: string;
}) {
    return (
        <SmartLink
            href={href}
            className={cn(
                'inline-flex min-h-11 items-center gap-2 text-[15px] font-semibold underline decoration-1 underline-offset-4 transition-colors',
                tone === 'dark'
                    ? 'text-peach hover:text-ground'
                    : 'text-terracotta hover:text-terracotta-hover',
                className,
            )}
        >
            {children}
            <Icon name="arrowRight" className="size-4 shrink-0" />
        </SmartLink>
    );
}

export function Badge({
    children,
    tone = 'terracotta',
    className,
}: {
    children: ReactNode;
    tone?: 'terracotta' | 'dark' | 'soft' | 'success' | 'warning' | 'muted';
    className?: string;
}) {
    const tones = {
        terracotta: 'bg-terracotta text-white',
        dark: 'bg-ink/85 text-white',
        soft: 'bg-sand text-ink',
        success: 'bg-[#E3EFE7] text-[#1F5A3A]',
        warning: 'bg-[#F8E1D4] text-[#8A3A17]',
        muted: 'bg-[#EFE9DE] text-caption',
    };

    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-[11px] py-[5px] text-xs leading-none font-semibold tracking-normal normal-case',
                tones[tone],
                className,
            )}
        >
            {children}
        </span>
    );
}

export function Container({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return <div className={cn('container-site', className)}>{children}</div>;
}
