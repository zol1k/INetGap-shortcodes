<?php
/**
 * Shortcode to list custom post types by specified fields.
 * Usage: [igp-list-cpt-by-fields cpt="your_cpt" fields="field1:value1,field2:value2" perpage="12" columns="3" relation="OR" orderby="title" order="ASC" orderby_meta_key="meta_key"]
 * 
 * This shortcode retrieves posts of a specified custom post type (CPT) and filters them based on ACF fields.
 * It supports pagination and displays results in a grid format.
 */
function igp_list_cpt_by_fields_shortcode($atts) {
    $atts = shortcode_atts([
        'cpt' => 'post',
        'fields' => '',
        'perpage' => 12,
        'columns' => 3,
        'relation' => 'OR',
        'orderby' => 'title',        // napr. 'title', 'date', 'meta_value'
        'order' => 'ASC',          // ASC alebo DESC
        'orderby_meta_key' => ''    // iba ak orderby = meta_value
    ], $atts);

    $paged = get_query_var('paged') ?: (get_query_var('page') ?: 1);
    $meta_query = ['relation' => strtoupper($atts['relation']) === 'OR' ? 'OR' : 'AND'];

    $pairs = array_map('trim', explode(',', $atts['fields']));
    foreach ($pairs as $pair) {
        if (strpos($pair, ':') !== false) {
            [$key, $value] = array_map('trim', explode(':', $pair, 2));
            $meta_query[] = [
                'key' => $key,
                'value' => '"' . $value . '"',
                'compare' => 'LIKE'
            ];
        }
    }

    $args = [
        'post_type' => sanitize_key($atts['cpt']),
        'posts_per_page' => intval($atts['perpage']),
        'paged' => $paged,
        'meta_query' => $meta_query,
        'orderby' => $atts['orderby'],
        'order' => strtoupper($atts['order']) === 'ASC' ? 'ASC' : 'DESC'
    ];

    // Ak sa triedi podľa ACF poľa
    if ($atts['orderby'] === 'meta_value' && !empty($atts['orderby_meta_key'])) {
        $args['meta_key'] = sanitize_text_field($atts['orderby_meta_key']);
    }

    $query = new WP_Query($args);
    return render_products_from_query($query, intval($atts['columns']));
}
add_shortcode('igp-list-cpt-by-fields', 'igp_list_cpt_by_fields_shortcode');