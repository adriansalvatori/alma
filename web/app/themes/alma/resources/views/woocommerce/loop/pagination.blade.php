@php
    /**
     * Pagination - Show numbered pagination for catalog pages
     *
     * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
     *
     * HOWEVER, on occasion WooCommerce will need to update template files and you
     * (the theme developer) will need to copy the new files to your theme to
     * maintain compatibility. We try to do this as little as possible, but it does
     * happen. When this occurs the version of the template file will be bumped and
     * the readme will list any important changes.
     *
     * @see     https://docs.woocommerce.com/document/template-structure/
     * @package WooCommerce\Templates
     * @version 3.3.1
     */

    if (!defined('ABSPATH')) {
        exit();
    }

    $total = isset($total) ? $total : wc_get_loop_prop('total_pages');
    $current = isset($current) ? $current : wc_get_loop_prop('current_page');
    $base = isset($base)
        ? $base
        : esc_url_raw(
            str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false))),
        );
    $format = isset($format) ? $format : '';

    if ($total <= 1) {
        return;
    }
@endphp

<nav class="woocommerce-pagination flex justify-between items-center mt-12 mb-8 sm:px-4"
    aria-label="{{ esc_attr__('Product Pagination', 'woocommerce') }}">
    @php
        $pages = paginate_links(
            apply_filters('woocommerce_pagination_args', [
                'base' => $base,
                'format' => $format,
                'add_args' => false,
                'current' => max(1, $current),
                'total' => $total,
                'prev_text' =>
                    '<span class="flex items-center gap-2 text-sm font-bold text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"><flux:icon.arrow-left class="w-4 h-4" /> Previous</span>',
                'next_text' =>
                    '<span class="flex items-center gap-2 text-sm font-bold text-zinc-900 dark:text-zinc-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Next <flux:icon.arrow-right class="w-4 h-4" /></span>',
                'type' => 'array',
                'end_size' => 1,
                'mid_size' => 2,
            ]),
        );

        if (is_array($pages)) {
            echo '<ul class="flex items-center gap-1 w-full justify-between m-0 p-0 list-none">';

            $prev = '';
            $next = '';
            $numbers = [];

            foreach ($pages as $page) {
                if (strpos($page, 'Previous') !== false) {
                    $prev = $page;
                } elseif (strpos($page, 'Next') !== false) {
                    $next = $page;
                } else {
                    $numbers[] = $page;
                }
            }

            // Prev Container
            echo '<li class="w-24 text-left">' . ($prev ?: '') . '</li>';

            // Numbers Container
            echo '<li class="items-center gap-1 hidden sm:flex">';
            foreach ($numbers as $number) {
                $is_current = strpos($number, 'current') !== false;

                if ($is_current) {
                    $number = str_replace(
                        'page-numbers current',
                        'page-numbers flex items-center justify-center w-8 h-8 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-white font-bold text-sm',
                        $number,
                    );
                } else {
                    $number = str_replace(
                        'page-numbers',
                        'page-numbers flex items-center justify-center w-8 h-8 rounded-md text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 hover:text-zinc-900 dark:hover:text-white font-semibold text-sm transition-colors',
                        $number,
                    );
                }
                echo '<div>' . $number . '</div>';
            }
            echo '</li>';

            // Next Container
            echo '<li class="w-24 text-right flex justify-end">' . ($next ?: '') . '</li>';

            echo '</ul>';
        }
    @endphp
</nav>
