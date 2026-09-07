<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Illuminate\Database\Eloquent\Model::unguard();

        if (str_contains(request()->getHost(), 'drestro.com')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Automatically run migrations on other PCs when they upgrade
        try {
            $composerPath = base_path('composer.json');
            $versionKey = file_exists($composerPath) ? md5_file($composerPath) : 'default';
            $cacheKey = 'auto_migrated_' . $versionKey;

            if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                \Illuminate\Support\Facades\Cache::put($cacheKey, true, 86400 * 30); // Cache for 30 days
            }
        } catch (\Exception $e) {
            // Prevent crashes during initial install before DB file exists
        }
    }
}
