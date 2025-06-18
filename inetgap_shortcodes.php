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

require_once plugin_dir_path(__FILE__) . 'includes/project-config.php';
$project = inetgap_get_current_project();

// Load correct render file
$project_render = plugin_dir_path(__FILE__) . "template/project/{$project}/render-products.php";
$default_render = plugin_dir_path(__FILE__) . 'template/render-products.php';
require_once file_exists($project_render) ? $project_render : $default_render;

// load assets
require_once plugin_dir_path(__FILE__) . 'includes/enqueue-assets.php';

/*
* Shortcode to display a filter form for products.
* Usage: [filter_form fields="field1,field2,field3"]
* 
* This shortcode generates a filter form with checkboxes for specified ACF fields.
* The form submits GET parameters to filter products based on selected values.
*/
function inetgap_filter_form($atts) {
    $atts = shortcode_atts([
        'fields' => ''
    ], $atts);
    //enqueue_main_css();
    $field_keys = array_map('trim', explode(',', $atts['fields']));
    if (empty($field_keys)) return '';

    ob_start();
    ?>
    <form method="get" action="#products" class="filter-form">
        <?php foreach ($field_keys as $index => $field_key):
            $is_first = $index === 0;
            $has_checked = isset($_GET[$field_key]) && is_array($_GET[$field_key]) && count($_GET[$field_key]) > 0;
            $field = get_field_object($field_key);
            if (!$field || empty($field['choices'])) continue;
            $field_id = 'filter_' . $index;
        ?>
            <div class="filter-section">
                <button type="button" class="filter-toggle <?= ($is_first or $has_checked) ? 'active' : '' ?> " data-target="#<?= $field_id; ?>">
                    <?= esc_html($field['label']); ?>
                    <span class="arrow"></span>
                </button>
                <div class="filter-content" id="<?= $field_id; ?>" style="<?= ($is_first or $has_checked) ? 'display:block;' : '' ?>">
                    <?php foreach ($field['choices'] as $value => $label): 
                        $checked = isset($_GET[$field_key]) && in_array($value, (array) $_GET[$field_key]);
                    ?>
                        <label>
                            <input type="checkbox" name="<?= esc_attr($field_key); ?>[]" value="<?= esc_attr($value); ?>" <?= $checked ? 'checked' : ''; ?>>
                            <?= esc_html($label); ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="fusion-button filter-submit">POTVRDIŤ VÝBER<i class="awb-factoryarrow-right awb-button__icon awb-button__icon--default button-icon-right" aria-hidden="true"></i></button>
    </form>

    <style>

    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.filter-toggle').forEach(button => {
                button.addEventListener('click', () => {
                    const target = document.querySelector(button.dataset.target);
                    const isOpen = target.style.display === 'block';
                    //document.querySelectorAll('.filter-content').forEach(el => el.style.display = 'none');
                    //document.querySelectorAll('.filter-toggle').forEach(btn => btn.classList.remove('active'));

                    if (!isOpen) {
                        target.style.display = 'block';
                        button.classList.add('active');
                    } else {
                        target.style.display = 'none';
                        button.classList.remove('active');
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('filter_form', 'inetgap_filter_form');

/* Shortcode to display filtered products based on GET parameters.
 * Usage: [filtered_products post_type="product_platne"]
 * 
 * This shortcode retrieves products of a specified post type and filters them based on GET parameters.
 * It uses a meta query to match the selected filter values and displays the results in a grid format.
*/
function inetgap_filtered_products_shortcode($atts) {
    $paged = get_query_var('paged') ?: (get_query_var('page') ?: 1);

    $filters = [];
    foreach ($_GET as $key => $value) {
        if (is_array($value)) {
            $filters[$key] = $value;
        }
    }

    $meta_query = [];

    foreach ($filters as $key => $values) {
        foreach ((array)$values as $val) {
            $meta_query[] = [
                'key' => $key,
                'value' => '"' . sanitize_text_field($val) . '"',
                'compare' => 'LIKE'
            ];
        }
    }

    $args = [
        'post_type' => sanitize_key($atts['post_type']),
        'posts_per_page' => 12,
        'paged' => $paged,
        'meta_query' => $meta_query,
        'orderby' => 'title',
        'order' => 'ASC'
    ];

    $query = new WP_Query($args);

    return render_products_from_query($query, 3);
}
add_shortcode('filtered_products', 'inetgap_filtered_products_shortcode');



/* Shortcode to list custom post types by specified fields.
 * Usage: [igp-list-cpt-by-fields cpt="your_cpt" fields="field1:value1, field2:value2" perpage="12" columns="3" relation="AND"]
 * 
 * This shortcode retrieves posts of a specified custom post type (CPT) and filters them based on ACF fields.
 * It supports pagination and displays the results in a grid format.
*/
// function igp_list_cpt_by_fields_shortcode($atts) {
//     $atts = shortcode_atts([
//         'cpt' => 'post',
//         'fields' => '',
//         'perpage' => 12,
//         'columns' => 3,
//         'relation' => 'AND' // Nový parameter: AND alebo OR
//     ], $atts);

//     $paged = get_query_var('paged') ?: (get_query_var('page') ?: 1);
//     $meta_query = ['relation' => strtoupper($atts['relation']) === 'OR' ? 'OR' : 'AND'];

//     // Spracovanie fields="field1:value1, field2:value2"
//     $pairs = array_map('trim', explode(',', $atts['fields']));
//     foreach ($pairs as $pair) {
//         if (strpos($pair, ':') !== false) {
//             [$key, $value] = array_map('trim', explode(':', $pair, 2));
//             $meta_query[] = [
//                 'key' => $key,
//                 'value' => '"' . $value . '"', // pre serialized arrays
//                 'compare' => 'LIKE'
//             ];
//         }
//     }

//     $args = [
//         'post_type' => sanitize_key($atts['cpt']),
//         'posts_per_page' => intval($atts['perpage']),
//         'paged' => $paged,
//         'meta_query' => $meta_query
//     ];

//     $query = new WP_Query($args);
//     return render_products_from_query($query, intval($atts['columns']));
// }
// add_shortcode('igp-list-cpt-by-fields', 'igp_list_cpt_by_fields_shortcode');




function igp_list_cpt_by_fields_shortcode($atts) {
    $atts = shortcode_atts([
        'cpt' => 'post',
        'fields' => '',
        'perpage' => 12,
        'columns' => 3,
        'relation' => 'OR',
        'orderby' => 'title',        // napr. 'title', 'date', 'meta_value'
        'order' => 'ASC',          // ASC alebo DESC
        'orderby_meta_key' => ''    // iba ak orderby = meta_value
    ], $atts);

    $paged = get_query_var('paged') ?: (get_query_var('page') ?: 1);
    $meta_query = ['relation' => strtoupper($atts['relation']) === 'OR' ? 'OR' : 'AND'];

    $pairs = array_map('trim', explode(',', $atts['fields']));
    foreach ($pairs as $pair) {
        if (strpos($pair, ':') !== false) {
            [$key, $value] = array_map('trim', explode(':', $pair, 2));
            $meta_query[] = [
                'key' => $key,
                'value' => '"' . $value . '"',
                'compare' => 'LIKE'
            ];
        }
    }

    $args = [
        'post_type' => sanitize_key($atts['cpt']),
        'posts_per_page' => intval($atts['perpage']),
        'paged' => $paged,
        'meta_query' => $meta_query,
        'orderby' => $atts['orderby'],
        'order' => strtoupper($atts['order']) === 'ASC' ? 'ASC' : 'DESC'
    ];

    // Ak sa triedi podľa ACF poľa
    if ($atts['orderby'] === 'meta_value' && !empty($atts['orderby_meta_key'])) {
        $args['meta_key'] = sanitize_text_field($atts['orderby_meta_key']);
    }

    $query = new WP_Query($args);
    return render_products_from_query($query, intval($atts['columns']));
}
add_shortcode('igp-list-cpt-by-fields', 'igp_list_cpt_by_fields_shortcode');











 function custom_collection_tags($atts) {
    // Default attributes
    $custom_atts = shortcode_atts(array(
        'tagcategory' => 'all',  // Default to 'all' if not provided
        'maxtags' => -1,  // Default to -1, meaning no limit on the number of tags
    ), $atts);

    // Determine if we're on an archive page
    if (is_post_type_archive()) {
        // Get the current post type
        $tagCategory = get_post_type();
    } else {
        $tagCategory = $custom_atts['tagcategory'];
    }

    $max_tags = intval($custom_atts['maxtags']);

    // Query parameters to fetch posts from custom_collection CPT
    $args = array(
        'post_type'      => 'custom_collection',
        'posts_per_page' => -1,
        'post_status'    => array('publish', 'draft'),
    );

    $query = new WP_Query($args);
    $posts_with_counts = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();

            // Get the category filter from the custom field
            $tagFilterCategories = get_field('product-related-category', $id);
            $related_products = get_field('product_related_products', $id);
            $count = is_array($related_products) ? count($related_products) : 0;

            // Check for custom URL field
            $custom_url = get_field('product_related_custom_link', $id);  // Replace 'custom_url_field' with your actual ACF field name

            // Determine the link to use
            $link = $custom_url ? esc_url($custom_url) : get_permalink($id);

            // Filter posts based on the tagCategory attribute or CPT name if on archive
            if ($tagCategory === 'all' || (is_array($tagFilterCategories) && in_array($tagCategory, $tagFilterCategories))) {
                $posts_with_counts[] = [
                    'name' => get_the_title() . (get_post_status($id) === 'draft' ? ' <span class="concept">(Concept)</span>' : ''),
                    'count' => $count,
                    'link' => $link
                ];
            }
        }
    }
    wp_reset_postdata();

			   
    $html = '<ul class="custom-collection-tags">';
    $current_row_limit = 5; // First row contains 5 elements
    $count_in_row = 0;
    $total_tags = count($posts_with_counts);

    foreach ($posts_with_counts as $index => $post) {
        // Add 'hide-element' class to tags exceeding the max_tags value
        $hide_class = ($max_tags > 0 && $index >= $max_tags) ? ' hide-element tag-hidden' : '';

        if ($count_in_row == $current_row_limit) {
            $current_row_limit++; // Increment the limit for the next row
            $count_in_row = 0; // Reset counter for the next row
        }
        $html .= '<li class="tag row-group-' . $current_row_limit . $hide_class . '"><a href="' . esc_url($post['link']) . '">' . $post['name'] . ' <span class="count">(' . $post['count'] . ')</span></a></li>';

        $count_in_row++;
    }

    $html .= '</ul>';

    // If there are more tags than the limit, add a "Show More" button
    if ($max_tags > 0 && $total_tags > $max_tags) {
        $html .= '<a class="show-more-tags fusion-button-default-size fusion-button button-3"><span class="fusion-button-text">Zobraziť všetky</span></a>';
        $html .= '<script>
            document.querySelector(".show-more-tags").addEventListener("click", function() {
                var hiddenTags = document.querySelectorAll(".custom-collection-tags .tag.hide-element");
                hiddenTags.forEach(function(tag) {
                    tag.classList.remove("hide-element");
                });
                this.style.display = "none"; // Hide the button after clicking
            });
        </script>';
    }

    return $html;
}
add_action('init', function() {
    add_shortcode('custom_collection_tags', 'custom_collection_tags');
});

/* Shortcode to display an ACF image field as an image element.
 * Usage: [igp-acf-img field-id="your_field_id" image-path="/path/to/images/" image-extension="png"]
 * 
 * This shortcode retrieves the value of a specified ACF field and constructs an image element with the given path and extension.
*/
function igp_acf_img_shortcode1($atts) {
    // Extract attributes
    $atts = shortcode_atts(array(
        'field-id' => '',
        'image-path' => '',
        'image-extension' => 'png',
    ), $atts, 'igp-acf-img');

    // Get ACF field value
    $field_value = get_field($atts['field-id']);

    if (!$field_value) {
        return ''; // no image if field is empty
    }

    // Build image URL
    $src = esc_url($atts['image-path'] . $field_value['value'] . '.' . $atts['image-extension']);

    // Return the image element
    return '<img src="' . $src . '" alt="">';
}
add_shortcode('igp-acf-img', 'igp_acf_img_shortcode1');


function inetgap_cpt_items_filter()
{

    ob_start();
    include(dirname( __FILE__ ) . "/template/acf_products.php");
    return ob_get_clean();
}
add_shortcode('inetgap_cpt_items_filter', 'inetgap_cpt_items_filter');

function inetgap_cpt_items_by_post_objects($atts) {
    ob_start();

    // Fetch the attributes from the shortcode
    $acf_field = $atts['acf_field'];
    $size = $atts['size'];
    $showTitle = $atts['showtitle'];
    $columnNumber = $atts['columns'];

    // Get the related posts from the ACF field
    $related_posts = get_field($acf_field, get_the_ID());

    if ($related_posts) {
        echo "<ul class='ing-item-grid clearfix " . ($columnNumber ? "ing-grid-" . $columnNumber : "") . "'>";
        foreach ($related_posts as $post_id) {
            $post = get_post($post_id);
            setup_postdata($post);
            
            // Get the image URL
            if (has_post_thumbnail($post_id)) {
                $imgUrl = get_the_post_thumbnail_url($post_id, $size);
            } else {
                $imgUrl = "/wp-content/uploads/static/file-img.png"; // Default image if no thumbnail
            }

            // Display the post
            ?>
            <li class="ing-item <?php echo get_the_ID(); ?>">
                <a href="<?php echo get_permalink($post_id); ?>">
                    <div class="ing-item-content" style="background-image: url('<?php echo esc_url($imgUrl); ?>');">
                        <?php if ($showTitle === 'true') { ?>
                            <h3 class="ing-item-content-desc"><?php echo get_the_title($post_id); ?></h3>
                        <?php } ?>
                    </div>
                </a>
            </li>
            <?php
        }
        echo "</ul>";
        wp_reset_postdata();
    }

    return ob_get_clean();
}
add_shortcode('inetgap-cpt-items-post-objects', 'inetgap_cpt_items_by_post_objects');


function inetgap_cpt_items($atts)
{
    ob_start();
    $items = get_field($atts["acf_field"]);
    $size = $atts['size'];
    $showTitle = $atts['showtitle'];
    $columnNumber = $atts['columns'];
    $parentPage = $atts['parent-page'];

    $args = array(
        'post_type'      => 'galeria-inspiracii',
        'posts_per_page' => -1,
        'post_parent'    => $parentPage,
        'order'          => 'ASC',
        'orderby'        => 'menu_order'
    );
    $parent = new WP_Query($args);
    if ( $parent->have_posts() ) : ?>
        <ul class='ing-item-grid clearfix <?php if ($columnNumber != "") echo "ing-grid-".$columnNumber; ?>'>
            <?php while ( $parent->have_posts() ) : $parent->the_post();
                $gallery = get_field('project_gallery');
                $id = get_the_ID();
                if(has_post_thumbnail()) {
                    $imgUrl = get_the_post_thumbnail_url($id,$size);
                } else {
                    if ($gallery) {
                        $imgUrl = esc_url($gallery[0]['sizes'][$size]);
                    } else {
                        $imgUrl = "/wp-content/uploads/static/file-img.png";
                    }
                }


                ?>
                <li class="ing-item <?php echo the_ID(); ?>">
                    <a href="<?php echo the_permalink(); ?>">
                    <div class="ing-item-content" style="background-image: url('<?php echo $imgUrl; ?>');">
                        <!-- img class="ing-item-content-img" src="<?php echo $imgUrl ?>" -->
                        <?php if ((bool) $showTitle == 'true') { ?>
                            <h3 class="ing-item-content-desc" ><?php the_title(); ?></h3>
                        <?php } ?>
                    </div>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('inetgap-cpt-items', 'inetgap_cpt_items');

function inetgap_cpt_items_by_field($atts)
{
    ob_start();

    $field = $atts["acf_field"];
    $value = $atts["acf_value"];
    $columnNumber = '3';
    $showTitle = true;
    $args2 = array(
        'post_type'      => array( 'product_platne', 'product_ploty', 'product_tvarnice', 'product_palisady', 'product_obrubniky', 'product_schody', 'product_zakryt', 'product_priemysel', 'product_mobiliar', 'product_doplnky', 'product-dlazby'),
        'posts_per_page' => -1,
        'exact' => true,
        'post_status'    => 'publish',
        'orderby'   => 'title',
        'order' => 'ASC',
        'meta_key'		=> $field,
        'meta_compare' => 'LIKE',
        'meta_value' => $value
    );

    $parent = new WP_Query($args2);

    if ( $parent->have_posts() ) : ?>
        <ul class='ing-item-grid clearfix <?php if ($columnNumber != "") echo "ing-grid-".$columnNumber; ?>'>
            <?php while ( $parent->have_posts() ) : $parent->the_post();

                //check exact search result option1 != option11
				$foundInArray = false;
				foreach (get_field($field) as $item) {
					if (strval($item) == $value) {
						$foundInArray = true;
						break;
					}
				}
				if (!$foundInArray){
					continue;
				}

                $gallery = get_field('project_gallery');
                $id = get_the_ID();
                if(has_post_thumbnail()) {
                    $imgUrl = get_the_post_thumbnail_url($id,$size);
                } else {
                    if ($gallery) {
                        $imgUrl = esc_url($gallery[0]['sizes'][$size]);
                    } else {
                        $imgUrl = "/wp-content/uploads/static/file-img.png";
                    }
                }


                ?>
                <li class="ing-item <?php echo the_ID(); ?>">
                    <a href="<?php echo the_permalink(); ?>">
                        <div class="ing-item-content" style="background-image: url('<?php echo $imgUrl; ?>');">
                            <!-- img class="ing-item-content-img" src="<?php echo $imgUrl ?>" -->
                            <?php if (get_field('product-new')) { ?>
                                <div class="ing-item-label">
                                    <?php
                                    $currentTld = getCurrentTld();
                                    // Output different content based on the TLD
                                    if ($currentTld === 'sk') {
                                        echo "Novinka";
                                    } elseif ($currentTld === 'hu') {
                                        echo "ÚJDONSÁG";
                                    } else {
                                        // Default content or handling for other TLDs
                                        echo "New";
                                    }
                                    ?>
                                </div>
                            <?php } ?>
							<?php if (get_field('product-top-offer')) { ?>
								<div class="ing-item-label label-top-offer">
									<?php
									$currentTld = getCurrentTld();
									if ($currentTld === 'sk') {
										echo "Top ponuka";
									} elseif ($currentTld === 'hu') {
										echo "Top ajánlat";
									} else {
										echo "Top Offer";
									}
									?>
								</div>
							<?php } ?>
                            <?php if ((bool) $showTitle == 'true') { ?>
                                <h3 class="ing-item-content-desc" ><?php the_title(); ?></h3>
                            <?php } ?>
                        </div>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('inetgap-cpt-items-by-field', 'inetgap_cpt_items_by_field');

function inetgap_cpt_archive()
{
    ob_start();
    $size = 'medium';
    $showTitle = true;
    $post_type = get_queried_object()->name;
    $cpt_args = array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'order'          => 'ASC',
        'orderby'        => 'menu_order'
    );
    $cpt_query = new WP_Query($cpt_args);

    // collect ID we want to exclude from additional query (related category)
    $excludeID = array();
    foreach ( $cpt_query->posts as $post ) {
        $excludeID[] = $post->ID;
    }

    $argsAdditional = array(
        'post_type'      => array( 'product_platne', 'product_ploty', 'product_tvarnice', 'product_palisady', 'product_obrubniky', 'product_schody', 'product_zakryt', 'product_priemysel', 'product_mobiliar', 'product_doplnky', 'product-dlazby'),
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'		 => 'product-related-category',
        'meta_compare'   => 'LIKE',
	    'meta_value'	 => $post_type,
        'post__not_in'   => $excludeID
        //'exclude'        => $exclude
    );
    $additional_query = new WP_Query($argsAdditional);
    $merged_query = new WP_Query();
    $merged_query->posts = array_merge( $cpt_query->posts, $additional_query->posts );
    $merged_query->post_count = $cpt_query->post_count + $additional_query->post_count;
	
    //echo_log($additional_query->post_count);
    if ( $merged_query->have_posts() ) : ?>
        <div id='ing-items-filter-table'>
            <ul class='ing-item-grid clearfix ing-grid-3'>
                <?php while ( $merged_query->have_posts() ) : $merged_query->the_post();

                    $id = get_the_ID();
                    $groupedTaxonomiesArray = array();

                    if(has_post_thumbnail()) {
                        $imgUrl = get_the_post_thumbnail_url($id,$size);
                    } else {

                        $gallery = get_field('product-content_product_gallery');
                        if ($gallery) {
                            $imgUrl = esc_url($gallery[0]['sizes'][$size]);
                        } else {
                            $imgUrl = "/wp-content/uploads/static/file-img.png";
                        }
                    } ?>
                    <li class="ing-item <?php echo the_ID(); ?> <?php echo BuildFilterString($groupedTaxonomiesArray); ?>">
                        <a href="<?php echo the_permalink(); ?>">
                            <div class="ing-item-content" style="background-image: url('<?php echo $imgUrl; ?>');">
                                <?php if (get_field('product-new')) { ?>
                                    <div class="ing-item-label label-new">
                                        <?php
                                        $currentTld = getCurrentTld();
                                        // Output different content based on the TLD
                                        if ($currentTld === 'sk') {
                                            echo "Novinka";
                                        } elseif ($currentTld === 'hu') {
                                            echo "ÚJDONSÁG";
                                        } else {
                                            // Default content or handling for other TLDs
                                            echo "New";
                                        }
                                        ?>
                                    </div>
                                <?php } ?>
								<?php if (get_field('product-top-offer')) { ?>
									<div class="ing-item-label label-top-offer">
										<?php
										$currentTld = getCurrentTld();
										if ($currentTld === 'sk') {
											echo "Top ponuka";
										} elseif ($currentTld === 'hu') {
											echo "Top ajánlat";
										} else {
											echo "Top Offer";
										}
										?>
									</div>
								<?php } ?>
                                <?php if ((bool) $showTitle == 'true') { ?>
                                    <h3 class="ing-item-content-desc" ><?php the_title(); ?></h3>
                                <?php } ?>
                            </div>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php endif; wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('inetgap-cpt-archive', 'inetgap_cpt_archive');

function inetgap_acf_gallery($atts)
{
    ob_start();
    $images = get_field($atts["acf_field"]);
    $size = $atts['size'];
    $showCaption = $atts['caption'];
    $showDescription = $atts['description'];
    $columnNumber = $atts['columns'];
    $showPreview = $atts['showpreview'];

    if( $images ): ?>
        <ul class='ing-item-grid clearfix <?php if ($columnNumber != "") echo "ing-grid-".$columnNumber; ?>'>
            <?php foreach( $images as $image ):
                $imageUrl = esc_url($image['url']);
                $imagePreviewUrl = esc_url($image['sizes'][$size]);
                $imageAlt = esc_attr($image['alt']);
                $imageElement = "<img data-imgsrc='$imageUrl' src='$imagePreviewUrl' alt='$imageAlt'/>";
                ?>
                <li class="ing-gallery-img">
                    <?php if ($showPreview == 'true') { ?>
                        <a href="<?php echo $imageUrl ?>" rel="noreferrer" data-rel="iLightbox[gallery_image_<?php echo $atts["acf_field"]; ?>]" class="fusion-lightbox" target="_self" data-caption="<?php echo $imageAlt; ?>">
                            <?php echo $imageElement ?>
                        </a>
                    <?php } else { ?>
                        <?php echo $imageElement ?>
                    <?php } ?>

                    <?php if ((bool) $showCaption ==  'true') { ?>

                        <p class="ing-gallery-caption"><?php echo esc_html($image['caption']); ?></p>
                    <?php } ?>

                    <?php if ((bool) $showDescription ==  'true') { ?>
                        <p class="ing-gallery-description"><?php echo esc_html($image['description']); ?></p>
                    <?php } ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif;
    return ob_get_clean();
}
add_shortcode('inetgap-acf-gallery', 'inetgap_acf_gallery');

/*pattern inside of initial slider to present options*/
function inetgap_acf_gallery_patterns($atts)
{
    ob_start();
    $patterns = get_field($atts["acf_field"]);
    $size = $atts['size'];
    $showCaption = $atts['caption'];
    $columnNumber = $atts['columns'];
    $currentTld = getCurrentTld();
    $newTextTranslation = "";
    $sidebarButtonTextTranslation = "";

    // Output different content based on the TLD
    if ($currentTld === 'sk') {
        $newTextTranslation = "Novinka";
        $sidebarButtonTextTranslation = "Farebné varianty";
    } elseif ($currentTld === 'hu') {
        $newTextTranslation = "ÚJDONSÁG";
        $sidebarButtonTextTranslation = "Színek";
    } else {
        // Default content or handling for other TLDs
        $newTextTranslation = "New";
        $sidebarButtonTextTranslation = "Colors";
    }

    if( $patterns ): ?>
        <div id='ing-sectionsidebarmenu-container'>
            <button id="toggle-sidebar"><?php echo $sidebarButtonTextTranslation; ?></button>
            <ul class='ing-sectionsidebarmenu-right active' id='csd-patterns'>
                <?php foreach( $patterns as $pattern ):
                    //echo_log($pattern);
                    $patternImg = $pattern['pattern'];
                    $usageImg = $pattern['usage'];
                    $patternImgUrl = esc_url($usageImg['url']);
                    $patternPreviewUrl = esc_url($patternImg['sizes'][$size]);
                    $imageAlt = esc_attr($patternImg['alt']);
                    $imageElement = "<img data-imgsrc='$patternImgUrl' src='$patternPreviewUrl' alt='$imageAlt'/>";
                    ?>
                    <li class="ing-sectionsidebarmenu-item" style="background-image: url('<?php echo $patternPreviewUrl; ?>');" data-imgsrc="<?php echo $patternImgUrl; ?>">
                        <?php if ($pattern["Novinka"]) { ?>
                        <div class="ing-sectionsidebarmenu-item-label">
                            <?php echo $newTextTranslation; ?>
                        </div>
                        <?php } ?>
                        <?php if ((bool) $showCaption ==  'true') { ?>

                            <p class="ing-sectionsidebarmenu-item-caption"><?php echo esc_html($patternImg['caption']); ?></p>
                        <?php } ?>

                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif;
    return ob_get_clean();
}
add_shortcode('inetgap-acf-gallery-patterns', 'inetgap_acf_gallery_patterns');


function inetgap_acf_debug()
{
    $fields = get_field_objects($post_id);

    echo '<pre>';
        print_r($fields);
    echo '</pre>';
        
}
add_shortcode('inetgap_acf_debug', 'inetgap_acf_debug');

/*GENERAL FUNCTION TO RETRIEVE ACF FIELD-SUBFIELD*/
function inetgap_acf_field($atts)
{
    $acf_field = $atts["acf_field"];
    $acf_sub_field = $atts["acf_sub_field"];
    $class = $atts["class"];

    ob_start();
    ?> <div class='<?php echo $class ?>'><?php 
        if ($acf_sub_field != "") {

            $main_field = get_field($acf_field);
            $sub_field = $main_field[$acf_sub_field];
            echo $sub_field;
            echo the_field(get_field($acf_field)[$acf_sub_field]);
        } else {
            the_field($atts["acf_field"]);
        } 
    ?> </div> <?php
    return ob_get_clean();
}
add_shortcode('inetgap-acf-field', 'inetgap_acf_field');


function inetgap_acf_cpt_plural_name()
{
    $pt = get_post_type_object( get_post_type( get_the_ID() ) );
    return $pt->labels->name;
}
add_shortcode('inetgap_acf_cpt_plural_name', 'inetgap_acf_cpt_plural_name');

function inetgap_acf_cpt_archive_page_url()
{
    $pt = get_post_type_archive_link(get_post_type( get_the_ID()));
    return $pt;
}
add_shortcode('inetgap-acf-cpt-archive-page-url', 'inetgap_acf_cpt_archive_page_url');

function inetgap_acf_pdf_files($atts)
{
    ob_start();
    $items = get_field($atts["acf_field"]);
    $size = $atts['size'];
    $showDescription = $atts['description'];
    $columnNumber = $atts['columns'];
    $showPreview = $atts['showpreview'];
    $staticFileImagePath = "/wp-content/uploads/static/file-img.png";

    if( $items ): ?>
        <ul class='ing-item-grid ing-files clearfix <?php if ($columnNumber != "") echo "ing-grid-".$columnNumber; ?>'>
            <?php foreach( $items as $item ):
                $description = esc_html($item['product_certificates_description']);
                $file = $item['product_certificates_file'];
                $thumbnail = $item['product_certificates_thumbnail'];

                $fileUrl = esc_url($file['url']);

                $thumbnailUrl = esc_url($thumbnail['url']);
                $thumbnailAlt = esc_html($thumbnail['title']);
                ?>
                <li class="ing-file ing-gallery-img">
                    <?php if ((bool) $showDescription ==  'true') { ?>
                        <p class="ing-file-description"><?php echo $description; ?></p>
                    <?php } ?>
                    <?php if ($file) { ?>
                        <a href="<?php echo $fileUrl ?>" target="_blank">
                            <span class="zoom"></span>
                            <img src="<?php echo ($thumbnail)? $thumbnailUrl : $staticFileImagePath ?>" alt="Nahlad suboru."/>
                        </a>
                    <?php } ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif;
    return ob_get_clean();
}
add_shortcode('inetgap-acf-pdf-files', 'inetgap_acf_pdf_files');


/* GENERAL REPEATER IMAGE-TEXT DISPLAY FUNCTION */
function inetgap_acf_repeater_image_text($atts)
{
    ob_start();
    $items = get_field($atts["acf_field"]);
	$count = is_array($items) ? count($items) : 0;
    $acf_field_image = $atts["acf_field_image"];
    $acf_field_text = $atts["acf_field_text"];
    $img_in_window = $atts['open_image_in_window'];
    $class = $atts["class"];
    $allign = $atts["allign"];
    $columnNumber = $atts['columns'];
    $staticFileImagePath = "/wp-content/uploads/static/file-img.png";

    if( $items ): ?>
        <div class='<?php echo $class ?>'>
            <ul class='inetgap_acf_repeater-container ing-item-grid clearfix <?php if ($columnNumber != "") echo "ing-grid-".$columnNumber; if ($allign != "") echo " allign-".$allign; if ($count != "") echo " inetgap-item-count-".$count;?>'>
                <?php foreach( $items as $item ):
                    $description = esc_html($item[$acf_field_text]);
                    $image = $item[$acf_field_image];
					$imageUrl = esc_url($image['url']);
					
					if ($count == 1) {
						$imagePreviewUrl = $imageUrl;
						/*$imagePreviewUrl = esc_url($image['sizes']['large']);*/
					} else {
						$imagePreviewUrl = esc_url($image['sizes']['medium']);
					}
                    
					$imageElement = "<img class='inetgap_acf_repeater_image' data-imgsrc='$imageUrl' src='$imagePreviewUrl' alt='$imageAlt'/>";
					
                    ?>
                    <li class="inetgap_acf_repeater-element ing-item-grid-element inetgap_acf_repeater_image_text">
						<?php if ((bool) $img_in_window ==  'true') { ?>
							<a href="<?php echo $imageUrl ?>" rel="noreferrer" data-rel="iLightbox[gallery_image_<?php echo $atts["acf_field"]; ?>]" class="fusion-lightbox" target="_self" data-caption="<?php echo $imageAlt; ?>">
								<?php echo $imageElement ?>
							</a>
						<?php } else { ?>
							<?php echo $imageElement ?>
						<?php } ?>
                        <p class="inetgap_acf_repeater_text"><?php echo $description; ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif;
    return ob_get_clean();
}
add_shortcode('inetgap-acf-repeater-image-text', 'inetgap_acf_repeater_image_text');


function inetgap_acf_files_download($atts)
{
    ob_start();
    $category = $atts['category'];
    $items = get_field('download_'.$category);

    if( $items ): ?>

        <div class="table-2 igp-acf-download-table">
            <table width="100%">
                <tbody>
                <?php foreach( $items as $item ):
                $description = esc_html($item['description']);
                $file = $item['file'];
                $fileUrl = esc_url($file['url']);
                $fileName = esc_html($file['filename']);
                ?>
                <tr>
                    <td><?php echo $description; ?></td>
                    <td style="text-align: right;">
                        <a class="fb-icon-element-1 fb-icon-element fontawesome-icon fa-eye fas circle-no fusion-text-flow fusion-link" href="<?php echo $fileUrl ?>" aria-label="Link to <?php echo $fileUrl ?>" target="_blank" rel="noopener noreferrer"></a>
                        <a href="<?php echo $fileUrl ?>" download="<?php echo $fileName; ?>"><i class="fb-icon-element-2 fb-icon-element fontawesome-icon fa-download fas circle-no fusion-text-flow" style="font-size:20px;margin-right:10px;"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif;
    return ob_get_clean();
}
add_shortcode('inetgap-acf-files-download', 'inetgap_acf_files_download');

/**
 * [inetgap-cpt-items-from-selected-acf-fields]
 * function retrieve items from Post Object type acf and display items in grid
 * Post Object - return type "post"
 */
function inetgap_cpt_items_from_selected_acf_fields($atts)
{
    ob_start();
    $columnNumber = '4';
    $showTitle = true;
	$showDescription = true;
	$size = 'medium'; // (thumbnail, medium, large, full or custom size)
    $selected_posts = get_field('product-content_product_related_products');
    if ( $selected_posts ) : ?>
        <ul style="" class='igp-related-products-items ing-item-grid clearfix <?php if ($columnNumber != "") echo "ing-grid-".$columnNumber; ?>'>
            <?php foreach ( $selected_posts as $selected_post  ):
                $selected_post_id = $selected_post->ID;
				
				if (get_post_status( $selected_post_id ) != 'publish') continue;
				
                $permalink = get_permalink( $selected_post_id );
                $title = get_the_title( $selected_post_id );
                $gallery = get_field('project_gallery', $selected_post_id);
                if(has_post_thumbnail($selected_post_id)) {
                    $imgUrl = get_the_post_thumbnail_url($selected_post_id,$size);
                } else {
                    if ($gallery) {
                        $imgUrl = esc_url($gallery[0]['sizes'][$size]);
                    } else {
                        $imgUrl = "/wp-content/uploads/static/file-img.png";
                    }
                }
                ?>
                <li class="ing-item <?php echo $selected_post_id; ?>">
                    <a href="<?php echo $permalink; ?>">
                    <div class="ing-item-content" style="background-image: url('<?php echo $imgUrl; ?>');">
                        <!-- img class="ing-item-content-img" src="<?php echo $imgUrl ?>" -->
                        <?php if ((bool) $showTitle == 'true') { ?>
                            <h3 class="ing-item-content-desc" ><?php echo $title; ?></h3>
                        <?php } ?>
                    </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('inetgap-cpt-items-from-selected-acf-fields', 'inetgap_cpt_items_from_selected_acf_fields');


/**
 * Extracts the TLD from the current host.
 *
 * @return string The TLD of the current host.
 */
function getCurrentTld() {
    // Get the host from the current URL
    $host = $_SERVER['HTTP_HOST'];

    // Split the host by '.' to isolate the TLD
    $hostParts = explode('.', $host);
    $tld = end($hostParts); // Gets the last part

    return $tld;
}

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



// function get_filtered_products_html($post_type, $filters = [], $paged = 1) {
//     $meta_query = [];

//     foreach ($filters as $key => $values) {
//         foreach ((array)$values as $val) {
//             $meta_query[] = [
//                 'key' => $key,
//                 'value' => '"' . sanitize_text_field($val) . '"',
//                 'compare' => 'LIKE'
//             ];
//         }
//     }

//     $args = [
//         'post_type' => sanitize_key($post_type),
//         'posts_per_page' => 12,
//         'paged' => $paged,
//         'meta_query' => $meta_query
//     ];

//     $query = new WP_Query($args);
//     ob_start();

//     if ($query->have_posts()) {
//         echo '<div class="inetgap-printed-list">';
//         while ($query->have_posts()) {
//             $query->the_post();
//             echo '<div class="omietka-item">';
//             the_title('<h3>', '</h3>');
//             echo '</div>';
//         }
//         echo '</div>';

//         echo paginate_links([
//             'total' => $query->max_num_pages,
//             'current' => $paged
//         ]);

//         wp_reset_postdata();
//     } else {
//         echo '<p>Nenašli sa žiadne výsledky.</p>';
//     }

//     return ob_get_clean();
// }