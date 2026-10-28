<?php
/** Invalid cart item handling and checkout handoff. */
defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Invalid_Cart {
	const SESSION_KEY = 'airsoft_invalid_cart_backup';

	public static function init() {
		add_filter( 'woocommerce_cart_item_visible', array( __CLASS__, 'hide_sold_out_items_from_active_list' ), 10, 3 );
		add_action( 'woocommerce_cart_contents', array( __CLASS__, 'render_sold_out_items' ) );

		if ( did_action( 'woocommerce_blocks_loaded' ) ) {
			self::register_store_api_extension();
		} else {
			add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'register_store_api_extension' ) );
		}

		add_action( 'template_redirect', array( __CLASS__, 'handle_cart_action' ), 1 );
		add_action( 'template_redirect', array( __CLASS__, 'restore_sold_out_items' ), 2 );
		add_action( 'woocommerce_thankyou', array( __CLASS__, 'clear_backup_after_order' ), 10, 1 );
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'clear_backup_after_order' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'clear_backup_after_order' ) );
		add_action( 'woocommerce_order_status_on-hold', array( __CLASS__, 'clear_backup_after_order' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 25 );
	}

	public static function hide_sold_out_items_from_active_list( $visible, $cart_item, $cart_item_key ) {
		if ( ! function_exists( 'is_cart' ) || ! is_cart() || empty( $cart_item['data'] ) || ! is_a( $cart_item['data'], 'WC_Product' ) ) {
			return $visible;
		}

		return $cart_item['data']->is_in_stock() ? $visible : false;
	}

	public static function render_sold_out_items() {
		if ( ! function_exists( 'is_cart' ) || ! is_cart() || ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$sold_out_items = array();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
			if ( $product instanceof WC_Product && $product->exists() && ! $product->is_in_stock() && ! empty( $cart_item['quantity'] ) ) {
				$sold_out_items[ $cart_item_key ] = $cart_item;
			}
		}

		if ( ! $sold_out_items ) {
			return;
		}

		echo '<tr class="cart-invalid-items-heading"><th colspan="6"><h2>' . esc_html__( 'Invalid items', 'product-manager' ) . '</h2></th></tr>';
		echo '<tr class="cart-invalid-items-actions"><td colspan="6"><p>' . esc_html__( 'Sold-out items will be removed when you continue to checkout.', 'product-manager' ) . '</p><a class="button cart-remove-invalid-items" href="' . esc_url( self::cart_action_url( 'remove_invalid' ) ) . '">' . esc_html__( 'Remove all invalid items', 'product-manager' ) . '</a></td></tr>';

		foreach ( $sold_out_items as $cart_item_key => $cart_item ) {
			$product    = $cart_item['data'];
			$product_id = isset( $cart_item['product_id'] ) ? absint( $cart_item['product_id'] ) : $product->get_id();
			$name       = $product->get_name();
			$thumbnail  = $product->get_image();
			$remove_link = sprintf(
				'<a role="button" href="%1$s" class="remove mfn-close-icon" aria-label="%2$s" data-product_id="%3$s" data-product_sku="%4$s"><span class="icon">&#10005;</span></a>',
				esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
				esc_attr( sprintf( __( 'Remove %s from cart', 'product-manager' ), $name ) ),
				esc_attr( $product_id ),
				esc_attr( $product->get_sku() )
			);
			?>
			<tr class="cart_item cart-invalid-item">
				<td class="product-thumbnail"><?php echo wp_kses_post( $thumbnail ); ?></td>
				<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'product-manager' ); ?>">
					<?php echo wp_kses_post( $name ); ?>
					<span class="cart-invalid-item__status"><?php esc_html_e( 'Sold out', 'product-manager' ); ?></span>
				</td>
				<td colspan="3" class="cart-invalid-item__spacer" aria-hidden="true"></td>
				<td class="product-remove"><?php echo wp_kses_post( $remove_link ); ?></td>
			</tr>
			<?php
		}
	}

	public static function register_store_api_extension() {
		if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
			return;
		}

		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => 'cart-item',
				'namespace'       => 'airsoft-cart',
				'schema_callback' => function () {
					return array(
						'sold_out' => array(
							'description' => __( 'Whether this cart item is sold out.', 'product-manager' ),
							'type'        => 'boolean',
						),
					);
				},
				'data_callback'   => function ( $cart_item ) {
					$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
					return array( 'sold_out' => $product instanceof WC_Product && ! $product->is_in_stock() );
				},
			)
		);
	}

	private static function cart_action_url( $action ) {
		return add_query_arg(
			array(
				'airsoft_cart_action' => sanitize_key( $action ),
				'_wpnonce'            => wp_create_nonce( 'airsoft_cart_action' ),
			),
			wc_get_cart_url()
		);
	}

	public static function handle_cart_action() {
		if ( ! function_exists( 'is_cart' ) || ! is_cart() || empty( $_GET['airsoft_cart_action'] ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		$action = sanitize_key( wp_unslash( $_GET['airsoft_cart_action'] ) );
		if ( ! in_array( $action, array( 'remove_invalid', 'checkout' ), true ) ) {
			return;
		}

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'airsoft_cart_action' ) ) {
			wc_add_notice( __( 'Your cart action expired. Please try again.', 'product-manager' ), 'error' );
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}

		if ( 'remove_invalid' === $action && WC()->session ) {
			WC()->session->__unset( self::SESSION_KEY );
		}

		$removed = 0;
		$backup  = array();
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
			if ( $product instanceof WC_Product && ! $product->is_in_stock() ) {
				if ( 'checkout' === $action ) {
					$backup[] = array(
						'product_id'     => absint( $cart_item['product_id'] ),
						'variation_id'   => isset( $cart_item['variation_id'] ) ? absint( $cart_item['variation_id'] ) : 0,
						'variation'      => isset( $cart_item['variation'] ) ? $cart_item['variation'] : array(),
						'quantity'       => max( 1, absint( $cart_item['quantity'] ) ),
						'cart_item_data' => array_diff_key(
							$cart_item,
							array_flip( array( 'key', 'product_id', 'variation_id', 'variation', 'quantity', 'data', 'line_tax_data', 'line_subtotal', 'line_subtotal_tax', 'line_total', 'line_tax' ) )
						),
					);
				}
				WC()->cart->remove_cart_item( $cart_item_key );
				++$removed;
			}
		}

		if ( 'checkout' === $action && WC()->session && $backup ) {
			WC()->session->set( self::SESSION_KEY, $backup );
		}
		WC()->cart->calculate_totals();

		if ( $removed ) {
			wc_add_notice(
				sprintf( _n( '%d sold-out item was removed from your cart.', '%d sold-out items were removed from your cart.', $removed, 'product-manager' ), $removed ),
				'notice'
			);
		}

		if ( 'checkout' === $action && ! WC()->cart->is_empty() ) {
			wp_safe_redirect( wc_get_checkout_url() );
			exit;
		}

		if ( 'checkout' === $action ) {
			wc_add_notice( __( 'Your cart has no available items. Continue shopping to find something in stock.', 'product-manager' ), 'notice' );
			wp_safe_redirect( wc_get_page_permalink( 'shop' ) );
			exit;
		}

		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}

	public static function restore_sold_out_items() {
		if ( ! function_exists( 'is_cart' ) || ! is_cart() || isset( $_GET['airsoft_cart_action'] ) || ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->session ) {
			return;
		}

		$backup = WC()->session->get( self::SESSION_KEY, array() );
		if ( empty( $backup ) || ! is_array( $backup ) ) {
			return;
		}

		$cart_contents = WC()->cart->get_cart_contents();
		foreach ( $backup as $item ) {
			$product_id     = isset( $item['product_id'] ) ? absint( $item['product_id'] ) : 0;
			$variation_id   = isset( $item['variation_id'] ) ? absint( $item['variation_id'] ) : 0;
			$variation      = isset( $item['variation'] ) && is_array( $item['variation'] ) ? $item['variation'] : array();
			$quantity       = isset( $item['quantity'] ) ? max( 1, absint( $item['quantity'] ) ) : 1;
			$cart_item_data = isset( $item['cart_item_data'] ) && is_array( $item['cart_item_data'] ) ? $item['cart_item_data'] : array();
			$product        = wc_get_product( $variation_id ? $variation_id : $product_id );

			if ( ! $product_id || ! $product ) {
				continue;
			}

			$cart_item_key = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );
			if ( isset( $cart_contents[ $cart_item_key ] ) ) {
				continue;
			}

			$cart_contents[ $cart_item_key ] = array_merge(
				$cart_item_data,
				array(
					'key'          => $cart_item_key,
					'product_id'   => $product_id,
					'variation_id' => $variation_id,
					'variation'    => $variation,
					'quantity'     => $quantity,
					'data'         => $product,
				)
			);
		}

		WC()->cart->set_cart_contents( $cart_contents );
		WC()->cart->calculate_totals();
		WC()->session->__unset( self::SESSION_KEY );
	}

	public static function clear_backup_after_order( $order_id = 0 ) {
		if ( ! function_exists( 'WC' ) || ! WC()->session || ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( $order && in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ), true ) ) {
			WC()->session->__unset( self::SESSION_KEY );
		}
	}

	public static function enqueue_assets() {
		if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
			return;
		}

		$script_path = AIRSOFT_PRODUCT_MANAGER_DIR . 'assets/js/invalid-cart-items.js';
		$style_path  = AIRSOFT_PRODUCT_MANAGER_DIR . 'assets/css/invalid-cart-items.css';
		wp_enqueue_script( 'airsoft-invalid-cart-items', AIRSOFT_PRODUCT_MANAGER_URL . 'assets/js/invalid-cart-items.js', array(), file_exists( $script_path ) ? filemtime( $script_path ) : '1.0.0', true );
		wp_localize_script(
			'airsoft-invalid-cart-items',
			'airsoftInvalidCart',
			array(
				'shopUrl'            => wc_get_page_permalink( 'shop' ),
				'checkoutUrl'        => self::cart_action_url( 'checkout' ),
				'normalCheckoutUrl'  => wc_get_checkout_url(),
				'removeAllUrl'       => self::cart_action_url( 'remove_invalid' ),
				'soldOutCheckoutNote' => __( 'Sold-out items will be removed when you continue to checkout.', 'product-manager' ),
				'continueShopping'   => __( 'Continue shopping', 'product-manager' ),
				'removeAllInvalid'   => __( 'Remove all invalid items', 'product-manager' ),
			)
		);
		wp_enqueue_style( 'airsoft-invalid-cart-items', AIRSOFT_PRODUCT_MANAGER_URL . 'assets/css/invalid-cart-items.css', array(), file_exists( $style_path ) ? filemtime( $style_path ) : '1.0.0' );
	}
}
