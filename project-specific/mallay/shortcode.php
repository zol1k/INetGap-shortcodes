<?php
function inetgap_product_params_shortcode() {
    ob_start(); // Start output buffering

    $post_type = get_post_type();

    $acf_field_name = $post_type === 'airconditioner' ? 'aircond-params' :
                      ($post_type === 'heatpump' ? 'heatpump-params' : '');

    if ($acf_field_name && have_rows($acf_field_name)) {
        echo '<ul class="product-params">';
        
        while (have_rows($acf_field_name)) {
            the_row();

            $label = get_sub_field('name');
            $value = get_sub_field('value');

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            echo '<li><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</li>';
        }

        echo '</ul>';
    }

    return ob_get_clean(); // Return the output
}
add_shortcode('product_params', 'inetgap_product_params_shortcode');
