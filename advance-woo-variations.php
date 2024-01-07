<?php
/*
Plugin Name: Custom WooCommerce Product Variations
Description: Add custom brand and color variations to WooCommerce products.
Version: 1.2
Author: Kythonlk
Author URI: https://kythonlk.com
*/



function wcv_enqueue_scripts() {
    wp_enqueue_script('wcv-script', plugins_url('/js/app.js', __FILE__), array('jquery'));
    wp_enqueue_style('wcv-style', plugins_url('/css/styles.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'wcv_enqueue_scripts');



add_action('woocommerce_before_add_to_cart_form', 'wcv_add_select_variant_button', 10);

function wcv_add_select_variant_button() {
    global $wpdb, $product;
    $target_product_ids = array(36, 37);

    if( in_array($product->get_id(), $target_product_ids) ) {
        $brands = $wpdb->get_col("SELECT DISTINCT manufacturer FROM wp_custom_colors ORDER BY manufacturer ASC");

        echo '<button id="selectVariantBtn">Select Variant</button>';
        echo '<div id="variantModal" class="modal" style="display:none;">';
        echo '<div class="modal-content">';
        echo '<span class="close">&times;</span>';
        echo '<h2>Select Variant</h2>';
        echo '<div class="select-variant-content">';
        echo '<select id="brandSelector">';
        foreach ($brands as $brand) {
            echo "<option value='$brand'>$brand</option>";
        }
        echo '</select>';
        echo '<div id="colorSelector"></div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}

add_action('wp_ajax_nopriv_load_colors', 'load_colors');
add_action('wp_ajax_load_colors', 'load_colors');

function load_colors() {
    global $wpdb;
    $brand_name = $_POST['brand_name'];

    $colors = $wpdb->get_results($wpdb->prepare(
        "SELECT colour_name, chip, code, stock_code FROM wp_custom_colors WHERE manufacturer = %s",
        $brand_name
    ));

    wp_send_json($colors);
}



function add_selected_variant_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
    if( isset( $_POST['selected_brand'] ) && isset( $_POST['selected_color'] ) ) {
        $cart_item_data['selected_brand'] = sanitize_text_field( $_POST['selected_brand'] );
        $cart_item_data['selected_color'] = sanitize_text_field( $_POST['selected_color'] );
    }
    return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'add_selected_variant_to_cart_item', 10, 3 );

function display_selected_variant_cart( $item_data, $cart_item ) {
    if( array_key_exists( 'selected_brand', $cart_item ) && array_key_exists( 'selected_color', $cart_item ) ) {
        $item_data[] = array(
            'name' => 'Selected Brand',
            'value' => $cart_item['selected_brand']
        );
        $item_data[] = array(
            'name' => 'Selected Color',
            'value' => $cart_item['selected_color']
        );
    }
    return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'display_selected_variant_cart', 10, 2 );

