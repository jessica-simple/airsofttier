<?php

// 1. by conditions (cat, tags)
// 2. from theme options by default

wp_enqueue_script('mfn-waypoints', get_theme_file_uri('/js/plugins/waypoints.min.js'), ['jquery'], MFN_THEME_VERSION, true);

if ( mfn_opts_get('shop-layout') === 'masonry' ) {
	wp_enqueue_script('mfn-isotope', get_theme_file_uri('/js/plugins/isotope.min.js'), ['jquery'], MFN_THEME_VERSION, true);
}

global $mfn_global;

if( !empty($mfn_global['archive_template']) && is_numeric( $mfn_global['archive_template'] ) && get_post_type( $mfn_global['archive_template'] ) == 'template' ){

	if( get_post_meta( $mfn_global['archive_template'], 'mfn-post-hide-content', true ) ){
		remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description' );
		remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description' );
	}

	$mfn_builder = new Mfn_Builder_Front($mfn_global['archive_template']);
	$mfn_builder->show();

} else {

	echo '<section class="section">';
		echo '<div class="section_wrapper clearfix default-woo-list">';
			woocommerce_content();
		echo '</div>';
	echo '</section>';

}


?>
