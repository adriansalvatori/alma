<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ProductCard extends Component
{

    /**
     * The product instance
     */
    public $product;
    public $gallery;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($product = null)
    {
        $this -> product = $this->prepareProduct($product);
        $this -> gallery = $this->buildGallery($product); 
    }

    public function prepareProduct($product){
        if(function_exists('wc_get_product')) {
            return wc_get_product($product);
        }
        return null;
    }

    /**
     * Create a list with the gallery images.
     *
     * @return json array
     */
    public function buildGallery($product_id){
        if($this->product == null) {
            return null;
        }
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
                return json_encode($gallery);
            }
        }

        return json_encode($gallery);
    }


    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.product-card');
    }
}
