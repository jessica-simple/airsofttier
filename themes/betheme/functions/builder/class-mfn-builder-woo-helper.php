<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

class Mfn_Builder_Woo_Helper {

  public static function get_woo_cat_image($attr, $cat){
  	$output = '';
  	if(!isset($attr['image']) || $attr['image'] == 1){
  		$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
			if(isset($thumbnail_id) && !empty($thumbnail_id)){
				$output .= wp_get_attachment_image( $thumbnail_id, 'shop_catalog', false, array('class'=>'scale-with-grid' ) );
			}else{
				$output .= wc_placeholder_img();
			}
		}
		return $output;
  }

  public static function get_woo_cat_title($attr, $cat){
  	$output = '';
  	if(!isset($attr['title']) || $attr['title'] == 1){
  		if( !isset($attr['title_tag']) ) $attr['title_tag'] = 'h2';
			$output .= '<'.$attr['title_tag'].' class="woocommerce-loop-category__title">'.$cat->name;
			if(isset($attr['count']) && $attr['count'] == 1){ $output .= '<mark class="count">('.$cat->count.')</mark>'; }
			$output .= '</'.$attr['title_tag'].'>';
		}
		return $output;
  }

  public static function get_woo_product_title($product, $attr = false){

  	$output = '';

  	if( !empty($attr['layout_version']) && !empty($attr['layout_new']) && $attr['layout_new'] == 'list' ) $output .= '<div class="mfn-list-layout-desc">';

  	// remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
  	$output .= '<div class="mfn-li-product-row mfn-li-product-row-title">';
  	ob_start();
  	do_action('woocommerce_before_shop_loop_item_title');
  	$output .= ob_get_clean();
  	$output .= '<'.$attr['title_tag'].' class="title"><a href="'.get_permalink($product->get_id()).'">'.get_the_title($product->get_id()).'</a></'.$attr['title_tag'].'>';
  	if ( wc_review_ratings_enabled() ) {
			$output .= wc_get_rating_html( $product->get_average_rating() );
		}
  	$output .= '</div>';

		if( has_action('woocommerce_after_shop_loop_item_title') ){
			ob_start();
	  	echo '<div class="mfn-after-shop-loop-item-title">';
	  		do_action('woocommerce_after_shop_loop_item_title');
	  	echo '</div>';
	  	$output .= ob_get_clean();
		}

		return $output;
  }

  public static function sample_item($type){
		$post = false;
		$posts = get_posts( array('post_type' => $type, 'numberposts' => 1, 'orderby' => 'ID', 'order' => 'ASC') );

		if( isset($posts[0]) && count($posts) > 0 ){
			$post = $posts[0];
		}

		return $post;
	}

