<?php

namespace App\Providers;

use App\Models\Testimonials;
use App\Models\Work;
use App\Policies\TestimonialPolicy;
use App\Policies\WorkPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

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

        Schema::defaultStringLength(191);
        Gate::policy(
            Work::class,
            WorkPolicy::class
        );

        Gate::policy(
            Testimonials::class,
            TestimonialPolicy::class
        );

        /*
         * Livewire update route.
         *
         * IMPORTANT:
         * Keep Livewire's original $handle. Do not wrap it in
         * app()->call() or invoke it ourselves.
         */

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/custom/livewire/update', $handle)
                ->middleware(['web'])
                ->name('custom.livewire.update');
        });

        /*
         * Temporary debugging for Livewire requests.
         *
         * This fires after Laravel has matched the route but does
         * not interfere with Livewire's request handler.
         */
        app()->booted(function () {
            app('router')->matched(function ($event) {
                $route = $event->route;
            });
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
