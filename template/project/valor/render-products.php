<?php
function render_products_from_query($query, $columns = 3) {
    ob_start();

    if ($query->have_posts()) {
        echo '<ul id="products" class="product-grid columns-' . intval($columns) . '">';

        while ($query->have_posts()) {
            $query->the_post();

            // Dummy data pre ilustráciu – uprav podľa ACF alebo custom polí
            $logo = get_field('coating-brand'); // napr. image field
            $description = get_field('coating-description-filter');
            echo '<li class="product-card">';
            echo '<h3>' . get_the_title() . '</h3>';

            if ($logo) {
                echo '<div class="product-logo"><img src="/wp-content/uploads/static/brands/' . $logo['value'] . '.png" alt=""></div>';
            }

            if ($description) {
                echo '<div class="product-description"><p>' . $description . '</p></div>';
            }

            echo '<div class="product-link"><a href="' . get_permalink() . '"><span class="arrow">»</span></a></div>';
            echo '</li>';
        }

        echo '</ul>'; // end .product-grid

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