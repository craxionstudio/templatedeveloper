import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import type { MouseEvent } from 'react';
import { Icon } from '@/components/site/icons';
import LeadForm from '@/components/lead/lead-form';
import type { LeadPosition } from '@/components/lead/lead-form';

export type LeadModalContext = {
    clusterId?: number | null;
    houseTypeId?: number | null;
    position?: LeadPosition;
};

const EVENT = 'lead-modal:open';

/**
 * Buka form lead singkat. Dipakai tombol "Jadwalkan Kunjungan/Survey"; link aslinya
 * (href) tetap jadi cadangan untuk crawler dan kalau modal dimatikan di admin.
 */
export function openLeadModal(context: LeadModalContext = {}): void {
    window.dispatchEvent(
        new CustomEvent<LeadModalContext>(EVENT, { detail: context }),
    );
}

/**
 * onClick untuk link pemicu modal: batal navigasi hanya kalau modal aktif.
 */
export function useLeadModalTrigger(context: LeadModalContext = {}) {
    const enabled = usePage().props.site.leadModal !== null;

    return enabled
        ? (event: MouseEvent<Element>) => {
              event.preventDefault();
              openLeadModal(context);
          }
        : undefined;
}

/**
 * Modal form lead global (dipasang sekali di layout). Pakai <dialog> native: fokus
 * terkunci di dalam, Esc menutup, fokus kembali ke tombol pemicu.
 */
export default function LeadModal() {
    const { site } = usePage().props;
    const config = site.leadModal;
    const dialog = useRef<HTMLDialogElement>(null);
    const [context, setContext] = useState<LeadModalContext | null>(null);

    useEffect(() => {
        const open = (event: Event) =>
            setContext((event as CustomEvent<LeadModalContext>).detail ?? {});
        window.addEventListener(EVENT, open);

        return () => window.removeEventListener(EVENT, open);
    }, []);

    useEffect(() => {
        if (context && dialog.current && !dialog.current.open) {
            dialog.current.showModal();
        }
    }, [context]);

    // Tutup saat pindah halaman (mis. setelah submit sukses ke /terima-kasih).
    useEffect(() => router.on('navigate', () => dialog.current?.close()), []);

    if (!config) {
        return null;
    }

    return (
        <dialog
            ref={dialog}
            aria-labelledby="lead-modal-title"
            onClose={() => setContext(null)}
            onClick={(event) => {
                // Klik di backdrop (di luar panel) menutup modal.
                if (event.target === dialog.current) {
                    dialog.current.close();
                }
            }}
            className="m-auto w-[calc(100%-2.5rem)] max-w-[480px] rounded-card-sm bg-transparent p-0 text-ink backdrop:bg-ink/60"
        >
            {context ? (
                <div className="flex flex-col gap-4 rounded-card-sm bg-white p-6 md:p-8">
                    <div className="flex items-start justify-between gap-4">
                        <h2
                            id="lead-modal-title"
                            className="font-display text-2xl leading-snug font-semibold"
                        >
                            {config.title}
                        </h2>
                        <button
                            type="button"
                            onClick={() => dialog.current?.close()}
                            aria-label={site.labels.close}
                            className="-mt-1 -mr-2 flex size-11 shrink-0 items-center justify-center rounded-full hover:bg-ground"
                        >
                            <Icon name="close" className="size-5" />
                        </button>
                    </div>
                    {config.description ? (
                        <p className="text-[15px] leading-[1.6] text-body">
                            {config.description}
                        </p>
                    ) : null}
                    <LeadForm
                        position={context.position ?? 'modal'}
                        clusterId={context.clusterId}
                        houseTypeId={context.houseTypeId}
                        labels={{ submit: config.submitLabel }}
                    />
                </div>
            ) : null}
        </dialog>
    );
}
