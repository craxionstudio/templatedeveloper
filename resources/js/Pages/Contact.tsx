import { usePage } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import Breadcrumbs from '@/components/site/breadcrumbs';
import CtaSection from '@/components/site/cta-section';
import { Icon } from '@/components/site/icons';
import type { IconName } from '@/components/site/icons';
import PageHead from '@/components/site/page-head';
import Picture from '@/components/site/picture';
import SmartLink from '@/components/site/smart-link';
import { ButtonLink, Eyebrow } from '@/components/site/ui';
import type { Crumb, CtaData, ImageData } from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    breadcrumbs: Crumb[];
    header: { eyebrow: string; title: string; description: string };
    info: {
        title: string;
        address: string | null;
        hours: { label: string; value: string | null };
        phone: { label: string; value: string | null; url: string | null };
        email: { label: string; value: string | null; url: string | null };
        whatsapp: { label: string; url: string | null };
    } | null;
    map: {
        embedUrl: string | null;
        image: ImageData;
        buttonLabel: string;
    } | null;
    form: {
        title: string;
        name_label: string;
        whatsapp_label: string;
        email_label: string;
        interest_label: string;
        interest_placeholder: string;
        payment_label: string;
        payment_options: string[];
        message_label: string;
        consent_label: string;
        submit_label: string;
        clusters: { value: number; label: string }[];
        privacyUrl: string;
    } | null;
    cta: CtaData;
};

function InfoRow({
    icon,
    label,
    children,
}: {
    icon: IconName;
    label: string;
    children: ReactNode;
}) {
    return (
        <div className="flex gap-4">
            <span className="flex size-11 shrink-0 items-center justify-center rounded-full bg-sand text-terracotta">
                <Icon name={icon} className="size-5" />
            </span>
            <div className="flex flex-col gap-0.5">
                <dt className="text-[13px] text-caption">{label}</dt>
                <dd className="text-[15px] leading-[1.6] font-semibold xl:text-base">
                    {children}
                </dd>
            </div>
        </div>
    );
}

/**
 * Peta sebagai facade: iframe Google Maps baru dimuat setelah tombol diklik (performa & privasi).
 */
function MapFacade({ map }: { map: NonNullable<Props['map']> }) {
    const [loaded, setLoaded] = useState(false);

    if (loaded && map.embedUrl) {
        return (
            <iframe
                src={map.embedUrl}
                title={map.image.alt}
                loading="lazy"
                referrerPolicy="no-referrer-when-downgrade"
                className="h-[260px] w-full rounded-card-sm border-0 xl:h-[360px] xl:rounded-card"
            />
        );
    }

    return (
        <Picture
            image={map.image}
            className="h-[260px] rounded-card-sm xl:h-[360px] xl:rounded-card"
        >
            {map.embedUrl ? (
                <button
                    type="button"
                    onClick={() => setLoaded(true)}
                    className="absolute bottom-4 left-4 inline-flex h-12 items-center gap-2 rounded-full bg-white px-5 text-[15px] font-semibold tracking-normal text-ink normal-case shadow-md hover:bg-ground"
                >
                    <Icon name="pin" className="size-5 text-terracotta" />
                    {map.buttonLabel}
                </button>
            ) : null}
        </Picture>
    );
}

/**
 * Teks persetujuan: frasa "Kebijakan Privasi" di dalamnya dijadikan link. Kalau frasa tidak ada,
 * link ditambahkan di belakang.
 */
function linkPrivacy(text: string, phrase: string, url: string): ReactNode {
    const link = (
        <SmartLink href={url} className="font-semibold">
            {phrase}
        </SmartLink>
    );
    const index = text.indexOf(phrase);

    if (index === -1) {
        return (
            <>
                {text} {link}
            </>
        );
    }

    return (
        <>
            {text.slice(0, index)}
            {link}
            {text.slice(index + phrase.length)}
        </>
    );
}

