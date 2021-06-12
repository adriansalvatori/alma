
@set($product, wc_get_product( get_the_ID() ))
@if($product)
<div class="card has-margin-bottom-30 has-text-white is-full-width">
    <div class="is-relative has-shadow-overlay has-min-height-300" style="background:url(@thumbnail('full', false)) center center / cover no-repeat;">
    </div>
    <div class="card-content content has-text-dark has-text-weight-bold">
        <div>
            <div class="title is-3">@title</div>
            <div class="subtitle">@excerpt</div>
        </div>
        <ul>
            <li>Regular price: {{$product->get_regular_price()}}</li>
            <li>Sale price: {{$product->get_sale_price()}}</li>
            <li>Price: {{$product->get_price()}}</li>
        </ul>
        <a data-id="{{get_the_ID()}}" class="button has-margin-top-20 add-cartinador state-animations"><i class="has-margin-right-10" data-feather="shopping-cart"></i>Agregar al carrito <span></span></a>
        <a href="@permalink" class="button has-margin-top-20"><i class="has-margin-right-10" data-feather="eye"></i>Ver mas</a>
    </div>
</div>
@else
<div class="card has-margin-bottom-30 has-text-white is-full-width">
    <div class="card-content content has-text-dark has-text-weight-bold">
        <h2>Error al cargar producto</h2>
        <p>Asegurate que tu wocoocommerce este activo y configurado</p>
    </div>
</div>
@endif
