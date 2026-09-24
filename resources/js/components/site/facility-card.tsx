import { ContentIcon, Icon } from '@/components/site/icons';
import Picture from '@/components/site/picture';
import type { FacilityCardData } from '@/types/content';

/**
 * Kartu fasilitas halaman Fasilitas: foto + badge kategori (desktop), baris foto kecil (mobile).
 */
export default function FacilityCard({
    facility,
}: {
    facility: FacilityCardData;
}) {
    return (
        <article className="grid grid-cols-[112px_1fr] gap-4 overflow-hidden rounded-card bg-white p-3 md:flex md:flex-col md:gap-0 md:p-0">
            <Picture
                image={facility.image}
                className="aspect-square rounded-[14px] text-[11px] md:aspect-auto md:h-[260px] md:rounded-none md:text-[13px]"
            >
                {facility.category ? (
                    <span className="absolute top-3.5 left-3.5 hidden items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-xs font-semibold tracking-normal text-ink normal-case md:inline-flex">
                        <ContentIcon
                            name={facility.category.icon ?? facility.icon}
                            className="size-3.5 text-terracotta"
                        />
                        {facility.category.name}
                    </span>
                ) : null}
            </Picture>
            <div className="flex flex-col gap-1.5 py-1 md:gap-2 md:p-6">
                {facility.category ? (
                    <span className="inline-flex items-center gap-1.5 text-[13px] font-semibold text-terracotta md:hidden">
                        <ContentIcon
                            name={facility.category.icon ?? facility.icon}
                            className="size-3.5"
                        />
                        {facility.category.name}
                    </span>
                ) : null}
                <h3 className="font-display text-lg leading-tight font-semibold md:text-[22px]">
                    {facility.name}
                </h3>
                {facility.description ? (
                    <p className="text-sm leading-[1.55] text-body">
                        {facility.description}
                    </p>
                ) : null}
                <span className="mt-auto hidden items-center gap-1.5 border-t border-[#ECE6DA] pt-3 text-[13px] text-caption md:inline-flex">
                    <Icon name="pin" className="size-3.5" />
                    {facility.kawasan}
                </span>
            </div>
        </article>
    );
}
