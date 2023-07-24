@if($product)
  <article href="@permalink" class="product-context" x-data="{
        active_thumbnail: 0,
        gallery: JSON.parse('{{ $gallery }}')
    }">
    <div class="product-card">
      @if(!has_post_thumbnail())
        <figure x-data class="is-relative is-clipped">
          <img class="product-image empty-state"
            src="https://static.vecteezy.com/system/resources/previews/000/122/698/original/vector-free-home-appliances-icon-set.jpg"
            alt="@title">
        </figure>
      @elseif($product->get_gallery_image_ids())
        <x-product-gallery />
      @else
        <figure x-data class="is-relative is-clipped">
          <img class="product-image" src="@thumbnail('full', false)" alt="@title">
        </figure>
      @endif
      <div class="is-overlay has-padding-30 has-text-right">
        <a data-cursor="-hidden" data-cursor-text=" " class="button eye is-large is-rounded is-white" href="@permalink">
          <span class="icon is-small">@feather('maximize-2')</span>
        </a>
      </div>

      <div class="card-footer has-padding-bottom-20 has-padding-right-20 has-padding-left-20 is-borderless">
        <x-store-actions />
      </div>
    </div>
    <div class="product-caption has-padding-30">
      <div class="columns is-mobile is-paddingless">
        <div class="column is-8">
          <span class="is-size-6">{{ __('Doral™️', 'sage') }}
            <b>@productcat($product->get_id())</b></span><br>
          <a href="@permalink" class="has-text-dark is-block is-size-6" style="margin-top: 3px;"><b>@title</b></a>
        </div>
        <div class="column is-4 has-text-right">
          @if($product->get_gallery_image_ids())
            <x-product-slide-controls />
          @else
            <span data-cursor-text=" " data-cursor="-hidden" class="icon is-small has-text-grey">
              <i data-feather="circle" style="stroke-width: 2.5"></i>
            </span>
          @endif
          <div class="price has-text-weight-bold is-size-6">
            <x-price />
          </div>
        </div>
      </div>
    </div>
  </article>
@else
  <div class="card">
    <a data-cursor="-hidden" data-cursor-text=" " class="button eye is-large is-rounded is-white" href="@permalink">
        <span class="icon is-small">@feather('alert-triangle')</span>
        <span class="is-size-6"> Please <b>install/activate Woocommerce</b> for Products to work</span>
    </a>
    <div class="card-footer has-padding-bottom-20 has-padding-right-20 has-padding-left-20 is-borderless">
        <div class="container">
            If you're using alma-one, you can do it by running <pre>wp plugin activate woocommerce</pre> in your console. After that, just add some products.
        </div>
    </div>
  </div>
@endif
