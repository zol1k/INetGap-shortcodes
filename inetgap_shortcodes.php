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
require_once plugin_dir_path(__FILE__) . 'shortcodes/init.php';
require_once plugin_dir_path(__FILE__) . 'includes/project-config.php';
$project = inetgap_get_current_project();

// Load correct render file
$project_render = plugin_dir_path(__FILE__) . "template/project/{$project}/render-products.php";
$default_render = plugin_dir_path(__FILE__) . 'template/render-products.php';
require_once file_exists($project_render) ? $project_render : $default_render;

// load assets
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-assets.php';

// Load helper functions. Example: echo_log($test);
// This file contains utility functions used across the plugin.
require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';


