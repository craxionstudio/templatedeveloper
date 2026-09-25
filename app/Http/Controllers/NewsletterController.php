<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsletterRequest;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function store(StoreNewsletterRequest $request): RedirectResponse
    {
        if (! $request->isHoneypotFilled()) {
            // Email yang sudah terdaftar tidak dianggap error (tidak membocorkan siapa yang terdaftar).
            $subscriber = NewsletterSubscriber::query()->firstOrCreate(
                ['email' => Str::lower(trim($request->string('email')->toString()))],
                ['source' => Str::limit((string) parse_url((string) $request->input('source_page'), PHP_URL_PATH), 250, '') ?: null],
            );
            $subscriber->update(['status' => 'aktif']);
        }

        return back()->with('newsletter', 'subscribed');
    }
}
