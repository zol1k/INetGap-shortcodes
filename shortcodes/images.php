<?php
/* Shortcode to display an ACF image field as an image element.
 * Usage: [igp-acf-img field-id="your_field_id" image-path="/path/to/images/" image-extension="png"]
 * 
 * This shortcode retrieves the value of a specified ACF field and constructs an image element with the given path and extension.
*/
function igp_acf_img_shortcode($atts) {
    // Extract attributes
    $atts = shortcode_atts(array(
        'field-id' => '',
        'image-path' => '',
        'image-extension' => 'png',
    ), $atts, 'igp-acf-img');

    // Get ACF field value
    $field_value = get_field($atts['field-id']);

    if (!$field_value) {
        return ''; // no image if field is empty
    }

    // Build image URL
    $src = esc_url($atts['image-path'] . $field_value['value'] . '.' . $atts['image-extension']);

    // Return the image element
    return '<img src="' . $src . '" alt="">';
}
add_shortcode('igp-acf-img', 'igp_acf_img_shortcode');