export default function Contact({
    meta,
    breadcrumbs,
    header,
    info,
    map,
    form,
    cta,
}: Props) {
    const { labels } = usePage().props.site;
    const input =
        'h-12 w-full rounded-xl border-[1.5px] border-[#CFC7B6] bg-white px-3.5 text-[15px] font-normal placeholder:text-caption focus:border-ink focus:outline-none';
    const field = 'flex flex-col gap-1.5 text-sm font-semibold';
    // Penyimpanan lead, validasi server, dan anti-spam di Milestone 4.
    const onSubmit = (e: FormEvent) => e.preventDefault();

    return (
        <>
            <PageHead meta={meta} />
            <section className="container-site flex flex-col gap-5 pt-4 xl:gap-8 xl:pt-6">
                <Breadcrumbs items={breadcrumbs} />
                <div className="grid gap-3 xl:grid-cols-[1.4fr_1fr] xl:items-end xl:gap-16">
                    <div className="flex flex-col gap-3 xl:gap-4">
                        <Eyebrow>{header.eyebrow}</Eyebrow>
                        <h1 className="font-display text-[34px] leading-[1.1] font-medium xl:text-[56px]">
                            {header.title}
                        </h1>
                    </div>
                    <p className="text-base leading-[1.7] text-body xl:text-[17px]">
                        {header.description}
                    </p>
                </div>
            </section>

            <section className="container-site grid gap-8 py-10 xl:grid-cols-[1fr_1.1fr] xl:items-start xl:gap-16 xl:py-16">
                {info || map ? (
                    <div className="flex flex-col gap-6">
                        {info ? (
                            <div className="flex flex-col gap-5 rounded-card-sm bg-white p-6 xl:rounded-card xl:p-8">
                                <h2 className="font-display text-2xl font-semibold xl:text-[28px]">
                                    {info.title}
                                </h2>
                                <dl className="flex flex-col gap-5">
                                    {info.address ? (
                                        <InfoRow
                                            icon="pin"
                                            label={labels.address}
                                        >
                                            {info.address}
                                        </InfoRow>
                                    ) : null}
                                    {info.hours.value ? (
                                        <InfoRow
                                            icon="clock"
                                            label={info.hours.label}
                                        >
                                            {info.hours.value}
                                        </InfoRow>
                                    ) : null}
                                    {info.phone.value ? (
                                        <InfoRow
                                            icon="phone"
                                            label={info.phone.label}
                                        >
                                            {info.phone.url ? (
                                                <SmartLink
                                                    href={info.phone.url}
                                                    className="text-ink no-underline hover:text-terracotta"
                                                >
                                                    {info.phone.value}
                                                </SmartLink>
                                            ) : (
                                                info.phone.value
                                            )}
                                        </InfoRow>
                                    ) : null}
                                    {info.email.value ? (
                                        <InfoRow
                                            icon="mail"
                                            label={info.email.label}
                                        >
                                            {info.email.url ? (
                                                <SmartLink
                                                    href={info.email.url}
                                                    className="break-all text-ink no-underline hover:text-terracotta"
                                                >
                                                    {info.email.value}
                                                </SmartLink>
                                            ) : (
                                                info.email.value
                                            )}
                                        </InfoRow>
                                    ) : null}
                                </dl>
                                {info.whatsapp.url ? (
                                    <ButtonLink
                                        href={info.whatsapp.url}
                                        variant="dark"
                                        icon="chat"
                                        newTab
                                        className="self-start"
                                    >
                                        {info.whatsapp.label}
                                    </ButtonLink>
                                ) : null}
                            </div>
                        ) : null}
                        {map ? <MapFacade map={map} /> : null}
                    </div>
                ) : null}

                {form ? (
                    <div
                        id="form-kontak"
                        className="flex scroll-mt-28 flex-col gap-5 rounded-card-sm bg-white p-6 shadow-[0_18px_40px_-24px_rgba(30,43,36,0.35)] xl:rounded-card xl:p-10"
                    >
                        <h2 className="font-display text-2xl font-semibold xl:text-[28px]">
                            {form.title}
                        </h2>
                        <form
                            onSubmit={onSubmit}
                            className="grid gap-4 md:grid-cols-2"
                        >
                            <label className={field}>
                                {form.name_label}
                                <input
                                    type="text"
                                    name="name"
                                    autoComplete="name"
                                    required
                                    className={input}
                                />
                            </label>
                            <label className={field}>
                                {form.whatsapp_label}
                                <input
                                    type="tel"
                                    name="whatsapp"
                                    autoComplete="tel"
                                    inputMode="tel"
                                    required
                                    className={input}
                                />
                            </label>
                            <label className={`${field} md:col-span-2`}>
                                {form.email_label}
                                <input
                                    type="email"
                                    name="email"
                                    autoComplete="email"
                                    className={input}
                                />
                            </label>
                            <label className={field}>
                                {form.interest_label}
                                <select
                                    name="cluster_id"
                                    className={input}
                                    defaultValue=""
                                >
                                    <option value="">
                                        {form.interest_placeholder}
                                    </option>
                                    {form.clusters.map((cluster) => (
                                        <option
                                            key={cluster.value}
                                            value={cluster.value}
                                        >
                                            {cluster.label}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            {form.payment_options.length > 0 ? (
                                <label className={field}>
                                    {form.payment_label}
                                    <select
                                        name="payment_plan"
                                        className={input}
                                        defaultValue=""
                                    >
                                        <option value="">-</option>
                                        {form.payment_options.map((option) => (
                                            <option key={option} value={option}>
                                                {option}
                                            </option>
                                        ))}
                                    </select>
                                </label>
                            ) : null}
                            <label className={`${field} md:col-span-2`}>
                                {form.message_label}
                                <textarea
                                    name="message"
                                    rows={4}
                                    className={`${input} h-auto py-3`}
                                />
                            </label>
                            <label className="flex items-start gap-3 text-sm leading-[1.5] text-body md:col-span-2">
                                <input
                                    type="checkbox"
                                    name="consent"
                                    required
                                    className="mt-0.5 size-5 shrink-0 accent-terracotta"
                                />
                                <span>
                                    {linkPrivacy(
                                        form.consent_label,
                                        labels.privacy_policy,
                                        form.privacyUrl,
                                    )}
                                </span>
                            </label>
                            <button
                                type="submit"
                                className="flex h-[52px] items-center justify-center rounded-full bg-terracotta font-semibold text-white hover:bg-terracotta-hover md:col-span-2"
                            >
                                {form.submit_label}
                            </button>
                        </form>
                    </div>
                ) : null}
            </section>

            <CtaSection cta={cta} />
        </>
    );
}
