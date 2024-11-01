<?php
namespace wppststky;

require_once('classes/wpps-posts.php');
require_once('classes/wpps-widget.php');
//require_once('includes/wpps-buttons.php');

//define( 'WP_DEBUG', true );
/*
Plugin Name: WP Post Stickies
Plugin URI: https://podz.io/agilepress/products/wp-post-stickies/
Description: This plugin uses shortcodes to insert sticky-note style sidebars
into page and post content.
Version: 1.93.9
Author: KMD Enterprises, LLC
Author URI: http://kmd.enterprises/
License: GPLv2
Tags: sticky, notes, sidebar
Text Domain: wp-post-stickies
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
* Plugin Activation
*/
register_activation_hook(__FILE__, __NAMESPACE__ . '\\wpps_activate');

function wpps_activate() {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    //global $wpdb;
    global $wp_version;

    if (version_compare($wp_version, '4.1', '<')) {
        wp_die('WP Post Stickies requires version 4.1 or higher.');
    }

    $wpps_options_arr = array(
        'wpps_admin_only' => 'No'
    );

    update_option('wpps_options', $wpps_options_arr);

    // flush rewrite cache
    flush_rewrite_rules();
}

/*
* Plugin Deactivation
*/
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\wpps_deactivate');

function wpps_deactivate() {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    delete_option('wpps_admin_only');

	unregister_post_type('wpps-post-stickies');

    // flush rewrite cache
    flush_rewrite_rules();
}

/**
 * Enqueue scripts and styles
 */
function wpps_admin_scripts() {
    //wp_register_style('poststickies_css', plugins_url('wp-post-stickies') . '/css/poststickies.css');
    //wp_enqueue_style('poststickies_css');

    wp_register_script('wpps-js', plugins_url('wp-post-stickies') . '/js/poststickies.js');
    wp_enqueue_script('wpps-js');
}
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\wpps_admin_scripts');

