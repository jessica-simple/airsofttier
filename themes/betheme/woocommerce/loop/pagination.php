<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/pagination.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total   = isset( $total ) ? $total : wc_get_loop_prop( 'total_pages' );
$current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
$base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
$format  = isset( $format ) ? $format : '';

if ( $total <= 1 ) {
	return;
}

$class = '';
$pagination_type = apply_filters( 'mfn_item_shop_pagination', '' );

if( mfn_opts_get( 'shop-infinite-load' ) ){
	$class = 'mfn-infinite-load-button';
}

?>
<div class="column one pager_wrapper">
	<div class="pager">

		<?php if( 'load_more' == $pagination_type || mfn_opts_get('shop-infinite-load') ): ?>

			<div class="<?php echo esc_attr($class); ?>">
				<?php
				if ($current < $total) {

					$next = $current + 1;
					$next = str_replace('%#%', $next, $base);

					$translate['load-more'] = mfn_opts_get('translate') ? mfn_opts_get('translate-load-more', 'Load more') : __('Load more', 'betheme');

					echo '<div class="column one pager_wrapper pager_lm">';
						echo '<a class="pager_load_more button has-icon" href="'. esc_url($next).'">';
							echo '<span class="button_icon"><i class="icon-layout" aria-hidden="true"></i></span>';
							echo '<span class="button_label">'. esc_html($translate['load-more']) .'</span>';
						echo'</a>';
					echo '</div>';
				}
				?>
			</div>

		<?php else: ?>

			<div class="pages">
				<?php
					echo paginate_links( apply_filters( 'woocommerce_pagination_args', array( // WPCS: XSS ok.
						'base'         => $base,
						'format'       => $format,
						'add_args'     => false,
						'current'      => max( 1, $current ),
						'total'        => $total,
						'prev_next'    => false,
						'type'         => 'plain',
						'end_size'     => 3,
						'mid_size'     => 3,
					) ) );
				?>
			</div>

		<?php endif; ?>

	</div>
</div>
