<?php
function inetgap_enqueue_assets() {
    global $inetgap_projects;

    $url = plugin_dir_url(__FILE__, 2); // go up two levels
    $path = plugin_dir_path(__FILE__, 2);

    // GLOBAL STYLES
    wp_enqueue_style(
        'inetgap-global-style',
        $url . 'assets/igp-utils.css',
        [],
        '1.0'
    );

    // GLOBAL SCRIPTS
    wp_enqueue_script(
        'inetgap-global-script',
        $url . 'assets/igp-scripts.js',
        [],
        '1.0',
        true
    );

    // DOMAIN-based loading
    $current_host = $_SERVER['HTTP_HOST'] ?? '';

    if (array_key_exists($current_host, $inetgap_projects)) {
        $project = $inetgap_projects[$current_host];
        $project_dir = INETGAP_PLUGIN_URL . 'project-specific/' . $project . '/';
        // PROJECT CSS
        $css_file = "style.css";
        if (file_exists($project_dir . $css_file)) {
            wp_enqueue_style(
                "inetgap-{$project}-style",
                $project_dir . $css_file,
                ['inetgap-global-style'],
                '1.0'
            );
        }

        // PROJECT JS
        $js_file = "script.js";
        if (file_exists($project_dir . $js_file)) {
            wp_enqueue_script(
                "inetgap-{$project}-script",
                $project_dir . $js_file,
                ['inetgap-global-script'],
                '1.0',
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'inetgap_enqueue_assets');
