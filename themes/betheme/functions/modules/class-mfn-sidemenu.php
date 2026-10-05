<?php
defined( 'ABSPATH' ) || exit;

/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

class MfnSideMenu {

	public $id = false;
	public $classes = array();
	public $datatags = array();
	public $align = false;
	public $visibility = false;
	public $blur = false;
	public $entrance = false;
	public $overlay = false;

	public function __construct( $id ){
		$this->id = $id;

		$this->datatags[] = 'data-id="'.$id.'"';
		$this->align = !empty( get_post_meta( $this->id, 'sidemenu_position', true) ) ? 'right' : 'left';
		$this->visibility = !empty( get_post_meta( $this->id, 'mfn_sidemenu_visibility', true) ) ? get_post_meta( $this->id, 'mfn_sidemenu_visibility', true) : '';
		$this->blur = !empty( get_post_meta( $this->id, 'sidemenu_overlay_blur', true) ) ? get_post_meta( $this->id, 'sidemenu_overlay_blur', true) : false;
		$this->overlay = !empty( get_post_meta( $this->id, 'sidemenu_overlay_background', true) ) ? get_post_meta( $this->id, 'sidemenu_overlay_background', true) : false;
		$this->entrance = !empty( get_post_meta( $this->id, 'sidemenu_entrance_animation', true) ) ? get_post_meta( $this->id, 'sidemenu_entrance_animation', true) : 'default';
		
		// align class
		$this->classes[] = 'mfn-sidemenu-align-'.$this->align;
		$this->datatags[] = 'data-align="'.$this->align.'"';
		// entrance
		$this->datatags[] = 'data-entrance="'.$this->entrance.'"';
		// close button
		$this->classes[] = !empty( get_post_meta( $this->id, 'sidemenu_close_button_active', true) ) ? 'mfn-sidemenu-closebutton-active' : 'mfn-sidemenu-closebutton-hidden';
		// close button align
		$this->classes[] = !empty( get_post_meta( $this->id, 'sidemenu_close_button_align', true) ) ? 'mfn-sidemenu-close-button-left' : 'mfn-sidemenu-close-button-right';
		// close on overlay click
		$this->classes[] = !empty( get_post_meta( $this->id, 'sidemenu_close_on_overlay_click', true) ) ? 'mfn-sidemenu-close-on-overlay-click' : '';
		
		// body scroll
		$this->datatags[] = !empty( get_post_meta( $this->id, 'sidemenu_body_scroll', true) ) ? 'data-bodyscroll="enabled"' : 'data-bodyscroll="disabled"';

		if( !empty($this->visibility) ) $this->classes[] = 'mfn-sidemenu-'.$this->visibility;

	}

	public function classes() {
		return $this->classes;
	}

	public function render( $css = true ) {

		echo '<div id="mfn-sidemenu-tmpl-'.$this->id.'" class="mfn-sidemenu-tmpl '.implode(' ', $this->classes).'" '.implode(' ', $this->datatags).'>';
			$mfn_footer_builder = new Mfn_Builder_Front( $this->id );
			$mfn_footer_builder->show( false );
		echo '</div>';

		if( $css ) $this->css();

	}

