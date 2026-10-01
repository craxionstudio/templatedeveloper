import { ContentIcon, Icon } from '@/components/site/icons';

export type BenefitSectionData = {
    title: string;
    groups: {
        category: string;
        items: { icon: string | null; text: string }[];
    }[];
    disclaimer: string;
    buttonLabel: string;
    whatsappUrl: string;
};

/**
 * "Promo & Benefit" di Detail Rumah: benefit dari Bank Benefit, dikelompokkan per kategori.
 * Klik tombol WA dilacak otomatis (analytics.listenForClicks) dengan sumber="promo_section".
 */
export default function BenefitSection({
    benefits,
    clusterName,
}: {
    benefits: BenefitSectionData;
    clusterName: string;
}) {
    return (
        <section className="rounded-card-sm bg-[#F6E3D8] p-5 md:p-7">
            <h2 className="font-display text-[22px] font-medium text-[#6E2E14] xl:text-2xl">
                {benefits.title}
            </h2>
            <div className="mt-5 grid gap-5 md:grid-cols-2">
                {benefits.groups.map((group) => (
                    <div key={group.category} className="flex flex-col gap-3">
                        <h3 className="text-[13px] font-semibold tracking-[0.08em] text-[#8A3A17] uppercase">
                            {group.category}
                        </h3>
                        <ul className="flex flex-col gap-2.5">
                            {group.items.map((item) => (
                                <li
                                    key={item.text}
                                    className="flex items-center gap-3"
                                >
                                    <span className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white text-terracotta">
                                        <ContentIcon
                                            name={item.icon}
                                            className="size-[18px]"
                                        />
                                    </span>
                                    <span className="font-semibold">
                                        {item.text}*
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </div>
                ))}
            </div>
            <p className="mt-5 text-[13px] text-[#8A3A17]">
                {benefits.disclaimer}
            </p>
            <a
                href={benefits.whatsappUrl}
                target="_blank"
                rel="noopener noreferrer"
                data-cluster={clusterName}
                data-position="promo_section"
                data-source="promo_section"
                className="mt-4 inline-flex min-h-12 items-center justify-center gap-2.5 rounded-full bg-terracotta px-6 py-3 text-center font-semibold text-white no-underline transition-colors hover:bg-terracotta-hover"
            >
                <Icon name="chat" className="size-5 shrink-0" />
                {benefits.buttonLabel}
            </a>
        </section>
    );
}
