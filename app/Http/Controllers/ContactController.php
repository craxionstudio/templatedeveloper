<?php

namespace App\Http\Controllers;

use App\Models\Cluster;
use App\Presenters\Image;
use App\Settings\ContactPageSettings;
use App\Settings\GlobalSettings;
use App\Support\Breadcrumbs;
use App\Support\Cta;
use App\Support\PageMeta;
use App\Support\SiteLayout;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman Kontak. Form ditampilkan di sini; penyimpanan lead, anti-spam, dan UTM di Milestone 4.
 */
class ContactController extends Controller
{
    public function __invoke(ContactPageSettings $settings, GlobalSettings $global): Response
    {
        $header = $settings->section('header');
        $info = $settings->section('info');
        $map = $settings->section('map');
        $form = $settings->section('form');
        $contact = $global->section('contact');

        return Inertia::render('Contact', [
            'meta' => PageMeta::make($settings->section('seo')['meta_title'] ?: $header['eyebrow'], $header['description'], $settings->section('seo')),
            'breadcrumbs' => Breadcrumbs::make([[$header['eyebrow']]]),
            'header' => $header,
            'info' => $info['enabled'] ? [
                'title' => $info['title'],
                'address' => $contact['office_address'],
                'hours' => ['label' => $info['hours_label'], 'value' => $contact['opening_hours']],
                'phone' => ['label' => $info['phone_label'], 'value' => $contact['phone'], 'url' => SiteLayout::telUrl($contact['phone'])],
                'email' => ['label' => $info['email_label'], 'value' => $contact['email'], 'url' => filter_var($contact['email'], FILTER_VALIDATE_EMAIL) ? 'mailto:'.$contact['email'] : null],
                'whatsapp' => ['label' => $info['whatsapp_label'], 'url' => SiteLayout::whatsappUrl($contact['whatsapp'], $contact['whatsapp_message'])],
            ] : null,
            'map' => $map['enabled'] ? [
                'embedUrl' => $map['embed_url'] ?: null,
                'image' => Image::path($map['image'], $map['image_alt']),
                'buttonLabel' => $map['button_label'],
            ] : null,
            'form' => $form['enabled'] ? [
                ...$form,
                'payment_options' => collect($form['payment_options'] ?? [])->map(fn ($o) => is_array($o) ? ($o['label'] ?? '') : (string) $o)->filter()->values()->all(),
                'clusters' => Cluster::query()->published()->ordered()->get(['id', 'name'])->map(fn (Cluster $c) => ['value' => $c->id, 'label' => $c->name])->all(),
                'privacyUrl' => '/kebijakan-privasi',
            ] : null,
            'cta' => Cta::resolve($settings->section('cta')),
        ]);
    }
}
