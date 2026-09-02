<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Poliza;
use App\Policies\PolizaPolicy;
use Illuminate\Support\Facades\Gate;


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
Gate::policy(Poliza::class, PolizaPolicy::class);    }
}
