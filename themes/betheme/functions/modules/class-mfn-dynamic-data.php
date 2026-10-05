<?php
defined( 'ABSPATH' ) || exit;

/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

class MfnDynamicData {

	public $id = false;
	public $second_param = false;
	public $first_param = false;
	public $is_term = false;
	public $third_param = false;

	public function render( $string, $post_id = false ) {

		//if( empty(Mfn_Builder_Front::$item_id) && Mfn_Builder_Front::$is_bebuilder ) return $string;

		if( empty($string) || strpos($string, '{') === false ) return $string;

		preg_match_all( '/\{([^<>&\/\{\]\x00-\x20=]+)\}/', $string, $match );

		/*echo '<pre>';
		print_r($match);
		echo '</pre>';*/

		if( is_array($match[0]) ) {
			foreach($match[0] as $dd) {

				$is_quote = false;

				$mfn_dyn = str_replace(array('{', '}'), '', $dd);
				$this->prepare_string($mfn_dyn, $post_id);
				$fun_name = strpos($mfn_dyn, ':') !== false ? explode(':', $mfn_dyn)[0] : $mfn_dyn;

				// echo $fun_name;

				if( strpos($fun_name, '"') !== false ) {
					$is_quote = true;
					$fun_name = str_replace('"', '', $fun_name);
				}

				if( method_exists( $this, $fun_name ) ) {
					$ready_string = $this->$fun_name();
					if( $is_quote ) $ready_string .= '"'; // tinymce permalink quota fix

					if( !is_wp_error($ready_string) && !is_iterable($ready_string) ) {
						$string = str_replace($dd, $ready_string, $string);
					}
				}

			}
		}

		return $string;

	}

	public function prepare_string($string, $post_id = false){

		$this->is_term = false;
		$this->second_param = false;
		$this->id = false;

		if( strpos($string, ':') !== false ) {

			$explode = explode(':', $string);

			$this->first_param = $explode[0];

			if( !empty($explode[3]) && !empty($explode[2]) && !empty($explode[1]) ) {
				$this->id = $explode[1];
				$this->second_param = $explode[2];
				$this->third_param = $explode[3];
			}elseif( !empty($explode[2]) && !empty($explode[1]) && is_numeric($explode[1]) ) {
				$this->id = $explode[1];
				$this->second_param = $explode[2];
			}elseif( !empty($explode[2]) && !empty($explode[1]) && is_numeric($explode[2]) ) {
				$this->id = $explode[2];
				$this->second_param = $explode[1];
			}elseif( !empty($explode[1]) && is_numeric($explode[1]) ) {
				$this->id = $explode[1];
			}elseif( !empty($explode[2]) ) {
				$this->second_param = $explode[1];
				$this->third_param = $explode[2];
			}elseif( !empty($explode[1]) ) {
				$this->second_param = $explode[1];

				if( !empty(Mfn_Builder_Front::$item_type) && Mfn_Builder_Front::$item_type == 'term' && !empty(Mfn_Builder_Front::$item_id) ) {
					$term = get_term(Mfn_Builder_Front::$item_id);
					$this->is_term = $term;
				}

			}

			if( !$this->id && $post_id ) $this->id = $post_id;
			if( !$this->id && !empty(Mfn_Builder_Front::$item_id) ) $this->id = Mfn_Builder_Front::$item_id;

			if( !$this->second_param && !empty(Mfn_Builder_Front::$item_type) ) $this->second_param = Mfn_Builder_Front::$item_type;

			if( !$this->id && is_singular() ) $this->id = get_the_ID();

			$qo = get_queried_object();
			if( !$this->id && isset($qo->term_id) ) {
				$this->id = $qo->term_id;
				$this->is_term = $qo;
			}

		}else{

			if( !empty(Mfn_Builder_Front::$item_id) ) {
				$this->id = Mfn_Builder_Front::$item_id;
				if( !$this->second_param && !empty(Mfn_Builder_Front::$item_type) ) $this->second_param = Mfn_Builder_Front::$item_type;
			}else if( $post_id ){
				$this->id = $post_id;
			}else if( is_singular() ){
				$this->id = get_the_ID();
			}else if( is_archive() ){
				if( function_exists('is_woocommerce') && is_shop() && !empty(wc_get_page_id('shop')) ){
					$this->id = wc_get_page_id('shop');
				}else if( is_tax() || is_category() || is_tag() || ( function_exists('is_woocommerce') && is_product_tag() ) ){
					$qo = get_queried_object();
					$this->id = $qo->term_id;
					$this->is_term = $qo;
					if( !$this->second_param ) $this->second_param = 'term';
				}
			}

		}

	}

