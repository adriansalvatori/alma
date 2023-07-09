<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Roots\Acorn\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
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

        /** Json helper Directive 
         Blade::directive('json', function ($expression) {
            return "<?php echo 'JSON.parse(".json_decode( $expression ).")'; ?>";
        });*/

        /** Feather Directive */
        Blade::directive('feather', function ($expression) {
            return "<?php echo '<span class=icon><i data-feather='.{$expression}.'></i></span>'; ?>";
        });

        /** Product Category Directive */
        Blade::directive('productcat', function ($product) {
            return "<?php echo wc_get_product_category_list( $product, ', ', '', '' ); ?>";
        });
    }
}
