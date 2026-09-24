import { cn } from '@/lib/utils';

const base = [
    '[&_a]:text-terracotta [&_a]:underline [&_blockquote]:bg-sand [&_blockquote_cite]:mt-2 [&_blockquote_cite]:block [&_blockquote_cite]:text-caption [&_blockquote_cite]:not-italic [&_blockquote_p]:font-display [&_blockquote_p]:leading-[1.45] [&_blockquote_p]:text-ink',
    '[&_figcaption]:text-caption [&_h2]:font-display [&_h2]:leading-[1.3] [&_h2]:font-semibold [&_h2]:text-ink [&_h3]:font-semibold [&_h3]:text-ink',
    '[&_li]:mt-2 [&_ol]:list-decimal [&_p:first-child]:mt-0 [&_ul]:list-disc [&_strong]:text-ink',
];

const sizes = {
    default: [
        'text-base leading-[1.75] text-body xl:text-[17px]',
        '[&_blockquote]:my-8 [&_blockquote]:rounded-card-sm [&_blockquote]:p-6 [&_blockquote_cite]:text-sm [&_blockquote_p]:text-xl [&_figcaption]:mt-2 [&_figcaption]:text-sm [&_figure]:my-8 [&_h2]:mt-10 [&_h2]:mb-3 [&_h2]:text-2xl [&_h3]:mt-8 [&_h3]:mb-2 [&_h3]:text-xl',
        '[&_img]:rounded-card-sm [&_ol]:pl-6 [&_p]:mt-4 [&_ul]:mt-4 [&_ul]:pl-6 [&_ol]:mt-4',
    ],
    /* Isi artikel (desain 06): paragraf 17/19px, H2 23/30px, paragraf pertama sebagai lead. */
    article: [
        'text-[17px] leading-[1.75] text-[#2E3A34] xl:text-[19px] xl:leading-[1.8]',
        '[&>p:first-child]:text-[19px] [&>p:first-child]:leading-[1.65] [&>p:first-child]:text-ink xl:[&>p:first-child]:text-[22px] xl:[&>p:first-child]:leading-[1.7]',
        '[&_h2]:mt-9 [&_h2]:mb-0 [&_h2]:text-[23px] xl:[&_h2]:mt-10 xl:[&_h2]:text-[30px] [&_h3]:mt-8 [&_h3]:text-xl xl:[&_h3]:text-2xl',
        '[&_p]:mt-5 xl:[&_p]:mt-6 [&_ul]:mt-5 [&_ul]:pl-[22px] [&_ol]:mt-5 [&_ol]:pl-[22px] xl:[&_ul]:pl-6 xl:[&_ol]:pl-6',
        '[&_blockquote]:my-7 [&_blockquote]:rounded-[18px] [&_blockquote]:px-5 [&_blockquote]:py-[22px] [&_blockquote_p]:text-xl [&_blockquote_cite]:text-[13px] xl:[&_blockquote]:my-10 xl:[&_blockquote]:rounded-card xl:[&_blockquote]:px-10 xl:[&_blockquote]:py-8 xl:[&_blockquote_p]:text-[26px] xl:[&_blockquote_cite]:text-[15px]',
        '[&_figure]:my-7 xl:[&_figure]:my-10 [&_figcaption]:mt-2 [&_figcaption]:text-[13px] xl:[&_figcaption]:mt-3 xl:[&_figcaption]:text-sm [&_img]:w-full [&_img]:rounded-[18px] xl:[&_img]:rounded-card',
    ],
};

/**
 * HTML rich text dari CMS (sudah disanitasi di server, App\Support\RichText).
 */
export default function RichText({
    html,
    className,
    size = 'default',
}: {
    html: string | null;
    className?: string;
    size?: keyof typeof sizes;
}) {
    if (!html) {
        return null;
    }

    return (
        <div
            className={cn(base, sizes[size], className)}
            dangerouslySetInnerHTML={{ __html: html }}
        />
    );
}
