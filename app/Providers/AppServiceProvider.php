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
        Paginator::useBootstrapFive();

        Carbon::setLocale(config('app.locale'));

        // Render sirve la aplicación por HTTPS detrás de un proxy.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Blade::directive('bs', function (string $expresion) {
            return "<?php echo formato_bs($expresion); ?>";
        });
    }
}
