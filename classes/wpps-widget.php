<?php
namespace wppststky;

// Register and load the widget
function wpps_load_widget() {
	register_widget(__NAMESPACE__ . '\\poststicky_widget');
}
add_action('widgets_init', __NAMESPACE__ . '\\wpps_load_widget');

// Creating the widget
class poststicky_widget extends \WP_Widget {

    function __construct() {
        parent::__construct(
            'poststicky_widget',
            __('PostSticky Widget', 'poststickies_widget_domain'),
            array('description' =>
                __( 'Add a PostSticky to your widget area',
                'poststickies_widget_domain' ),
            )
        );
    }

    // Creating widget front-end
    public function widget($args, $instance) {

        $title = apply_filters('widget_title', $instance['title']);
		$stickynote_id = apply_filters('widget_note_id', $instance['stickynote_id']);
		$linktext = apply_filters('widget_linktext', $instance['linktext']);
		$linkurl = apply_filters('widget_linkurl', $instance['linkurl']);
        //echo $shortcode;

        // before and after widget arguments are defined by themes
        echo $args['before_widget'];

        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

		echo __($this->widget_main($stickynote_id), 'poststickies_widget_domain' );

		if ((!empty($linktext)) && (!empty($linkurl))) {
			if (($linktext != '(optional)') && ($linkurl != '(optional)')) {
				$stickynote_link = '<p><center>
					<a href="' . esc_url($linkurl) . '" target="_blank">
					' . esc_html($linktext) . '</a></center></p>';
				echo __($stickynote_link, 'poststickies_widget_domain' );
			}
		}

        echo $args['after_widget'];
    }

    // Widget Backend
    public function form($instance) {

        if (isset($instance['title'])) {
            $title = $instance[ 'title' ];
        } else {
            $title = __( 'New title', 'poststickies_widget_domain' );
        }

		if (isset($instance['stickynote_id'])) {
            $stickynote_id = $instance[ 'stickynote_id' ];
        } else {
            $stickynote_id = __('(Paste Post Sticky ID here)', 'poststickies_widget_domain');
        }

		if (isset($instance['linktext'])) {
            $linktext = $instance[ 'linktext' ];
        } else {
            $linktext = __('(optional)', 'poststickies_widget_domain');
        }

		if (isset($instance['linkurl'])) {
            $linkurl = $instance[ 'linkurl' ];
        } else {
            $linkurl = __('(optional)', 'poststickies_widget_domain');
        }

        // Widget admin form
        ?>
        <p>
            <label for="<?php echo esc_html($this->get_field_id('title')); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo esc_html($this->get_field_id('title')); ?>"
                name="<?php echo esc_html($this->get_field_name('title')); ?>"
                type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
		<p>
            <label for="<?php echo esc_html($this->get_field_id('stickynote_id')); ?>"><?php _e('Post Sticky ID:'); ?></label>
            <input class="widefat" id="<?php echo esc_html($this->get_field_id('stickynote_id')); ?>"
                name="<?php echo esc_html($this->get_field_name('stickynote_id')); ?>"
                type="text" value="<?php echo esc_attr($stickynote_id); ?>" />
        </p>
		<hr>
		<p>
            <label for="<?php echo esc_html($this->get_field_id('linktext')); ?>"><?php _e('Link Text:'); ?></label>
            <input class="widefat" id="<?php echo esc_html($this->get_field_id('linktext')); ?>"
                name="<?php echo esc_html($this->get_field_name('linktext')); ?>"
                type="text" value="<?php echo esc_attr($linktext); ?>" />
        </p>
		<p>
            <label for="<?php echo esc_html($this->get_field_id('linkurl')); ?>"><?php _e('Link URL:'); ?></label>
            <input class="widefat" id="<?php echo esc_html($this->get_field_id('linkurl')); ?>"
                name="<?php echo esc_html($this->get_field_name('linkurl')); ?>"
                type="text" value="<?php echo esc_attr($linkurl); ?>" />
        </p>
        <?php
    }

    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['stickynote_id'] = (!empty($new_instance['stickynote_id'])) ? strip_tags($new_instance['stickynote_id']) : '';
        $instance['linktext'] = (!empty($new_instance['linktext'])) ? strip_tags($new_instance['linktext']) : '';
        $instance['linkurl'] = (!empty($new_instance['linkurl'])) ? strip_tags($new_instance['linkurl']) : '';

        return $instance;
    }

    public function widget_main($note_id) {

		if (isset($note_id) && (!empty($note_id))) {
			$myStickyNote = new stickyNote();

	        $sidebar = $myStickyNote->stickynote($note_id, true);

			return $sidebar;
		} else {
			return "Please enter a valid Post Stickies shortcode.";
		}
    }

    private function extract_attribute($search_string, $attr) {
        // do nothing if the tag doesn't exist
        if (strpos($search_string, $attr) > 0) {
            // used for offsetting the attribute name + the "=^"
            $attr_offset = strlen($attr) + 2;

            // total search string length to prevent overflow
            $max_offset = strlen($search_string);

            $start_pos = strpos($search_string, $attr.'=^') + $attr_offset;

            if ($start_pos <= $max_offset) {
                $string_len = (strpos($search_string, '"', $start_pos) - $start_pos);
                if ($string_len <= $max_offset) {
                    return substr($search_string, $start_pos, $string_len);
                }
            }
        }

        return '';

    }
}
