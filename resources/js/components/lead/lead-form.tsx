import { useForm, usePage } from '@inertiajs/react';
import { useId, useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import Turnstile from '@/components/lead/turnstile';
import SmartLink from '@/components/site/smart-link';
import { cn } from '@/lib/utils';

export type LeadPosition = 'sidebar' | 'inline' | 'modal' | 'kontak' | 'sticky';

type Option = { value: string | number; label: string };

export type LeadFormProps = {
    position: LeadPosition;
    clusterId?: number | null;
    houseTypeId?: number | null;
    /** Field tambahan (halaman Kontak). */
    email?: { label: string } | null;
    interest?: { label: string; placeholder: string; options: Option[] } | null;
    payment?: { label: string; options: string[] } | null;
    message?: { label: string } | null;
    labels?: Partial<{
        name: string;
        namePlaceholder: string;
        whatsapp: string;
        whatsappPlaceholder: string;
        consent: string;
        submit: string;
    }>;
    privacyUrl?: string;
    layout?: 'stack' | 'grid';
    className?: string;
    footer?: ReactNode;
};

export const inputClass =
    'h-12 w-full rounded-xl border-[1.5px] border-[#CFC7B6] bg-white px-3.5 text-[15px] font-normal text-ink placeholder:text-caption focus:border-ink focus:outline-none aria-[invalid=true]:border-[#B3261E]';

function FieldError({ id, message }: { id: string; message?: string }) {
    return message ? (
        <span id={id} className="text-[13px] font-normal text-[#B3261E]">
            {message}
        </span>
    ) : null;
}

/**
 * Teks persetujuan: frasa "Kebijakan Privasi" di dalamnya dijadikan link.
 */
function consentText(text: string, phrase: string, url: string): ReactNode {
    const index = text.indexOf(phrase);
    const link = (
        <SmartLink href={url} className="font-semibold" newTab>
            {phrase}
        </SmartLink>
    );

    return index === -1 ? (
        <>
            {text} {link}
        </>
    ) : (
        <>
            {text.slice(0, index)}
            {link}
            {text.slice(index + phrase.length)}
        </>
    );
}

/**
 * Form lead (brief 9): nama, WhatsApp, persetujuan UU PDP, honeypot, Turnstile opsional.
 * Submit → POST /lead → /terima-kasih (event konversi dikirim di sana).
 */
export default function LeadForm({
    position,
    clusterId = null,
    houseTypeId = null,
    email,
    interest,
    payment,
    message,
    labels: override = {},
    privacyUrl = '/kebijakan-privasi',
    layout = 'stack',
    className,
    footer,
}: LeadFormProps) {
    const { site } = usePage().props;
    const labels = site.labels;
    const id = useId();
    const [attempt, setAttempt] = useState(0);

    const form = useForm({
        name: '',
        whatsapp: '',
        email: '',
        cluster_id: clusterId ? String(clusterId) : '',
        house_type_id: houseTypeId ? String(houseTypeId) : '',
        payment_plan: '',
        message: '',
        consent: false,
        website: '',
        turnstile_token: '',
        source_page: '',
        source_position: position,
    });

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            // Tipe hanya relevan kalau cluster tidak diganti.
            house_type_id:
                data.cluster_id === String(clusterId ?? '')
                    ? data.house_type_id
                    : '',
            source_page: window.location.pathname + window.location.search,
        }));
        form.post('/lead', {
            preserveScroll: true,
            onError: () => setAttempt((n) => n + 1),
        });
    };

    const errors = form.errors as Record<string, string | undefined>;
    const field = 'flex flex-col gap-1.5 text-sm font-semibold';
    const wide = layout === 'grid' ? 'md:col-span-2' : '';
    const describedBy = (name: string) =>
        errors[name] ? `${id}-${name}-error` : undefined;
    const general = errors.form ?? errors.turnstile_token;
    const hasErrors = Object.keys(errors).length > 0;

    return (
        <form
            onSubmit={onSubmit}
            noValidate
            className={cn(
                'relative grid gap-3',
                layout === 'grid' && 'gap-4 md:grid-cols-2',
                className,
            )}
        >
            <label className={field}>
                {override.name ?? labels.form_name}
                <input
                    type="text"
                    name="name"
                    autoComplete="name"
                    required
                    maxLength={100}
                    value={form.data.name}
                    onChange={(e) => form.setData('name', e.target.value)}
                    placeholder={
                        override.namePlaceholder ?? labels.form_name_placeholder
                    }
                    className={inputClass}
                    aria-invalid={Boolean(errors.name)}
                    aria-describedby={describedBy('name')}
                />
                <FieldError id={`${id}-name-error`} message={errors.name} />
            </label>
            <label className={field}>
                {override.whatsapp ?? labels.form_whatsapp}
                <input
                    type="tel"
                    name="whatsapp"
                    autoComplete="tel"
                    inputMode="tel"
                    required
                    maxLength={25}
                    value={form.data.whatsapp}
                    onChange={(e) => form.setData('whatsapp', e.target.value)}
                    placeholder={
                        override.whatsappPlaceholder ??
                        labels.form_whatsapp_placeholder
                    }
                    className={inputClass}
                    aria-invalid={Boolean(errors.whatsapp)}
                    aria-describedby={describedBy('whatsapp')}
                />
                <FieldError
                    id={`${id}-whatsapp-error`}
                    message={errors.whatsapp}
                />
            </label>
            {email ? (
                <label className={cn(field, wide)}>
                    {email.label}
                    <input
                        type="email"
                        name="email"
                        autoComplete="email"
                        maxLength={150}
                        value={form.data.email}
                        onChange={(e) => form.setData('email', e.target.value)}
                        className={inputClass}
                        aria-invalid={Boolean(errors.email)}
                        aria-describedby={describedBy('email')}
                    />
                    <FieldError
                        id={`${id}-email-error`}
                        message={errors.email}
                    />
                </label>
            ) : null}
            {interest ? (
                <label className={field}>
                    {interest.label}
                    <select
                        name="cluster_id"
                        value={form.data.cluster_id}
                        onChange={(e) =>
                            form.setData('cluster_id', e.target.value)
                        }
                        className={inputClass}
                        aria-invalid={Boolean(errors.cluster_id)}
                        aria-describedby={describedBy('cluster_id')}
                    >
                        <option value="">{interest.placeholder}</option>
                        {interest.options.map((option) => (
                            <option
                                key={option.value}
                                value={String(option.value)}
                            >
                                {option.label}
                            </option>
                        ))}
                    </select>
                    <FieldError
                        id={`${id}-cluster_id-error`}
                        message={errors.cluster_id}
                    />
                </label>
            ) : null}
            {payment && payment.options.length > 0 ? (
                <label className={field}>
                    {payment.label}
                    <select
                        name="payment_plan"
                        value={form.data.payment_plan}
                        onChange={(e) =>
                            form.setData('payment_plan', e.target.value)
                        }
                        className={inputClass}
                    >
                        <option value="">-</option>
                        {payment.options.map((option) => (
                            <option key={option} value={option}>
                                {option}
                            </option>
                        ))}
                    </select>
                </label>
            ) : null}
            {message ? (
                <label className={cn(field, wide)}>
                    {message.label}
                    <textarea
                        name="message"
                        rows={4}
                        maxLength={2000}
                        value={form.data.message}
                        onChange={(e) =>
                            form.setData('message', e.target.value)
                        }
                        className={cn(inputClass, 'h-auto py-3')}
                        aria-invalid={Boolean(errors.message)}
                        aria-describedby={describedBy('message')}
                    />
                    <FieldError
                        id={`${id}-message-error`}
                        message={errors.message}
                    />
                </label>
            ) : null}

            {/* Honeypot: tidak terlihat & tidak bisa difokus; bot yang mengisinya diabaikan server. */}
            <div
                aria-hidden="true"
                className="absolute -left-[9999px] h-px w-px overflow-hidden"
            >
                <label>
                    Website
                    <input
                        type="text"
                        name="website"
                        tabIndex={-1}
                        autoComplete="off"
                        value={form.data.website}
                        onChange={(e) =>
                            form.setData('website', e.target.value)
                        }
                    />
                </label>
            </div>

            <label
                className={cn(
                    'flex items-start gap-3 text-[13px] leading-[1.5] text-body xl:text-sm',
                    wide,
                )}
            >
                <input
                    type="checkbox"
                    name="consent"
                    required
                    checked={form.data.consent}
                    onChange={(e) => form.setData('consent', e.target.checked)}
                    className="mt-0.5 size-5 shrink-0 accent-terracotta"
                    aria-invalid={Boolean(errors.consent)}
                    aria-describedby={describedBy('consent')}
                />
                <span className="flex flex-col gap-1">
                    <span>
                        {consentText(
                            override.consent ?? labels.form_consent,
                            labels.privacy_policy,
                            privacyUrl,
                        )}
                    </span>
                    <FieldError
                        id={`${id}-consent-error`}
                        message={errors.consent}
                    />
                </span>
            </label>

            {site.tracking.turnstileSiteKey ? (
                <div className={wide}>
                    <Turnstile
                        siteKey={site.tracking.turnstileSiteKey}
                        onToken={(token) =>
                            form.setData('turnstile_token', token)
                        }
                        resetKey={attempt}
                    />
                </div>
            ) : null}

            {general || hasErrors ? (
                <p
                    role="alert"
                    className={cn(
                        'rounded-xl bg-[#FBE9E7] px-3.5 py-2.5 text-[13px] text-[#8C1D18]',
                        wide,
                    )}
                >
                    {general ?? labels.form_error}
                </p>
            ) : null}

            <button
                type="submit"
                disabled={form.processing}
                className={cn(
                    'mt-1 flex h-[52px] items-center justify-center rounded-full bg-terracotta font-semibold text-white hover:bg-terracotta-hover disabled:opacity-70',
                    wide,
                )}
            >
                {form.processing
                    ? labels.form_sending
                    : (override.submit ?? labels.form_submit)}
            </button>
            {footer}
        </form>
    );
}
