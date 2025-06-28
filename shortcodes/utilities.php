<?php
/**
 * Shortcode to display a "Read More" button that toggles content visibility.
 * Usage: [igp-read-more more_text="Zobraziť viac" less_text="Zobraziť menej"]Your content here[/igp-read-more]
 * 
 * This shortcode creates a button that expands or collapses the content when clicked.
 */
function igp_read_more($atts, $content = null) {
    // Shortcode attributes
    $atts = shortcode_atts(array(
        'more_text' => 'Zobraziť viac',
        'less_text' => 'Zobraziť menej',
    ), $atts, 'igp-read-more');

    // Output
    ob_start();
    ?>
    <div class="igp-read-more">
        <div class="short-content">
            <?php echo wpautop($content); ?>
        </div>
        <a class="show-more-tags fusion-button-default-size fusion-button button-3 read-more-button" onclick="toggleReadMore(this)" data-more-text="<?php echo esc_html($atts['more_text']); ?>" data-less-text="<?php echo esc_html($atts['less_text']); ?>">
            <span class="fusion-button-text"><?php echo esc_html($atts['more_text']); ?></span>
        </a>
    </div>
    <script>
        function toggleReadMore(button) {
            var container = button.closest('.igp-read-more');
            var shortContent = container.querySelector('.short-content');
            var buttonText = button.querySelector('.fusion-button-text');
            
            if (shortContent.classList.contains('show')) {
                shortContent.style.maxHeight = null;
                shortContent.classList.remove('show');
                buttonText.textContent = button.getAttribute('data-more-text');
            } else {
                shortContent.style.maxHeight = shortContent.scrollHeight + "px";
                shortContent.classList.add('show');
                buttonText.textContent = button.getAttribute('data-less-text');
            }
        }
    </script>
    <style>
        .igp-read-more .short-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s ease-out;
        }
        .igp-read-more .short-content.show {
            max-height: 2000px; /* Adjust as necessary */
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('igp-read-more', 'igp_read_more');

/**
 * Shortcode to print all ACF fields for the current post.
 * Usage: [acf_dump]
 * 
 * This shortcode outputs all ACF fields in a structured format.
 */
function print_all_acf_fields_shortcode() {
    ob_start();

    // Show CPT
    $post_type = get_post_type(get_post());
    echo '<p><strong>Custom Post Type:</strong> <code>' . esc_html($post_type) . '</code></p>';

    $fields = get_field_objects();

    if ($fields) {
        echo '<div class="acf-fields-list">';
        foreach ($fields as $field_key => $field) {
            echo '<div class="acf-field">';
            
            // Show field label and name (slug)
            echo '<strong>' . esc_html($field['label']) . '</strong> ';
            echo '<code>(' . esc_html($field['name']) . ')</code><br>';

            // Repeater handling
            if ($field['type'] === 'repeater' && !empty($field['value']) && is_array($field['value'])) {
                echo '<ul>';
                foreach ($field['value'] as $row) {
                    echo '<li>';
                    foreach ($row as $sub_key => $sub_value) {
                        echo esc_html($sub_key) . ': ' . esc_html($sub_value) . '<br>';
                    }
                    echo '</li>';
                }
                echo '</ul>';
            } else {
                // Other fields
                echo esc_html(is_array($field['value']) ? json_encode($field['value']) : $field['value']);
            }

            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>No ACF fields found for this post.</p>';
    }

    return ob_get_clean();
}
add_shortcode('acf_dump', 'print_all_acf_fields_shortcode');
