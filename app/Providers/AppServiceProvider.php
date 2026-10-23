<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
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
        // En producción todas las URLs deben generarse con HTTPS.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        Carbon::setLocale(config('app.locale'));

        Blade::directive('bs', function (string $expresion) {
            return "<?php echo formato_bs($expresion); ?>";
        });
    }
}
