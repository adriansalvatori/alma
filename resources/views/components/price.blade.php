<div>
    @global($product)
    @set($symbol, get_woocommerce_currency_symbol() )
    @set($regularprice, $product->get_regular_price())
    @set($saleprice, $product->get_sale_price())

    @if($saleprice)
        <del class=has-text-grey> {!!$symbol!!}{{$regularprice}}</del>
        <span class="has-text-dark">{!!$symbol!!}{{$saleprice}}</span>
    @elseif($regularprice)
        <span class="has-text-dark">{!!$symbol!!}{{$regularprice}}</span>
    @else
        <span class="has-text-dark">{{__('Price Unavailable', 'sage')}}</span>
    @endif
</div>