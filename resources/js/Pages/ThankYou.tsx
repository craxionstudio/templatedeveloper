import { Icon } from '@/components/site/icons';
import MessagePage from '@/components/site/message-page';
import PageHead from '@/components/site/page-head';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    content: {
        title: string;
        message: string | null;
        links: { label: string; url: string }[];
    };
};

export default function ThankYou({ meta, content }: Props) {
    return (
        <>
            <PageHead meta={meta} />
            <MessagePage
                title={content.title}
                message={content.message}
                links={content.links}
                icon={
                    <span className="flex size-16 items-center justify-center rounded-full bg-[#E3EFE7] text-[#1F5A3A]">
                        <Icon name="check" className="size-8" />
                    </span>
                }
            />
        </>
    );
}
