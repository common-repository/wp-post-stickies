<?php
namespace wppststky;

class stickyNote
{
    // getters and setters
    public $color;
    public function set_color($color) {
        $this->color = $color;
    }
    public function get_color() {
        return $this->color;
    }

    public $font;
    public function set_font($font) {
        $this->font = $font;
    }
    public function get_font() {
        return $this->font;
    }

    public $align;
    public function set_align($align) {
        $this->align = $align;
    }
    public function get_align() {
        return $this->align;
    }

    public $header;
    public function set_header($header) {
        $this->header = $header;
    }
    public function get_header() {
        return $this->header;
    }

    public $body;
    public function set_body($body) {
        $this->body = $body;
    }
    public function get_body() {
        return $this->$body;
    }

    public $footer;
    public function set_footer($footer) {
        $this->footer = $footer;
    }
    public function get_footer() {
        return $this->footer;
    }

    public function stickynote($note_id = null, $is_widget = false) {
        $sticky_post = get_post($note_id);

		$wpps_meta = get_post_meta($note_id, '_wpps_poststicky_data', true);

        $sidebar = '
          <div id="notes">
              <div class="' . esc_attr($wpps_meta['color']) . 'note ' . esc_attr($this->is_widget($is_widget, $wpps_meta['align'])) . ' tilt' . esc_attr($wpps_meta['tilt']) . '">
                  <p class="notebody-' . esc_attr($wpps_meta['font']) . '">' . $sticky_post->post_content . '</p>
                  <p class="notefoot">' . $sticky_post->post_excerpt . '</p>
              </div>
          </div>';

          return $sidebar;
    }

    // future release
    /*
    public function quotebar() {
        $sidebar =
            '<center><div class="w3-panel w3-leftbar w3-signal-white w3-large w3-serif" '
            . 'style="width:80%">'
            . '<p>' . $this->body . '</p>'
            . '</div></center>';

        return $sidebar;
    } */

    // future release
    /*
    public function tripanel() {
        $sidebar =
            '<div class="w3-card-4 w3-right w3-margin" style="width:45%">'
            . '<header class="w3-container w3-blue">'
            . '<h2>' . $this->header . '</h2>'
            . '</header><div class="w3-container"><p>'.$this->body.'</p></div>'
            . '<footer class="w3-container w3-blue"><h5>Footer</h5></footer></div>';

        return $sidebar;
    } */

    // selects CSS for widget if applicable
    private function is_widget($widget, $align) {
        if ($widget) {
            return 'notewidget';
        } else {
            return 'noteall note' . $align;
        }

    }
}
