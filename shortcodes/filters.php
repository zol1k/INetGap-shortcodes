<?php
/*
* Shortcode to display a filter form for products.
* Usage: [filter_form fields="field1,field2,field3"]
* 
* This shortcode generates a filter form with checkboxes for specified ACF fields.
* The form submits GET parameters to filter products based on selected values.
*/
function inetgap_filter_form($atts) {
    $atts = shortcode_atts([
        'fields' => ''
    ], $atts);
    //enqueue_main_css();
    $field_keys = array_map('trim', explode(',', $atts['fields']));
    if (empty($field_keys)) return '';

    ob_start();
    ?>
    <form method="get" action="#products" class="filter-form">
        <?php foreach ($field_keys as $index => $field_key):
            $is_first = $index === 0;
            $has_checked = isset($_GET[$field_key]) && is_array($_GET[$field_key]) && count($_GET[$field_key]) > 0;
            $field = get_field_object($field_key);
            if (!$field || empty($field['choices'])) continue;
            $field_id = 'filter_' . $index;
        ?>
            <div class="filter-section">
                <button type="button" class="filter-toggle <?= ($is_first or $has_checked) ? 'active' : '' ?> " data-target="#<?= $field_id; ?>">
                    <?= esc_html($field['label']); ?>
                    <span class="arrow"></span>
                </button>
                <div class="filter-content" id="<?= $field_id; ?>" style="<?= ($is_first or $has_checked) ? 'display:block;' : '' ?>">
                    <?php foreach ($field['choices'] as $value => $label): 
                        $checked = isset($_GET[$field_key]) && in_array($value, (array) $_GET[$field_key]);
                    ?>
                        <label>
                            <input type="checkbox" name="<?= esc_attr($field_key); ?>[]" value="<?= esc_attr($value); ?>" <?= $checked ? 'checked' : ''; ?>>
                            <?= esc_html($label); ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="fusion-button filter-submit">POTVRDIŤ VÝBER<i class="awb-factoryarrow-right awb-button__icon awb-button__icon--default button-icon-right" aria-hidden="true"></i></button>
    </form>

    <style>

    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.filter-toggle').forEach(button => {
                button.addEventListener('click', () => {
                    const target = document.querySelector(button.dataset.target);
                    const isOpen = target.style.display === 'block';
                    //document.querySelectorAll('.filter-content').forEach(el => el.style.display = 'none');
                    //document.querySelectorAll('.filter-toggle').forEach(btn => btn.classList.remove('active'));

                    if (!isOpen) {
                        target.style.display = 'block';
                        button.classList.add('active');
                    } else {
                        target.style.display = 'none';
                        button.classList.remove('active');
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('filter_form', 'inetgap_filter_form');

/* Shortcode to display filtered products based on GET parameters.
 * Usage: [filtered_products post_type="product_platne"]
 * 
 * This shortcode retrieves products of a specified post type and filters them based on GET parameters.
 * It uses a meta query to match the selected filter values and displays the results in a grid format.
*/
function inetgap_filtered_products_shortcode($atts) {
    $paged = get_query_var('paged') ?: (get_query_var('page') ?: 1);

    $filters = [];
    foreach ($_GET as $key => $value) {
        if (is_array($value)) {
            $filters[$key] = $value;
        }
    }

    $meta_query = [];

    foreach ($filters as $key => $values) {
        foreach ((array)$values as $val) {
            $meta_query[] = [
                'key' => $key,
                'value' => '"' . sanitize_text_field($val) . '"',
                'compare' => 'LIKE'
            ];
        }
    }

    $args = [
        'post_type' => sanitize_key($atts['post_type']),
        'posts_per_page' => 12,
        'paged' => $paged,
        'meta_query' => $meta_query,
        'orderby' => 'title',
        'order' => 'ASC'
    ];

    $query = new WP_Query($args);

    return render_products_from_query($query, 3);
}
add_shortcode('filtered_products', 'inetgap_filtered_products_shortcode');
