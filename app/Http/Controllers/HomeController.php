<?php

namespace App\Http\Controllers;

use App\Settings\HomePageSettings;
use App\Support\PageTitle;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Beranda. Milestone 2: masih hanya Hero (dari HomePageSettings); section lain di Milestone 3.
 */
class HomeController extends Controller
{
    public function __invoke(HomePageSettings $settings): Response
    {
        $seo = $settings->section('seo');
        $hero = $settings->section('hero');

        return Inertia::render('Home', [
            'meta' => [
                'title' => $seo['meta_title'] ?: PageTitle::home(),
                'description' => $seo['meta_description'],
            ],
            'hero' => $hero['enabled'] ? [
                'eyebrow' => $hero['eyebrow'],
                'title' => $hero['title'],
                'description' => $hero['description'],
                'media_label' => $hero['image_alt'],
                'primary_label' => $hero['primary_label'],
                'primary_url' => $hero['primary_url'],
                'secondary_label' => $hero['secondary_label'],
                'secondary_url' => $hero['secondary_url'] ?: null,
            ] : null,
        ]);
    }
}
