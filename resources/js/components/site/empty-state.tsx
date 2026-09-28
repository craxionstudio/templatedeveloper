import { ButtonLink } from '@/components/site/ui';
import type { LinkData } from '@/types/content';

export type EmptyStateData = {
    title: string;
    description: string | null;
    button: LinkData | null;
};

/**
 * Keadaan kosong halaman daftar (belum ada konten yang dipublikasikan). Teks dari Pengaturan Halaman.
 */
export default function EmptyState({ state }: { state: EmptyStateData }) {
    return (
        <div className="flex flex-col items-center gap-4 rounded-card bg-white px-6 py-12 text-center xl:py-16">
            <h2 className="font-display text-[26px] leading-tight font-medium xl:text-[32px]">
                {state.title}
            </h2>
            {state.description ? (
                <p className="max-w-[560px] text-[15px] leading-relaxed text-body xl:text-base">
                    {state.description}
                </p>
            ) : null}
            {state.button ? (
                <ButtonLink href={state.button.url} className="mt-2">
                    {state.button.label}
                </ButtonLink>
            ) : null}
        </div>
    );
}
