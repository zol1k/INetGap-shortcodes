<?php
function render_products_from_query($query, $columns = 3) {
    ob_start();

    if ($query->have_posts()) {
        echo '<ul id="products" class="product-grid columns-' . intval($columns) . '">';

        while ($query->have_posts()) {
            $query->the_post();

            echo '<li class="product-card">';

            // Featured image
            if (has_post_thumbnail()) {
                $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full');
                echo '<div class="product-image" style="background-image: url(\''.esc_url($featured_img_url).'\')"></div>';
            }

            echo '<h3>' . get_the_title() . '</h3>';

            $post_type = get_post_type();

            $acf_field_name = $post_type === 'airconditioner' ? 'aircond-params' :
                            ($post_type === 'heatpump' ? 'heatpump-params' : '');

            if ($acf_field_name && have_rows($acf_field_name)) {
                echo '<ul class="product-params">';
                
                while (have_rows($acf_field_name)) {
                    the_row();

                    $label = get_sub_field('name');
                    $value = get_sub_field('value');

                    // Handle array values (like checkboxes)
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    echo '<li><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</li>';
                }

                echo '</ul>';
            }


            // Button/link
            echo '<div class="product-link"><a href="' . get_permalink() . '" class="fusion-button button-flat fusion-button-default-size button-default fusion-button-default button-3 fusion-button-default-span fusion-button-default-type">Viac info</a></div>';
            echo '</li>';
        }

        echo '</ul>';

        echo '<nav class="igp-pagination">';
        echo paginate_links([
            'total' => $query->max_num_pages,
            'current' => max(1, get_query_var('paged')),
            'add_fragment' => '#products',
            'prev_text' => '« Predchádzajúca',
            'next_text' => 'Ďalšia »',
        ]);
        echo '</nav>';

        wp_reset_postdata();
    } else {
        echo '<div id="products"><p>Žiadne produkty.</p></div>';
    }

    return ob_get_clean();
}
?>