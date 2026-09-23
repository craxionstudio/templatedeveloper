import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';

type BrandLogoProps = {
    name: string;
    tagline?: string;
    homeLabel: string;
    className?: string;
};

export function BrandMark({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 32 32"
            fill="none"
            stroke="currentColor"
            strokeWidth={2}
            aria-hidden="true"
            className={cn('shrink-0 text-terracotta', className)}
        >
            <path d="M4 28V14L16 4l12 10v14" />
            <path d="M12 28v-8h8v8" />
        </svg>
    );
}

/**
 * Logo teks sementara. Di Milestone 2 bisa diganti file logo dari Pengaturan Global.
 */
export default function BrandLogo({
    name,
    tagline,
    homeLabel,
    className,
}: BrandLogoProps) {
    return (
        <Link
            href="/"
            aria-label={`${name} — ${homeLabel}`}
            className={cn(
                'flex min-h-11 items-center gap-2.5 text-ink no-underline xl:gap-3',
                className,
            )}
        >
            <BrandMark className="size-7 xl:size-[34px]" />
            <span className="flex flex-col leading-[1.1]">
                <span className="font-display text-xl font-semibold xl:text-2xl">
                    {name}
                </span>
                {tagline ? (
                    <span className="hidden text-[11px] tracking-[0.12em] text-caption uppercase xl:block">
                        {tagline}
                    </span>
                ) : null}
            </span>
        </Link>
    );
}