function wpps_wp_scripts() {
    wp_register_style('poststickies_css', plugins_url('wp-post-stickies') . '/css/poststickies.css');
    wp_enqueue_style('poststickies_css');
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\wpps_wp_scripts');

/**
* Initialization
*
* Registers the custom post type (CPT).
*
* @param null
* @return null
*
* @author Vinland Media, LLC.
*/
function wpps_init() {

    //register the products custom post type
    $labels = array(
        'name'               => __( 'Post Stickies', 'wp-post-stickies' ),
        'singular_name'      => __( 'Post Sticky', 'wp-post-stickies' ),
        'add_new'            => __( 'Add New', 'wp-post-stickies' ),
        'add_new_item'       => __( 'Add New Post Sticky', 'wp-post-stickies' ),
        'edit_item'          => __( 'Edit Post Sticky', 'wp-post-stickies' ),
        'new_item'           => __( 'New Post Sticky', 'wp-post-stickies' ),
        'all_items'          => __( 'All Post Stickies', 'wp-post-stickies' ),
        'view_item'          => __( 'View Post Sticky', 'wp-post-stickies' ),
        'search_items'       => __( 'Search Post Stickies', 'wp-post-stickies' ),
        'not_found'          =>  __( 'No Post Stickies found', 'wp-post-stickies' ),
        'not_found_in_trash' => __( 'No Post Stickies found in Trash', 'wp-post-stickies' ),
        'menu_name'          => __( 'Post Stickies', 'wp-post-stickies' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        //'show_in_menu'       => true,
        'show_in_menu'       => 'post-sticky-menu',
        'menu_icon'			 => 'dashicons-paperclip',
        'query_var'          => true,
        'rewrite'            => true,
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'show_in_rest'       => true,
        'rest_base'          => 'post-stickies',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'taxonomies'         => array('category')
    );

  	//register the products custom post type
  	register_post_type('wpps-post-stickies', $args);

  	// flush rewrite cache
    flush_rewrite_rules();

}
add_action('init', __NAMESPACE__.'\\wpps_init');

/**
 * Post Sticky CPT Columns
 *
 * The Admin-screen list display of the Post Sticy CPT needs to be explicitly
 * told what columns to show.
 *
 * @param $columns  Default column array for Post Stickies.
 * @return $new_columns  Modified column array for Post Stickies.
 *
 * @author Vinland Media, LLC.
 */
function add_new_wpps_poststicky_columns($columns) {
    $new_columns['cb'] = '<input type="checkbox" />';

    //$new_columns['id'] = __('ID');
    $new_columns['title'] = _x('Note Title', 'column name');
    //$new_columns['images'] = __('Images');
    $new_columns['author'] = _x('Created by', 'column name');

    $new_columns['color'] = __('Color');
    $new_columns['font'] = __('Font');
    $new_columns['align'] = __('Align');

	$new_columns['shortcode'] = __('Shortcode');

    $new_columns['categories'] = __('Categories');
    $new_columns['tags'] = __('Tags');

    $new_columns['date'] = _x('Date', 'column name');

    return $new_columns;
}
add_filter('manage_edit-wpps-post-stickies_columns', __NAMESPACE__.'\\add_new_wpps_poststicky_columns');

/**
 * List Custom Columns
 *
 * Function to show custom columns in list screens for Sticky Note CPT
 *
 * @param $column_name Column name
 * @param $post_ID Post ID
 * @return null
 *
 * @author Vinland Media, LLC.
 */
function list_custom_columns($column_name, $post_ID) {
	global $wpdb;

	$post_type = get_post_type($post_ID);

    if ($post_type == 'wpps-post-stickies') {
        $wpps_meta = get_post_meta($post_ID, '_wpps_poststicky_data', true);

        switch ($column_name) {
            case 'color':
                if ((isset($wpps_meta['color'])) && (!empty($wpps_meta['color']))) {
        			$post_color = $wpps_meta['color'];
                } else {
                    $post_color = '-';
                }
                echo $post_color;
                break;

            case 'font':
                if ((isset($wpps_meta['font'])) && (!empty($wpps_meta['font']))) {
                    $post_font = $wpps_meta['font'];
                } else {
                    $post_font = '-';
                }
                echo $post_font;
                break;

            case 'align':
                if ((isset($wpps_meta['align'])) && (!empty($wpps_meta['align']))) {
                    $post_align = $wpps_meta['align'];
                } else {
                    $post_align = '-';
                }
                echo $post_align;
                break;

			case 'shortcode':
                if ((isset($wpps_meta['shortcode'])) && (!empty($wpps_meta['shortcode']))) {
                    $post_align = $wpps_meta['shortcode'];
                } else {
                    $post_align = '-';
                }
                echo $post_align;
                break;

            default:
                $post_color = '-';
                $post_font = '-';
                $post_align = '-';
                break;
        }
    }

}
add_action('manage_posts_custom_column', __NAMESPACE__.'\\list_custom_columns', 10, 2);


/**
 * Admin Menu
 *
 * Creates the custom left-hand admin menu for the CPT.
 *
 * @param null
 * @return null
 *
 * @author Vinland Media, LLC.
 */
function wpps_admin_menu() {
    add_menu_page(
        'Post Stickies',
        'Post Stickies',
        'read',
        'post-sticky-menu',
        '', // Callback, leave empty
        'dashicons-paperclip',
        '25' // Position
    );

    // flush rewrite cache
    flush_rewrite_rules();

}
add_action('admin_menu', __NAMESPACE__.'\\wpps_admin_menu' );

/**
 * Register Metabox
 *
 * Function to register the CPT metabox.
 *
 * @param null
 * @return null
 *
 * @author Vinland Media, LLC.
 */
function wpps_add_meta_box() {
	add_meta_box('wpps_poststicky_meta', __('Post Sticky Attributes', 'wp-post-stickies'), __NAMESPACE__.'\\wpps_post_sticky_meta_box', 'wpps-post-stickies', 'normal', 'default');
}
add_action('add_meta_boxes', __NAMESPACE__.'\\wpps_add_meta_box');

/**
 * Post Sticky Metabox
 *
 * This function defines the form and function of the Post Sticky metabox.
 *
 * @param $post  The current post in The Loop.
 * @return null
 *
 * @author Vinland Media, LLC.
 */
function wpps_post_sticky_meta_box($post) {
    // retrieve our custom meta box values
    $wpps_meta = get_post_meta($post->ID, '_wpps_poststicky_data', true);

	$wpps_color = (!empty($wpps_meta['color'])) ? $wpps_meta['color'] : '';
    $wpps_font = (!empty($wpps_meta['font'])) ? $wpps_meta['font'] : '';
    $wpps_align = (!empty($wpps_meta['align'])) ? $wpps_meta['align'] : '';
    $wpps_tilt = (!empty($wpps_meta['tilt'])) ? $wpps_meta['tilt'] : '';

	wp_nonce_field('meta-box-save', 'wpps-post-stickies');

    $metabox_display = '<table>';
    $metabox_display .= '<tr>';


    $metabox_display .= '<td>' .__('Color', 'wp-post-stickies').':</td>';

    // start select box
    $metabox_display .= '<td><select name="wpps_meta[color]">';

    if ($wpps_color == '') {
        $metabox_display .= '<option value="" selected>Please select a color</option>';
    } else {
        $metabox_display .= '<option value="">Please select a color</option>';
    }
    $metabox_display .= '<option value="yellow"' . selected('yellow', esc_attr($wpps_color), false) . '>Yellow</option>';
    $metabox_display .= '<option value="pink"' . selected('pink', esc_attr($wpps_color), false) . '>Pink</option>';
    $metabox_display .= '<option value="blue"' . selected('blue', esc_attr($wpps_color), false) . '>Blue</option>';
    $metabox_display .= '<option value="green"' . selected('green', esc_attr($wpps_color), false) . '>Green</option>';

    $metabox_display .= '</tr>';
    $metabox_display .= '<tr>';


    $metabox_display .= '<td>' .__('Font', 'wp-post-stickies').':</td>';

    // start select box
    $metabox_display .= '<td><select name="wpps_meta[font]">';

    if ($wpps_font == '') {
        $metabox_display .= '<option value="" selected>Please select a font</option>';
    } else {
        $metabox_display .= '<option value="">Please select a font</option>';
    }
    $metabox_display .= '<option value="allura"' . selected('allura', esc_attr($wpps_font), false) . '>Allura</option>';
    $metabox_display .= '<option value="architects-daughter"' . selected('architects-daughter', esc_attr($wpps_font), false) . '>Architects Daughter</option>';
    $metabox_display .= '<option value="damion"' . selected('damion', esc_attr($wpps_font), false) . '>Damion</option>';
    $metabox_display .= '<option value="homemade-apple"' . selected('homemade-apple', esc_attr($wpps_font), false) . '>Homemade Apple</option>';
    $metabox_display .= '<option value="indie-flower"' . selected('indie-flower', esc_attr($wpps_font), false) . '>Indie Flower</option>';
    $metabox_display .= '<option value="patrick-hand-sc"' . selected('patrick-hand-sc', esc_attr($wpps_font), false) . '>Patrick Hand SC</option>';
    $metabox_display .= '<option value="permanent-marker"' . selected('permanent-marker', esc_attr($wpps_font), false) . '>Permanent Marker</option>';
    $metabox_display .= '<option value="reenie-beanie"' . selected('reenie-beanie', esc_attr($wpps_font), false) . '>Reenie Beanie</option>';
    $metabox_display .= '<option value="rock-salt"' . selected('rock-salt', esc_attr($wpps_font), false) . '>Rock Salt</option>';
    $metabox_display .= '<option value="sacramento"' . selected('sacramento', esc_attr($wpps_font), false) . '>Sacramento</option>';
    $metabox_display .= '<option value="shadows-into-light-two"' . selected('shadows-into-light-two', esc_attr($wpps_font), false) . '>Shadows Into Light Two</option>';
    $metabox_display .= '</select></td>';
    $metabox_display .= '</tr>';
    $metabox_display .= '<tr>';

    $metabox_display .= '<td>' .__('Align', 'wp-post-stickies').':</td>';
    // start select box
    $metabox_display .= '<td><select name="wpps_meta[align]">';
    if ($wpps_align == '') {
        $metabox_display .= '<option value="" selected>Please select the alignment</option>';
    } else {
        $metabox_display .= '<option value="">Please select the alignment</option>';
    }
    $metabox_display .= '<option value="left"' . selected('left', esc_attr($wpps_align), false) . '>Left</option>';
    $metabox_display .= '<option value="right"' . selected('right', esc_attr($wpps_align), false) . '>Right</option>';
    $metabox_display .= '</select></td>';

    $metabox_display .= '</tr>';

	$metabox_display .= '<tr>';

    $metabox_display .= '<td>' .__('Tilt', 'wp-post-stickies').':</td>';
    // start select box
    $metabox_display .= '<td><select name="wpps_meta[tilt]">';
    if ($wpps_tilt == '') {
        $metabox_display .= '<option value="" selected>Please select the tilt</option>';
    } else {
        $metabox_display .= '<option value="">Please select the tilt</option>';
    }
    $metabox_display .= '<option value="left"' . selected('left', esc_attr($wpps_tilt), false) . '>Left</option>';
    $metabox_display .= '<option value="right"' . selected('right', esc_attr($wpps_tilt), false) . '>Right</option>';
    $metabox_display .= '</select></td>';

    $metabox_display .= '</tr>';

    $metabox_display .= '</table>';

	$metabox_display .= '<input type="hidden" name="wpps_meta[shortcode]" value="[post-sticky note-id=\'' . esc_html($post->ID) . '\']">';

	echo $metabox_display;

}

/**
* Post Sticky Metabox Save
*
* This function fascilitates the saving of Post Sticky metabox data.
*
* @param $post_id  The id of the post whose meta is being stored.
* @return null
*
* @author Vinland Media, LLC.
*/
function wpps_save_poststicky_meta_box($post_id) {
	//verify the post type is for AgilePress and metadata has been posted
	if (get_post_type($post_id) == 'wpps-post-stickies' && isset($_POST['wpps_meta'])) {

		//if autosave skip saving data
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return;

		//check nonce for security
		wp_verify_nonce('meta-box-save', 'wpps-post-stickies');

        //store option values in a variable
        $wpps_poststicky_data = sanitize_meta('_wpps_poststicky_data', $_POST['wpps_meta'], 'post');

        //use array map function to sanitize option values
        $wpps_poststicky_data = array_map('sanitize_text_field', $wpps_poststicky_data);

        if (empty($wpps_poststicky_data['color'])) {
	        $wpps_poststicky_data['color'] = 'yellow';
        }

		if (empty($wpps_poststicky_data['font'])) {
			$wpps_poststicky_data['font'] = 'permanent-marker';
		}

		if (empty($wpps_poststicky_data['align'])) {
			$wpps_poststicky_data['align'] = 'right';
		}

		if (empty($wpps_poststicky_data['tilt'])) {
			$wpps_poststicky_data['tilt'] = 'left';
		}

        // save the meta box data as post metadata
        update_post_meta($post_id, '_wpps_poststicky_data', $wpps_poststicky_data);
	}
}
add_action('save_post', __NAMESPACE__.'\\wpps_save_poststicky_meta_box');


/*
* Main
*/
function wpps_main($atts)
{
    //$panel_options = get_option('panel_options');

    $fetch_data_atts = shortcode_atts( array(
        // the following are for backward compatability and will go away
        // with a subsequent release
        'type' => '',
        'color' => '',
        'font' => '',
        'align' => '',
        'header' => '',
        'body' => '',
        'footer' => '',
        // end deprecated list
        'note-id' => ''
    ), $atts );

    // new method
    if (isset($fetch_data_atts['note-id']) && (!empty($fetch_data_atts['note-id']))) {
        $myStickyNote = new stickyNote();
        $sidebarID = wpps_sanitize_options($fetch_data_atts['note-id']);

        $sidebar = $myStickyNote->stickynote($sidebarID);
    }

    return $sidebar;
}

// Hooks a function to the shortcode
add_shortcode('post-sticky', __NAMESPACE__ . '\\wpps_main');

/*
* Settings submenu
*/
add_action('admin_menu', __NAMESPACE__ . '\\wpps_settings_submenu');

function wpps_settings_submenu() {
    add_options_page('WP Post Stickies Settings Page', 'WP Post Stickies Settings',
        'manage_options', __NAMESPACE__ . '\\wpps_settings_menu',
        __NAMESPACE__ . '\\wpps_settings_page');

    add_action('admin_init', __NAMESPACE__ . '\\wpps_register_settings');
}

/*
* Settings registration
*/
function wpps_register_settings() {
    register_setting('wpps-settings-group', __NAMESPACE__ . '\\wpps_options',
        __NAMESPACE__ . '\\wpps_sanitize_options');
}

/*
* Settings page
*/
function wpps_settings_page() {
?>
<div class="wrap container">
    <h3>WP Post Stickies Plugin</h3>
	<p><em>IMPORTANT: WP Post Stickies is moving toward the use of a Custom Post Type (CPT) for its notes.  We didn't want to break anything that you might currently be using, so the old way (shortcodes with lots of parameters!) still works.</em></p>
	<p><em>Please switch any existing notes to - and create all new notes - using the new method.</em></p>
	<hr />
	<p>In order to use the new style of Post Sticky, use the "Post Stickies" menu option and select "All Post Stickies" (left admin menu) or use "New," "Post Sticky" from the top admin menu.  Create your Post Sticky like any other post, and use the fields under the post content section to set fonts, colors, and alignment.  Then grab your shortcode from the "All Post Stickies" list screen and past it into a post, page, or into the Post Stickies widget.</p>
	<p>Currently, the title of your WP Post Sticky custom post is not being used in the note itself; we are slowly integrating all features of the new as we deprecate the old to avoid any conflicts.  The body of the custom post becomes the body of the Post Sticky, and the "excerpt" field, if used, is shown as the Post Sticky footer.</p>
</div>
<?php
}


function wpps_sanitize_options($input) {
    return $input;
}

/*
* Settings initialize
*/
add_action('admin_init', __NAMESPACE__ . '\\wpps_settings_init');

function wpps_settings_init() {
    return 0;
}
