<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

$widgetsClass =  new Mfn_Builder_Fields();
$widgets = $widgetsClass->get_items();

if ( !defined( 'ICL_SITEPRESS_VERSION' ) ) unset($widgets['header_language_switcher']);
if ( !defined( 'WCML_VERSION' ) ) unset($widgets['header_currency_switcher']);

$favs = json_decode( get_option( 'mfn_fav_items_'.get_current_user_id() ) );

$grouped_widgets_labels = array(
	'header' => __('Header template', 'mfn-opts'),
	'footer' => __('Footer template', 'mfn-opts'),
	'search' => __('Search template', 'mfn-opts'),
	'popup' => __('Popup', 'mfn-opts'),
	'sidemenu' => __('Sidemenu', 'mfn-opts'),
	'megamenu' => __('Mega menu', 'mfn-opts'),
	'single-post' => __('Blog post template', 'mfn-opts'),
	'archive-post' => __('Blog template', 'mfn-opts'),
	'single-portfolio' => __('Portfolio post template', 'mfn-opts'),
	'archive-portfolio' => __('Portfolio template', 'mfn-opts'),
	'single-product' => __('Product template', 'mfn-opts'),
	'archive-product' => __('Shop template', 'mfn-opts'),
	'thanks' => __('Thank you template', 'mfn-opts'),
	'cart' => __('Cart template', 'mfn-opts'),
	'checkout' => __('Checkout template', 'mfn-opts')
); 

$grouped_widgets = array(
	'header' => array('header_logo', 'header_menu', 'header_burger', 'header_icon', 'header_search', 'header_promo_bar', 'column', 'button', 'heading', 'html', 'payment_methods', 'image', 'plain_text', 'header_language_switcher', 'marquee_text', 'header_currency_switcher'),
	'footer' => array('footer_menu', 'footer_logo'),
	'popup' => array('popup_exit'),
	'megamenu' => array('megamenu_menu'),
	'search' => array('search_results'),
	'sidemenu' => array('sidemenu_menu', 'header_logo', 'header_icon', 'header_search', 'header_promo_bar', 'column', 'button', 'heading', 'html', 'payment_methods', 'image', 'plain_text', 'header_language_switcher', 'marquee_text', 'header_currency_switcher'),
	'single-post' => array('post_heading', 'post_image', 'post_author', 'post_content', 'post_comments', 'post_date', 'post_excerpt', 'post_love', 'post_blog_related', 'post_blog_categories', 'post_blog_tags'),
	'archive-post' => array('archive_heading', 'archive_image', 'archive_read_more', 'archive_content', 'archive_blog_categories', 'filters', 'active_filters'),
	'single-portfolio' => array('post_heading', 'post_image', 'post_author', 'post_content', 'post_comments', 'post_date', 'post_excerpt', 'post_love', 'post_portfolio_related', 'post_portfolio_categories'),
	'archive-portfolio' => array('archive_heading', 'archive_image', 'archive_read_more', 'archive_content', 'archive_portfolio_categories', 'filters', 'active_filters'),

	'archive-post-query-loop' => array('post_heading', 'post_image', 'post_excerpt', 'post_author', 'post_date', 'post_love', 'post_blog_categories', 'post_blog_tags'),
	'archive-portfolio-query-loop' => array('post_heading', 'post_image', 'post_excerpt', 'post_author', 'post_date', 'post_love', 'post_portfolio_categories'),
	/*'search-query-loop' => array('heading_title', 'image_featured_image', 'button_read_more', 'plain_text_excerpt')*/
);

if( function_exists('is_woocommerce') ) {
	$grouped_widgets['single-product'] = array('product_title', 'product_images', 'product_price', 'product_cart_button', 'product_breadcrumbs', 'product_brands', 'product_reviews', 'product_stock', 'product_meta', 'product_rating', 'product_short_description', 'product_tabs', 'product_content', 'product_additional_information', 'shop_products_related', 'shop_products_upsells', 'woo_alert');

	$grouped_widgets['archive-product'] = array('shop_products', 'shop_title', 'shop_cat_desc', 'shop_cat_top_desc', 'shop_cat_bottom_desc', 'woo_alert', 'filters', 'active_filters');

	$grouped_widgets['thanks'] = array('thankyou_overview', 'thankyou_order', 'order_steps');
	$grouped_widgets['cart'] = array('cart_table', 'cart_totals', 'cart_cross_sells', 'order_steps');
	$grouped_widgets['checkout'] = array('checkout', 'order_steps');

	$grouped_widgets['archive-product-query-loop'] = array('heading_title', 'image_featured_image', 'button_read_more', 'heading_price', 'plain_text_excerpt', 'product_rating');
}

