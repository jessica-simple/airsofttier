<?php

$title = $el_class = $open = $css_animation = '';

$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$el_class = $this->getExtraClass($el_class);
$open = ($open == 'true') ? ' wpb_toggle_title_active' : '';
$el_class .= ($open == ' wpb_toggle_title_active') ? ' wpb_toggle_open' : '';

$css_class = apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'wpb_toggle' . $open, $this->settings['base'], $atts);
$css_class .= $this->getCSSAnimation($css_animation);

// output -----

echo '<div class="faq">';
	echo '<div class="mfn-acc faq_wrapper">';

		echo '<div class="question">';

      if( ! empty( $atts['use_custom_heading'] ) ){
        $heading = $this->getHeading( $atts );
      } else {
        $heading = $title;
      }

			echo  '<div class="title '. esc_attr($css_class) .'"><i class="icon-plus acc-icon-plus"></i><i class="icon-minus acc-icon-minus"></i>'. $heading .'</div>';

			$css_class = apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'wpb_toggle_content answer' . $el_class, $this->settings['base'], $atts);
			echo '<div class="'. esc_attr($css_class) .'">'. wpb_js_remove_wpautop($content, true) .'</div>'. $this->endBlockComment('toggle') ."\n";

		echo '</div>'."\n";

	echo '</div>';
echo '</div>'."\n";
