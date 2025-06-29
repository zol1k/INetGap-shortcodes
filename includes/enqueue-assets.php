<?php
function inetgap_enqueue_assets() {
    global $inetgap_projects;

    $assets_url = INETGAP_PLUGIN_URL . 'includes/assets/'; // go up two levels

    // GLOBAL STYLES
    wp_enqueue_style(
        'inetgap-global-style',
        $assets_url . 'igp-utils.css',
        [],
        '1.0'
    );

    // GLOBAL SCRIPTS
    wp_enqueue_script(
        'inetgap-global-script',
        $assets_url . 'igp-scripts.js',
        [],
        '1.0',
        true
    );

    // DOMAIN-based loading
    $current_host = $_SERVER['HTTP_HOST'] ?? '';

    if (array_key_exists($current_host, $inetgap_projects)) {
        $project = $inetgap_projects[$current_host];
        $specific_dir_path = INETGAP_PLUGIN_PATH . 'project-specific/' . $project . '/';
        $specific_dir_url = INETGAP_PLUGIN_URL . 'project-specific/' . $project . '/';

        // PROJECT CSS
        $css_file = "style.css";
        if (file_exists($specific_dir_path . $css_file)) {
            wp_enqueue_style(
                "inetgap-{$project}-style",
                $specific_dir_url . $css_file,
                ['inetgap-global-style'],
                '1.0'
            );
        }

        // PROJECT JS
        $js_file = "script.js";
        if (file_exists($specific_dir_path . $js_file)) {
            wp_enqueue_script(
                "inetgap-{$project}-script",
                $specific_dir_url . $js_file,
                ['inetgap-global-script'],
                '1.0',
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'inetgap_enqueue_assets');
