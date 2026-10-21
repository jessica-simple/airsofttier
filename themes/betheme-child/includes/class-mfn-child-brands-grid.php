<?php
/**
 * WooCommerce brands grid shortcode.
 *
 * @package betheme-child
 */

/**
 * Display all WooCommerce product brands in a responsive grid.
 *
 * Usage:
 * [brands_grid]
 * [brands_grid columns="5" hide_empty="false" show_title="true"]
 * [brands_grid search="false"]
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function mfn_child_brands_grid_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'columns'    => '4',
			'hide_empty' => 'true',
			'orderby'    => 'name',
			'order'      => 'ASC',
			'show_title' => 'true',
			'show_logo'  => 'true',
			'search'     => 'true',
		),
		$atts,
		'brands_grid'
	);

	if ( ! class_exists( 'WooCommerce' ) || ! taxonomy_exists( 'product_brand' ) ) {
		return '';
	}

	$columns = max( 1, min( 6, absint( $atts['columns'] ) ) );
	$orderby = sanitize_key( $atts['orderby'] );
	$allowed_orderby = array( 'name', 'slug', 'term_id', 'id', 'count', 'term_group', 'description' );

	if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
		$orderby = 'name';
	}

	$order = 'DESC' === strtoupper( sanitize_text_field( $atts['order'] ) ) ? 'DESC' : 'ASC';
	$show_title  = filter_var( $atts['show_title'], FILTER_VALIDATE_BOOLEAN );
	$show_logo   = filter_var( $atts['show_logo'], FILTER_VALIDATE_BOOLEAN );
	$show_search = filter_var( $atts['search'], FILTER_VALIDATE_BOOLEAN );

	$brands = get_terms(
		array(
			'taxonomy'   => 'product_brand',
			'hide_empty' => filter_var( $atts['hide_empty'], FILTER_VALIDATE_BOOLEAN ),
			'orderby'    => $orderby,
			'order'      => $order,
		)
	);

	if ( is_wp_error( $brands ) || empty( $brands ) ) {
		return '';
	}

	$stylesheet_path = get_stylesheet_directory() . '/css/brands-grid.css';

	wp_enqueue_style(
		'betheme-child-brands-grid',
		get_stylesheet_directory_uri() . '/css/brands-grid.css',
		array( 'child-style' ),
		file_exists( $stylesheet_path ) ? filemtime( $stylesheet_path ) : null
	);

	if ( $show_search ) {
		$script_path = get_stylesheet_directory() . '/js/brands-grid.js';

		wp_enqueue_script(
			'betheme-child-brands-grid',
			get_stylesheet_directory_uri() . '/js/brands-grid.js',
			array(),
			file_exists( $script_path ) ? filemtime( $script_path ) : null,
			true
		);
	}

	$search_id = wp_unique_id( 'wc-brands-grid-search-' );
	$grid_id   = wp_unique_id( 'wc-brands-grid-' );

	ob_start();
	?>
	<div class="wc-brands-grid__wrapper" data-wc-brands-grid>
		<?php if ( $show_search ) : ?>
			<div class="wc-brands-grid__toolbar">
				<label class="wc-brands-grid__search-label" for="<?php echo esc_attr( $search_id ); ?>">
					<?php esc_html_e( 'Find a brand', 'betheme-child' ); ?>
				</label>
				<div class="wc-brands-grid__search-control">
					<input class="wc-brands-grid__search" type="search" id="<?php echo esc_attr( $search_id ); ?>" aria-controls="<?php echo esc_attr( $grid_id ); ?>" placeholder="<?php echo esc_attr__( 'Search brands...', 'betheme-child' ); ?>" autocomplete="off">
					<span class="wc-brands-grid__search-icon" aria-hidden="true"></span>
				</div>
			</div>
			<p class="wc-brands-grid__no-results" role="status" hidden><?php esc_html_e( 'No brands match your search.', 'betheme-child' ); ?></p>
		<?php endif; ?>

		<ul id="<?php echo esc_attr( $grid_id ); ?>" class="wc-brands-grid wc-brands-grid--columns-<?php echo esc_attr( $columns ); ?>">
		<?php foreach ( $brands as $brand ) : ?>
			<?php
			$brand_link = get_term_link( $brand, 'product_brand' );

			if ( is_wp_error( $brand_link ) ) {
				continue;
			}

			$brand_name = $brand->name;
			$logo_url   = '';

			if ( $show_logo && function_exists( 'wc_get_brand_thumbnail_url' ) ) {
				$logo_url = wc_get_brand_thumbnail_url( $brand->term_id, 'full' );
			}
			?>
			<li class="wc-brands-grid__item" data-brand-name="<?php echo esc_attr( $brand_name ); ?>">
				<a class="wc-brands-grid__card" href="<?php echo esc_url( $brand_link ); ?>" aria-label="<?php echo esc_attr( $brand_name ); ?>">
					<?php if ( $show_logo ) : ?>
						<span class="wc-brands-grid__logo">
							<?php if ( $logo_url ) : ?>
								<img class="wc-brands-grid__image" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $brand_name ); ?>" loading="lazy" decoding="async">
							<?php else : ?>
								<span class="wc-brands-grid__logo-fallback"><?php echo esc_html( $brand_name ); ?></span>
							<?php endif; ?>
						</span>
					<?php endif; ?>

					<?php if ( $show_title ) : ?>
						<span class="wc-brands-grid__title"><?php echo esc_html( $brand_name ); ?></span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php

	return ob_get_clean();
}
