import type { ReactNode } from 'react';
import { ButtonLink, Eyebrow } from '@/components/site/ui';

/**
 * Halaman pesan singkat di tengah (Terima Kasih, 404).
 */
export default function MessagePage({
    eyebrow,
    title,
    message,
    links,
    icon,
    action,
}: {
    eyebrow?: string | null;
    title: string;
    message: string | null;
    links: { label: string; url: string }[];
    icon?: ReactNode;
    /** Tombol utama di atas link lanjutan (mis. lanjut chat WhatsApp). */
    action?: ReactNode;
}) {
    return (
        <section className="container-site flex min-h-[60vh] flex-col items-center justify-center gap-5 py-16 text-center xl:py-[120px]">
            {icon}
            {eyebrow ? <Eyebrow>{eyebrow}</Eyebrow> : null}
            <h1 className="max-w-[720px] font-display text-[34px] leading-[1.1] font-medium xl:text-[56px]">
                {title}
            </h1>
            {message ? (
                <p className="max-w-[560px] text-base leading-[1.7] text-body xl:text-[17px]">
                    {message}
                </p>
            ) : null}
            {action ? (
                <div className="mt-3 w-full md:w-auto [&>a]:w-full">
                    {action}
                </div>
            ) : null}
            {links.length > 0 ? (
                <div className="mt-3 flex w-full flex-col items-stretch gap-3 md:w-auto md:flex-row md:justify-center">
                    {links.map((link, index) => (
                        <ButtonLink
                            key={link.url}
                            href={link.url}
                            variant={
                                index === 0 && !action ? 'primary' : 'outline'
                            }
                        >
                            {link.label}
                        </ButtonLink>
                    ))}
                </div>
            ) : null}
        </section>
    );
}
