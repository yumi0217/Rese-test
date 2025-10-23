<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 本当の本番(トンネルやリバプロ越し)だけHTTPS強制。localhostは除外
        if (app()->environment('production') && request()->getHost() !== 'localhost') {
            URL::forceScheme('https');
        }
    }
}