  public static function get_woo_product_image($product, $attr = false){

  	$wishlist_position = mfn_opts_get('shop-wishlist-position');

		$is_translatable = mfn_opts_get('translate');
  	$translate['translate-add-to-cart'] = $is_translatable ? mfn_opts_get('translate-add-to-cart', 'Add to cart') : __('Add to cart', 'woocommerce');
  	$translate['translate-view-product'] = $is_translatable ? mfn_opts_get('translate-view-product', 'View product') : __('View product', 'woocommerce');
  	$translate['translate-add-to-wishlist'] = $is_translatable ? mfn_opts_get('translate-add-to-wishlist', 'Add to wishlist') : __('Add to wishlist', 'betheme'); // ! betheme
  	$translate['translate-if-preview'] = $is_translatable ? mfn_opts_get('translate-if-preview', 'Preview') : __('Preview', 'woocommerce');

		// output -----

  	$output = '<div class="mfn-li-product-row mfn-li-product-row-image">';
  	$shop_images = mfn_opts_get( 'shop-images' );

  	if( 'plugin' == $shop_images ){

			$output .= '<a href="'. apply_filters( 'the_permalink', get_permalink($product->get_id()) ) .'" class="product-loop-thumb">';

				ob_start();

				add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
				do_action( 'woocommerce_before_shop_loop_item_title' );
				remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

				$output .= ob_get_clean();

			$output .= '</a>';

		} else {

			$output .= '<div class="image_frame scale-with-grid product-loop-thumb">';

				if( mfn_opts_get('shop-wishlist') && isset($wishlist_position[1]) ){
					$output .= '<span data-position="left" data-id="'.$product->get_id().'" class="mfn-wish-button mfn-abs-top"><svg width="26" viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-width:1.5px;}</style></defs><path class="path" d="M16.7,6a3.78,3.78,0,0,0-2.3.8A5.26,5.26,0,0,0,13,8.5a5,5,0,0,0-1.4-1.6A3.52,3.52,0,0,0,9.3,6a4.33,4.33,0,0,0-4.2,4.6c0,2.8,2.3,4.7,5.7,7.7.6.5,1.2,1.1,1.9,1.7H13a.37.37,0,0,0,.3-.1c.7-.6,1.3-1.2,1.9-1.7,3.4-2.9,5.7-4.8,5.7-7.7A4.3,4.3,0,0,0,16.7,6Z"></path></svg></span>';
				}

				ob_start();

				wc_get_template('single-product/sale-flash.php');
				do_action('mfn_product_image');

				$output .= ob_get_clean();

				// secondary image on hover

				$secondary_image_id = false;

				if( 'secondary' == $shop_images ){
					if( $attachment_ids = $product->get_gallery_image_ids() ) {
						if( isset( $attachment_ids['0'] ) ){
							$secondary_image_id = $attachment_ids['0'];
						}
					}
				}

				$gallery_ids = false;
				$imgs_slider = array();

				$image_wrapper_classes = array('image_wrapper');

				if( 'slider' == $shop_images ) {

					$gallery_ids = $product->get_gallery_image_ids();

					if( !empty($gallery_ids) ){
						$image_wrapper_classes[] = 'mfn-product-list-gallery-slider';
						if( !empty(mfn_opts_get('product-list-gallery-slider-pagination-style')) ) $image_wrapper_classes[] = 'mfn-product-list-gallery-slider-lines-pagination';

						if( !empty($product->get_image_id()) ) $imgs_slider[] = wp_get_attachment_url( $product->get_image_id(), 'shop_catalog' );

						foreach ( $gallery_ids as $g=>$image_id ) {
							$imgs_slider[] = wp_get_attachment_url( $image_id, 'shop_catalog');
							if( $g == 2 ) break;
						}


						//$slider_data = "data-images='".json_encode($imgs_slider)."'";

					}

				}else if( $secondary_image_id ) {
					$image_wrapper_classes[] = 'hover-secondary-image';
				}

				$output .= '<div data-offset="0" class="'.implode(' ', $image_wrapper_classes).'">';

					$output .= '<a href="'. apply_filters( 'the_permalink', get_permalink($product->get_id()) ) .'" aria-label="'. esc_attr($product->get_title()) .'" tabindex="-1">';

						$output .= '<div class="mask"></div>';

						if( 'slider' == $shop_images && !empty($gallery_ids) ) $output .= '<div class="mfn-product-list-gallery-slider-track">';

						$output .= '<div data-index="0" class="mfn-product-list-gallery-item mfn-slide-current mfn-slide-first">'.woocommerce_get_product_thumbnail('shop_catalog').'</div>';

						if( !empty($gallery_ids) ) {
							foreach ($gallery_ids as $i=>$img) {
								$output .= '<div data-index="'.($i+1).'" class="mfn-product-list-gallery-item">'.wp_get_attachment_image( $img, 'shop_catalog', '', $attr = array( 'loading' => 'lazy' ) ).'</div>';
									if( $i == 2 ) break;
							}
						}

						if( 'slider' == $shop_images && !empty($gallery_ids) ) $output .= '</div>';

						if( $secondary_image_id ) {
							$output .= wp_get_attachment_image( $secondary_image_id, 'shop_catalog', '', $attr = array( 'class' => 'image-secondary scale-with-grid' ) );
						}

					$output .= '</a>';


					if( 'slider' == $shop_images && !empty($gallery_ids) ) {

							$output .= '<a href="#" class="mfn-product-list-gallery-slider-arrow mfn-plgsn-prev" data-index="0"><i class="icon-left-open-big"></i></a>';

							$output .= '<div href="#" class="mfn-product-list-gallery-slider-pagination">';
								for ($i=0; $i < count($imgs_slider); $i++) {
									$output .= '<a '.( $i == 0 ? 'class="active mfn-plgsnp-dot"' : 'class="mfn-plgsnp-dot"' ).' href="#" data-index="'.$i.'"></a>';
								}
							$output .= '</div>';

							$output .= '<a href="#" class="mfn-product-list-gallery-slider-arrow mfn-plgsn-next" data-index="0"><i class="icon-right-open-big"></i></a>';
					}

					$output .= '<div class="image_links">';

						if( $product->is_in_stock() && (! mfn_opts_get('shop-catalogue')) && (! in_array($product->get_type(), array('external', 'grouped', 'variable'))) ){

							if( mfn_opts_get('image-frame-style') == 'modern-overlay' ){

								if( $product->supports( 'ajax_add_to_cart' ) ){
									$output .= '<a rel="nofollow" tabindex="-1" data-tooltip="'. esc_html($translate['translate-add-to-cart']) .'" data-position="left" aria-label="'. esc_html($translate['translate-add-to-cart']) .'" href="'. apply_filters('add_to_cart_url', esc_url($product->add_to_cart_url())) .'" data-quantity="1" data-product_id="'. esc_attr($product->get_id()) .'" class="add_to_cart_button ajax_add_to_cart product_type_simple tooltip tooltip-txt"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><polygon class="path" points="20.4 20.4 5.6 20.4 6.83 10.53 19.17 10.53 20.4 20.4"></polygon><path class="path" d="M9.3,10.53V9.3a3.7,3.7,0,1,1,7.4,0v1.23"></path></svg></a>';
								} else {
									$output .= '<a rel="nofollow" tabindex="-1" data-tooltip="'. esc_html($translate['translate-add-to-cart']) .'" data-position="left" aria-label="'. esc_html($translate['translate-add-to-cart']) .'" href="'. apply_filters('add_to_cart_url', esc_url($product->add_to_cart_url())) .'" data-quantity="1" data-product_id="'. esc_attr($product->get_id()) .'" class="add_to_cart_button product_type_simple tooltip tooltip-txt"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><polygon class="path" points="20.4 20.4 5.6 20.4 6.83 10.53 19.17 10.53 20.4 20.4"></polygon><path class="path" d="M9.3,10.53V9.3a3.7,3.7,0,1,1,7.4,0v1.23"></path></svg></a>';
								}

							}else{

								if( $product->supports( 'ajax_add_to_cart' ) ){
									$output .= '<a rel="nofollow" tabindex="-1" href="'. apply_filters('add_to_cart_url', esc_url($product->add_to_cart_url())) .'" data-quantity="1" data-product_id="'. esc_attr($product->get_id()) .'" class="add_to_cart_button ajax_add_to_cart product_type_simple"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><polygon class="path" points="20.4 20.4 5.6 20.4 6.83 10.53 19.17 10.53 20.4 20.4"></polygon><path class="path" d="M9.3,10.53V9.3a3.7,3.7,0,1,1,7.4,0v1.23"></path></svg></a>';
								} else {
									$output .= '<a rel="nofollow" tabindex="-1" href="'. apply_filters('add_to_cart_url', esc_url($product->add_to_cart_url())) .'" data-quantity="1" data-product_id="'. esc_attr($product->get_id()) .'" class="add_to_cart_button product_type_simple"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><polygon class="path" points="20.4 20.4 5.6 20.4 6.83 10.53 19.17 10.53 20.4 20.4"></polygon><path class="path" d="M9.3,10.53V9.3a3.7,3.7,0,1,1,7.4,0v1.23"></path></svg></a>';
								}

							}
						}

						if( mfn_opts_get('image-frame-style') == 'modern-overlay' ){
							$output .= '<a class="link tooltip tooltip-txt" data-tooltip="'. esc_html($translate['translate-view-product']) .'" data-position="left" aria-label="'. esc_html($translate['translate-view-product']) .'" href="'. apply_filters('the_permalink', get_permalink($product->get_id())) .'"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><g><path d="M10.17,8.76l2.12-2.12a5,5,0,0,1,7.07,0h0a5,5,0,0,1,0,7.07l-2.12,2.12" class="path"/><path d="M15.83,17.24l-2.12,2.12a5,5,0,0,1-7.07,0h0a5,5,0,0,1,0-7.07l2.12-2.12" class="path"/><line x1="10.17" y1="15.83" x2="15.83" y2="10.17" class="path"/></g></svg></a>';
						}else{
							$output .= '<a class="link" tabindex="-1" href="'. apply_filters('the_permalink', get_permalink($product->get_id())) .'"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><g><path d="M10.17,8.76l2.12-2.12a5,5,0,0,1,7.07,0h0a5,5,0,0,1,0,7.07l-2.12,2.12" class="path"/><path d="M15.83,17.24l-2.12,2.12a5,5,0,0,1-7.07,0h0a5,5,0,0,1,0-7.07l2.12-2.12" class="path"/><line x1="10.17" y1="15.83" x2="15.83" y2="10.17" class="path"/></g></svg></a>';
						}

						if( mfn_opts_get('shop-wishlist') && isset($wishlist_position[2]) ){

							if( mfn_opts_get('image-frame-style') == 'modern-overlay' ){
								$output .= '<a href="#" tabindex="-1" data-tooltip="'. $translate['translate-add-to-wishlist'] .'" data-position="left" aria-label="'. $translate['translate-add-to-wishlist'] .'" data-id="'.$product->get_id().'" class="mfn-wish-button tooltip tooltip-txt link"><svg width="26" viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-width:1.5px;}</style></defs><path class="path" d="M16.7,6a3.78,3.78,0,0,0-2.3.8A5.26,5.26,0,0,0,13,8.5a5,5,0,0,0-1.4-1.6A3.52,3.52,0,0,0,9.3,6a4.33,4.33,0,0,0-4.2,4.6c0,2.8,2.3,4.7,5.7,7.7.6.5,1.2,1.1,1.9,1.7H13a.37.37,0,0,0,.3-.1c.7-.6,1.3-1.2,1.9-1.7,3.4-2.9,5.7-4.8,5.7-7.7A4.3,4.3,0,0,0,16.7,6Z"></path></svg></a>';
							}else{
								$output .= '<a href="#" tabindex="-1" data-id="'.$product->get_id().'" class="mfn-wish-button link"><svg width="26" viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-width:1.5px;}</style></defs><path class="path" d="M16.7,6a3.78,3.78,0,0,0-2.3.8A5.26,5.26,0,0,0,13,8.5a5,5,0,0,0-1.4-1.6A3.52,3.52,0,0,0,9.3,6a4.33,4.33,0,0,0-4.2,4.6c0,2.8,2.3,4.7,5.7,7.7.6.5,1.2,1.1,1.9,1.7H13a.37.37,0,0,0,.3-.1c.7-.6,1.3-1.2,1.9-1.7,3.4-2.9,5.7-4.8,5.7-7.7A4.3,4.3,0,0,0,16.7,6Z"></path></svg></a>';
							}

						}

						if(mfn_opts_get('shop-quick-view') == 1){

							if( mfn_opts_get('image-frame-style') == 'modern-overlay' ){
								$output .= '<a href="#" tabindex="-1" data-tooltip="'. esc_html($translate['translate-if-preview']) .'" data-position="left" aria-label="'. esc_html($translate['translate-if-preview']) .'" data-id="'.$product->get_id().'" data-id="'.$product->get_id().'" class="mfn-quick-view tooltip tooltip-txt"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><line x1="7" y1="7" x2="11.29" y2="11.29" class="path"/><line x1="14.62" y1="14.62" x2="18.91" y2="18.91" class="path"/><polyline points="7 15.57 7 19 10.43 19" class="path"/><polyline points="15.57 19 19 19 19 15.57" class="path"/><polyline points="10.43 7 7 7 7 10.43" class="path"/><polyline points="19 10.43 19 7 15.57 7" class="path"/><line x1="14.71" y1="11.29" x2="19" y2="7" class="path"/><line x1="7" y1="19" x2="11.29" y2="14.71" class="path"/></svg></a>';
							}else{
								$output .= '<a href="#" tabindex="-1" data-id="'.$product->get_id().'" data-id="'.$product->get_id().'" class="mfn-quick-view"><svg viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><line x1="7" y1="7" x2="11.29" y2="11.29" class="path"/><line x1="14.62" y1="14.62" x2="18.91" y2="18.91" class="path"/><polyline points="7 15.57 7 19 10.43 19" class="path"/><polyline points="15.57 19 19 19 19 15.57" class="path"/><polyline points="10.43 7 7 7 7 10.43" class="path"/><polyline points="19 10.43 19 7 15.57 7" class="path"/><line x1="14.71" y1="11.29" x2="19" y2="7" class="path"/><line x1="7" y1="19" x2="11.29" y2="14.71" class="path"/></svg></a>';
							}

						}

					$output .= '</div>';

				$output .= '</div>';

				if( ! $product->is_in_stock() && $soldout = mfn_opts_get( 'shop-soldout' ) ){
					$output .= '<span class="soldout"><h4>'. $soldout .'</h4></span>';
				}

				$output .= '<a href="'. apply_filters( 'the_permalink', get_permalink($product->get_id()) ) .'" aria-label="'. esc_attr($product->get_title()) .'" tabindex="-1"><span class="product-loading-icon added-cart"></span></a>';

			$output .= '</div>';
		}

		$output .= '</div>';

		return $output;
  }

