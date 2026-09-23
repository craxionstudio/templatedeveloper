import { usePage } from '@inertiajs/react';
import SmartLink from '@/components/site/smart-link';

const linkClass =
    'flex min-h-11 items-center text-mist no-underline transition-colors hover:text-ground xl:min-h-0';

export default function SiteFooter() {
    const { site } = usePage().props;
    const { footer, contact, brand } = site;

    return (
        <footer className="bg-ink text-mist">
            <div className="container-site flex flex-col gap-8 pt-12 pb-8 xl:gap-14 xl:pt-20 xl:pb-12">
                <div className="flex flex-col gap-8 xl:grid xl:grid-cols-[2fr_1fr_1fr_1fr_1.4fr] xl:gap-12">
                    {/* Profil singkat */}
                    <div className="order-1 flex flex-col gap-2.5 xl:gap-4">
                        <p className="font-display text-2xl font-semibold text-ground xl:text-[28px]">
                            {brand.name}
                        </p>
                        <p className="text-sm leading-[1.7] xl:max-w-[340px] xl:text-[15px]">
                            {footer.description}
                        </p>
                    </div>

                    {/* Kolom link: 2 kolom di mobile, jadi kolom grid sendiri di desktop */}
                    <div className="order-2 grid grid-cols-2 gap-6 xl:contents">
                        {footer.columns.map((column) => (
                            <nav
                                key={column.title}
                                aria-label={column.title}
                                className="flex flex-col text-sm xl:order-2 xl:gap-3 xl:text-[15px]"
                            >
                                <h2 className="mb-1 font-bold text-ground xl:mb-0">
                                    {column.title}
                                </h2>
                                {column.links.map((link) => (
                                    <SmartLink
                                        key={`${link.label}-${link.url}`}
                                        href={link.url}
                                        newTab={link.new_tab}
                                        className={linkClass}
                                    >
                                        {link.label}
                                    </SmartLink>
                                ))}
                            </nav>
                        ))}
                    </div>

                    {/* Sosial: baris link di mobile, kolom di desktop */}
                    <nav
                        aria-label={footer.socialTitle}
                        className="order-4 flex flex-wrap gap-x-5 text-sm xl:order-3 xl:flex-col xl:gap-3 xl:text-[15px]"
                    >
                        <h2 className="sr-only font-bold text-ground xl:not-sr-only">
                            {footer.socialTitle}
                        </h2>
                        {footer.social.map((link) => (
                            <a
                                key={link.label}
                                href={link.url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex min-h-11 items-center text-ground underline underline-offset-4 transition-colors hover:text-peach xl:min-h-0 xl:text-mist xl:no-underline xl:hover:text-ground"
                            >
                                {link.label}
                            </a>
                        ))}
                    </nav>

                    {/* Kantor pemasaran */}
                    <address className="order-3 flex flex-col gap-1.5 text-sm leading-[1.6] not-italic xl:order-4 xl:gap-3 xl:text-[15px]">
                        <h2 className="font-bold text-ground">
                            {footer.officeTitle}
                        </h2>
                        <span>{contact.officeAddress}</span>
                        <span>
                            {contact.phone} · {contact.email}
                        </span>
                        <span>{contact.openingHours}</span>
                    </address>
                </div>

                <div className="flex flex-col gap-2 border-t border-forest-line pt-5 text-xs leading-[1.6] text-mist-muted xl:flex-row xl:justify-between xl:gap-8 xl:pt-7 xl:text-[13px]">
                    <p>{footer.copyright}</p>
                    <p className="xl:max-w-[640px] xl:text-right">
                        {footer.disclaimer}
                    </p>
                </div>
            </div>
        </footer>
    );
}
