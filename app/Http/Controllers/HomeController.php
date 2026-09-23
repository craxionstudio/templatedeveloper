<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $brand = config('site.brand');

        return Inertia::render('home', [
            'meta' => [
                'title' => $brand['name'].' — '.$brand['tagline'],
                'description' => config('content.home.meta_description'),
            ],
            'hero' => config('content.home.hero'),
        ]);
    }
}
