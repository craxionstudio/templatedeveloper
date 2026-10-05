<?php

namespace App\Http\Controllers;

use App\Models\DeveloperProfile;
use App\Presenters\Image;
use App\Settings\AboutPageSettings;
use App\Support\Breadcrumbs;
use App\Support\Content;
use App\Support\Cta;
use App\Support\PageMeta;
use App\Support\RichText;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(AboutPageSettings $settings): Response
    {
        $profile = DeveloperProfile::current();
        $hero = $settings->section('hero');
        $history = $settings->section('history');
        $vision = $settings->section('vision');
        $stats = $settings->section('stats');
        $timeline = $settings->section('timeline');
        $team = $settings->section('team');
        $awards = $settings->section('awards');

        $title = $hero['title'] ?: $profile->headline;
        $description = $hero['description'] ?: $profile->description;

        $crumbs = Breadcrumbs::make([[Breadcrumbs::nav('/tentang-kami', $hero['eyebrow'])]]);
        $heroImage = $hero['image'] ? Image::path($hero['image'], $hero['image_alt']) : Image::media($profile, 'photo', $profile->photo_alt, $hero['image_alt']);

        return Inertia::render('About', [
            'meta' => PageMeta::make(
                $settings->section('seo')['meta_title'] ?: $hero['eyebrow'],
                $description,
                $settings->section('seo'),
                image: $heroImage['url'],
                section: 'tentang',
                breadcrumbs: $crumbs,
            ),
            'breadcrumbs' => $crumbs,
            'hero' => [
                'eyebrow' => $hero['eyebrow'],
                'title' => $title,
                'description' => $description,
                'image' => $heroImage,
                'quote' => $profile->vision_quote,
            ],
            'history' => Content::filled($history['body'] ?: $profile->history) ? [
                'title' => $history['title'],
                'body' => RichText::sanitize($history['body'] ?: $profile->history),
                'image' => Image::media($profile, 'secondary_photo', $profile->secondary_photo_alt, 'Foto tim'),
            ] : null,
            'vision' => Content::filled($vision['vision']) ? [
                'title' => $vision['title'],
                'visionLabel' => $vision['vision_label'],
                'vision' => $vision['vision'],
                'missionLabel' => $vision['mission_label'],
                'missions' => collect(Content::items($vision['missions'] ?? []))->map(fn ($m) => is_array($m) ? ($m['text'] ?? '') : (string) $m)->filter()->values()->all(),
            ] : null,
            'stats' => Content::filled($profile->stats) ? ['title' => $stats['title'], 'items' => array_values($profile->stats)] : null,
            'timeline' => Content::filled($timeline['items']) ? ['title' => $timeline['title'], 'items' => Content::items($timeline['items'])] : null,
            'team' => Content::filled($team['items']) ? [
                'title' => $team['title'],
                'items' => collect(Content::items($team['items']))->map(fn (array $m) => [
                    'name' => $m['name'] ?? '',
                    'role' => $m['role'] ?? '',
                    'photo' => Image::path($m['photo'] ?? null, $m['photo_alt'] ?? null, 'Foto '.($m['name'] ?? '')),
                ])->values()->all(),
            ] : null,
            'awards' => Content::filled($awards['items']) ? ['title' => $awards['title'], 'items' => Content::items($awards['items'])] : null,
            'cta' => Cta::resolve($settings->section('cta')),
        ]);
    }
}