  public static function get_woo_product_price($product, $attr = false){

  	/*ob_start();
		mfn_display_custom_attributes($product->get_id());
		$output = ob_get_clean();*/

		$output = '';
		if( !empty($product->get_price_html()) ){
	  	$output .= '<div class="mfn-li-product-row mfn-li-product-row-price">';
	  	$output .= '<p class="price">'.$product->get_price_html().'</p>';
	  	$output .= '</div>';
	  }
		return $output;
  }

  public static function get_woo_product_description($product, $attr = false) {

		$output = '';

		if( $attr && !empty($attr['description']) && $attr['description'] == 'list' && $attr['tmp_layout'] != 'list' ) return $output;

  	if( get_the_excerpt($product->get_id()) && !empty($attr['description']) ) {
			$output .= '<div class="mfn-li-product-row mfn-li-product-row-description excerpt-'. ( !empty($attr['description']) ? $attr['description'] : 'unset') .'">';
				$output .= '<div class="excerpt">'. do_shortcode( get_the_excerpt($product->get_id()) ) .'</div>';
			$output .= '</div>';
		}

		return $output;
  }

  public static function get_woo_product_button($product, $attr = false) {

		$classes = '';
		$output = '';

		/*echo '<pre>';
		print_r($attr);
		echo '</pre>';*/

		if( $attr && isset($attr['button']) && $attr['button'] == '0' ) return $output;

		if( $attr && isset($attr['button']) && $attr['button'] === 'list' && $attr['tmp_layout'] !== 'list' ) {
			return $output;
		}

		if( mfn_opts_get('shop-catalogue') ) return;

		$product->is_purchasable() ? $classes .= 'add_to_cart_button' : null;
		$product->supports( 'ajax_add_to_cart' ) ? $classes .= ' ajax_add_to_cart' : null;

		$output .= '<div class="mfn-li-product-row mfn-li-product-row-button button-'. ( !empty($attr['button']) ? $attr['button'] : 'unset') .'">';

			$output .= apply_filters(
        'woocommerce_loop_add_to_cart_link',
        sprintf(
          '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button %s product_type_%s">%s</a>',
          esc_url(  $product->add_to_cart_url() ),
          esc_attr( $product->get_id() ),
          esc_attr( $product->get_sku() ),
          $classes,
          esc_attr( $product->get_type() ),
          esc_html( $product->add_to_cart_text() )
        ),
        $product
	    );

	    $wishlist = mfn_opts_get('shop-wishlist');
	    $wishlist_position = mfn_opts_get('shop-wishlist-position');

	    if( $wishlist && isset($wishlist_position[0]) && is_array($wishlist_position) && in_array(0, $wishlist_position)){
				$output .= '<a href="#" data-id="'.$product->get_id().'" class="mfn-wish-button"><svg width="26" viewBox="0 0 26 26"><defs><style>.path{fill:none;stroke:#333;stroke-width:1.5px;}</style></defs><path class="path" d="M16.7,6a3.78,3.78,0,0,0-2.3.8A5.26,5.26,0,0,0,13,8.5a5,5,0,0,0-1.4-1.6A3.52,3.52,0,0,0,9.3,6a4.33,4.33,0,0,0-4.2,4.6c0,2.8,2.3,4.7,5.7,7.7.6.5,1.2,1.1,1.9,1.7H13a.37.37,0,0,0,.3-.1c.7-.6,1.3-1.2,1.9-1.7,3.4-2.9,5.7-4.8,5.7-7.7A4.3,4.3,0,0,0,16.7,6Z"></path></svg></a>';
			}

		$output .= '</div>';

		return $output;
  }

