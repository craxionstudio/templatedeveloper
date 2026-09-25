<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Rahasia di settings (access token CAPI, secret key Turnstile) disimpan terenkripsi
 * dengan APP_KEY dan hanya didekripsi di server saat dipakai.
 */
class Secret
{
    public static function encrypt(?string $value): string
    {
        return filled($value) ? Crypt::encryptString(trim($value)) : '';
    }

    public static function decrypt(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            // APP_KEY berganti atau nilai rusak: anggap belum diisi.
            return null;
        }
    }
}
