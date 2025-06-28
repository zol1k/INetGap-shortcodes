<?php
function render_products_from_query($query, $columns = 3) {
    ob_start();

    if ($query->have_posts()) {
        echo '<ul id="products" class="product-grid columns-' . intval($columns) . '">';

        while ($query->have_posts()) {
            $query->the_post();
            $repeater_field = 'aircond-params';

            echo '<li class="product-card">';

            // Featured image
            if (has_post_thumbnail()) {
                echo '<div class="product-image">' . get_the_post_thumbnail(get_the_ID(), 'medium') . '</div>';
            }

            echo '<h3>' . get_the_title() . '</h3>';

            // Parameters (repeater field)
            if ($repeater_field && have_rows($repeater_field)) {
                echo '<div class="product-description">';
                while (have_rows($repeater_field)) {
                    the_row();
                    $name = get_sub_field('name');
                    $value = get_sub_field('value');
                    if ($name && $value) {
                        echo '<p><strong>' . esc_html($name) . ':</strong> ' . esc_html($value) . '</p>';
                    }
                }
                echo '</div>';
            }

            // Button/link
            echo '<div class="product-link"><a href="' . get_permalink() . '" class="btn">Viac info</a></div>';
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