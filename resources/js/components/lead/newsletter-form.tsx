import { useForm, usePage } from '@inertiajs/react';
import { useId, useState } from 'react';
import type { FormEvent } from 'react';
import Turnstile from '@/components/lead/turnstile';

/**
 * Form newsletter (email saja). POST /newsletter, tetap di halaman yang sama.
 */
export default function NewsletterForm({
    placeholder,
    buttonLabel,
}: {
    placeholder: string;
    buttonLabel: string;
}) {
    const { site, flash } = usePage().props;
    const id = useId();
    const [attempt, setAttempt] = useState(0);
    const form = useForm({
        email: '',
        website: '',
        turnstile_token: '',
        source_page: '',
    });
    const errors = form.errors as Record<string, string | undefined>;
    const error = errors.email ?? errors.form ?? errors.turnstile_token;

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            source_page: window.location.pathname,
        }));
        form.post('/newsletter', {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => form.reset(),
            onError: () => setAttempt((n) => n + 1),
        });
    };

    if (flash?.newsletter === 'subscribed' && form.wasSuccessful) {
        return (
            <p
                role="status"
                className="rounded-2xl bg-forest-2 px-5 py-4 text-[15px] text-ground"
            >
                {site.labels.newsletter_success}
            </p>
        );
    }

    return (
        <form
            onSubmit={onSubmit}
            noValidate
            className="relative flex flex-col gap-3"
        >
            <div className="flex flex-col gap-3 md:flex-row">
                <label htmlFor={`${id}-email`} className="sr-only">
                    {placeholder}
                </label>
                <input
                    id={`${id}-email`}
                    type="email"
                    name="email"
                    autoComplete="email"
                    required
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                    placeholder={placeholder}
                    aria-invalid={Boolean(error)}
                    aria-describedby={error ? `${id}-error` : undefined}
                    className="h-[52px] flex-1 rounded-full border-0 bg-white px-5 text-[15px] text-ink"
                />
                <button
                    type="submit"
                    disabled={form.processing}
                    className="h-[52px] rounded-full bg-terracotta px-7 font-semibold text-white hover:bg-terracotta-hover disabled:opacity-70"
                >
                    {form.processing ? site.labels.form_sending : buttonLabel}
                </button>
            </div>
            <div
                aria-hidden="true"
                className="absolute -left-[9999px] h-px w-px overflow-hidden"
            >
                <input
                    type="text"
                    name="website"
                    tabIndex={-1}
                    autoComplete="off"
                    value={form.data.website}
                    onChange={(e) => form.setData('website', e.target.value)}
                />
            </div>
            {site.tracking.turnstileSiteKey ? (
                <Turnstile
                    siteKey={site.tracking.turnstileSiteKey}
                    onToken={(token) => form.setData('turnstile_token', token)}
                    resetKey={attempt}
                />
            ) : null}
            {error ? (
                <p
                    id={`${id}-error`}
                    role="alert"
                    className="text-sm text-peach"
                >
                    {error}
                </p>
            ) : null}
        </form>
    );
}
