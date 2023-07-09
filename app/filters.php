<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Ver más" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return ' &hellip; <a href="' . get_permalink() . '">' . __('Ver más', 'sage') . '</a>';
});

add_filter ('woocommerce_enqueue_styles', '__return_empty_array'); 


