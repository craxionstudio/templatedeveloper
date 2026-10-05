<?php

namespace App\Http\Controllers;

use App\Presenters\Image;
use App\Settings\ContactPageSettings;
use App\Settings\GlobalSettings;
use App\Support\Breadcrumbs;
use App\Support\PageMeta;
use App\Support\SiteLayout;
use App\Support\StructuredData;
use App\Support\WhatsApp;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman Kontak: info kantor pemasaran, peta, dan tombol WhatsApp (tanpa form).
 */
class ContactController extends Controller
{
    public function __invoke(ContactPageSettings $settings, GlobalSettings $global): Response
    {
        $header = $settings->section('header');
        $info = $settings->section('info');
        $map = $settings->section('map');
        $contact = $global->section('contact');

        $crumbs = Breadcrumbs::make([[$header['eyebrow']]]);

        return Inertia::render('Contact', [
            'meta' => PageMeta::make(
                $settings->section('seo')['meta_title'] ?: $header['eyebrow'],
                $header['description'],
                $settings->section('seo'),
                section: 'kontak',
                breadcrumbs: $crumbs,
                schema: [StructuredData::marketingOffice()],
            ),
            'breadcrumbs' => $crumbs,
            'header' => $header,
            'info' => [
                'title' => $info['title'],
                'address' => $contact['office_address'],
                'hours' => ['label' => $info['hours_label'], 'value' => $contact['opening_hours']],
                'phone' => ['label' => $info['phone_label'], 'value' => $contact['phone'], 'url' => SiteLayout::telUrl($contact['phone'])],
                'email' => ['label' => $info['email_label'], 'value' => $contact['email'], 'url' => filter_var($contact['email'], FILTER_VALIDATE_EMAIL) ? 'mailto:'.$contact['email'] : null],
                'whatsapp' => ['label' => $info['whatsapp_label'], 'url' => WhatsApp::url()],
            ],
            'map' => filled($map['embed_url']) || filled($map['image']) ? [
                'embedUrl' => $map['embed_url'] ?: null,
                'image' => Image::path($map['image'], $map['image_alt']),
                'buttonLabel' => $map['button_label'],
            ] : null,
            // Form kontak dihapus: semua lewat WhatsApp dengan pesan otomatis (Pengaturan Umum).
            'whatsapp' => [
                'title' => 'Chat langsung via WhatsApp',
                'description' => 'Tanya harga, tipe rumah, promo, atau atur jadwal survey. Tim marketing kami membalas di jam kerja.',
                'label' => $global->section('cta')['whatsapp_label'],
                'url' => WhatsApp::url(),
            ],
            // Halaman Kontak sudah berisi ajakan menghubungi, jadi tanpa section CTA.
            'cta' => null,
        ]);
    }
}