	public function id(){
		if( $this->id ) return $this->id;
		return false;
	}

	public function postmeta() {

		//echo $this->id.' / '.$this->second_param;

		if( !empty( $this->third_param ) && $this->id && $this->second_param ){
			if( $this->third_param == 'date_format' && !empty(get_post_meta($this->id, $this->second_param, true)) ){
				$timestamp = strtotime( get_post_meta($this->id, $this->second_param, true) );
				return date_i18n(get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp);
			}
		}

		if( $this->id && $this->second_param ){
			return get_post_meta($this->id, $this->second_param, true);
		}

		return '';
	}

	public function termmeta(){

		//echo $this->id.' / '.$this->second_param;

		if( $this->id && $this->second_param ){
			return get_term_meta($this->id, $this->second_param, true);
		}

		return '';
	}

	public function title(){

		//echo $this->id.' / '.$this->second_param;

		if( ( is_singular() || $this->id ) && (empty($this->second_param) || !in_array($this->second_param, array('term', 'site', 'author', 'add_to_cart')) ) ) {
			return get_the_title( $this->id );
		}else if( $this->second_param && $this->second_param == 'site' ){
			return get_option( 'blogname' );
		}else if(is_author() && empty($this->second_param)) {
			return get_the_author();
		}else if( $this->second_param && $this->second_param == 'term' ){
			$term = get_term($this->id);
			if( isset($term->name) ) return $term->name;
		}else if( $this->second_param && $this->second_param == 'add_to_cart' ){

			$label = __('Add to cart', 'woocommerce');
			if( get_post_type($this->id) == 'product' ) {
				$product =  wc_get_product($this->id);
				if( $product->is_type('variable') ) {
					$label = __('Select options', 'woocommerce');
				}else if( $product->is_type('external') && !empty( get_post_meta($this->id, '_button_text', true) ) ) {
					$label = get_post_meta($this->id, '_button_text', true);
				}
			}
			return $label;
		}else if( $this->second_param && $this->second_param == 'author' ){
			return get_the_author( $this->id );
		}else if( is_home() && get_option( 'page_for_posts' ) ){
			return get_the_title( get_option( 'page_for_posts' ) );
		}else if( is_archive() ){
			if( function_exists('is_woocommerce') && is_shop() ){
				return get_the_title( wc_get_page_id('shop') );
			}else{
				return single_term_title('', false);
			}
		}
	}

	public function featured_image(){

		//echo $this->id.' / '.$this->second_param;

		if( $this->second_param && $this->second_param == 'site' ){
			return mfn_opts_get('logo-img', get_theme_file_uri('/images/logo/logo.png'));
		}else if( $this->id && $this->second_param && $this->second_param == 'author' ){
			$author_id  = get_post_field( 'post_author', $this->id );
			return get_avatar_url($author_id);
		}else if( $this->id && $this->second_param && $this->second_param == 'user' ){
			if( !is_user_logged_in() ) return false;
			$current_user = wp_get_current_user();
			return get_avatar_url($current_user->ID);
		}else if( $this->second_param && $this->second_param == 'term' ){
			$term = get_term($this->id);
			if( get_term_meta( $term->term_id, 'thumbnail_id', true ) ){
				//return wp_get_attachment_url( get_term_meta( $term->term_id, 'thumbnail_id', true ), 'full' );
				return get_term_meta( $term->term_id, 'thumbnail_id', true );
			}else{
				return get_theme_file_uri('/muffin-options/svg/placeholders/image.svg');
			}
		}else if( $this->id ){
			if( has_post_thumbnail($this->id) ) {
				if( $this->second_param && $this->second_param == 'tag' ){
					return get_the_post_thumbnail( $this->id, 'full' );
				}else if( $this->second_param && $this->second_param == 'url' ){
					return get_the_post_thumbnail_url( $this->id, 'full' );
				}else{
					return get_post_thumbnail_id($this->id, 'full');
				}
			}else{
				if( $this->second_param && $this->second_param == 'tag' ){
					return '<img src="'.get_theme_file_uri('/muffin-options/svg/placeholders/image.svg').'" alt="">';
				}else{
					return get_theme_file_uri('/muffin-options/svg/placeholders/image.svg');
				}
			}
		}else if( function_exists('is_woocommerce') && is_product_category() ){
			$qo = get_queried_object();
			if( get_term_meta( $qo->term_id, 'thumbnail_id', true ) ){
				return get_post_thumbnail_id( get_term_meta( $qo->term_id, 'thumbnail_id', true ), 'full' );
			}else{
				return get_theme_file_uri('/muffin-options/svg/placeholders/image.svg');
			}
		}else if( function_exists('is_woocommerce') && is_shop() ){
			if( has_post_thumbnail(wc_get_page_id('shop')) ) {
				return get_post_thumbnail_id( wc_get_page_id('shop'), 'full');
			}else{
				return get_theme_file_uri('/muffin-options/svg/placeholders/image.svg');
			}

		}else if( is_home() && get_option( 'page_for_posts' ) ){
			if( has_post_thumbnail( get_option( 'page_for_posts' ) ) ) {
				return get_post_thumbnail_id( get_option( 'page_for_posts' ), 'full' );
			}else{
				return get_theme_file_uri('/muffin-options/svg/placeholders/image.svg');
			}
		}

		return get_theme_file_uri('/muffin-options/svg/placeholders/image.svg');

	}

