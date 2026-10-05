<?php
/**
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

defined( 'ABSPATH' ) || exit;

$custom_layout = mfn_layout_ID();
$layout_header_height = get_post_meta( $custom_layout, 'mfn-post-header-height', true );
$css_variables = !empty(get_option('be_css_variables')) ? json_decode(get_option('be_css_variables'), true) : array();
?>
html{
	background-color: <?php echo esc_attr(mfn_opts_get('background-html', '#FCFCFC')); ?>;
}

<?php

if( empty($_GET['visual']) && !empty($css_variables)) {
echo 'body{';
  	foreach($css_variables as $cv){
  		echo $cv['title'].':'.$cv['value'].';'."\r\n";
  	}
  echo '}';
}
?>

#Wrapper,#Content,
.mfn-popup .mfn-popup-content,.mfn-off-canvas-sidebar .mfn-off-canvas-content-wrapper,.mfn-cart-holder,.mfn-header-login,
#Top_bar .search_wrapper, #Top_bar .top_bar_right .mfn-live-search-box,
.column_livesearch .mfn-live-search-wrapper, .column_livesearch .mfn-live-search-box{
	background-color: <?php echo esc_attr(mfn_opts_get('background-body', '#FCFCFC')); ?>;
}
.layout-boxed.mfn-bebuilder-header.mfn-ui #Wrapper .mfn-only-sample-content{ background-color: <?php echo esc_attr(mfn_opts_get('background-body', '#FCFCFC')); ?>; }

<?php if ( $custom_layout && ( '' !== $layout_header_height ) ) : ?>
	body:not(.template-slider) #Header{
		min-height: <?php echo esc_attr( $layout_header_height .'px' ); ?>;
	}
	body.header-below:not(.template-slider) #Header{
		padding-top: <?php echo esc_attr( $layout_header_height .'px' ); ?>;
	}
<?php else: ?>
	body:not(.template-slider) #Header{
		min-height: <?php echo esc_attr( mfn_opts_get( 'header-height', 250, [ 'unit' => 'px' ] ) ); ?>;
	}
	body.header-below:not(.template-slider) #Header{
		padding-top: <?php echo esc_attr( mfn_opts_get( 'header-height', 250, [ 'unit' => 'px' ] ) ); ?>;
	}
<?php endif; ?>

<?php if ( mfn_opts_get( 'section-padding' ) ) : ?>
	@media only screen and (min-width:768px){
		.section_wrapper {
			padding-left: <?php echo esc_attr( mfn_opts_get( 'section-padding', 0, [ 'unit' => 'px' ] ) ); ?>;
			padding-right: <?php echo esc_attr( mfn_opts_get( 'section-padding', 0, [ 'unit' => 'px' ] ) ); ?>;
		}
	}
<?php endif; ?>

<?php if ( mfn_opts_get( 'subheader-padding' ) ) : ?>
	#Subheader {
		padding: <?php echo esc_attr( mfn_opts_get( 'subheader-padding' ) ); ?>;
	}
<?php endif; ?>

<?php if ( mfn_opts_get( 'footer-padding' ) ) : ?>
	#Footer .widgets_wrapper {
		padding: <?php echo esc_attr( mfn_opts_get( 'footer-padding' ) ); ?>;
	}
<?php endif; ?>

<?php if ( mfn_opts_get( 'search-overlay-color' ) ) : ?>
	.has-search-overlay.search-overlay-opened #search-overlay {
		background-color: <?php echo esc_attr(mfn_opts_get('search-overlay-color')) ?>;
	}
<?php endif; ?>
<?php if ( mfn_opts_get( 'search-overlay-blur' ) ) : ?>
	.has-search-blur.search-overlay-opened #Wrapper::after {
		backdrop-filter: blur(<?php echo esc_attr(mfn_opts_get('search-overlay-blur')) ?>px);
	}
<?php endif; ?>
<?php if ( ! mfn_opts_get( 'elementor-container-content' ) ) : ?>
	.elementor-page.elementor-default #Content .the_content .section_wrapper{max-width: 100%;}
	.elementor-page.elementor-default #Content .section.the_content{width: 100%;}
	.elementor-page.elementor-default #Content .section_wrapper .the_content_wrapper{margin-left: 0; margin-right: 0; width: 100%;}
<?php endif; ?>
<?php if ( mfn_opts_get( 'tap-highlight-disable' ) ) : ?>
	a{-webkit-tap-highlight-color:transparent!important}
<?php endif; ?>

/**
 * Font | Family ********************************************************************************
 */

<?php
	$fonts = [
		'content' => mfn_opts_get('font-content'),
		'lead' => mfn_opts_get('font-lead', mfn_opts_get('font-content')),
		'menu' => mfn_opts_get('font-menu'),
		'title' => mfn_opts_get('font-title'),
		'headings' => mfn_opts_get('font-headings'),
		'headings-small' => mfn_opts_get('font-headings-small'),
		'blockquote' => mfn_opts_get('font-blockquote'),
		'decorative' => mfn_opts_get('font-decorative'),
	];

	foreach( $fonts as $font_k => $font_v ){

		if( $font_v ){
			$fonts[$font_k] = '"'. str_replace('#', '', $font_v) .'"';
		}

		if( mfn_opts_get('google-font-mode') !== 'local') {
			if( $fonts[$font_k] ){
				$fonts[$font_k] .= ',';
			}
			$fonts[$font_k] .= '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif';
		}

	}

?>

body, span.date_label, .timeline_items li h3 span, input[type="date"],
input[type="text"], input[type="password"], input[type="tel"], input[type="email"], input[type="url"], textarea, select, .offer_li .title h3, .mfn-menu-item-megamenu {
	font-family: <?php echo $fonts['content']; ?>;
}

.lead, .big {
	font-family: <?php echo $fonts['lead']; ?>;
}

#menu > ul > li > a, #overlay-menu ul li a {
	font-family: <?php echo $fonts['menu']; ?>;
}

#Subheader .title {
	font-family: <?php echo $fonts['title']; ?>;
}

h1, h2, h3, h4, .text-logo #logo {
	font-family: <?php echo $fonts['headings']; ?>;
}

h5, h6 {
	font-family: <?php echo $fonts['headings-small']; ?>;
}

blockquote {
	font-family: <?php echo $fonts['blockquote']; ?>;
}

.chart_box .chart .num, .counter .desc_wrapper .number-wrapper, .how_it_works .image .number,
.pricing-box .plan-header .price, .quick_fact .number-wrapper, .woocommerce .product div.entry-summary .price {
	font-family: <?php echo $fonts['decorative']; ?>;
}

/**
 * Font | Size & Style ********************************************************************************
 */

<?php

	$aFont = array(
		'content'	=> mfn_opts_get('font-size-content'),
		'big'			=> mfn_opts_get('font-size-big'),
		'menu'		=> mfn_opts_get('font-size-menu'),
		'title'		=> mfn_opts_get('font-size-title'),
		'h1'			=> mfn_opts_get('font-size-h1'),
		'h2'			=> mfn_opts_get('font-size-h2'),
		'h3'			=> mfn_opts_get('font-size-h3'),
		'h4'			=> mfn_opts_get('font-size-h4'),
		'h5'			=> mfn_opts_get('font-size-h5'),
		'h6'			=> mfn_opts_get('font-size-h6'),
		'intro'		=> mfn_opts_get('font-size-single-intro'),
	);

	// prevent passing not numeral value for letter spacing
	foreach( $aFont as $key => $val ) {
		if( ! empty($val['letter_spacing']) ) {
			$aFont[$key]['letter_spacing'] = intval($val['letter_spacing']);
		} else {
			$aFont[$key]['letter_spacing'] = 0;
		}
	}

	// main menu has no line height attribute
	$aFont['menu']['line_height'] = 0;

	// save initial values, we will use it later
	$aFontInit = $aFont;