  public static function sample_products_loop($attr) {
  	$sl_arr = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => !empty($attr['products']) ? $attr['products'] : 8,
  	);
  	if( get_option('woocommerce_hide_out_of_stock_items') && get_option('woocommerce_hide_out_of_stock_items') == 'yes' ) {
  		$sl_arr['meta_query'] = array(
				array(
					'key' => '_stock_status',
					'value' => 'instock'
				),
			);
  	}

  	if( !empty($attr['ordering']) ){
  		switch ($attr['ordering']) {
  			case 'price':
  				$sl_arr['meta_key'] = '_price';
  				$sl_arr['orderby'] = 'meta_value_num';
  				$sl_arr['order'] = 'ASC';
  				break;

  			case 'price-desc':
  				$sl_arr['meta_key'] = '_price';
  				$sl_arr['orderby'] = 'meta_value_num';
  				$sl_arr['order'] = 'DESC';
  				break;

  			case 'date':
  				$sl_arr['orderby'] = 'post_date';
  				$sl_arr['order'] = 'DESC';
  				break;

  			case 'popularity':
  				$sl_arr['meta_key'] = 'total_sales';
					$sl_arr['orderby'] = 'meta_value_num';
					$sl_arr['order'] = 'DESC';
  				break;

  			case 'rating':
  				$sl_arr['meta_key'] = '_wc_average_rating';
					$sl_arr['orderby'] = 'meta_value_num';
					$sl_arr['order'] = 'DESC';
  				break;

  			default:
  				$sl_arr['orderby'] = 'menu_order';
  				$sl_arr['order'] = 'ASC';
  				break;
  		}
  	}

