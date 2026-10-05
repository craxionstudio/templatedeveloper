import { usePage } from '@inertiajs/react';
import SmartLink from '@/components/site/smart-link';
import { cn } from '@/lib/utils';

const linkClass =
    'flex min-h-11 items-center text-mist no-underline transition-colors hover:text-ground xl:min-h-0';

export default function SiteFooter() {
    const { site, cluster } = usePage<{
        cluster?: { whatsappUrl?: string };
    }>().props;
    const onCluster = Boolean(cluster?.whatsappUrl);
    const { footer, footerContact: office, brand } = site;
    const contactRows = (
        [
            ['phone', 'Telp', office.phone],
            ['whatsapp', 'WhatsApp', office.whatsapp],
            ['email', 'Email', office.email],
        ] as const
    ).filter(
        (row): row is typeof row & { 2: NonNullable<(typeof row)[2]> } =>
            row[2] !== null,
    );

    return (
        <footer className="bg-ink text-mist">
            <div
                className={cn(
                    // Ruang bawah untuk tombol WhatsApp melayang (+ bar harga sticky Detail Rumah di mobile).
                    'container-site flex flex-col gap-8 pt-12 xl:gap-14 xl:pt-20 xl:pb-32',
                    onCluster ? 'pb-[172px] md:pb-24' : 'pb-24',
                )}
            >
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
                                        data-position={link.position}
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

                    {/* Kantor pemasaran: pengganti halaman Kontak (isi dari Pengaturan Umum). */}
                    <address
                        id="info-kontak"
                        className="order-3 flex scroll-mt-6 flex-col gap-1.5 text-sm leading-[1.6] not-italic xl:order-4 xl:gap-3 xl:text-[15px]"
                    >
                        <h2 className="font-bold text-ground">
                            {footer.officeTitle}
                        </h2>
                        {office.address ? (
                            <span>{office.address.value}</span>
                        ) : null}
                        {contactRows.map(([key, label, item]) => (
                            <span key={key}>
                                {label}{' '}
                                {item.url ? (
                                    <SmartLink
                                        href={item.url}
                                        newTab={key === 'whatsapp'}
                                        data-position={
                                            key === 'whatsapp'
                                                ? 'footer'
                                                : undefined
                                        }
                                        className="break-words text-ground underline underline-offset-4 transition-colors hover:text-peach"
                                    >
                                        {item.value}
                                    </SmartLink>
                                ) : (
                                    item.value
                                )}
                            </span>
                        ))}
                        {office.hours ? (
                            <span>{office.hours.value}</span>
                        ) : null}
                        {office.maps?.url ? (
                            <SmartLink
                                href={office.maps.url}
                                newTab
                                className="self-start text-ground underline underline-offset-4 transition-colors hover:text-peach"
                            >
                                {office.maps.value}
                            </SmartLink>
                        ) : null}
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
