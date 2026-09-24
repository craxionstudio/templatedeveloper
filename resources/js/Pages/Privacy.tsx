import Breadcrumbs from '@/components/site/breadcrumbs';
import PageHead from '@/components/site/page-head';
import RichText from '@/components/site/rich-text';
import type { Crumb } from '@/types/content';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    breadcrumbs: Crumb[];
    content: { title: string; body: string | null; effective: string | null };
};

export default function Privacy({ meta, breadcrumbs, content }: Props) {
    return (
        <>
            <PageHead meta={meta} />
            <article className="container-site flex flex-col gap-6 pt-4 pb-16 xl:items-center xl:gap-10 xl:pt-6 xl:pb-[120px]">
                <div className="w-full">
                    <Breadcrumbs items={breadcrumbs} />
                </div>
                <header className="flex flex-col gap-3 xl:w-[760px]">
                    <h1 className="font-display text-[34px] leading-[1.1] font-medium xl:text-[52px]">
                        {content.title}
                    </h1>
                    {content.effective ? (
                        <p className="text-sm text-caption">
                            {content.effective}
                        </p>
                    ) : null}
                </header>
                <div className="xl:w-[760px]">
                    <RichText html={content.body} />
                </div>
            </article>
        </>
    );
}
