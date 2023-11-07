<?php
/*
Plugin Name: Custom WooCommerce Product Variations
Description: Add custom brand and color variations to WooCommerce products.
Version: 1.1
Author: Kythonlk
Author URI: https://kythonlk.com
*/


 function enqueue_custom_js() {
        wp_enqueue_script('custom-js', plugin_dir_url(__FILE__) . './app.js', array('jquery'), '1.0', true);
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.2', true);
        wp_enqueue_style('styles', plugin_dir_url(__FILE__) . '/styles.css');
}

add_action('wp_enqueue_scripts', 'enqueue_custom_js');


function get_custom_brands() {
    global $wpdb;
    $table_name = 'brands'; // Replace with your custom brands table name
    $brands = $wpdb->get_results("SELECT * FROM $table_name");
    return $brands;
}

// Function to retrieve colors based on the selected brand
function get_colors_from_custom_database($brand_id) {
    global $wpdb;
    $table_name = 'color_codes'; // Replace with your custom colors table name
    $brand_id = intval($brand_id);
    $colors = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE brand_id = %d", $brand_id));
    return $colors;
}

function custom_get_colors_by_brand($request) {
    $brand_id = $request['brand_id'];
    $colors = get_colors_from_custom_database($brand_id);
    return rest_ensure_response($colors);
}

add_action('rest_api_init', function () {
    register_rest_route('custom-api/v1', '/colors/(?P<brand_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'custom_get_colors_by_brand',
    ));
});

 

function add_custom_selectors() {
    global $product;

    $allowed_product_ids = array(21164,21166 );
    if (is_a($product, 'WC_Product') && in_array($product->get_id(), $allowed_product_ids)) {
        echo '<div class="custom-selectors">
            <button type="button" class="btn SelectColorButton" data-bs-toggle="modal" data-bs-target="#selectionModal">Select Brand & Color</button>
        </div>';
    }
    
    echo '<div class="modal fade" id="selectionModal" tabindex="-1" aria-labelledby="selectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectionModalLabel">Select Brand & Color</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                    <div class="col-6">
                    <label for="brand">Brand: </label>
                    <select name="brand" id="brand" class="form-select">
                        <option value="">Select Brand</option>';
    
    // Retrieve brands from your custom database table
    $brands = get_custom_brands();

    foreach ($brands as $brand) {
        echo '<option value="' . esc_attr($brand->id) . '">' . esc_html($brand->brand_name) . '</option>';
    }

    echo '</select>
                    </div>
                    <div class="col-6">
                    <label for="color">Color: </label>
                    <select name="color" id="color" class="form-select">
                        <option value="">Select Brand First</option>
                    </select>
                    </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save changes</button>
                </div>
            </div>
        </div>
    </div>';
}
add_action('woocommerce_before_add_to_cart_button', 'add_custom_selectors');





function validate_custom_field($passed, $product_id, $quantity, $variation_id = null) {
    if (in_array($product_id, array(21164,21166))) {
        $brand = isset($_POST['brand']) ? wc_clean($_POST['brand']) : '';
        $color = isset($_POST['color']) ? wc_clean($_POST['color']) : '';

        if (empty($brand) || empty($color)) {
            wc_add_notice(__('Please select both brand and color.', 'text-domain'), 'error');
            $passed = false;
        }
    }

    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'validate_custom_field', 10, 4);

function add_custom_field_data_to_cart_item($cart_item_data, $product_id, $variation_id) {
    if (in_array($product_id, array(21164,21166))) {
        $brand = isset($_POST['brand']) ? wc_clean($_POST['brand']) : '';
        $color = isset($_POST['color']) ? wc_clean($_POST['color']) : '';

        if (!empty($brand) && !empty($color)) {
            $cart_item_data['custom_input'] = $brand . ' - ' . $color;
        }
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_custom_field_data_to_cart_item', 10, 3);

function display_custom_field_data_cart($item_data, $cart_item) {
    if (isset($cart_item['custom_input'])) {
        $product_id = $cart_item['product_id'];

        if (in_array($product_id, array(21164,21166))) {
            $item_data[] = array(
                'name' => __('Brand and Color', 'text-domain'),
                'value' => sanitize_text_field($cart_item['custom_input'])
            );
        }
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_custom_field_data_cart', 10, 2);

