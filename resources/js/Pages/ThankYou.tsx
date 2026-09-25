import { useEffect, useRef } from 'react';
import { Icon } from '@/components/site/icons';
import MessagePage from '@/components/site/message-page';
import PageHead from '@/components/site/page-head';
import { ButtonLink } from '@/components/site/ui';
import { pixel, track } from '@/lib/analytics';
import type { PageMeta } from '@/types/site';

type Props = {
    meta: PageMeta;
    content: {
        title: string;
        message: string | null;
        links: { label: string; url: string }[];
    };
    /** Hanya ada tepat setelah submit form (flash sekali pakai). */
    conversion: {
        eventId: string;
        cluster: string | null;
        houseType: string | null;
        position: string | null;
    } | null;
    whatsapp: { label: string; url: string } | null;
};

export default function ThankYou({
    meta,
    content,
    conversion,
    whatsapp,
}: Props) {
    const sent = useRef<string | null>(null);

    // Event konversi: generate_lead (GA4/GTM) + Lead (Pixel) dengan event_id yang sama dengan CAPI.
    useEffect(() => {
        if (!conversion || sent.current === conversion.eventId) {
            return;
        }

        sent.current = conversion.eventId;
        track('generate_lead', {
            cluster: conversion.cluster,
            house_type: conversion.houseType,
            form_position: conversion.position,
            event_id: conversion.eventId,
        });
        pixel(
            'Lead',
            {
                content_name: conversion.cluster,
                content_category: conversion.position,
            },
            conversion.eventId,
        );
    }, [conversion]);

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
                action={
                    whatsapp ? (
                        <ButtonLink
                            href={whatsapp.url}
                            variant="dark"
                            icon="chat"
                            size="lg"
                            newTab
                            position="terima-kasih"
                            cluster={conversion?.cluster ?? undefined}
                        >
                            {whatsapp.label}
                        </ButtonLink>
                    ) : null
                }
            />
        </>
    );
}