  	$sample_loop = new WP_Query( $sl_arr );
  	return $sample_loop;
  }

  public static function productslist($product, $attr, $classes) {
  	$order = str_replace(' ', '', $attr['order']);
		$order_arr = explode(',', $order);

		// if ( empty( $product ) || ! $product->is_visible() )  return;

		$output = '<li class="mfn-product-li-item '.implode(' ', wc_get_product_class( $classes, $product )).'">';

		ob_start();
  	echo '<div class="mfn-before-shop-loop-item">';
  	do_action('woocommerce_before_shop_loop_item');
  	echo '</div>';
  	$output .= ob_get_clean();

  	if( ( !empty($attr['layout_version']) && !empty($attr['layout_new']) && $attr['layout_new'] == 'list_2' ) || ( empty($attr['layout_version']) && !empty($attr['layout']) && $attr['layout'] == 'list_2' ) ) {
  		$output .= self::displayList2Layout($product, $attr);
  	}else{
			if( isset($order_arr) && is_iterable($order_arr) ) {
				foreach( $order_arr as $el ) {
					if( ! isset( $attr[$el] ) || ( isset($attr[$el] ) && $attr[$el] ) ) {
						$fun_name = 'get_woo_product_'.$el;
						if( method_exists('Mfn_Builder_Woo_Helper', $fun_name) ){
							$output .= self::$fun_name($product, $attr);
						}
					}
				}
			}
		}

		ob_start();
  	echo '<div class="mfn-after-shop-loop-item">';
  	do_action('woocommerce_after_shop_loop_item');
  	echo '</div>';

  	$output .= ob_get_clean();

  	if( !empty($attr['layout_version']) && !empty($attr['layout_new']) && $attr['layout_new'] == 'list' ) $output .= '</div>';
		$output .= '</li>';

		return $output;
  }

  public static function displayList2Layout($product, $attr) {
  	$output = '<div class="mfn-list-layout-wrapper">';

  	$output .= '<div class="mfn-list-layout-inner-left">';

  	// image
  	$output .= self::get_woo_product_image($product, $attr);

  	$output .= '<div class="mfn-list-layout-desc">';

  		$output .= self::get_woo_product_title($product, $attr);
  		$output .= self::get_woo_product_brand($product, $attr);

  		if( has_excerpt( $product->get_id() ) ) $output .= self::get_woo_product_description($product, $attr);

  		$output .= self::get_woo_product_attributes($product, $attr);

  	$output .= '</div>';

  	$output .= '</div>';

  	$output .= '<div class="mfn-list-layout-inner-right">';

  	$output .= '<div class="mfn-list-layout-inner-right-button-wrapper">';

  		if ( wc_review_ratings_enabled() ) {
				$output .= wc_get_rating_html( $product->get_average_rating() );
			}

			$output .= self::get_woo_product_price($product, $attr);
			$output .= self::get_woo_product_button($product, $attr);

		$output .= '</div>';

			$output .= self::get_woo_product_additional_info($product, $attr);

  	$output .= '</div>';

  	$output .= '</div>';

  	return $output;

  }

  public static function get_woo_product_brand($product, $attr = false) {

  	$terms = get_the_terms( $product->get_id(), 'product_brand' );
  	$output = '';

  	if( $terms && ! is_wp_error( $terms ) ) {
	  	$classes = array('mfn-li-product-row', 'mfn-li-product-row-brands');
			$output .= '<div class="'.implode(' ', $classes).'">';

					$output .= '<span class="mfn-brand-label">'.__( 'Brand', 'woocommerce' ).': </span>';

					foreach ( $terms as $term ) {
						$term_link = get_term_link( $term->slug, 'product_brand' );
						if( ! is_wp_error( $term_link ) ){
								$output .= '<a class="mfn-brand-name" href="' . esc_attr( $term_link ) . '">';

								if( get_term_meta( $term->term_id, 'thumbnail_id', true ) ) {
									$output .= wp_get_attachment_image( get_term_meta( $term->term_id, 'thumbnail_id', true ), 'full' );
								}else{
									$output .= __( $term->name );
								}

								$output .= '</a>';
						}
					}

			$output .= '</div>';
		}

		return $output;

  }

  public static function get_woo_product_attributes($product, $attr = false) {
  	$output = '';

  	$attributes = $product ? $product->get_attributes() : [];

  	$output .= '<ul class="mfn-li-product-row mfn-li-product-row-attributes">';

	  	foreach ( $attributes as $attr_key => $attr ) {
				/** @var WC_Product_Attribute $attr */

				$name      = $attr->get_name();      // e.g. 'pa_color' or 'Material'
				$is_tax    = $attr->is_taxonomy();   // true for global attributes
				$is_var    = $attr->get_variation(); // used for variations?
				$is_vis    = $attr->get_visible();   // shown on product page?

				if ( $is_tax ) {
					// Global attribute (taxonomy): get term names
					$terms  = wc_get_product_terms( $product->get_id(), $name, [ 'fields' => 'names' ] );
					$values = $terms; // array of strings

					$output .= '<li>'.wc_attribute_label($name, $product).': <strong>'. implode(', ', $values) .'</strong></li>';
				}

			}

		$output .= '</ul>';

  	return $output;
  }

  public static function get_woo_product_additional_info($product, $attr = false) {
  	$output = '';

  	$sku = $product->get_sku();
		$gtin = get_post_meta($product->get_id(), '_global_unique_id', true);
		$stock_status = $product->get_stock_status();

		switch ( $stock_status ) {
			case 'instock':
				$label = __( 'In stock', 'woocommerce' );
				break;

			case 'onbackorder':
				$label = __( 'On backorder', 'woocommerce' );
				break;

			default: // outofstock
				$label = __( 'Out of stock', 'woocommerce' );
				break;
		}

  	$output .= '<ul class="mfn-li-product-row mfn-li-product-row-additional-info">';

  		if( !empty($sku) ) $output .= '<li class="mfn-li-product-row-additional-info-sku">'.__( 'SKU:', 'woocommerce' ).' <span class="sku_wrapper">'.$sku.'</span></li>';
  		if( !empty($gtin) ) $output .= '<li class="mfn-li-product-row-additional-info-gtin">'.__( 'GTIN', 'woocommerce' ).': <span class="sku_wrapper">'.$gtin.'</span></li>';

  		if( !empty($attr['shipping_cheapest']) && !$product->is_virtual() ) $output .= '<li class="mfn-li-product-row-additional-info-shipping">'.__( 'Shipping', 'woocommerce' ).' '.__( 'From:', 'woocommerce' ).' '. $attr['shipping_cheapest'] .'</li>';

  		$output .= sprintf(
				'<li class="mfn-li-product-row-additional-info-stock %s">%s: <span class="stock-label">%s</span></li>',
				esc_attr( $stock_status ),
				esc_html__( 'Availability', 'woocommerce' ),
				esc_html( $label )
			);


  	$output .= '</ul>';

  	return $output;
  }

  public static function mfn_build_product_query(array $base_args, array $attr): WP_Query {
    $ordering_args = self::mfn_get_ordering_query_args($attr);
    $args = array_merge($base_args, $ordering_args);
    return new WP_Query($args);
	}



  public static function mfn_resolve_products_loop(array $attr): array {
    $display = $attr['display'] ?? '';

    $ordering = $attr['ordering'] ?? '';
    $paged = (get_query_var('paged')) ? get_query_var('paged') : ((get_query_var('page')) ? get_query_var('page') : 1);

    /*echo '<pre>';
    print_r($attr);
    echo '</pre>';*/

    if ( $display === 'related' ) {

		    $limit   = isset( $attr['products'] ) ? absint( $attr['products'] ) : 4;
		    $post_id = 0;

		    if ( ! empty( $attr['vb_postid'] ) ) {
		        $post_id = absint( $attr['vb_postid'] );
		    } elseif ( is_product() ) {
		        $post_id = get_the_ID();
		    }

		    if ( ! $post_id ) {
		        return [ 'type' => 'none' ];
		    }

		    $product = wc_get_product( $post_id );
		    if ( ! $product ) {
		        return [ 'type' => 'none' ];
		    }

		    $related_ids = wc_get_related_products( $post_id, $limit, $product->get_upsell_ids() );

		    if ( empty( $related_ids ) ) {
		        return [ 'type' => 'none' ];
		    }

		    $args = [
		        'post_type'      => 'product',
		        'post_status'    => 'publish',
		        'posts_per_page' => $limit,
		        'post__in'       => $related_ids,
		        'orderby'        => 'post__in',
		    ];

		    $q = self::mfn_build_product_query( $args, $attr );

		    return [ 'type' => 'custom', 'query' => $q ];

			}

			if ( $display === 'upsells' ) {

		    $limit   = isset( $attr['products'] ) ? absint( $attr['products'] ) : 4;
		    $post_id = 0;

		    if ( ! empty( $attr['vb_postid'] ) ) {
		        $post_id = absint( $attr['vb_postid'] );
		    } elseif ( is_product() ) {
		        $post_id = get_the_ID();
		    }

		    if ( ! $post_id ) {
		        return [ 'type' => 'none' ];
		    }

		    $product = wc_get_product( $post_id );
		    if ( ! $product ) {
		        return [ 'type' => 'none' ];
		    }

		    $upsell_ids = array_slice(
		        array_filter( array_map( 'absint', $product->get_upsell_ids() ) ),
		        0,
		        $limit
		    );

		    if ( empty( $upsell_ids ) ) {
		        return [ 'type' => 'none' ];
		    }

		    $args = [
		        'post_type'      => 'product',
		        'post_status'    => 'publish',
		        'posts_per_page' => $limit,
		        'post__in'       => $upsell_ids,
		        'orderby'        => 'post__in', // keep WooCommerce admin order
		    ];

		    $q = self::mfn_build_product_query( $args, $attr );

		    return [ 'type' => 'custom', 'query' => $q ];

			}


	    if (!empty($attr['category']) && is_array($attr['category']) ) {

	    	$cat_ids = array_map(fn($o) => $o['key'], $attr['category']);
	    	if( empty($cat_ids) ) return ['type' => 'none'];

	    	$argss = [
			        'post_type'           => 'product',
			        'post_status'         => 'publish',
			        'posts_per_page'      => $attr['products'] ?? get_option('posts_per_page'),
			        'ignore_sticky_posts' => true,
			        'paged'               => $paged,
			        'tax_query'           => [[
			            'taxonomy' => 'product_cat',
			            'field'    => 'term_id',
			            'terms'    => $cat_ids,
      						'operator' => 'IN',
			        ]],
			    ];

	    	if ($display === 'onsale' && function_exists('wc_get_product_ids_on_sale')) {
	    		$ids = wc_get_product_ids_on_sale();
			    if (empty($ids)) $ids = [0];
			    $argss['post__in'] = $ids;
	    	}

			    $q = self::mfn_build_product_query($argss, $attr);

			    return ['type' => 'custom', 'query' => $q];
			}

			if ($display === 'onsale') {
			    if (!function_exists('wc_get_product_ids_on_sale')) {
			        return ['type' => 'none'];
			    }

			    $ids = wc_get_product_ids_on_sale();
			    if (empty($ids)) $ids = [0];

			    $argss = [
			        'post_type'           => 'product',
			        'post_status'         => 'publish',
			        'posts_per_page'      => $attr['products'] ?? get_option('posts_per_page'),
			        'ignore_sticky_posts' => true,
			        'paged'               => $paged,
			        'post__in'            => $ids,
			    ];

			    $q = self::mfn_build_product_query($argss, $attr);

			    return ['type' => 'custom', 'query' => $q];
			}

	    $is_wc_archive = (!is_singular() && function_exists('is_woocommerce') && is_woocommerce() );

	    if ($is_wc_archive && empty($display)) {
	        return ['type' => 'main_archive'];
	    }


	    $is_wishlist = (is_page() && mfn_opts_get('shop-wishlist-page') && mfn_opts_get('shop-wishlist-page') == get_the_ID());

	    if ($is_wishlist && empty($display)) {
			    $wish_arr = [0];
			    if (isset($_COOKIE['mfn_wishlist'])) {
			        $wish_arr = array_filter(array_map('absint', explode(',', $_COOKIE['mfn_wishlist'])));
			        if (empty($wish_arr)) $wish_arr = [0];
			    }

			    $argss = [
			        'post_type'      => 'product',
			        'post_status'    => 'publish',
			        'posts_per_page' => $attr['products'] ?? get_option('posts_per_page'),
			        'paged'          => $paged,
			        'post__in'       => $wish_arr,
			    ];

			    $q = self::mfn_build_product_query($argss, $attr);

			    return ['type' => 'custom', 'query' => $q];
			}

    	$q = self::sample_products_loop($attr);
    	if($q->have_posts()) return ['type' => 'custom', 'query' => $q];

	    return ['type' => 'none'];
	}

	public static function mfn_get_ordering_query_args(array $attr): array {

	    $orderby = '';
	    if (!empty($_GET['orderby'])) {
	        $orderby = wc_clean(wp_unslash($_GET['orderby']));
	    } else {
	        $orderby = $attr['ordering'] ?? '';
	    }

	    // Woo default if empty
	    if ($orderby === '' || $orderby === 'menu_order') {
	        return [
	            'orderby' => 'menu_order title',
	            'order'   => 'ASC',
	        ];
	    }

	    switch ($orderby) {

	        case 'date':
	            return [
	                'orderby' => 'date',
	                'order'   => 'DESC',
	            ];

	        case 'price':
	            return [
	                'meta_key' => '_price',
	                'orderby'  => 'meta_value_num',
	                'order'    => 'ASC',
	            ];

	        case 'price-desc':
	            return [
	                'meta_key' => '_price',
	                'orderby'  => 'meta_value_num',
	                'order'    => 'DESC',
	            ];

	        case 'rating':
	            return [
	                'meta_key' => '_wc_average_rating',
	                'orderby'  => 'meta_value_num',
	                'order'    => 'DESC',
	            ];

	        case 'popularity':
	            return [
	                'meta_key' => 'total_sales',
	                'orderby'  => 'meta_value_num',
	                'order'    => 'DESC',
	            ];

	        case 'title':
	            return [
	                'orderby' => 'title',
	                'order'   => 'ASC',
	            ];

	        case 'title-desc':
	            return [
	                'orderby' => 'title',
	                'order'   => 'DESC',
	            ];

	        case 'rand':
	            return [
	                'orderby' => 'rand',
	            ];

	        default:
	            return [
	                'orderby' => 'date',
	                'order'   => 'DESC',
	            ];
	    }
	}



  public static function getDiscount($product) {
  	$percent = 0;
  	if( $product->is_type('variable') ){
  		$percentages = array();
	    $prices = $product->get_variation_prices();
	    foreach( $prices['price'] as $key => $price ){
	      if( $prices['regular_price'][$key] !== $price ){
	        $percentages[] = round(100 - ($prices['sale_price'][$key] / $prices['regular_price'][$key] * 100));
	      }
	    }
	    $percent = round(max($percentages));
  	}elseif($product->get_regular_price() && $product->get_sale_price()){
			$percent = round( (1 - ($product->get_sale_price() / $product->get_regular_price()))*100);
  	}
  	return $percent.'%';
  }

}
