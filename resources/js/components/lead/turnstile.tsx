import { useEffect, useRef } from 'react';

const SCRIPT =
    'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
let loader: Promise<void> | null = null;

function loadScript(): Promise<void> {
    loader ??= new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = SCRIPT;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => {
            loader = null;
            reject(new Error('Turnstile gagal dimuat'));
        };
        document.head.appendChild(script);
    });

    return loader;
}

/**
 * Widget Cloudflare Turnstile. Hanya dirender kalau site key & secret key diatur di admin.
 * `resetKey` berubah → widget di-reset (mis. setelah submit gagal, token sekali pakai).
 */
export default function Turnstile({
    siteKey,
    onToken,
    resetKey,
}: {
    siteKey: string;
    onToken: (token: string) => void;
    resetKey?: unknown;
}) {
    const ref = useRef<HTMLDivElement>(null);
    const widget = useRef<string | null>(null);
    const callback = useRef(onToken);
    callback.current = onToken;

    useEffect(() => {
        let cancelled = false;

        void loadScript().then(() => {
            if (cancelled || !ref.current || !window.turnstile) {
                return;
            }

            widget.current = window.turnstile.render(ref.current, {
                sitekey: siteKey,
                language: 'id',
                appearance: 'interaction-only',
                callback: (token: string) => callback.current(token),
                'expired-callback': () => callback.current(''),
                'error-callback': () => callback.current(''),
            });
        });

        return () => {
            cancelled = true;

            if (widget.current) {
                window.turnstile?.remove(widget.current);
                widget.current = null;
            }
        };
    }, [siteKey]);

    useEffect(() => {
        if (resetKey !== undefined && widget.current) {
            window.turnstile?.reset(widget.current);
            callback.current('');
        }
    }, [resetKey]);

    return <div ref={ref} className="empty:hidden" />;
}
