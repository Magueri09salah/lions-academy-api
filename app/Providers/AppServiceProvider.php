<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Stricter Eloquent: throw on lazy-loading and missing attribute access
        // in non-prod so issues are caught early.
        Model::shouldBeStrict(! $this->app->isProduction());
        Model::unguard(false);

        // Honor APP_URL when generating absolute URLs (signed media routes,
        // password-reset links sent to the admin app, etc.).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
