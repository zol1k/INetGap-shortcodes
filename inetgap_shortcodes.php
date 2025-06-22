<?php
/**
 * Plugin Name: INetGap shortcodes
 * Plugin URI: https://www.inetgap.sk
 * Description: Display content using a shortcode to insert in a page or post
 * Version: 0.1
 * Text Domain: inetgap_shortcodes
 * Author: iNetGap solutions
 * Author URI: https://www.inetgap.sk
 * for debug purposes use echo_log($test);
 */

/* Define projects and their domains */
$inetgap_projects = [
    'valor.inetgap.sk' => 'valor',
    'klima.inetgap.sk' => 'mallay'
];

// DEFINES
define('INETGAP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('INETGAP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Init helpers
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
// Init shortcodes
require_once plugin_dir_path(__FILE__) . 'shortcodes/init.php';

// Get current project based on host
$project = inetgap_get_current_project();

// Load correct render file
$project_render = plugin_dir_path(__FILE__) . "project-specific/{$project}/render-products.php";
$default_render = plugin_dir_path(__FILE__) . 'template/render-products.php';
require_once file_exists($project_render) ? $project_render : $default_render;

// Load project-specific assets
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-assets.php';

// Load project-specific shortcodes
$project_shortcodes = INETGAP_PLUGIN_PATH . "project-specific/{$project}/shortcodes1.php";
if (file_exists($project_shortcodes)) {
    require_once $project_shortcodes;
}


