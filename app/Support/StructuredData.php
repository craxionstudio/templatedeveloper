<?php

namespace App\Support;

use App\Enums\ClusterStatus;
use App\Models\Area;
use App\Models\Article;
use App\Models\Cluster;
use App\Models\HouseType;
use App\Models\Kawasan;
use App\Settings\GlobalSettings;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\ItemAvailability;
use Spatie\SchemaOrg\MultiTypedEntity;
use Spatie\SchemaOrg\Product;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\SingleFamilyResidence;

/**
 * JSON-LD (brief 8.3) dengan spatie/schema-org. Semua method mengembalikan array siap
 * json_encode (termasuk @context); dirender sebagai <script type="application/ld+json">
 * lewat <Head> Inertia sehingga ikut di HTML SSR.
 */
class StructuredData
{
    public static function url(string $path = '/'): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return str_starts_with($path, 'http') ? $path : ($path === '/' ? $base.'/' : $base.'/'.ltrim($path, '/'));
    }

    public static function organizationId(): string
    {
        return self::url('/').'#organization';
    }

    /**
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $global = app(GlobalSettings::class);
        $identity = $global->section('identity');
        $contact = $global->section('contact');
        $telephone = self::telephone($contact['hotline']) ?? self::telephone($contact['phone']);

        $organization = Schema::organization()
            ->identifier(self::organizationId())
            ->name($identity['brand_name'])
            ->url(self::url('/'))
            ->logo(self::logo())
            ->sameAs(self::sameAs());

        if (self::isReal($identity['company_name'])) {
            $organization->legalName($identity['company_name']);
        }

        if ($telephone || filter_var($contact['email'], FILTER_VALIDATE_EMAIL)) {
            $organization->contactPoint(self::filter(Schema::contactPoint()
                ->contactType('sales')
                ->areaServed('ID')
                ->availableLanguage(['id'])
                ->if($telephone, fn ($point) => $point->telephone($telephone))
                ->if(filter_var($contact['email'], FILTER_VALIDATE_EMAIL), fn ($point) => $point->email($contact['email']))));
        }

        return $organization->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public static function website(): array
    {
        $identity = app(GlobalSettings::class)->section('identity');

        return Schema::webSite()
            ->identifier(self::url('/').'#website')
            ->name($identity['brand_name'])
            ->url(self::url('/'))
            ->inLanguage('id-ID')
            ->publisher(Schema::organization()->identifier(self::organizationId()))
            ->toArray();
    }

    /**
     * Kantor pemasaran sebagai RealEstateAgent (LocalBusiness).
     *
     * @return array<string, mixed>
     */
    public static function marketingOffice(): array
    {
        $global = app(GlobalSettings::class);
        $identity = $global->section('identity');
        $contact = $global->section('contact');
        $telephone = self::telephone($contact['phone']) ?? self::telephone($contact['hotline']);

        $office = Schema::realEstateAgent()
            ->identifier(self::url('/kontak').'#kantor-pemasaran')
            ->name($identity['brand_name'].' — '.$global->section('footer')['office_title'])
            ->url(self::url('/kontak'))
            ->image(self::logo())
            ->parentOrganization(Schema::organization()->identifier(self::organizationId()))
            ->address(self::address($contact['office_address']))
            ->priceRange('IDR');

        if ($telephone) {
            $office->telephone($telephone);
        }

        if (filter_var($contact['email'], FILTER_VALIDATE_EMAIL)) {
            $office->email($contact['email']);
        }

        if (is_numeric($contact['latitude'] ?? null) && is_numeric($contact['longitude'] ?? null)) {
            $office->geo(Schema::geoCoordinates()->latitude((float) $contact['latitude'])->longitude((float) $contact['longitude']));
        }

        if ($hours = self::openingHours((string) $contact['opening_hours'])) {
            $office->openingHoursSpecification($hours);
        }

        return $office->toArray();
    }

    /**
     * BreadcrumbList sama dengan breadcrumb yang tampil. Item terakhir (halaman ini) memakai canonical.
     *
     * @param  list<array{label: string, url: ?string}>  $crumbs
     * @return array<string, mixed>|null
     */
    public static function breadcrumbs(array $crumbs, string $canonical): ?array
    {
        if ($crumbs === []) {
            return null;
        }

        return Schema::breadcrumbList()
            ->itemListElement(collect($crumbs)->values()->map(fn (array $crumb, int $i) => Schema::listItem()
                ->position($i + 1)
                ->name($crumb['label'])
                ->item($crumb['url'] ? self::url($crumb['url']) : $canonical))->all())
            ->toArray();
    }

    /**
     * @param  iterable<array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public static function itemList(string $name, iterable $items): array
    {
        $elements = collect($items)->values()->map(fn (array $item, int $i) => Schema::listItem()
            ->position($i + 1)
            ->name($item['name'])
            ->url(self::url($item['url'])))->all();

        return Schema::itemList()->name($name)->numberOfItems(count($elements))->itemListElement($elements)->toArray();
    }

    /**
     * Detail Kawasan: Place (containedInPlace = lokasi Arunika).
     *
     * @return array<string, mixed>
     */
    public static function kawasan(Kawasan $kawasan, ?string $image): array
    {
        $area = Area::current();

        $place = Schema::place()
            ->identifier(self::url($kawasan->publicPath()).'#kawasan')
            ->name($kawasan->name)
            ->url(self::url($kawasan->publicPath()))
            ->description(PageMeta::description($kawasan->summary ?: $kawasan->description))
            ->containedInPlace(self::filter(Schema::place()
                ->name($area->name)
                ->if(self::isReal($area->location), fn ($p) => $p->address(self::address($area->location)))));

        if ($image) {
            $place->image($image);
        }

        if ($kawasan->latitude !== null && $kawasan->longitude !== null) {
            $place->geo(Schema::geoCoordinates()->latitude((float) $kawasan->latitude)->longitude((float) $kawasan->longitude));
        }

        return self::filter($place)->toArray();
    }

    /**
     * Detail Rumah: Residence (cluster) berisi tiap tipe sebagai Product + SingleFamilyResidence
     * dengan Offer (harga mulai, IDR, ketersediaan).
     *
     * @param  iterable<HouseType>  $types
     * @param  list<string>  $images
     * @return array<string, mixed>
     */
    public static function cluster(Cluster $cluster, iterable $types, array $images): array
    {
        $url = self::url($cluster->publicPath());
        $address = self::address($cluster->address);

        $residence = Schema::residence()
            ->identifier($url.'#cluster')
            ->name($cluster->name)
            ->url($url)
            ->description(PageMeta::description($cluster->summary ?: $cluster->description))
            ->address($address);

        if ($images !== []) {
            $residence->image($images);
        }

        if ($cluster->kawasan) {
            $residence->containedInPlace(Schema::place()->name($cluster->kawasan->name)->url(self::url($cluster->kawasan->publicPath())));
        }

        $residence->containsPlace(collect($types)->values()->map(function (HouseType $type) use ($cluster, $url, $address, $images): array {
            $entity = (new MultiTypedEntity)
                ->product(function (Product $product) use ($cluster, $type, $url, $images): void {
                    $product->name($cluster->name.' — '.$type->name)
                        ->url($url.'?tipe='.$type->slug)
                        ->brand(Schema::brand()->name($cluster->name));

                    if ($images !== []) {
                        $product->image($images[0]);
                    }

                    if ($type->price_from) {
                        $product->offers(Schema::offer()
                            ->price($type->price_from)
                            ->priceCurrency('IDR')
                            ->availability(self::availability($cluster, $type))
                            ->url($url.'?tipe='.$type->slug)
                            ->seller(Schema::organization()->identifier(self::organizationId())));
                    }
                })
                ->singleFamilyResidence(function (SingleFamilyResidence $house) use ($type, $address): void {
                    $house->address($address)
                        ->numberOfRooms($type->bedrooms + $type->extra_bedrooms)
                        ->numberOfBedrooms($type->bedrooms + $type->extra_bedrooms)
                        ->numberOfBathroomsTotal($type->bathrooms);

                    if ($type->building_area) {
                        $house->floorSize(Schema::quantitativeValue()->value($type->building_area)->unitCode('MTK')->unitText('m²'));
                    }
                });

            $data = $entity->toArray();
            unset($data['@context']);

            return $data;
        })->all());

        return $residence->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public static function article(Article $article, string $canonical, ?string $image): array
    {
        $posting = Schema::blogPosting()
            ->identifier($canonical.'#article')
            ->headline(mb_substr($article->title, 0, 110))
            ->description(PageMeta::description($article->excerpt ?: $article->body))
            ->url($canonical)
            ->mainEntityOfPage($canonical)
            ->inLanguage('id-ID')
            ->datePublished($article->published_at ?? $article->created_at)
            ->dateModified($article->updated_at ?? $article->published_at)
            ->publisher(Schema::organization()->identifier(self::organizationId())->name(app(GlobalSettings::class)->section('identity')['brand_name'])->logo(self::logo()));

        if ($image) {
            $posting->image($image);
        }

        if ($article->category) {
            $posting->articleSection($article->category->name);
        }

        if ($article->relationLoaded('tags') && $article->tags->isNotEmpty()) {
            $posting->keywords($article->tags->pluck('name')->implode(', '));
        }

        $posting->author($article->author
            ? Schema::person()->name($article->author->name)
            : Schema::organization()->identifier(self::organizationId()));

        return $posting->toArray();
    }

    private static function availability(Cluster $cluster, HouseType $type): string
    {
        return match (true) {
            $cluster->status === ClusterStatus::SoldOut, $type->units_available === 0 => ItemAvailability::SoldOut,
            $cluster->status === ClusterStatus::Inden => ItemAvailability::PreOrder,
            default => ItemAvailability::InStock,
        };
    }

    private static function address(?string $street): BaseType
    {
        return self::filter(Schema::postalAddress()
            ->addressCountry('ID')
            ->if(self::isReal($street), fn ($a) => $a->streetAddress((string) $street)));
    }

    /**
     * "Setiap hari, 09.00–17.00" / "Senin–Sabtu 08.00-16.00" → OpeningHoursSpecification.
     *
     * @return list<BaseType>
     */
    private static function openingHours(string $text): array
    {
        if (! preg_match('/(\d{1,2})[.:](\d{2})\s*[–\-]\s*(\d{1,2})[.:](\d{2})/u', $text, $time)) {
            return [];
        }

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $lower = mb_strtolower($text);

        if (preg_match('/senin\s*[–\-]\s*jumat/u', $lower)) {
            $days = array_slice($days, 0, 5);
        } elseif (preg_match('/senin\s*[–\-]\s*sabtu/u', $lower)) {
            $days = array_slice($days, 0, 6);
        } elseif (! str_contains($lower, 'setiap hari') && ! str_contains($lower, 'tiap hari')) {
            return [];
        }

        return [Schema::openingHoursSpecification()
            ->dayOfWeek(array_map(fn (string $day) => 'https://schema.org/'.$day, $days))
            ->opens(sprintf('%02d:%s', $time[1], $time[2]))
            ->closes(sprintf('%02d:%s', $time[3], $time[4]))];
    }

    private static function logo(): string
    {
        $logo = app(GlobalSettings::class)->section('identity')['logo_light'];

        return $logo ? asset('storage/'.ltrim($logo, '/')) : self::url('/apple-touch-icon.png');
    }

    /**
     * @return list<string>
     */
    private static function sameAs(): array
    {
        $global = app(GlobalSettings::class);

        return collect($global->section('seo')['same_as'] ?? [])
            ->merge(collect($global->section('footer')['social'] ?? [])->pluck('url'))
            ->filter(fn ($url) => is_string($url) && filter_var($url, FILTER_VALIDATE_URL) && str_starts_with($url, 'https://'))
            ->unique()
            ->values()
            ->all();
    }

    private static function telephone(?string $number): ?string
    {
        $tel = SiteLayout::telUrl($number);

        return $tel ? substr($tel, 4) : null;
    }

    /**
     * Teks dummy "[...]" tidak dianggap data asli.
     */
    private static function isReal(?string $value): bool
    {
        return filled($value) && ! preg_match('/^\[.*\]$/', trim((string) $value));
    }

    /**
     * Hapus properti kosong.
     */
    private static function filter(BaseType $type): BaseType
    {
        foreach ($type->getProperties() as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                unset($type[$key]);
            }
        }

        return $type;
    }
}
