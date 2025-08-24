<?php

/**
 * Theme filters.
 */

namespace App;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'alma'));
});

// Deactivate Woocommerce styles
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );