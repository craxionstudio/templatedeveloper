<?php

namespace App\Support;

/**
 * Props keadaan kosong halaman daftar dari key empty_* di section settings.
 */
class EmptyState
{
    /**
     * @param  array<string, mixed>  $section
     * @return array{title: string, description: string|null, button: array{label: string, url: string}|null}
     */
    public static function make(array $section): array
    {
        return [
            'title' => (string) $section['empty_title'],
            'description' => filled($section['empty_description'] ?? null) ? $section['empty_description'] : null,
            'button' => filled($section['empty_button_label'] ?? null) && filled($section['empty_button_url'] ?? null)
                ? ['label' => $section['empty_button_label'], 'url' => $section['empty_button_url']]
                : null,
        ];
    }
}
