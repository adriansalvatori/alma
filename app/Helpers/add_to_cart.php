<?php

add_action( 'wp_ajax_nopriv_add_to_cartinador', function () {
    $product_id = apply_filters('ql_woocommerce_add_to_cart_product_id', absint($_POST['id']));

    $quantities = WC()->cart->get_cart_item_quantities();
    $disponibles = get_post_meta(33, '_stock', true) - $quantities[$product_id];
    $cantidad = 0;

    $data = [];
    $success = false;


    if($disponibles >= $cantidad || !get_post_meta($_POST['id'], '_stock', true) == 1){
        $cantidad = empty($_POST['quantity']) ? 1 : $_POST['quantity'];
    }else if($disponibles > 0) {
        $cantidad = $disponibles;
    }

    $cantidad = $cantidad == 0 ? 1 : $cantidad;
    $quantity = wc_stock_amount($cantidad);
    $variation_id = absint($_POST['id']);
    $passed_validation = apply_filters('ql_woocommerce_add_to_cart_validation', true, $product_id, $quantity);

    $product_status = get_post_status($product_id); 
    
    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity) && 'publish' === $product_status) { 
        $success = true;
    } else { 
        $success = false;
    }


    $cart = WC()->cart;
    $cart_details = [];
    $cart_details['single_total_price'] = $cart->get_cart_contents_total();
    $cart_details['currency_symbol'] = get_woocommerce_currency_symbol();
    $cart_details['total_price'] = $cart->get_cart_total();

    $cart_list = $cart->get_cart();
    $cart_total = [];
    $cart_total['cart_list'] = $cart_list;
    $cart_total['cart_details'] = $cart_details;
    
    $data['quantity'] = $quantity;
    $data['variation_id'] = $variation_id;
    $data['passed_validation'] = $passed_validation;
    $data['status'] = $product_status;
    $data['product_id'] = $product_id;
    $data['cart'] = $cart_total;
    $data['available_products'] = $disponibles;
    $data['product_url'] = get_permalink($product_id);

    if($success){
        wp_send_json_success(__($data));
    }else {
        wp_send_json_error(__($data));
    }
    wp_die();
});