echo '<div class="panel panel-items" id="mfn-widgets-list">
    <div class="panel-search mfn-form">
        <input class="mfn-form-control mfn-form-input search mfn-search" type="search" autocomplete="off" placeholder="Search">
    </div>';

    if( !isset($this->template_type) || $this->template_type != 'header' ) {
    	/* Favourites*/
	    echo '<div class="mfn-widgets-group mfn-widgets-group-favourites '.($favs && count($favs) > 0 ? 'mfn-not-empty-widgets-group' : 'mfn-empty-widgets-group').'">';
		 	echo '<h5 class="mfn-widgets-group-title">'.esc_html__('Favourite elements', 'mfn-opts').'</h5>';

		 	echo '<div class="mfn-widgets-group-content mfn-fav-items-content">';
		 	echo '<ul class="items-list mfn-widgets-group-list fav-items-list list">';
		 	if( $favs && count($favs) > 0 ) {
		 		foreach( $favs as $fav ) {
		 			if( $widgets[$fav]['cat'] !== 'header' )
		 				echo '<li class="mfn-item-'.$fav.' category-'.$widgets[$fav]['cat'].'" data-tags="'.strtolower(strip_tags($widgets[$fav]['title'])).' '.$fav.' '.( $widgets[$fav]['tags'] ?? '' ).'" data-title="'.strip_tags($widgets[$fav]['title']).'" data-type="'.$widgets[$fav]['type'].'" data-alias="'.$fav.'"><a href="#"><div class="mfn-icon card-icon"></div><span class="title">'.$widgets[$fav]['title'].'</span></a></li>';
		 		}
		 	}
		 	echo '</ul>';
		 	echo '<span class="mfn-empty-widgets-group-info">Collect favourite elements in one place by<br /> <span class="mfn-icon mfn-icon-right-click"></span> &gt; <i>Add to favourites'. ( ! is_admin() ? ' (Unavailable in Demo)' : '' ) .'</i></span>';
		 echo '</div></div>';
	}

	if( !empty($this->template_type) && !empty($grouped_widgets[$this->template_type]) ) {
		echo '<div class="mfn-widgets-group mfn-widgets-group-template mfn-not-empty-widgets-group">';
		 	echo '<h5 class="mfn-widgets-group-title">'.( !empty($grouped_widgets_labels[$this->template_type]) ? $grouped_widgets_labels[$this->template_type] : get_the_title($this->post_id) ).'</h5>';

		 	echo '<div class="mfn-widgets-group-content">';
		 	echo '<ul class="items-list mfn-widgets-group-list list">';

		 	foreach( $grouped_widgets[$this->template_type] as $item ) {
		 		if( !isset($widgets[$item]['title']) ) continue;
		 		echo '<li class="mfn-item-'.$item.' category-'.$widgets[$item]['cat'].'" data-tags="'.strtolower(strip_tags($widgets[$item]['title'])).' '.$item.' '.( $widgets[$item]['tags'] ?? '' ).'" data-title="'.strip_tags($widgets[$item]['title']).'" data-type="'.$widgets[$item]['type'].'" data-alias="'.$item.'"><a href="#"><div class="mfn-icon card-icon"></div><span class="title">'.$widgets[$item]['title'].'</span></a></li>';
		 	}

		 	echo '</ul>';
		 echo '</div></div>';
	}

	if( !empty($this->template_type) && !empty($grouped_widgets[$this->template_type."-query-loop"]) ) {
		echo '<div class="mfn-widgets-group mfn-widgets-group-ql mfn-not-empty-widgets-group">';
		 	echo '<h5 class="mfn-widgets-group-title">'.esc_html__('Query loop', 'mfn-opts').'</h5>';
		 	echo '<div class="mfn-widgets-group-content">';
		 	echo '<p class="mfn-widgets-group-desc">Design any type of slider, blog, portfolio or shop listing with no limits. <a href="https://muffingroup.com/betheme/loop-builder/" target="_blank">Learn more</a></p>';
		 	echo '<ul class="items-list mfn-widgets-group-list list">';

		 	foreach( $grouped_widgets[$this->template_type."-query-loop"] as $item ) {
		 		if( !isset($widgets[$item]['title']) ) continue;
		 		echo '<li class="mfn-item-'.$item.' category-'.$widgets[$item]['cat'].'" data-tags="'.strtolower(strip_tags($widgets[$item]['title'])).' '.$item.' '.( $widgets[$item]['tags'] ?? '' ).'" data-title="'.$widgets[$item]['title'].'" data-type="'.$widgets[$item]['type'].'" data-alias="'.$item.'"><a href="#"><div class="mfn-icon card-icon"></div><span class="title">'.$widgets[$item]['title'].'</span></a></li>';
		 	}

		 	echo '</ul>';
		 	
		 echo '</div></div>';
	}

	if( empty($this->template_type) || (!empty($this->template_type) && $this->template_type != 'header') ) { // header has own few widgets
		echo '<div class="mfn-widgets-group mfn-widgets-group-global-list mfn-not-empty-widgets-group">';
		echo '<h5 class="mfn-widgets-group-title">'.esc_html__('Global elements', 'mfn-opts').'</h5>';
		echo '<div class="mfn-widgets-group-content">';
	    echo '<ul class="items-list global-list list">';

	    foreach($widgets as $w=>$widget) {

	    	if( !function_exists('is_woocommerce') && (in_array($widget['cat'], array('woocommerce', 'shop-archive', 'single-product', 'archive-product')) || in_array($w, array('product_rating', 'shop_slider', 'shop', 'shop_categories', 'shop_products'))) ) continue; // woo items without woocommerce

	    	if( in_array($w, array('shop', 'product_related', 'product_upsells')) ) continue; // deprecated

	    	if( !empty($this->template_type) && !empty($grouped_widgets[$this->template_type]) && in_array($w, $grouped_widgets[$this->template_type]) ) continue; // exclude grouped widgets from global list

	    	if( in_array($widget['cat'], array('shop-archive', 'archive-product', 'single-product', 'header', 'megamenu', 'footer', 'popup', 'sidemenu', 'single-post', 'single-blog-post', 'single-portfolio-post', 'archive', 'archive-post', 'blog', 'portfolio', 'archive-portfolio', 'cart', 'checkout', 'thanks', 'order-shared', 'woocommerce', 'custom_query_loop')) ) continue;  // exclude template's widgets from page list

	    	if( !empty($this->template_type) && $this->template_type == 'megamenu' && in_array($w, array('active_filters','filters','product_rating', 'table_of_contents')) ) continue; // exclude some widgets from megamenu template

	    	if( !empty($this->template_type) && $this->template_type == 'popup' && in_array($w, array('sidebar_widget', 'slider_plugin', 'table_of_contents', 'slider' )) ) continue; // exclude some widgets from popup template

	    	if( !empty($this->template_type) && $this->template_type == 'sidemenu' && in_array($w, array('sidebar_widget', 'slider_plugin', 'table_of_contents', 'slider', 'content')) ) continue; // exclude some widgets from sidemenu template

		    echo '<li class="mfn-item-'.$w.' category-'.$widget['cat'].'" data-tags="'.strtolower(strip_tags($widget['title'])).' '.$w.' '.( $widget['tags'] ?? '' ).'" data-title="'.strip_tags($widget['title']).'" data-alias="'.$w.'" data-type="'.$widget['type'].'"><a href="#"><div class="mfn-icon card-icon"></div><span class="title">'.$widget['title'].'</span></a></li>';

		}

		echo '</ul>';
		echo '</div>';
		echo '</div>';
	}
echo '</div>';
?>
