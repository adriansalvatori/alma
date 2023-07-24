<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Billboard extends Component
{
    public $type;
    public $title;
    public $super;
    public $background;
    public $products;
    public $size;
    
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->type = $data['type'];
        $this->title = $data['title'];
        $this->super = $data['super'];
        $this->background = $this->defineBackground($data);
        $this->products = $this->defineProducts($data);
        $this->size = $data['size'] == 'full' ? 'is-12' : 'is-6';
    }

    /**
     * Define the background based on the selected type.
     * 
     * @return url string
     */

     public function defineBackground($data){
        if($data['type'] == 'video'){
            return $data['video'];
        }
       return $data['image'];
     }

    /**
     * Define the products based on if the Billboard has or hasn't a Space.
     * 
     * @return product array
     */

     public function defineProducts($data){
        if($data['hasspace']){
            return get_field('featured_products', $data['space']); 
        }
        return $data['products'];
     }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */

    public function render()
    {
        return view('components.billboard');
    }
}
