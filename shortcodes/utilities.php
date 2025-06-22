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


