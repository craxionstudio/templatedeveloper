import MessagePage from '@/components/site/message-page';
import PageHead from '@/components/site/page-head';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    content: {
        eyebrow: string | null;
        title: string;
        message: string | null;
        links: { label: string; url: string }[];
    };
};

export default function NotFound({ meta, content }: Props) {
    return (
        <>
            <PageHead meta={meta} />
            <MessagePage
                eyebrow={content.eyebrow}
                title={content.title}
                message={content.message}
                links={content.links}
            />
        </>
    );
}