	public function css(){

		$tablet_styles = array();
		$mobile_styles = array();
		$laptop_styles = array();

		$get_styles = get_post_meta($this->id, 'mfn-page-options-style', true);

			/*echo '<pre>';
			print_r($get_styles);
			echo '</pre>';*/
			
			$avdw = false;
			$avtw = false;
			$avmw = false;

			echo '<style class="mfn-sidemenu-tmpl-local-styles">';

			if( !empty($this->visibility) ) {
				echo '@media(min-width: 1240px) { #mfn-sidemenu-tmpl-'.$this->id.'{display: block; opacity:1; '.$this->align.': 0;} }';
				if( !empty( get_post_meta($this->id, 'mfn_sidemenu_visibility_header', true) ) ) echo '@media(min-width: 1240px) { #mfn-header-template{display: none !important;} }';
			}
			echo '.mfn-sidemenu-'.$this->id.'-active #Wrapper{transition: 0.5s; pointer-events: none;}';

			echo '.mfn-sidemenu-'.$this->id.'-active body.mobile-side-slide{overflow-x: hidden;}';

			if( $this->blur ) echo '.mfn-sidemenu-'.$this->id.'-active #Wrapper{filter: blur('.$this->blur.'px); overflow: hidden;}';
			if( $this->overlay ) echo '.mfn-sidemenu-'.$this->id.'-active body:before{content: ""; position: fixed; top: 0; left: 0; z-index: '.( isset( $get_styles['#mfn-sidemenu-tmpl-postid']['z-index'] ) && !empty( $get_styles['#mfn-sidemenu-tmpl-postid']['z-index'] ) ? $get_styles['#mfn-sidemenu-tmpl-postid']['z-index'] : '10000' ).'; display: block; width: 100%; height: 100%; background-color: '.$this->overlay.'; transition: 0.5s;}';

			if( $this->entrance == 'move-content' ) {
				$entrance_offset = $this->align == 'left' ? '200px' : '-200px';
				echo '.mfn-sidemenu-'.$this->id.'-active #Wrapper{ transform:translateX('.$entrance_offset.'); }';
				echo '.mfn-closing-sidemenu-'.$this->id.' #Wrapper{ transition: 0.4s; transform:translateX(0)}';
			}
		
		if( $get_styles ){

			$styles = $get_styles;

			foreach($styles as $s=>$style) {
				$s = str_replace('postid', $this->id, $s);
				
				echo $s.'{';
					foreach ($style as $st => $value) {

						if( strpos($st, '_tablet') === false && strpos($st, '_mobile') === false && strpos($st, '_laptop') === false ) {
							if( is_array($value) ) {
								foreach ($value as $v=>$val) {
									if( $v == 'laptop' ) {
										$laptop_styles[$s][$st] = $val;
									}else if( $v == 'tablet' ) {
										$tablet_styles[$s][$st] = $val;
									}else if( $v == 'mobile' ) {
										$mobile_styles[$s][$st] = $val;
									}else{
										if( $v == 'desktop' ) {
											if( is_array($val) && is_iterable($val) ) {
												foreach ($val as $k => $w) {
													if( !empty($w) ) echo $st.'-'.$k.':'.$w.';';
												}
											}else{
												if( !empty($val) ) echo $st.':'.$val.';';
												if( !empty($this->visibility) && $this->visibility == 'always-visible' && $st == '--mfn-sidemenu-width' ) $avdw = $val;
											}
										}elseif( !empty($val) ) {
											echo $st.'-'.$v.':'.$val.';';
											if( !empty($this->visibility) && $this->visibility == 'always-visible' && $st == '--mfn-sidemenu-width' ) $avdw = $val;
										}
									}
								}
							}else if( is_string($value) ) {
								if( !empty($value) ) echo $st.':'.$value.';';
								if( !empty($this->visibility) && $this->visibility == 'always-visible' && $st == '--mfn-sidemenu-width' ) $avdw = $value;
							}
						}else{
							if( strpos($st, '_tablet') !== false ) {
								$tablet_styles[$s][str_replace('_tablet', '', $st)] = $value;
							}else if( strpos($st, '_mobile') !== false ) {
								$mobile_styles[$s][str_replace('_mobile', '', $st)] = $value;
							}else if( strpos($st, '_laptop') !== false ) {
								$laptop_styles[$s][str_replace('_laptop', '', $st)] = $value;
							}
						}
					}
				echo '}';
			}

			if( count($laptop_styles) > 0 ) {
				echo '@media(max-width: 1240px) {';
				foreach($laptop_styles as $s=>$style) {
					echo $s.'{';
						foreach($style as $st=>$value){
							if( is_array($value) && is_iterable($value) ){
								foreach ($value as $v => $val) {
									if( !empty($val) ) echo $st.'-'.$v.':'.$val.';';
								}
							}else{
								if( !empty($value) ) echo $st.':'.$value.';';
							}
						}
					echo '}';
				}
				echo '}';
			}

			if( count($tablet_styles) > 0 ) {
				echo '@media(max-width: 959px) {';
				foreach($tablet_styles as $s=>$style) {
					echo $s.'{';
						foreach($style as $st=>$value){
							if( is_array($value) && is_iterable($value) ){
								foreach ($value as $v => $val) {
									if( !empty($val) ) echo $st.'-'.$v.':'.$val.';';
								}
							}else{
								if( !empty($value) ) echo $st.':'.$value.';';
							}
						}
					echo '}';
				}
				echo '}';
			}

			if( count($mobile_styles) > 0 ) {
				echo '@media(max-width: 767px) {';
				foreach($mobile_styles as $s=>$style) {
					echo $s.'{';
						foreach($style as $st=>$value){
							if( is_array($value) && is_iterable($value) ){
								foreach ($value as $v => $val) {
									if( !empty($val) ) echo $st.'-'.$v.':'.$val.';';
								}
							}else{
								if( !empty($value) ) echo $st.':'.$value.';';
							}
						}
					echo '}';
				}
				echo '}';
			}

			if( $avdw ) echo 'body{ --mfn-sidemenu-always-visible-offset: '.$avdw .' }';

		}

		echo '</style>';
	}

}