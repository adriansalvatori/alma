<?php

namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;
use Illuminate\Support\Facades\Blade;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /** Feather Directive */
        Blade::directive('feather', function ($expression) {
            return "<?php echo '<span class=icon><i data-feather='.{$expression}.'></i></span>'; ?>";
        });

        /** Product Category Directive */
        Blade::directive('productcat', function ($product) {
            return "<?php echo wc_get_product_category_list( $product, ', ', '', '' ); ?>";
        });

        parent::boot();
    }
}
