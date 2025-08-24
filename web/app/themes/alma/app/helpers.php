<?php

namespace App;

function single_product_gallery() {
    global $product;

    $attachment_ids = $product->get_gallery_image_ids();
    $gallery = [];
    
    /** First we have to add the featured image, it's excluded from the gallery */
    $featured_image = wp_get_attachment_url( $product->get_image_id() );
    array_push($gallery, $featured_image);
    
    /** Then we procceed with the full gallery */
    
    foreach( $attachment_ids as $attachment_id ) {
        $image_link = wp_get_attachment_url( $attachment_id );
    
        // We make sure there's never more than 5 images in the gallery
        if(count($gallery) < 5) {
            array_push($gallery, $image_link);
        } else {
            return $gallery;
        }
    }
    
    return $gallery;
}
