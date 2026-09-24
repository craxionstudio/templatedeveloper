<?php

namespace App\Support;

use App\Models\Area;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Author;
use App\Models\Cluster;
use App\Models\DeveloperProfile;
use App\Models\Facility;
use App\Models\FacilityCategory;
use App\Models\FutureDevelopment;
use App\Models\GalleryItem;
use App\Models\HouseType;
use App\Models\Kawasan;
use App\Models\Lead;
use App\Models\NewsletterSubscriber;
use App\Models\Promo;
use App\Models\Redirect;
use App\Models\SeoMeta;
use App\Models\Tag;
use App\Models\User;

/**
 * Hak akses admin per role (policy manual, dipasang lewat Gate::before di AppServiceProvider).
 *
 * - super_admin  : semua menu
 * - admin_konten : properti, konten, artikel, pengaturan halaman, pengaturan global, menu, redirect
 * - marketing    : lead & newsletter (tidak bisa hapus lead)
 */
class AdminAccess
{
    /**
     * @var list<class-string>
     */
    public const CONTENT_MODELS = [
        Area::class, Article::class, ArticleCategory::class, Author::class, Cluster::class, DeveloperProfile::class,
        Facility::class, FacilityCategory::class, FutureDevelopment::class, GalleryItem::class, HouseType::class,
        Kawasan::class, Promo::class, Redirect::class, SeoMeta::class, Tag::class,
    ];

    /**
     * @param  array<int, mixed>  $arguments
     */
    public static function check(User $user, string $ability, array $arguments): ?bool
    {
        $subject = $arguments[0] ?? null;
        $model = is_object($subject) ? $subject::class : $subject;

        if (! is_string($model)) {
            return null;
        }

        return match (true) {
            in_array($model, self::CONTENT_MODELS, true) => $user->canManageContent(),
            $model === Lead::class => self::lead($user, $ability),
            $model === NewsletterSubscriber::class => $user->canManageLeads(),
            $model === User::class => $user->isSuperAdmin(),
            default => null,
        };
    }

    private static function lead(User $user, string $ability): bool
    {
        return match ($ability) {
            // Lead hanya masuk dari form website.
            'create', 'replicate', 'reorder' => false,
            'delete', 'deleteAny', 'forceDelete', 'forceDeleteAny', 'restore', 'restoreAny' => $user->isSuperAdmin(),
            default => $user->canManageLeads(),
        };
    }
}
