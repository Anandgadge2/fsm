<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\Traits\FilterDataTrait;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

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
    public function boot()
{
    View::composer('*', function ($view) {
        $provider = new class {
            use FilterDataTrait;
        };

        $view->with($provider->filterData());
    });

    // Register Blade directive for formatting names
    Blade::directive('formatName', function ($expression) {
        return "<?php echo App\Helpers\FormatHelper::formatName($expression); ?>";
    });
}
}