?>

body, .mfn-menu-item-megamenu {
	font-size: <?php echo esc_attr($aFont['content']['size']); ?>px;
	line-height: <?php echo esc_attr($aFont['content']['line_height']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['content']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['content']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['content']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}
.lead, .big {
	font-size: <?php echo esc_attr($aFont['big']['size']); ?>px;
	line-height: <?php echo esc_attr($aFont['big']['line_height']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['big']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['big']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['big']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}
#menu > ul > li > a, #overlay-menu ul li a{
	font-size: <?php echo esc_attr($aFont['menu']['size']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['menu']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['menu']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['menu']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}
#overlay-menu ul li a{
	line-height: <?php echo esc_attr($aFont['menu']['size'] * 1.5); ?>px;
}
#Subheader .title {
	font-size: <?php echo esc_attr($aFont['title']['size']); ?>px;
	line-height: <?php echo esc_attr($aFont['title']['line_height']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['title']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['title']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['title']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}
h1, .text-logo #logo {
	font-size: <?php echo esc_attr($aFont['h1']['size']); ?>px;
	line-height: <?php echo esc_attr($aFont['h1']['line_height']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h1']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['h1']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['h1']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}
h2 {
	font-size: <?php echo esc_attr($aFont['h2']['size']); ?>px;
	line-height: <?php echo esc_attr($aFont['h2']['line_height']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h2']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['h2']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['h2']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}
h3, .woocommerce ul.products li.product h3, .woocommerce #customer_login h2 {
	font-size: <?php echo esc_attr($aFont['h3']['size']); ?>px;
	line-height: <?php echo esc_attr($aFont['h3']['line_height']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h3']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['h3']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['h3']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}
h4, .woocommerce .woocommerce-order-details__title,
.woocommerce .wc-bacs-bank-details-heading, .woocommerce .woocommerce-customer-details h2 {
	font-size: <?php echo esc_attr($aFont['h4']['size']); ?>px;
	line-height: <?php echo esc_attr($aFont['h4']['line_height']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h4']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['h4']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['h4']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}
h5 {
	font-size: <?php echo esc_attr($aFont['h5']['size']); ?>px;
	line-height: <?php echo esc_attr($aFont['h5']['line_height']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h5']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['h5']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['h5']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}
h6 {
	font-size: <?php echo esc_attr($aFont['h6']['size']); ?>px;
	line-height: <?php echo esc_attr($aFont['h6']['line_height']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h6']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['h6']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['h6']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}
#Intro .intro-title {
	font-size: <?php echo esc_attr($aFont['intro']['size']); ?>px;
	line-height: <?php echo esc_attr($aFont['intro']['line_height']); ?>px;
	font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['intro']['weight_style'])); ?>;
	letter-spacing: <?php echo esc_attr($aFont['intro']['letter_spacing']); ?>px;
	<?php
		if (strpos($aFont['intro']['weight_style'], 'italic')) {
			echo 'font-style: italic;';
		}
	?>
}

/**
 * Font | Size - Responsive ********************************************************************************
 */

<?php if ( mfn_opts_get('responsive') ): ?>

	<?php

		// auto font size

		$min_size = 13;
		$min_line = 19;

		// Tablet (Landscape) |  768 - 959

		if( mfn_opts_get('font-size-responsive') ){

			$multiplier = 0.85;

			foreach ($aFont as $key => $font) {
				if (is_numeric($font['size'])) {
					$aFont[$key]['size'] = round($font['size'] * $multiplier);
					if ($aFont[$key]['size'] < $min_size) {
						$aFont[$key]['size'] = $min_size;
					}
				}

				if (is_numeric($font['line_height'])) {
					$aFont[$key]['line_height'] = round($font['line_height'] * $multiplier);
					if ($aFont[$key]['line_height'] < $min_line) {
						$aFont[$key]['line_height'] = $min_line;
					}
				}

				if (is_numeric($font['letter_spacing'])) {
					$aFont[$key]['letter_spacing'] = round($font['letter_spacing'] * $multiplier);
				}
			}

		} else {

			// custom font size

			$aCustom = array(
				'content'	=> mfn_opts_get('font-size-content-tablet'),
				'big'			=> mfn_opts_get('font-size-big-tablet'),
				'menu'		=> mfn_opts_get('font-size-menu-tablet'),
				'title'		=> mfn_opts_get('font-size-title-tablet'),
				'h1'			=> mfn_opts_get('font-size-h1-tablet'),
				'h2'			=> mfn_opts_get('font-size-h2-tablet'),
				'h3'			=> mfn_opts_get('font-size-h3-tablet'),
				'h4'			=> mfn_opts_get('font-size-h4-tablet'),
				'h5'			=> mfn_opts_get('font-size-h5-tablet'),
				'h6'			=> mfn_opts_get('font-size-h6-tablet'),
				'intro'		=> mfn_opts_get('font-size-single-intro-tablet'),
			);

			foreach ( $aCustom as $key => $font ) {
				if( ! empty( $font['size'] ) ){
					foreach( $font as $attr_k => $attr_v ){
						if( ! empty($attr_v) ){
							$aFont[$key][$attr_k] = $attr_v;
						}
					}
				}
			}

		}

	?>

	@media only screen and (min-width:768px) and (max-width:959px){
		body, .mfn-menu-item-megamenu {
			font-size: <?php echo esc_attr($aFont['content']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['content']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['content']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['content']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['content']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		.lead, .big {
			font-size: <?php echo esc_attr($aFont['big']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['big']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['big']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['big']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['big']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		#menu > ul > li > a, #overlay-menu ul li a{
			font-size: <?php echo esc_attr($aFont['menu']['size']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['menu']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['menu']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['menu']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		#overlay-menu ul li a{
			line-height: <?php echo esc_attr($aFont['menu']['size'] * 1.5); ?>px;
		}

		#Subheader .title {
			font-size: <?php echo esc_attr($aFont['title']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['title']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['title']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['title']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['title']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h1, .text-logo #logo {
			font-size: <?php echo esc_attr($aFont['h1']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h1']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h1']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h1']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h1']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h2 {
			font-size: <?php echo esc_attr($aFont['h2']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h2']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h2']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h2']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h2']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h3, .woocommerce ul.products li.product h3, .woocommerce #customer_login h2 {
			font-size: <?php echo esc_attr($aFont['h3']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h3']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h3']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h3']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h3']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h4, .woocommerce .woocommerce-order-details__title,
		.woocommerce .wc-bacs-bank-details-heading, .woocommerce .woocommerce-customer-details h2 {
			font-size: <?php echo esc_attr($aFont['h4']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h4']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h4']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h4']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h4']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h5 {
			font-size: <?php echo esc_attr($aFont['h5']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h5']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h5']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h5']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h5']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h6 {
			font-size: <?php echo esc_attr($aFont['h6']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h6']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h6']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h6']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h6']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		#Intro .intro-title {
			font-size: <?php echo esc_attr($aFont['intro']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['intro']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['intro']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['intro']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['intro']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}

		blockquote { font-size: 15px;}

		.chart_box .chart .num { font-size: 45px; line-height: 45px; }

		.counter .desc_wrapper .number-wrapper { font-size: 45px; line-height: 45px;}
		.counter .desc_wrapper .title { font-size: 14px; line-height: 18px;}

		.faq .question .title { font-size: 14px; }

		.fancy_heading .title { font-size: 38px; line-height: 38px; }

		.offer .offer_li .desc_wrapper .title h3 { font-size: 32px; line-height: 32px; }
		.offer_thumb_ul li.offer_thumb_li .desc_wrapper .title h3 {  font-size: 32px; line-height: 32px; }

		.pricing-box .plan-header h2 { font-size: 27px; line-height: 27px; }
		.pricing-box .plan-header .price > span { font-size: 40px; line-height: 40px; }
		.pricing-box .plan-header .price sup.currency { font-size: 18px; line-height: 18px; }
		.pricing-box .plan-header .price sup.period { font-size: 14px; line-height: 14px;}

		.quick_fact .number-wrapper { font-size: 80px; line-height: 80px;}

		.trailer_box .desc h2 { font-size: 27px; line-height: 27px; }

		.widget > h3 { font-size: 17px; line-height: 20px; }
	}

	<?php

		// Tablet (Portrait) & Mobile (Landscape) | 480 - 767

		if( mfn_opts_get('font-size-responsive') ){

			$aFont = $aFontInit;
			$multiplier = 0.75;

			foreach ($aFont as $key => $font) {
				if (is_numeric($font['size'])) {
					$aFont[$key]['size'] = round($font['size'] * $multiplier);
					if ($aFont[$key]['size'] < $min_size) {
						$aFont[$key]['size'] = $min_size;
					}
				}

				if (is_numeric($font['line_height'])) {
					$aFont[$key]['line_height'] = round($font['line_height'] * $multiplier);
					if ($aFont[$key]['line_height'] < $min_line) {
						$aFont[$key]['line_height'] = $min_line;
					}
				}

				if (is_numeric($font['letter_spacing'])) {
					$aFont[$key]['letter_spacing'] = round($font['letter_spacing'] * $multiplier);
				}
			}

		} else {

			// custom font size

			$aCustom = array(
				'content'	=> mfn_opts_get('font-size-content-mobile'),
				'big'			=> mfn_opts_get('font-size-big-mobile'),
				'menu'		=> mfn_opts_get('font-size-menu-mobile'),
				'title'		=> mfn_opts_get('font-size-title-mobile'),
				'h1'			=> mfn_opts_get('font-size-h1-mobile'),
				'h2'			=> mfn_opts_get('font-size-h2-mobile'),
				'h3'			=> mfn_opts_get('font-size-h3-mobile'),
				'h4'			=> mfn_opts_get('font-size-h4-mobile'),
				'h5'			=> mfn_opts_get('font-size-h5-mobile'),
				'h6'			=> mfn_opts_get('font-size-h6-mobile'),
				'intro'		=> mfn_opts_get('font-size-single-intro-mobile'),
			);

			foreach ( $aCustom as $key => $font ) {
				if( ! empty( $font['size'] ) ){
					foreach( $font as $attr_k => $attr_v ){
						if( ! empty($attr_v) ){
							$aFont[$key][$attr_k] = $attr_v;
						}
					}
				}
			}

		}
	?>

	@media only screen and (min-width:480px) and (max-width:767px){
		body, .mfn-menu-item-megamenu {
			font-size: <?php echo esc_attr($aFont['content']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['content']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['content']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['content']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['content']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		.lead, .big {
			font-size: <?php echo esc_attr($aFont['big']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['big']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['big']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['big']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['big']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		#menu > ul > li > a, #overlay-menu ul li a{
			font-size: <?php echo esc_attr($aFont['menu']['size']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['menu']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['menu']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['menu']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		#overlay-menu ul li a{
			line-height: <?php echo esc_attr($aFont['menu']['size'] * 1.5); ?>px;
		}

		#Subheader .title {
			font-size: <?php echo esc_attr($aFont['title']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['title']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['title']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['title']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['title']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h1, .text-logo #logo {
			font-size: <?php echo esc_attr($aFont['h1']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h1']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h1']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h1']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h1']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h2 {
			font-size: <?php echo esc_attr($aFont['h2']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h2']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h2']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h2']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h2']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h3, .woocommerce ul.products li.product h3, .woocommerce #customer_login h2 {
			font-size: <?php echo esc_attr($aFont['h3']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h3']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h3']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h3']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h3']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h4, .woocommerce .woocommerce-order-details__title,
		.woocommerce .wc-bacs-bank-details-heading, .woocommerce .woocommerce-customer-details h2 {
			font-size: <?php echo esc_attr($aFont['h4']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h4']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h4']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h4']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h4']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h5 {
			font-size: <?php echo esc_attr($aFont['h5']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h5']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h5']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h5']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h5']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h6 {
			font-size: <?php echo esc_attr($aFont['h6']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h6']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h6']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h6']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h6']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		#Intro .intro-title {
			font-size: <?php echo esc_attr($aFont['intro']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['intro']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['intro']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['intro']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['intro']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}

		blockquote { font-size: 14px;}

		.chart_box .chart .num { font-size: 40px; line-height: 40px; }

		.counter .desc_wrapper .number-wrapper { font-size: 40px; line-height: 40px;}
		.counter .desc_wrapper .title { font-size: 13px; line-height: 16px;}

		.faq .question .title { font-size: 13px; }

		.fancy_heading .title { font-size: 34px; line-height: 34px; }

		.offer .offer_li .desc_wrapper .title h3 { font-size: 28px; line-height: 28px; }
		.offer_thumb_ul li.offer_thumb_li .desc_wrapper .title h3 {  font-size: 28px; line-height: 28px; }

		.pricing-box .plan-header h2 { font-size: 24px; line-height: 24px; }
		.pricing-box .plan-header .price > span { font-size: 34px; line-height: 34px; }
		.pricing-box .plan-header .price sup.currency { font-size: 16px; line-height: 16px; }
		.pricing-box .plan-header .price sup.period { font-size: 13px; line-height: 13px;}

		.quick_fact .number-wrapper { font-size: 70px; line-height: 70px;}

		.trailer_box .desc h2 { font-size: 24px; line-height: 24px; }

		.widget > h3 { font-size: 16px; line-height: 19px; }
	}

	<?php

		// Mobile (Portrait) | < 479

		if( mfn_opts_get('font-size-responsive') ){

			$aFont = $aFontInit;
			$multiplier = 0.6;

			foreach ($aFont as $key => $font) {
				if (is_numeric($font['size'])) {
					$aFont[$key]['size'] = round($font['size'] * $multiplier);
					if ($aFont[$key]['size'] < $min_size) {
						$aFont[$key]['size'] = $min_size;
					}
				}

				if (is_numeric($font['line_height'])) {
					$aFont[$key]['line_height'] = round($font['line_height'] * $multiplier);
					if ($aFont[$key]['line_height'] < $min_line) {
						$aFont[$key]['line_height'] = $min_line;
					}
				}

				if (is_numeric($font['letter_spacing'])) {
					$aFont[$key]['letter_spacing'] = round($font['letter_spacing'] * $multiplier);
				}
			}

		}
	?>

	@media only screen and (max-width:479px){
		body, .mfn-menu-item-megamenu {
			font-size: <?php echo esc_attr($aFont['content']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['content']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['content']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['content']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['content']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		.lead, .big {
			font-size: <?php echo esc_attr($aFont['big']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['big']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['big']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['big']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['big']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		#menu > ul > li > a, #overlay-menu ul li a{
			font-size: <?php echo esc_attr($aFont['menu']['size']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['menu']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['menu']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['menu']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		#overlay-menu ul li a{
			line-height: <?php echo esc_attr($aFont['menu']['size'] * 1.5); ?>px;
		}

		#Subheader .title {
			font-size: <?php echo esc_attr($aFont['title']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['title']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['title']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['title']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['title']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h1, .text-logo #logo {
			font-size: <?php echo esc_attr($aFont['h1']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h1']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h1']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h1']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h1']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h2 {
			font-size: <?php echo esc_attr($aFont['h2']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h2']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h2']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h2']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h2']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h3, .woocommerce ul.products li.product h3, .woocommerce #customer_login h2 {
			font-size: <?php echo esc_attr($aFont['h3']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h3']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h3']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h3']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h3']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h4, .woocommerce .woocommerce-order-details__title,
		.woocommerce .wc-bacs-bank-details-heading, .woocommerce .woocommerce-customer-details h2 {
			font-size: <?php echo esc_attr($aFont['h4']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h4']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h4']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h4']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h4']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h5 {
			font-size: <?php echo esc_attr($aFont['h5']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h5']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h5']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h5']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h5']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		h6 {
			font-size: <?php echo esc_attr($aFont['h6']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['h6']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['h6']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['h6']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['h6']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}
		#Intro .intro-title {
			font-size: <?php echo esc_attr($aFont['intro']['size']); ?>px;
			line-height: <?php echo esc_attr($aFont['intro']['line_height']); ?>px;
			font-weight: <?php echo esc_attr(str_replace('italic', '', $aFont['intro']['weight_style'])); ?>;
			letter-spacing: <?php echo esc_attr($aFont['intro']['letter_spacing']); ?>px;
			<?php
				if (strpos($aFont['intro']['weight_style'], 'italic')) {
					echo 'font-style: italic;';
				}
			?>
		}

		blockquote { font-size: 13px;}

		.chart_box .chart .num { font-size: 35px; line-height: 35px; }

		.counter .desc_wrapper .number-wrapper { font-size: 35px; line-height: 35px;}
		.counter .desc_wrapper .title { font-size: 13px; line-height: 26px;}

		.faq .question .title { font-size: 13px; }

		.fancy_heading .title { font-size: 30px; line-height: 30px; }

		.offer .offer_li .desc_wrapper .title h3 { font-size: 26px; line-height: 26px; }
		.offer_thumb_ul li.offer_thumb_li .desc_wrapper .title h3 {  font-size: 26px; line-height: 26px; }

		.pricing-box .plan-header h2 { font-size: 21px; line-height: 21px; }
		.pricing-box .plan-header .price > span { font-size: 32px; line-height: 32px; }
		.pricing-box .plan-header .price sup.currency { font-size: 14px; line-height: 14px; }
		.pricing-box .plan-header .price sup.period { font-size: 13px; line-height: 13px;}

		.quick_fact .number-wrapper { font-size: 60px; line-height: 60px;}

		.trailer_box .desc h2 { font-size: 21px; line-height: 21px; }

		.widget > h3 { font-size: 15px; line-height: 18px; }
	}

<?php endif; ?>

/**
 * Sidebar | Width ********************************************************************************
 */

<?php
	$sidebarW = mfn_opts_get('sidebar-width', '23');
	$contentW = 100 - $sidebarW;
	$sidebar2W = $sidebarW - 5;
	$content2W = 100 - ($sidebar2W * 2);
	$sidebar2M = $content2W + $sidebar2W;
	$content2M = $sidebar2W;
?>

.with_aside .sidebar.columns {
	width: <?php echo esc_attr($sidebarW); ?>%;
}
.with_aside .sections_group {
	width: <?php echo esc_attr($contentW); ?>%;
}

.aside_both .sidebar.columns {
	width: <?php echo esc_attr($sidebar2W); ?>%;
}
.aside_both .sidebar.sidebar-1{
	margin-left: -<?php echo esc_attr($sidebar2M); ?>%;
}
.aside_both .sections_group {
	width: <?php echo esc_attr($content2W); ?>%;
	margin-left: <?php echo esc_attr($content2M); ?>%;
}

/**
 * Grid | Width ********************************************************************************
 */

<?php if (mfn_opts_get('responsive')): ?>

	<?php
		$gridW = mfn_opts_get('grid-width', 1240);
	?>

	@media only screen and (min-width:1240px){
		#Wrapper, .with_aside .content_wrapper {
			max-width: <?php echo esc_attr($gridW); ?>px;
		}
		body.layout-boxed.mfn-header-scrolled .mfn-header-tmpl.mfn-sticky-layout-width{
			max-width: <?php echo esc_attr($gridW); ?>px;
			left: 0;
			right: 0;
			margin-left: auto;
			margin-right: auto;
		}
		body.layout-boxed:not(.mfn-header-scrolled) .mfn-header-tmpl.mfn-header-layout-width,
		body.layout-boxed .mfn-header-tmpl.mfn-header-layout-width:not(.mfn-hasSticky){
			max-width: <?php echo esc_attr($gridW); ?>px;
			left: 0;
			right: 0;
			margin-left: auto;
			margin-right: auto;
		}
		body.layout-boxed.mfn-bebuilder-header.mfn-ui .mfn-only-sample-content{ max-width: <?php echo esc_attr($gridW); ?>px; margin-left: auto; margin-right: auto;}
		.section_wrapper, .container {
			max-width: <?php echo esc_attr($gridW - 20); ?>px;
		}
		.layout-boxed.header-boxed #Top_bar.is-sticky{
			max-width: <?php echo esc_attr($gridW); ?>px;
		}
	}

	<?php
		if ($box_padding = mfn_opts_get('layout-boxed-padding')):
	?>

		@media only screen and (min-width:768px){

			.layout-boxed #Subheader .container,
			.layout-boxed:not(.with_aside) .section:not(.full-width),
			.layout-boxed.with_aside .content_wrapper,
			.layout-boxed #Footer .container { padding-left: <?php echo esc_attr($box_padding); ?>; padding-right: <?php echo esc_attr($box_padding); ?>;}

			.layout-boxed.header-modern #Action_bar .container,
			.layout-boxed.header-modern #Top_bar:not(.is-sticky) .container { padding-left: <?php echo esc_attr($box_padding); ?>; padding-right: <?php echo esc_attr($box_padding); ?>;}
		}

	<?php endif; ?>

	<?php
		$mobileGridW = mfn_opts_get('mobile-grid-width', 700);
		$mobileSitePadding = mfn_opts_get('mobile-site-padding', 33);
	?>

	@media only screen and (max-width: 767px){

		#Wrapper{max-width:calc(100% - <?php echo (((float)$mobileSitePadding*2)+1); ?>px);}
		.content_wrapper .section_wrapper,
		.container,
		.four.columns .widget-area { max-width: <?php echo esc_attr($mobileGridW + 70); ?>px !important; padding-left: <?php echo $mobileSitePadding; ?>px; padding-right: <?php echo $mobileSitePadding; ?>px; }
	}

<?php endif; ?>

/**
 * WOO Lightbox ********************************************************************************
 */

<?php if( mfn_opts_get('product-lightbox-caption') == 'off' ){ ?> body .pswp .pswp__caption{ display: none; }<?php } ?>
<?php if( mfn_opts_get('product-lightbox-bg') ){ ?> body .pswp .pswp__bg{ background-color: <?php echo mfn_opts_get('product-lightbox-bg');?>; }<?php } ?>

/**
 * Buttons | .button ***************************************************************************
 */

<?php
	// font family

	$button_font_family = mfn_opts_get( 'button-font-family' );
	if( $button_font_family ){
		$button_font_family = '"'. str_replace('#', '', esc_attr($button_font_family)) .'"';
	} else {
		$button_font_family = 'inherit';
	}

	// font

	$button_font = mfn_opts_get( 'button-font', ['size'=>'14','weight_style'=>'400','letter_spacing'=>0] );
	if( strpos( $button_font['weight_style'], 'italic' ) !== false ){
		$button_font['weight'] = str_replace('italic','',$button_font['weight_style']);
		$button_font['style'] = 'italic';
	} else {
		$button_font['weight'] = $button_font['weight_style'];
		$button_font['style'] = 'inherit';
	}

	$button_font_tablet = mfn_opts_get( 'button-font-tablet', false );
	if( $button_font_tablet ){
		if( strpos( $button_font_tablet['weight_style'], 'italic' ) !== false ){
			$button_font_tablet['weight'] = str_replace('italic','',$button_font_tablet['weight_style']);
			$button_font_tablet['style'] = 'italic';
		} else {
			$button_font_tablet['weight'] = $button_font_tablet['weight_style'];
			if( $button_font_tablet['weight'] ){
				$button_font_tablet['style'] = 'normal';
			}
		}
	}

	$button_font_mobile = mfn_opts_get( 'button-font-mobile', false );
	if( $button_font_mobile ){
		if( strpos( $button_font_mobile['weight_style'], 'italic' ) !== false ){
			$button_font_mobile['weight'] = str_replace('italic','',$button_font_mobile['weight_style']);
			$button_font_mobile['style'] = 'italic';
		} else {
			$button_font_mobile['weight'] = $button_font_mobile['weight_style'];
			if( $button_font_mobile['weight'] ){
				$button_font_mobile['style'] = 'normal';
			}
		}
	}

	// padding

	$button_padding = mfn_opts_get( 'button-padding', '10px 20px', ['implode'=>' ','unit'=>'px']);
	$button_padding_tablet = trim( mfn_opts_get( 'button-padding-tablet', false, ['implode'=>' ','unit'=>'px']) );
	$button_padding_mobile = trim( mfn_opts_get( 'button-padding-mobile', false, ['implode'=>' ','unit'=>'px']) );
	$button_border_width = trim( mfn_opts_get( 'button-border-width', '0', ['implode'=>' ','unit'=>'px'] ) );
	if( ! $button_border_width ){
		$button_border_width = 0;
	}
?>

body{
	--mfn-button-font-family: <?php echo $button_font_family; ?>;
	--mfn-button-font-size: <?php echo esc_attr( $button_font['size'] ); ?>px;
	--mfn-button-font-weight: <?php echo esc_attr( $button_font['weight'] ); ?>;
	--mfn-button-font-style: <?php echo esc_attr( $button_font['style'] ); ?>;
	--mfn-button-letter-spacing: <?php echo esc_attr( $button_font['letter_spacing'] ); ?>px;
	--mfn-button-padding: <?php echo esc_attr( $button_padding ); ?>;
	--mfn-button-border-width: <?php echo esc_attr( $button_border_width ); ?>;
	--mfn-button-border-radius: <?php echo esc_attr( mfn_opts_get( 'button-border-radius', '0', ['implode'=>' ','unit'=>'px']) ); ?>;
	--mfn-button-gap: <?php echo esc_attr( mfn_opts_get( 'button-gap', '10px', ['unit'=>'px']) ); ?>;
	--mfn-button-transition: <?php echo esc_attr( mfn_opts_get( 'button-animation-time', '0.2', ['unit'=>'s']) ); ?>;

	--mfn-button-color: <?php echo esc_attr( mfn_opts_get( 'button-color', '#626262', [ 'key' => 'normal' ]) ); ?>;
  --mfn-button-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-color', '#626262', [ 'key' => 'hover' ]) ); ?>;
  --mfn-button-bg: <?php echo esc_attr( mfn_opts_get( 'button-background', '#dbdddf', [ 'key' => 'normal' ]) ); ?>;
  --mfn-button-bg-hover: <?php echo esc_attr( mfn_opts_get( 'button-background', '#d3d3d3', [ 'key' => 'hover' ]) ); ?>;
  --mfn-button-border-color: <?php echo esc_attr( mfn_opts_get( 'button-border-color', 'transparent', [ 'key' => 'normal', 'not_empty' => true ]) ); ?>;
  --mfn-button-border-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-border-color', 'transparent', [ 'key' => 'hover', 'not_empty' => true ]) ); ?>;
	--mfn-button-icon-color: <?php echo esc_attr( mfn_opts_get( 'button-icon-color', '#626262', [ 'key' => 'normal' ]) ); ?>;
	--mfn-button-icon-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-icon-color', '#626262', [ 'key' => 'hover' ]) ); ?>;
	--mfn-button-box-shadow: <?php echo esc_attr( mfn_opts_get( 'button-box-shadow', 'unset', [ 'not_empty' => true ]) ); ?>;

	<?php if( mfn_opts_get('button-background-type') ): ?>
		--mfn-button-bg-image: <?php echo esc_attr( mfn_opts_get( 'button-gradient', 'unset', [ 'key' => 'string' ]) ); ?>;
		--mfn-button-bg-image-hover: <?php echo esc_attr( mfn_opts_get( 'button-gradient-hover', 'unset', [ 'key' => 'string' ]) ); ?>;
	<?php endif; ?>

	--mfn-button-theme-color: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-color', '#626262', [ 'key' => 'normal' ]) ); ?>;
  --mfn-button-theme-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-color', '#626262', [ 'key' => 'hover' ]) ); ?>;
  --mfn-button-theme-bg: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-background', '#dbdddf', [ 'key' => 'normal' ]) ); ?>;
  --mfn-button-theme-bg-hover: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-background', '#d3d3d3', [ 'key' => 'hover' ]) ); ?>;
  --mfn-button-theme-border-color: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-border-color', 'transparent', [ 'key' => 'normal', 'not_empty' => true ]) ); ?>;
  --mfn-button-theme-border-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-border-color', 'transparent', [ 'key' => 'hover', 'not_empty' => true ]) ); ?>;
	--mfn-button-theme-icon-color: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-icon-color', '#626262', [ 'key' => 'normal' ]) ); ?>;
	--mfn-button-theme-icon-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-icon-color', '#626262', [ 'key' => 'hover' ]) ); ?>;
	--mfn-button-theme-box-shadow: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-box-shadow', 'unset', [ 'not_empty' => true ]) ); ?>;

	<?php if( mfn_opts_get('button-highlighted-background-type') ): ?>
		--mfn-button-theme-bg-image: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-gradient', 'unset', [ 'key' => 'string' ]) ); ?>;
		--mfn-button-theme-bg-image-hover: <?php echo esc_attr( mfn_opts_get( 'button-highlighted-gradient-hover', 'unset', [ 'key' => 'string' ]) ); ?>;
	<?php endif; ?>

	--mfn-button-shop-color: <?php echo esc_attr( mfn_opts_get( 'button-shop-color', '#626262', [ 'key' => 'normal' ]) ); ?>;
	--mfn-button-shop-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-shop-color', '#626262', [ 'key' => 'hover' ]) ); ?>;
	--mfn-button-shop-bg: <?php echo esc_attr( mfn_opts_get( 'button-shop-background', '#dbdddf', [ 'key' => 'normal' ]) ); ?>;
	--mfn-button-shop-bg-hover: <?php echo esc_attr( mfn_opts_get( 'button-shop-background', '#d3d3d3', [ 'key' => 'hover' ]) ); ?>;
	--mfn-button-shop-border-color: <?php echo esc_attr( mfn_opts_get( 'button-shop-border-color', 'transparent', [ 'key' => 'normal', 'not_empty' => true ]) ); ?>;
	--mfn-button-shop-border-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-shop-border-color', 'transparent', [ 'key' => 'hover', 'not_empty' => true ]) ); ?>;
	--mfn-button-shop-icon-color: <?php echo esc_attr( mfn_opts_get( 'button-shop-icon-color', '#626262', [ 'key' => 'normal' ]) ); ?>;
	--mfn-button-shop-icon-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-shop-icon-color', '#626262', [ 'key' => 'hover' ]) ); ?>;
	--mfn-button-shop-box-shadow: <?php echo esc_attr( mfn_opts_get( 'button-shop-box-shadow', 'unset', [ 'not_empty' => true ]) ); ?>;

	<?php if( mfn_opts_get('button-shop-background-type') ): ?>
		--mfn-button-shop-bg-image: <?php echo esc_attr( mfn_opts_get( 'button-shop-gradient', 'unset', [ 'key' => 'string' ]) ); ?>;
		--mfn-button-shop-bg-image-hover: <?php echo esc_attr( mfn_opts_get( 'button-shop-gradient-hover', 'unset', [ 'key' => 'string' ]) ); ?>;
	<?php endif; ?>

	--mfn-button-action-color: <?php echo esc_attr( mfn_opts_get( 'button-action-color', '#626262', [ 'key' => 'normal' ]) ); ?>;
	--mfn-button-action-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-action-color', '#626262', [ 'key' => 'hover' ]) ); ?>;
	--mfn-button-action-bg: <?php echo esc_attr( mfn_opts_get( 'button-action-background', '#dbdddf', [ 'key' => 'normal' ]) ); ?>;
	--mfn-button-action-bg-hover: <?php echo esc_attr( mfn_opts_get( 'button-action-background', '#d3d3d3', [ 'key' => 'hover' ]) ); ?>;
	--mfn-button-action-border-color: <?php echo esc_attr( mfn_opts_get( 'button-action-border-color', 'transparent', [ 'key' => 'normal', 'not_empty' => true ]) ); ?>;
	--mfn-button-action-border-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-action-border-color', 'transparent', [ 'key' => 'hover', 'not_empty' => true ]) ); ?>;
	--mfn-button-action-icon-color: <?php echo esc_attr( mfn_opts_get( 'button-action-icon-color', '#626262', [ 'key' => 'normal' ]) ); ?>;
	--mfn-button-action-icon-color-hover: <?php echo esc_attr( mfn_opts_get( 'button-action-icon-color', '#626262', [ 'key' => 'hover' ]) ); ?>;
	--mfn-button-action-box-shadow: <?php echo esc_attr( mfn_opts_get( 'button-action-box-shadow', 'unset', [ 'not_empty' => true ]) ); ?>;

	<?php if( mfn_opts_get('button-action-background-type') ): ?>
		--mfn-button-action-bg-image: <?php echo esc_attr( mfn_opts_get( 'button-action-gradient', 'unset', [ 'key' => 'string' ]) ); ?>;
		--mfn-button-action-bg-image-hover: <?php echo esc_attr( mfn_opts_get( 'button-action-gradient-hover', 'unset', [ 'key' => 'string' ]) ); ?>;
	<?php endif; ?>

	--mfn-product-list-gallery-slider-arrow-bg: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-arrow-bg', '#fff', [ 'key' => 'normal' ]) ); ?>;
	--mfn-product-list-gallery-slider-arrow-bg-hover: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-arrow-bg', '#fff', [ 'key' => 'hover' ]) ); ?>;
	--mfn-product-list-gallery-slider-arrow-color: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-arrow-color', '#000', [ 'key' => 'normal' ]) ); ?>;
	--mfn-product-list-gallery-slider-arrow-color-hover: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-arrow-color', '#000', [ 'key' => 'hover' ]) ); ?>;

	--mfn-product-list-gallery-slider-dots-bg: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-dots-bg', 'rgba(0,0,0,0.3)', [ 'key' => 'normal' ]) ); ?>;
	--mfn-product-list-gallery-slider-dots-bg-active: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-dots-bg', '#000', [ 'key' => 'active' ]) ); ?>;
	--mfn-product-list-gallery-slider-padination-bg: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-pagination-bg', 'transparent') ); ?>;
	--mfn-product-list-gallery-slider-dots-size: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-pagination-dots-size', '8').'px' ); ?>;
	--mfn-product-list-gallery-slider-nav-border-radius: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-border-radius', '0') ).'px'; ?>;
	--mfn-product-list-gallery-slider-nav-offset: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-nav-offset', '0') ).'px'; ?>;
	--mfn-product-list-gallery-slider-dots-gap: <?php echo esc_attr( mfn_opts_get( 'product-list-gallery-slider-pagination-dots-gap', '5') ).'px'; ?>;
}



@media only screen and (max-width: 959px){
	body{
		<?php
			if( '' !== $button_padding_tablet ) echo '--mfn-button-padding:'. esc_attr( $button_padding_tablet ) .';';
			if( isset($button_font_tablet['size']) && '' !== $button_font_tablet['size'] ) echo '--mfn-button-font-size:'. esc_attr( $button_font_tablet['size'] ) .'px;';
			if( isset($button_font_tablet['weight']) && '' !== $button_font_tablet['weight'] ) echo '--mfn-button-font-weight:'. esc_attr( $button_font_tablet['weight'] ) .';';
			if( isset($button_font_tablet['style']) && '' !== $button_font_tablet['style'] ) echo '--mfn-button-font-style:'. esc_attr( $button_font_tablet['style'] ) .';';
			if( isset($button_font_tablet['letter_spacing']) && '' !== $button_font_tablet['letter_spacing'] ) echo '--mfn-button-letter-spacing:'. esc_attr( $button_font_tablet['letter_spacing'] ) .'px;';
		?>
	}

<?php

	$product_gallery_grid_cols_tablet = mfn_opts_get('shop-product-gallery-grid-cols-tablet');
	if( !empty($product_gallery_grid_cols_tablet) ) {
		echo '.column_product_images .mfn-product-images-wrapper .mfn-product-gallery-grid{ grid-template-columns: repeat('.$product_gallery_grid_cols_tablet.', 1fr); }';
	}

?>

}

@media only screen and (max-width: 768px){
	body{
		<?php
			if( '' !== $button_padding_mobile ) echo '--mfn-button-padding:'. esc_attr( $button_padding_mobile ) .';';
			if( isset($button_font_mobile['size']) && '' !== $button_font_mobile['size'] ) echo '--mfn-button-font-size:'. esc_attr( $button_font_mobile['size'] ) .'px;';
			if( isset($button_font_mobile['weight']) && '' !== $button_font_mobile['weight'] ) echo '--mfn-button-font-weight:'. esc_attr( $button_font_mobile['weight'] ) .';';
			if( isset($button_font_mobile['style']) && '' !== $button_font_mobile['style'] ) echo '--mfn-button-font-style:'. esc_attr( $button_font_mobile['style'] ) .';';
			if( isset($button_font_mobile['letter_spacing']) && '' !== $button_font_mobile['letter_spacing'] ) echo '--mfn-button-letter-spacing:'. esc_attr( $button_font_mobile['letter_spacing'] ) .'px;';
		?>
	}
}

/**
 * GDPR 2.0 ********************************************************************************
 */

.mfn-cookies, .mfn-cookies-reopen{
	--mfn-gdpr2-container-text-color: <?php echo esc_attr( mfn_opts_get( 'gdpr2-color-container-font', '#626262') ); ?>;
	--mfn-gdpr2-container-strong-color: <?php echo esc_attr( mfn_opts_get( 'gdpr2-color-container-font-strong', '#07070a') ); ?>;
	--mfn-gdpr2-container-bg: <?php echo esc_attr( mfn_opts_get( 'gdpr2-color-container', '#ffffff') ); ?>;
	--mfn-gdpr2-container-overlay: <?php echo esc_attr( mfn_opts_get( 'gdpr2-color-overlay', 'rgba(25,37,48,0.6)') ); ?>;

	--mfn-gdpr2-details-box-bg: <?php echo esc_attr( mfn_opts_get( 'gdpr2-color-details-box-bg', '#fbfbfb') ); ?>;
	--mfn-gdpr2-details-switch-bg: <?php echo esc_attr( mfn_opts_get( 'gdpr2-color-details-switch', '#00032a') ); ?>;
	--mfn-gdpr2-details-switch-bg-active: <?php echo esc_attr( mfn_opts_get( 'gdpr2-color-details-switch-active', '#5acb65') ); ?>;

	--mfn-gdpr2-tabs-text-color: <?php echo esc_attr( mfn_opts_get( 'gdpr2-tabs-text-color', '#07070a') ); ?>;
	--mfn-gdpr2-tabs-text-color-active: <?php echo esc_attr( mfn_opts_get( 'gdpr2-tabs-text-color-active', '#0089f7') ); ?>;
	--mfn-gdpr2-tabs-border: <?php echo esc_attr( mfn_opts_get( 'gdpr2-tabs-border', 'rgba(8,8,14,0.1)') ); ?>;

	--mfn-gdpr2-buttons-box-bg: <?php echo esc_attr( mfn_opts_get( 'gdpr2-color-buttons-box-bg', '#fbfbfb') ); ?>;

	--mfn-gdpr2-reopen-background: <?php echo esc_attr( mfn_opts_get( 'gdpr2-reopen-background', '#fff') ); ?>;
	--mfn-gdpr2-reopen-color: <?php echo esc_attr( mfn_opts_get( 'gdpr2-reopen-color', '#222') ); ?>;

	<?php
		$shadow = mfn_opts_get( 'gdpr2-reopen-boxshadow', '', [ 'unit' => 'px' ] );
		if( is_array( $shadow ) ){
			echo '--mfn-gdpr2-reopen-box-shadow:'. ( $shadow['inset'] ? 'inset' : '') .' '. $shadow['x'] .' '. $shadow['y'] .' '. $shadow['blur'] .' '. $shadow['spread'] .' '. $shadow['color'] .';';
		}
	?>

	<?php
		$gdpr2_button = [
			'color' => mfn_opts_get( 'gdpr2-color-buttons', '', [ 'key' => 'normal' ] ),
			'color-hover' => mfn_opts_get( 'gdpr2-color-buttons', '', [ 'key' => 'hover' ] ),
			'bg' => mfn_opts_get( 'gdpr2-color-buttons-bg', '', [ 'key' => 'normal' ] ),
			'bg-hover' => mfn_opts_get( 'gdpr2-color-buttons-bg', '', [ 'key' => 'hover' ] ),
			'border-color' => mfn_opts_get( 'gdpr2-color-buttons-border', '', [ 'key' => 'normal' ] ),
			'border-color' => mfn_opts_get( 'gdpr2-color-buttons-border', '', [ 'key' => 'hover' ] ),

			'theme-color' => mfn_opts_get( 'gdpr2-color-buttons-active', '', [ 'key' => 'normal' ] ),
			'theme-color-hover' => mfn_opts_get( 'gdpr2-color-buttons-active', '', [ 'key' => 'hover' ] ),
			'theme-bg' => mfn_opts_get( 'gdpr2-color-buttons-bg-active', '', [ 'key' => 'normal' ] ),
			'theme-bg-hover' => mfn_opts_get( 'gdpr2-color-buttons-bg-active', '', [ 'key' => 'hover' ] ),
			'theme-border-color' => mfn_opts_get( 'gdpr2-color-buttons-border-active', '', [ 'key' => 'normal' ] ),
			'theme-border-color' => mfn_opts_get( 'gdpr2-color-buttons-border-active', '', [ 'key' => 'hover' ] ),
		];

		foreach( $gdpr2_button as $k => $v ){
			if( $v ){
				echo '--mfn-button-'. $k .':'. esc_attr( $v ) .';';
			}
		}

	?>
}

/**
 * Logo ********************************************************************************
 */

<?php
	$aLogo = array(
		'height' => intval(mfn_opts_get('logo-height', 60)),
		'vertical_padding' => intval(mfn_opts_get('logo-vertical-padding', 15)),
	);

	$aLogo['top_bar_right_H'] = $aLogo['height'] + ($aLogo['vertical_padding'] * 2);
	$aLogo['content_padding'] = $aLogo['top_bar_right_H'] + 30;
	$aLogo['top_bar_right_T'] = ($aLogo['top_bar_right_H'] / 2) - 20;

	$aLogo['menu_padding'] = ($aLogo['top_bar_right_H'] / 2) - 30;
	$aLogo['menu_margin'] = ($aLogo['top_bar_right_H'] / 2) - 25;
	// $aLogo['responsive_menu_T'] = ($aLogo['height'] / 2) + 10; /* mobile logo | margin: 10px */

	$aLogo['header_fixed_LH'] = ($aLogo['top_bar_right_H'] - 30) / 2 ;
?>

#Top_bar #logo,
.header-fixed #Top_bar #logo,
.header-plain #Top_bar #logo,
.header-transparent #Top_bar #logo {
	height: <?php echo esc_attr($aLogo['height']); ?>px;
	line-height: <?php echo esc_attr($aLogo['height']); ?>px;
	padding: <?php echo esc_attr($aLogo['vertical_padding']); ?>px 0;
}
.logo-overflow #Top_bar:not(.is-sticky) .logo {
  height: <?php echo esc_attr($aLogo['top_bar_right_H']); ?>px;
}
.single-post.post-template-default.header-transparent #Content{
	padding-top: <?php echo esc_attr($aLogo['content_padding']); ?>px;
}
#Top_bar .menu > li > a {
  padding: <?php echo esc_attr($aLogo['menu_padding']); ?>px 0;
}
.menu-highlight:not(.header-creative) #Top_bar .menu > li > a {
	margin: <?php echo esc_attr($aLogo['menu_margin']); ?>px 0;
}
.header-plain:not(.menu-highlight) #Top_bar .menu > li > a span:not(.description) {
  line-height: <?php echo esc_attr($aLogo['top_bar_right_H']); ?>px;
}
.header-fixed #Top_bar .menu > li > a {
  padding: <?php echo esc_attr($aLogo['header_fixed_LH']); ?>px 0;
}

<?php if (! $aLogo['vertical_padding']): ?>
.logo-overflow #Top_bar.is-sticky #logo{padding:0!important;}
<?php endif; ?>

<?php if ($aLogo['vertical_padding']): ?>
@media only screen and (max-width: 767px){
	.mobile-header-mini #Top_bar #logo{
		height:50px!important;
		line-height:50px!important;
		margin:5px 0;
	}
}
<?php endif; ?>

<?php

	// SVG logo width

	$logo_width = mfn_opts_get( 'logo-width', 100 );
	echo '#Top_bar #logo img.svg{width:'. $logo_width .'px}';

	if( $logo_width_tablet = mfn_opts_get( 'logo-width-tablet' ) ){
		echo '@media(max-width: 959px){
			#Top_bar #logo img.svg{width:'. $logo_width_tablet .'px}
		}';
	}

	if( $logo_width_mobile = mfn_opts_get( 'logo-width-mobile' ) ){
		echo '@media(max-width: 767px){
			#Top_bar #logo img.svg{width:'. $logo_width_mobile .'px}
		}';
	}

?>

/**
 * Other ********************************************************************************
 */

/* Image frame */

.image_frame,.wp-caption{
	border-width:<?php echo esc_attr(mfn_opts_get('image-frame-border-width', 0, ['unit' => 'px'])); ?>
}

<?php

$product_gallery_grid_cols = mfn_opts_get('shop-product-gallery-grid-cols');
if( !empty($product_gallery_grid_cols) ) {
	echo '.column_product_images .mfn-product-images-wrapper .mfn-product-gallery-grid{ grid-template-columns: repeat('.$product_gallery_grid_cols.', 1fr); }';
}


 ?>

/* Alerts */

<?php
	$alert_border_radius = mfn_opts_get('alert-border-radius', 0, ['unit' => 'px']);
?>

<?php if ($alert_border_radius): ?>

	.alert{
		border-radius:<?php echo esc_attr($alert_border_radius); ?>
	}

<?php endif; ?>

/* Search + Live search */

#Top_bar .top_bar_right .top-bar-right-input input{
	width:<?php echo mfn_opts_get('header-search-input-width', 200, ['unit' => 'px']); ?>
}

.mfn-live-search-box .mfn-live-search-list{
	max-height:<?php echo mfn_opts_get('header-search-live-container-height', 300, ['unit' => 'px']); ?>
}

/* Form | Border width */

<?php
	$form_border_width = trim(mfn_opts_get('form-border-width', ''));
	if( $form_border_width || ($form_border_width === '0') ):
?>

	input[type="date"],input[type="email"],input[type="number"],input[type="password"],input[type="search"],
	input[type="tel"],input[type="text"],input[type="url"],select,textarea,.woocommerce .quantity input.qty{
		border-width:<?php echo esc_attr($form_border_width); ?>;
		<?php if ($form_border_width != '1px'): ?>
			box-shadow:unset;
			resize:none;
		<?php endif; ?>
	}

	.select2-container--default .select2-selection--single,.select2-dropdown,
	.select2-container--default.select2-container--open .select2-selection--single{
		border-width:<?php echo esc_attr($form_border_width); ?>;
	}

<?php endif; ?>

<?php
	$form_border_radius = trim(mfn_opts_get('form-border-radius', ''));
	if( is_numeric( $form_border_radius ) ){
		$form_border_radius .= 'px';
	}
?>

<?php if ($form_border_radius): ?>

	input[type="date"],input[type="email"],input[type="number"],input[type="password"],input[type="search"],
	input[type="tel"],input[type="text"],input[type="url"],select,textarea,.woocommerce .quantity input.qty{
		border-radius:<?php echo esc_attr($form_border_radius); ?>
	}

	.select2-container--default .select2-selection--single,
	.select2-dropdown, .select2-container--default.select2-container--open .select2-selection--single{
		border-radius:<?php echo esc_attr($form_border_radius); ?>
	}

<?php endif; ?>

/* Side Slide */

#Side_slide{
	right:-<?php echo esc_attr(mfn_opts_get('responsive-side-slide-width', 250)); ?>px;
	width:<?php echo esc_attr(mfn_opts_get('responsive-side-slide-width', 250)); ?>px;
}
#Side_slide.left{
	left:-<?php echo esc_attr(mfn_opts_get('responsive-side-slide-width', 250)); ?>px;
}

/* Other */

/* Blog teaser | Android phones 1pt line fix - do NOT move it somewhere else */

.blog-teaser li .desc-wrapper .desc{background-position-y:-1px;}

/**
 * Free delivery progress bar *****
 */

<?php $mfn_free_delivery_color = esc_attr( mfn_opts_get('free-delivery-color-active', mfn_opts_get('color-theme', '#0089F7')) ); ?>

.mfn-free-delivery-info{
	--mfn-free-delivery-bar: <?php echo $mfn_free_delivery_color; ?>;
	--mfn-free-delivery-bg: <?php echo esc_attr( mfn_opts_get('free-delivery-color-inactive', 'rgba(0,0,0,0.1)') ); ?>;
	--mfn-free-delivery-achieved: <?php echo esc_attr( mfn_opts_get('free-delivery-color-achieved', $mfn_free_delivery_color) ); ?>;
}

<?php if( !empty( mfn_opts_get('background-footer-backtotop') ) ): ?>
#back_to_top{
	background-color: <?php echo esc_attr(mfn_opts_get('background-footer-backtotop')); ?>
}
<?php endif; ?>

<?php if( !empty( mfn_opts_get('color-footer-backtotop') ) ): ?>
#back_to_top i{
	color: <?php echo esc_attr(mfn_opts_get('color-footer-backtotop')); ?>
}
<?php endif; ?>

