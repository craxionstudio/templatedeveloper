import { ButtonLink } from '@/components/site/ui';
import type { CtaData } from '@/types/content';

export default function CtaSection({ cta }: { cta: CtaData }) {
    if (!cta) {
        return null;
    }

    return (
        <section id="kontak" className="container-site pb-16 xl:pb-[120px]">
            <div className="grid gap-8 rounded-card bg-terracotta px-6 py-9 text-white md:px-12 md:py-14 xl:grid-cols-2 xl:items-center xl:gap-16 xl:rounded-section xl:px-20 xl:py-[72px]">
                <div className="flex flex-col gap-4">
                    <p className="text-[13px] font-semibold tracking-[0.06em] text-[#FBE3D6] uppercase xl:text-sm">
                        {cta.eyebrow}
                    </p>
                    <h2 className="font-display text-[28px] leading-[1.2] font-medium xl:text-[44px] xl:leading-[1.15]">
                        {cta.title}
                    </h2>
                    <p className="text-[15px] leading-[1.6] text-[#FBE3D6] xl:text-[17px]">
                        {cta.description}
                    </p>
                </div>
                <div className="flex flex-col gap-3 xl:items-end">
                    <ButtonLink
                        href={cta.whatsappUrl}
                        variant="white"
                        icon="chat"
                        size="lg"
                    >
                        {cta.whatsappLabel}
                    </ButtonLink>
                    <ButtonLink
                        href={cta.visitUrl}
                        variant="outlineLight"
                        icon="calendar"
                        size="lg"
                    >
                        {cta.visitLabel}
                    </ButtonLink>
                </div>
            </div>
        </section>
    );
}
