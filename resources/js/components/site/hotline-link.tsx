import { PhoneIcon } from '@/components/site/icons';
import { cn } from '@/lib/utils';

export default function HotlineLink({
    hotline,
    href,
    label,
    className,
}: {
    hotline: string;
    href: string | null;
    label: string;
    className?: string;
}) {
    const classes = cn(
        'flex items-center gap-2 text-[15px] font-semibold whitespace-nowrap text-ink no-underline',
        className,
    );

    // Selama hotline masih placeholder, tampil sebagai teks (tanpa link tel: palsu).
    if (!href) {
        return (
            <span className={classes}>
                <PhoneIcon className="size-[18px]" />
                {hotline}
            </span>
        );
    }

    return (
        <a href={href} aria-label={`${label} ${hotline}`} className={classes}>
            <PhoneIcon className="size-[18px]" />
            {hotline}
        </a>
    );
}
