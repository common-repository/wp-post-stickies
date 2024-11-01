<?php
namespace wppststky;

add_action('admin_head', __NAMESPACE__ . '\\postpanel_add_button');

function postpanel_add_button() {
    global $typenow;
    // check user permissions
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
        return;
    }
    // verify the post type
    if( ! in_array( $typenow, array( 'post', 'page' ) ) )
        return;
    // check if WYSIWYG is enabled
    if ( get_user_option('rich_editing') == 'true') {
        add_filter('mce_external_plugins', __NAMESPACE__ . '\\postpanel_add_tinymce_plugin');
        add_filter('mce_buttons', __NAMESPACE__ . '\\postpanel_register_button');
    }
}

function postpanel_add_tinymce_plugin($plugin_array) {
    $plugin_array['poststicky_button'] = plugins_url('wp-post-stickies') . '/js/postbutton.js';
    return $plugin_array;
}

function postpanel_register_button($buttons) {
   array_push($buttons, "poststicky_button");
   return $buttons;
}