.mfn-product-list-gallery-slider .mfn-product-list-gallery-slider-arrow{
	display: <?php echo !empty( mfn_opts_get('product-list-gallery-slider-arrows-visibility') ) ? 'none' : 'flex'; ?>;
}

/**
 * Preloader
 */

<?php if( ! empty( mfn_opts_get('preloader-image') ) ): ?>
html.preloading:after{
	background-image:url(<?php echo esc_attr(mfn_opts_get('preloader-image')); ?>);
}
<?php endif; ?>

/**
 * Responsive ********************************************************************************
 */

@media only screen and ( max-width: 767px ){
	<?php

	$product_gallery_grid_cols_mobile = mfn_opts_get('shop-product-gallery-grid-cols-mobile');
	if( !empty($product_gallery_grid_cols_mobile) ) {
		echo '.column_product_images .mfn-product-images-wrapper .mfn-product-gallery-grid{ grid-template-columns: repeat('.$product_gallery_grid_cols_mobile.', 1fr); }';
	}

	?>
	<?php if ( trim( mfn_opts_get( 'mobile-header-height', '' ) ) || '0' === mfn_opts_get( 'mobile-header-height', '' ) ) : ?>
		body:not(.template-slider) #Header{
			min-height: <?php echo esc_attr( mfn_opts_get( 'mobile-header-height', false, [ 'unit' => 'px' ] ) ); ?>;
		}
	<?php endif; ?>
	<?php if ( trim( mfn_opts_get( 'mobile-subheader-padding', '' ) ) || '0' === mfn_opts_get( 'mobile-subheader-padding', '' ) ) : ?>
		#Subheader{
			padding: <?php echo esc_attr( mfn_opts_get( 'mobile-subheader-padding', false, [ 'unit' => 'px' ] ) ); ?>;
		}
	<?php endif; ?>
}
