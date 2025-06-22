<?php
function project1_custom_shortcode($atts) {
    return "<div>Project 1 custom content</div>";
}
add_shortcode('project1_box', 'project1_custom_shortcode');