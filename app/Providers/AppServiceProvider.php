<?php

namespace App\Providers;

use App\Models\User;
use App\Support\AdminAccess;
use App\Support\DummyData;
use Carbon\CarbonImmutable;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::before(fn (User $user, string $ability, array $arguments) => AdminAccess::check($user, $ability, $arguments));

        $this->registerFilamentMacros();

        $this->configureRateLimiting();
    }

    /**
     * Rate limit form publik per IP (anti-spam, brief 9).
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('leads', fn (Request $request) => [
            Limit::perMinute(5)->by('lead-min:'.$request->ip()),
            Limit::perDay(30)->by('lead-day:'.$request->ip()),
        ]);

        RateLimiter::for('newsletter', fn (Request $request) => [
            Limit::perMinute(5)->by('newsletter-min:'.$request->ip()),
            Limit::perDay(20)->by('newsletter-day:'.$request->ip()),
        ]);
    }

    /**
     * ->dummyHint(fn (...) => bool): penanda "Data dummy" di field admin selama nilainya
     * masih nilai contoh dari seeder (lihat docs/DATA-DUMMY.md).
     */
    protected function registerFilamentMacros(): void
    {
        Field::macro('dummyHint', function (Closure $isDummy): Field {
            /** @var Field $this */
            $show = fn (Field $component): bool => (bool) $component->evaluate($isDummy);

            return $this
                ->hint(fn (Field $component): ?string => $show($component) ? DummyData::HINT : null)
                ->hintColor('warning')
                ->hintIcon(
                    fn (Field $component) => $show($component) ? Heroicon::OutlinedExclamationTriangle : null,
                    tooltip: DummyData::TOOLTIP,
                );
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