	public function permalink(){

		// echo '<br>'.$this->id.' x/x '.$this->second_param;

		if( $this->second_param && $this->second_param == 'home' ){
			return get_home_url();
		}else if( $this->id && $this->second_param && $this->second_param == 'author' ){
			$author_id  = get_post_field( 'post_author', $this->id );
			return get_author_posts_url($author_id);
		}else if( $this->id && $this->second_param && $this->second_param == 'user' ){
			if( !is_user_logged_in() ) return false;
			$current_user = wp_get_current_user();
			return get_author_posts_url( $current_user->ID );
		}else if( $this->id && $this->second_param && $this->second_param == 'add_to_cart' ){

			$permalink = get_the_permalink($this->id);
			if( get_post_type($this->id) == 'product' ) {
				$product =  wc_get_product($this->id);
				if( $product->is_type('simple') ) {
					$permalink = wc_get_cart_url().'?add-to-cart='.$this->id.'&quantity=1';
				}else if( $product->is_type('external') && !empty( get_post_meta($this->id, '_product_url', true) ) ) {
					$permalink = get_post_meta($this->id, '_product_url', true);
				}
			}

			return $permalink;
		}else if( $this->second_param && $this->second_param == 'term' ){
			$term = get_term($this->id);
			return get_term_link($term);
		}else{
			return get_the_permalink($this->id);
		}
	}

	public function excerpt(){
		if( (is_singular() || $this->id) && $this->second_param != 'term' ){
			return get_the_excerpt($this->id);
		}else if( $this->id && $this->second_param && $this->second_param == 'term' ){
			$term = get_term($this->id);
			return $term->description;
		}else if( function_exists('is_woocommerce') && is_shop() ){
			return get_the_content(wc_get_page_id('shop'));
		}else if( is_archive() ){
			return term_description();
		}
	}

	public function content(){

		//echo '<br>'.$this->id.' x/x '.$this->second_param;

		if( (is_singular() || $this->id) && $this->second_param != 'term' ){
			if( function_exists('is_woocommerce') && !empty(wc_get_page_id('shop')) && wc_get_page_id('shop') == $this->id ) return false;
			//return get_the_content($this->id);
			$mfnpost = get_post($this->id);
			return apply_filters('the_content',$mfnpost->post_content);
		}else if( $this->second_param && $this->second_param == 'term' ){
			$term = get_term($this->id);
			return $term->description;
		}else if( function_exists('is_woocommerce') && is_shop() ){
			return get_the_content(wc_get_page_id('shop'));
		}else if( is_archive() ){
			return term_description();
		}
	}

