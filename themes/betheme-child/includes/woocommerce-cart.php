<div class="mfn-cart-overlay"></div>

<div class="mfn-cart-holder woocommerce" tabindex="0" aria-expanded="false" aria-label="<?php esc_attr_e( 'shop cart', 'betheme' ); ?>" role="navigation">
	<div class="mfn-ch-row mfn-ch-header">
		<a href="#" class="toggle-mfn-cart close-mfn-cart mfn-close-icon" tabindex="0"><span class="icon">&#10005;</span></a>
		<h3>
			<?php
			$cart_icon = mfn_opts_get( 'shop-cart' );

			if ( $cart_icon ) {
				echo '<i class="' . esc_attr( trim( $cart_icon ) ) . '" aria-label="' . esc_attr__( 'cart icon', 'betheme' ) . '"></i>';
			} else {
				echo '<svg width="26" viewBox="0 0 26 26" aria-label="' . esc_attr__( 'cart icon', 'betheme' ) . '"><defs><style>.path{fill:none;stroke:#333;stroke-miterlimit:10;stroke-width:1.5px;}</style></defs><polygon class="path" points="20.4 20.4 5.6 20.4 6.83 10.53 19.17 10.53 20.4 20.4"/><path class="path" d="M9.3,10.53V9.3a3.7,3.7,0,1,1,7.4,0v1.23"/></svg>';
			}

			$cart_page_id = get_option( 'woocommerce_cart_page_id' );
			if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
				$cart_page_id = apply_filters( 'wpml_object_id', $cart_page_id, 'page' );
			}
			echo esc_html( get_the_title( $cart_page_id ) );
			?>
		</h3>
	</div>
	<div class="mfn-ch-row mfn-ch-content-wrapper">
		<div class="mfn-ch-row mfn-ch-content"></div>
	</div>
	<div class="mfn-ch-row mfn-ch-footer">
		<div class="mfn-ch-footer-totals"></div>
		<div class="mfn-ch-footer-buttons">
			<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="button button_full_width alt"><?php esc_html_e( 'Proceed to checkout', 'woocommerce' ); ?></a>

			<?php if ( ! empty( mfn_opts_get( 'shop-sidecart-continue-shopping' ) ) ) : ?>
				<div class="mfn-ch-footer-links">
					<a class="toggle-mfn-cart" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><i class="icon-left-open-mini"></i> <?php esc_html_e( 'Continue shopping', 'woocommerce' ); ?></a>
					<a class="mfn-child-view-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" tabindex="0"><?php esc_html_e( 'View cart', 'woocommerce' ); ?></a>
				</div>
			<?php else : ?>
				<a class="mfn-child-view-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" tabindex="0"><?php esc_html_e( 'View cart', 'woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>