	public function date(){

		if( !empty($this->second_param) && $this->second_param == 'modified' ){
			return get_the_modified_date('', $this->id);
		}else if( !empty($this->second_param) && $this->second_param == 'current' ){
			return date(get_option('date_format'));
		}else if( !empty($this->second_param) && $this->second_param == 'year' ){
			return date('Y');
		}else if( !empty($this->second_param) && $this->second_param == 'month' ){
			return date('m');
		}else if( !empty($this->second_param) && $this->second_param == 'day' ){
			return date('d');
		}else{
			return get_the_date('', $this->id);
		}
	}

	public function author(){

		//echo '<br>'.$this->id.' x/x '.$this->second_param;

		$post = get_post($this->id);

		if( !empty($this->second_param) && !in_array($this->second_param, array('post', 'product', 'portfolio', 'page', 'client', 'offer', 'slide', 'testimonial')) ){
			return get_the_author_meta( $this->second_param, $post->post_author );
		}

		return get_the_author_meta( 'display_name', $post->post_author );

		//return get_the_author();
	}

	public function categories(){

		if( !$this->id ) return '';

		$post_type = get_post_type($this->id);

		if( $post_type == 'page' ) return '';
		if( !empty($this->second_param) && $this->second_param == 'term' ) return '';

		if( $post_type == 'post' ){
			$terms = get_the_terms( $this->id, 'category' );
		}else if( $post_type == 'product' ){
			$terms = get_the_terms( $this->id, 'product_cat' );
		}else if( $post_type == 'portfolio' ){
			$terms = get_the_terms( $this->id, 'portfolio-types' );
		}else if( $post_type == 'offer' ){
			$terms = get_the_terms( $this->id, 'offer-types' );
		}else if( $post_type == 'client' ){
			$terms = get_the_terms( $this->id, 'client-types' );
		}else if( $post_type == 'slide' ){
			$terms = get_the_terms( $this->id, 'slide-types' );
		}

		if( is_array($terms) ){
			return join(', ', wp_list_pluck($terms, 'name'));
		}else{
			return $terms;
		}

	}

	public function category(){

		if( !$this->id ) return '';

		$post_type = get_post_type($this->id);

		if( $post_type == 'page' ) return '';
		if( !empty($this->second_param) && $this->second_param == 'term' ) return '';

		if( $post_type == 'post' ){
			$terms = get_the_terms( $this->id, 'category' );
		}else if( $post_type == 'product' ){
			$terms = get_the_terms( $this->id, 'product_cat' );
		}else if( $post_type == 'portfolio' ){
			$terms = get_the_terms( $this->id, 'portfolio-types' );
		}else if( $post_type == 'offer' ){
			$terms = get_the_terms( $this->id, 'offer-types' );
		}else if( $post_type == 'client' ){
			$terms = get_the_terms( $this->id, 'client-types' );
		}else if( $post_type == 'slide' ){
			$terms = get_the_terms( $this->id, 'slide-types' );
		}

		$main = array();

		if (!is_wp_error($terms) && !empty($terms)) {
		    foreach ($terms as $term) {
		      	if( $term->parent ) continue;
		        $main[] = $term;
		    }
		}

		if( count($main) > 0 ){
			return join(', ', wp_list_pluck($main, 'name'));
		}elseif( is_array($terms) && count($terms) > 0 ){
			return join(', ', wp_list_pluck($terms, 'name'));
		}else{
			return $terms;
		}

	}

	public function acf(){
		if( !class_exists( 'ACF' ) ) return 'ACF is not installed.';

		$string = '';
		
		if( $this->is_term && get_field($this->second_param, $this->is_term ) ){
			$string = get_field($this->second_param, $this->is_term );
		}else if( get_field($this->second_param, $this->id ) ){
			$string = get_field($this->second_param, $this->id );
		}
			


		if( gettype($string) == 'string' ) return $string;

		return false;
	}

	public function price(){
		if( !function_exists('is_woocommerce') ) return false;

		$product = wc_get_product($this->id);

		if( !$product ) return 'Price unavailable';

		return $product->get_price_html();
	}

	public function user(){

		//echo '<br>'.$this->id.' x/x '.$this->second_param;

		if( !is_user_logged_in() ) return false;

		$current_user = wp_get_current_user();
		$user_info = get_userdata($current_user->ID);

		if( !empty($this->second_param) && $this->second_param == 'first_name' ){
			return $user_info->first_name;
		}else if( !empty($this->second_param) && $this->second_param == 'last_name' ){
			return $user_info->last_name;
		}

		return $user_info->display_name;

	}
}
