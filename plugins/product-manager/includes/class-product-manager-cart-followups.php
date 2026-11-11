<?php
/**
 * Tracks inactive WooCommerce carts for store-admin follow-up.
 */

defined( 'ABSPATH' ) || exit;

class Airsoft_Product_Manager_Cart_Followups {
	const PAGE_SLUG       = 'product-manager-cart-followups';
	const SETTINGS_OPTION = 'product_manager_cart_followup_settings';
	const DB_VERSION      = '1.0';
	const CRON_HOOK        = 'product_manager_scan_abandoned_carts';
	const CLEANUP_HOOK     = 'product_manager_cleanup_cart_followups';
	const PAGE_SIZE        = 50;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'maybe_install_table' ) );
		add_action( 'admin_post_product_manager_dismiss_all_cart_followups', array( __CLASS__, 'dismiss_all' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_post_product_manager_cart_followup_action', array( __CLASS__, 'handle_row_action' ) );
		add_action( 'admin_post_product_manager_save_cart_followup_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'woocommerce_add_to_cart', array( __CLASS__, 'capture_cart' ), 20 );
		add_action( 'woocommerce_cart_updated', array( __CLASS__, 'capture_cart' ), 20 );
		add_action( 'woocommerce_cart_item_removed', array( __CLASS__, 'capture_cart' ), 20 );
		add_action( 'woocommerce_cart_item_restored', array( __CLASS__, 'capture_cart' ), 20 );
		add_action( 'woocommerce_cart_item_set_quantity', array( __CLASS__, 'capture_cart' ), 20 );
		add_action( 'woocommerce_cart_emptied', array( __CLASS__, 'mark_cart_cleared' ) );
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'mark_cart_converted' ), 20, 1 );
		add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'mark_cart_converted' ), 20, 1 );
		add_action( self::CRON_HOOK, array( __CLASS__, 'send_abandoned_cart_digest' ) );
		add_action( self::CLEANUP_HOOK, array( __CLASS__, 'cleanup_old_records' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_schedules' ) );

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 300, 'pm_fifteen_minutes', self::CRON_HOOK );
		}
		if ( ! wp_next_scheduled( self::CLEANUP_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CLEANUP_HOOK );
		}
	}

	public static function activate() {
		self::create_table();
		add_filter( 'cron_schedules', array( __CLASS__, 'add_cron_schedules' ) );
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 300, 'pm_fifteen_minutes', self::CRON_HOOK );
		}
		if ( ! wp_next_scheduled( self::CLEANUP_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CLEANUP_HOOK );
		}
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
		wp_clear_scheduled_hook( self::CLEANUP_HOOK );
	}

	public static function add_cron_schedules( $schedules ) {
		$schedules['pm_fifteen_minutes'] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => __( 'Every 15 minutes', 'product-manager' ),
		);
		return $schedules;
	}

	public static function maybe_install_table() {
		if ( get_option( 'product_manager_cart_followups_db_version' ) !== self::DB_VERSION ) {
			self::create_table();
		}
	}

	private static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'pm_cart_followups';
	}

	private static function create_table() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$table_name      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();
		$sql             = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			cart_key char(64) NOT NULL,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			customer_email varchar(190) NOT NULL DEFAULT '',
			cart_data longtext NOT NULL,
			cart_total decimal(19,4) NOT NULL DEFAULT 0,
			currency varchar(10) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'active',
			last_activity datetime NOT NULL,
			admin_notified_at datetime NULL DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY cart_key (cart_key),
			KEY status_activity (status,last_activity),
			KEY user_id (user_id)
		) {$charset_collate};";
		dbDelta( $sql );
		update_option( 'product_manager_cart_followups_db_version', self::DB_VERSION );
	}

	public static function register_menu() {
		add_submenu_page(
			'product-manager-categories',
			__( 'Cart Follow-ups', 'product-manager' ),
			__( 'Cart Follow-ups', 'product-manager' ),
			'manage_woocommerce',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	private static function can_manage() {
		return current_user_can( 'manage_woocommerce' );
	}

	public static function capture_cart() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->session ) {
			return;
		}

		$session_id = WC()->session->get_customer_id();
		if ( ! $session_id ) {
			return;
		}

		$cart_data = array();
		foreach ( WC()->cart->get_cart() as $item ) {
			$product = isset( $item['data'] ) ? $item['data'] : null;
			if ( ! $product instanceof WC_Product ) {
				continue;
			}
			$cart_data[] = array(
				'product_id'   => absint( $item['product_id'] ),
				'variation_id' => absint( $item['variation_id'] ),
				'name'         => wp_strip_all_tags( $product->get_name() ),
				'quantity'     => max( 1, absint( $item['quantity'] ) ),
				'line_total'   => isset( $item['line_total'] ) ? (float) $item['line_total'] : 0,
			);
		}

		if ( empty( $cart_data ) ) {
			return;
		}

		$user_id = get_current_user_id();
		$email   = '';
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			$email = $user ? sanitize_email( $user->user_email ) : '';
		} elseif ( WC()->customer && is_callable( array( WC()->customer, 'get_billing_email' ) ) ) {
			$email = sanitize_email( WC()->customer->get_billing_email() );
		}

		global $wpdb;
		$table_name = self::table_name();
		$cart_key   = hash( 'sha256', (string) $session_id );
		$now        = current_time( 'mysql', true );
		$cart_total = WC()->cart->get_total( 'edit' );
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table_name} (cart_key,user_id,customer_email,cart_data,cart_total,currency,status,last_activity,created_at,updated_at)
				VALUES (%s,%d,%s,%s,%f,%s,'active',%s,%s,%s)
				ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),customer_email=IF(VALUES(customer_email)='',customer_email,VALUES(customer_email)),cart_data=VALUES(cart_data),cart_total=VALUES(cart_total),currency=VALUES(currency),status=IF(status IN ('converted','cleared'), 'active', status),last_activity=VALUES(last_activity),updated_at=VALUES(updated_at)",
				$cart_key,
				$user_id,
				$email,
				wp_json_encode( $cart_data ),
				(float) $cart_total,
				get_woocommerce_currency(),
				$now,
				$now,
				$now
			)
		);
	}

	public static function mark_cart_cleared() {
		self::set_current_cart_status( 'cleared' );
	}

	public static function mark_cart_converted( $order_id ) {
		self::set_current_cart_status( 'converted' );
	}

	private static function set_current_cart_status( $status ) {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return;
		}
		$session_id = WC()->session->get_customer_id();
		if ( ! $session_id ) {
			return;
		}
		global $wpdb;
		if ( 'cleared' === $status ) {
			$wpdb->query(
				$wpdb->prepare(
					'UPDATE ' . self::table_name() . " SET status='cleared',updated_at=%s WHERE cart_key=%s AND status <> 'converted'",
					current_time( 'mysql', true ),
					hash( 'sha256', (string) $session_id )
				)
			);
			return;
		}
		$wpdb->update(
			self::table_name(),
			array( 'status' => $status, 'updated_at' => current_time( 'mysql', true ) ),
			array( 'cart_key' => hash( 'sha256', (string) $session_id ) ),
			array( '%s', '%s' ),
			array( '%s' )
		);
	}

	public static function send_abandoned_cart_digest() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}
		$settings = self::get_settings();
		$delay    = max( 15, absint( $settings['delay_minutes'] ) );
		$cutoff   = gmdate( 'Y-m-d H:i:s', time() - ( $delay * MINUTE_IN_SECONDS ) );
		global $wpdb;
		$table_name = self::table_name();
		$rows       = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE status='active' AND admin_notified_at IS NULL AND last_activity <= %s ORDER BY last_activity ASC LIMIT 100",
				$cutoff
			)
		);
		if ( empty( $rows ) ) {
			return;
		}

		$items = array();
		foreach ( $rows as $row ) {
			$customer = $row->user_id ? sprintf( 'User #%d', $row->user_id ) : 'Guest customer';
			if ( $row->customer_email ) {
				$customer .= ' (' . $row->customer_email . ')';
			}
			$products = array();
			foreach ( (array) json_decode( $row->cart_data, true ) as $product ) {
				$products[] = sprintf( '%s × %d', $product['name'], $product['quantity'] );
			}
			$items[] = sprintf(
				"- %s\n  Items: %s\n  Cart value: %s %s\n  Last active: %s UTC",
				$customer,
				implode( ', ', $products ),
				$row->currency,
				$row->cart_total,
				$row->last_activity
			);
		}

		$admin_url = admin_url( 'admin.php?page=' . self::PAGE_SLUG );
		$subject   = sprintf( __( '%d cart(s) need follow-up', 'product-manager' ), count( $rows ) );
		$message   = __( "The following carts have been inactive past your follow-up delay:\n\n", 'product-manager' );
		$message  .= implode( "\n\n", $items );
		$message  .= "\n\n" . sprintf( __( 'Review carts in WordPress admin: %s', 'product-manager' ), $admin_url );
		$sent      = Airsoft_Product_Manager_Reseller_Emails::send( $settings['email'], $subject, $message );
		if ( $sent ) {
			$ids = array_map( 'absint', wp_list_pluck( $rows, 'id' ) );
			$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
			$wpdb->query(
				$wpdb->prepare(
					'UPDATE ' . self::table_name() . " SET admin_notified_at=%s WHERE id IN ({$placeholders})",
					array_merge( array( current_time( 'mysql', true ) ), $ids )
				)
			);
		}
	}

	public static function cleanup_old_records() {
		global $wpdb;
		$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( 90 * DAY_IN_SECONDS ) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::table_name() . ' WHERE updated_at < %s', $cutoff ) );
	}

	public static function render_page() {
		if ( ! self::can_manage() ) {
			wp_die( esc_html__( 'You are not allowed to manage cart follow-ups.', 'product-manager' ) );
		}
		$settings = self::get_settings();
		$delays   = self::delay_options();
		$custom   = ! isset( $delays[ $settings['delay_minutes'] ] );
		$status   = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'active';
		$allowed  = array( 'active', 'contacted', 'dismissed', 'converted', 'cleared', 'all' );
		if ( ! in_array( $status, $allowed, true ) ) {
			$status = 'active';
		}
		$paged = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
		$rows  = self::get_rows( $status, $paged );
		$total = self::count_rows( $status );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Cart Follow-ups', 'product-manager' ); ?></h1>
			<p><?php esc_html_e( 'Review carts that have been inactive and track customer follow-up.', 'product-manager' ); ?></p>
			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Cart follow-up settings saved.', 'product-manager' ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['dismissed_count'] ) ) : ?>
				<?php $dismissed_count = absint( wp_unslash( $_GET['dismissed_count'] ) ); ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php echo esc_html( sprintf( _n( '%s active cart dismissed.', '%s active carts dismissed.', $dismissed_count, 'product-manager' ), number_format_i18n( $dismissed_count ) ) ); ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::PAGE_SLUG, 'status' => 'dismissed' ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'View dismissed carts', 'product-manager' ); ?></a>
				</p></div>
			<?php endif; ?>
			<h2><?php esc_html_e( 'Carts', 'product-manager' ); ?></h2>
			<div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;margin-bottom:12px">
			<nav aria-label="<?php esc_attr_e( 'Cart status', 'product-manager' ); ?>">
				<ul class="subsubsub" style="margin:0">
					<?php foreach ( $allowed as $filter ) : ?>
						<li>
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => self::PAGE_SLUG, 'status' => $filter ), admin_url( 'admin.php' ) ) ); ?>" <?php echo $status === $filter ? 'class="current" aria-current="page"' : ''; ?>>
								<?php echo esc_html( ucfirst( $filter ) ); ?>
								<?php if ( 'active' === $filter ) : ?>
									<span class="count">(<?php echo esc_html( number_format_i18n( 'active' === $status ? $total : self::count_rows( 'active' ) ) ); ?>)</span>
								<?php endif; ?>
							</a>
							<?php echo 'all' === $filter ? '' : ' | '; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<?php if ( 'active' === $status ) : ?>
				<?php
				$confirmation = sprintf( _n( 'Dismiss %s active cart?', 'Dismiss all %s active carts?', $total, 'product-manager' ), number_format_i18n( $total ) );
				?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="text-align:right;margin:8px 0" onsubmit="return confirm('<?php echo esc_js( $confirmation ); ?>');">
					<input type="hidden" name="action" value="product_manager_dismiss_all_cart_followups">
					<?php wp_nonce_field( 'product_manager_dismiss_all_cart_followups' ); ?>
					<button class="button" type="submit" <?php disabled( 0 === $total ); ?>><?php esc_html_e( 'Dismiss all active carts', 'product-manager' ); ?></button>
				</form>
			<?php endif; ?>
			</div>
			<?php if ( empty( $rows ) ) : ?>
				<p style="padding:20px;background:#fff;border:1px solid #c3c4c7"><?php echo esc_html( 'active' === $status && 0 === $total ? __( 'No active carts to follow up.', 'product-manager' ) : __( 'No carts found on this page.', 'product-manager' ) ); ?></p>
			<?php else : ?>
			<table class="widefat striped"><thead><tr>
				<th><?php esc_html_e( 'Customer', 'product-manager' ); ?></th><th><?php esc_html_e( 'Cart items', 'product-manager' ); ?></th><th><?php esc_html_e( 'Cart value', 'product-manager' ); ?></th><th><?php esc_html_e( 'Last active (UTC)', 'product-manager' ); ?></th><th><?php esc_html_e( 'Status', 'product-manager' ); ?></th><th><?php esc_html_e( 'Actions', 'product-manager' ); ?></th>
			</tr></thead><tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><strong><?php echo esc_html( self::customer_label( $row, false ) ); ?></strong><?php if ( $row->customer_email ) : ?><br><?php echo esc_html( $row->customer_email ); ?><?php endif; ?></td>
						<td><?php echo wp_kses_post( self::format_cart_items( $row->cart_data, true ) ); ?></td>
						<td><strong><?php echo esc_html( $row->currency . ' ' . wc_format_decimal( $row->cart_total, 2 ) ); ?></strong></td>
						<td>
							<details>
								<summary><?php echo esc_html( sprintf( __( '%s ago', 'product-manager' ), human_time_diff( strtotime( $row->last_activity . ' UTC' ), time() ) ) ); ?></summary>
								<?php echo esc_html( $row->last_activity . ' UTC' ); ?>
							</details>
						</td>
						<td><?php echo esc_html( ucfirst( $row->status ) ); ?><?php if ( $row->admin_notified_at ) : ?><br><small><?php echo esc_html( sprintf( __( 'Emailed %s UTC', 'product-manager' ), $row->admin_notified_at ) ); ?></small><?php endif; ?></td>
						<td><?php self::render_actions( $row ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody></table>
			<?php endif; ?>
			<?php self::render_pagination( $status, $paged, $total ); ?>
			<details style="margin-top:24px;padding:14px;background:#fff;border:1px solid #c3c4c7" <?php echo isset( $_GET['updated'] ) ? 'open' : ''; ?>>
				<summary style="cursor:pointer">
					<strong><?php esc_html_e( 'Notification settings', 'product-manager' ); ?></strong>
					<span><?php echo esc_html( sprintf(
						/* translators: 1: inactivity delay, 2: notification email address. */
						__( 'Email after %1$s of inactivity · %2$s', 'product-manager' ),
						$delays[ $settings['delay_minutes'] ] ?? sprintf( __( '%s minutes', 'product-manager' ), number_format_i18n( $settings['delay_minutes'] ) ),
						$settings['email']
					) ); ?></span>
				</summary>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="product_manager_save_cart_followup_settings">
				<?php wp_nonce_field( 'product_manager_save_cart_followup_settings' ); ?>
				<table class="form-table" role="presentation"><tbody>
					<tr><th scope="row"><label for="cart-followup-email"><?php esc_html_e( 'Notification email', 'product-manager' ); ?></label></th><td><input id="cart-followup-email" type="email" class="regular-text" name="email" value="<?php echo esc_attr( $settings['email'] ); ?>" required></td></tr>
					<tr>
						<th scope="row" id="cart-followup-delay-label"><?php esc_html_e( 'Inactive for', 'product-manager' ); ?></th>
						<td>
							<fieldset aria-labelledby="cart-followup-delay-label" onchange="var custom = this.querySelector('input[name=delay_minutes]:checked').value === 'custom'; var fields = this.querySelector('#cart-followup-custom'); fields.hidden = !custom; fields.querySelectorAll('input, select').forEach(function (field) { field.disabled = !custom; });">
								<?php foreach ( $delays as $minutes => $label ) : ?>
									<label style="display:inline-block;margin:0 12px 6px 0"><input type="radio" name="delay_minutes" value="<?php echo esc_attr( $minutes ); ?>" <?php checked( $settings['delay_minutes'], $minutes ); ?>> <?php echo esc_html( $label ); ?></label>
								<?php endforeach; ?>
								<label><input type="radio" name="delay_minutes" value="custom" <?php checked( $custom ); ?>> <?php esc_html_e( 'Custom', 'product-manager' ); ?></label>
								<?php
								$units = self::delay_units();
								$unit  = 1;
								foreach ( array_keys( $units ) as $factor ) {
									if ( 0 === $settings['delay_minutes'] % $factor ) {
										$unit = $factor;
									}
								}
								?>
								<div id="cart-followup-custom" style="margin-top:8px" <?php echo $custom ? '' : 'hidden'; ?>>
									<label for="cart-followup-custom-delay"><?php esc_html_e( 'Delay', 'product-manager' ); ?></label>
									<input id="cart-followup-custom-delay" class="small-text" type="number" name="custom_delay_minutes" min="1" max="43200" step="1" required value="<?php echo esc_attr( intdiv( $settings['delay_minutes'], $unit ) ); ?>" <?php disabled( ! $custom ); ?>>
									<select name="custom_delay_unit" aria-label="<?php esc_attr_e( 'Delay unit', 'product-manager' ); ?>" <?php disabled( ! $custom ); ?>>
										<?php foreach ( $units as $factor => $label ) : ?>
											<option value="<?php echo esc_attr( $factor ); ?>" <?php selected( $unit, $factor ); ?>><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>
									</select>
									<p class="description"><?php esc_html_e( 'Enter a delay from 15 minutes to 30 days.', 'product-manager' ); ?></p>
								</div>
							</fieldset>
						</td>
					</tr>
				</tbody></table>
				<?php submit_button( __( 'Save notification settings', 'product-manager' ) ); ?>
			</form>
			</details>
		</div>
		<?php
	}

	private static function get_rows( $status, $paged ) {
		global $wpdb;
		$table_name = self::table_name();
		$offset     = ( $paged - 1 ) * self::PAGE_SIZE;
		if ( 'all' === $status ) {
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} ORDER BY last_activity DESC LIMIT %d OFFSET %d", self::PAGE_SIZE, $offset ) );
		}
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE status=%s ORDER BY last_activity DESC LIMIT %d OFFSET %d", $status, self::PAGE_SIZE, $offset ) );
	}

	private static function customer_label( $row, $include_email = true ) {
		if ( $row->user_id ) {
			$user = get_userdata( $row->user_id );
			$name = $user ? $user->display_name : sprintf( __( 'User #%d', 'product-manager' ), $row->user_id );
		} else {
			$name = __( 'Guest', 'product-manager' );
		}
		if ( $include_email && $row->customer_email ) {
			$name .= ' — ' . $row->customer_email;
		}
		return $name;
	}

	private static function format_cart_items( $json, $preview = false ) {
		$items = json_decode( $json, true );
		if ( ! is_array( $items ) ) {
			return '';
		}
		$items = array_values( $items );
		$count = count( $items );
		$output = '<ul style="margin:0">';
		foreach ( $items as $index => $item ) {
			if ( $preview && $count > 2 && 2 === $index ) {
				$output .= '</ul><details><summary>' . esc_html( sprintf( __( 'Show all %s items', 'product-manager' ), number_format_i18n( $count ) ) ) . '</summary><ul style="margin:0">';
			}
			$name     = isset( $item['name'] ) ? $item['name'] : __( 'Product', 'product-manager' );
			$quantity = isset( $item['quantity'] ) ? absint( $item['quantity'] ) : 0;
			$output  .= '<li>' . esc_html( $name . ' × ' . $quantity ) . '</li>';
		}
		return $output . ( $preview && $count > 2 ? '</ul></details>' : '</ul>' );
	}

	private static function render_actions( $row ) {
		if ( $row->customer_email ) {
			echo '<a class="button button-small" href="' . esc_url( 'mailto:' . $row->customer_email ) . '">' . esc_html__( 'Email customer', 'product-manager' ) . '</a> ';
		}
		if ( ! in_array( $row->status, array( 'converted', 'dismissed', 'cleared' ), true ) ) {
			self::render_action_button( $row->id, 'contacted', __( 'Mark contacted', 'product-manager' ) );
			echo '<details><summary>' . esc_html__( 'More actions', 'product-manager' ) . '</summary>';
			self::render_action_button( $row->id, 'dismissed', __( 'Dismiss', 'product-manager' ) );
			echo '</details>';
		}
	}

	private static function render_action_button( $id, $status, $label ) {
		?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin:2px 0">
			<input type="hidden" name="action" value="product_manager_cart_followup_action">
			<input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>">
			<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>">
			<?php wp_nonce_field( 'product_manager_cart_followup_' . absint( $id ) ); ?>
			<button class="button button-small" type="submit"><?php echo esc_html( $label ); ?></button>
		</form>
		<?php
	}

	public static function handle_row_action() {
		if ( ! self::can_manage() ) {
			wp_die( esc_html__( 'You are not allowed to manage cart follow-ups.', 'product-manager' ) );
		}
		$id     = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		$status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';
		check_admin_referer( 'product_manager_cart_followup_' . $id );
		if ( $id && in_array( $status, array( 'contacted', 'dismissed' ), true ) ) {
			global $wpdb;
			$wpdb->update( self::table_name(), array( 'status' => $status, 'updated_at' => current_time( 'mysql', true ) ), array( 'id' => $id ), array( '%s', '%s' ), array( '%d' ) );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) );
		exit;
	}

	public static function dismiss_all() {
		if ( ! self::can_manage() ) {
			wp_die( esc_html__( 'You are not allowed to manage cart follow-ups.', 'product-manager' ) );
		}
		check_admin_referer( 'product_manager_dismiss_all_cart_followups' );

		global $wpdb;
		$result = $wpdb->update(
			self::table_name(),
			array( 'status' => 'dismissed', 'updated_at' => current_time( 'mysql', true ) ),
			array( 'status' => 'active' ),
			array( '%s', '%s' ),
			array( '%s' )
		);
		if ( false === $result ) {
			wp_die( esc_html__( 'Could not dismiss cart follow-ups.', 'product-manager' ) );
		}

		wp_safe_redirect( add_query_arg( array( 'page' => self::PAGE_SLUG, 'status' => 'dismissed', 'dismissed_count' => $result ), admin_url( 'admin.php' ) ) );
		exit;
	}

	private static function delay_options() {
		return array(
			1440  => __( '1 day', 'product-manager' ),
			10080 => __( '1 week', 'product-manager' ),
			43200 => __( '1 month (30 days)', 'product-manager' ),
		);
	}

	private static function delay_units() {
		return array(
			1    => __( 'Minutes', 'product-manager' ),
			60   => __( 'Hours', 'product-manager' ),
			1440 => __( 'Days', 'product-manager' ),
		);
	}

	public static function save_settings() {
		if ( ! self::can_manage() ) {
			wp_die( esc_html__( 'You are not allowed to manage cart follow-ups.', 'product-manager' ) );
		}
		check_admin_referer( 'product_manager_save_cart_followup_settings' );
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			$email = get_option( 'admin_email' );
		}
		$choice = isset( $_POST['delay_minutes'] ) ? sanitize_key( wp_unslash( $_POST['delay_minutes'] ) ) : '';
		$delay  = absint( $choice );
		if ( 'custom' === $choice ) {
			$value = isset( $_POST['custom_delay_minutes'] ) ? wp_unslash( $_POST['custom_delay_minutes'] ) : '';
			$unit  = isset( $_POST['custom_delay_unit'] ) ? absint( wp_unslash( $_POST['custom_delay_unit'] ) ) : 0;
			$value = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1, 'max_range' => 43200 ) ) );
			$delay = false !== $value && isset( self::delay_units()[ $unit ] ) ? $value * $unit : false;
			if ( $delay < 15 || $delay > 43200 ) {
				$delay = false;
			}
		} elseif ( ! isset( self::delay_options()[ $delay ] ) ) {
			$delay = false;
		}
		if ( false === $delay ) {
			wp_die( esc_html__( 'Choose a preset or enter a custom delay between 15 and 43,200 minutes.', 'product-manager' ) );
		}

		update_option( self::SETTINGS_OPTION, array( 'email' => $email, 'delay_minutes' => $delay ) );
		wp_safe_redirect( add_query_arg( array( 'page' => self::PAGE_SLUG, 'updated' => 1 ), admin_url( 'admin.php' ) ) );
		exit;
	}

	private static function get_settings() {
		$settings = get_option( self::SETTINGS_OPTION, array() );
		return array(
			'email'         => ! empty( $settings['email'] ) && is_email( $settings['email'] ) ? $settings['email'] : get_option( 'admin_email' ),
			'delay_minutes' => isset( $settings['delay_minutes'] ) ? min( 43200, max( 15, absint( $settings['delay_minutes'] ) ) ) : 1440,
		);
	}

	private static function count_rows( $status ) {
		global $wpdb;
		$table_name = self::table_name();
		if ( 'all' === $status ) {
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
		}
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE status=%s", $status ) );
	}

	private static function render_pagination( $status, $paged, $total ) {
		$pages = (int) ceil( $total / self::PAGE_SIZE );
		if ( $pages < 2 ) {
			return;
		}
		echo '<div class="tablenav"><div class="tablenav-pages">';
		echo wp_kses_post( paginate_links( array(
			'base'      => add_query_arg( array( 'page' => self::PAGE_SLUG, 'status' => $status, 'paged' => '%#%' ), admin_url( 'admin.php' ) ),
			'format'    => '',
			'current'   => $paged,
			'total'     => $pages,
			'prev_text' => __( 'Previous', 'product-manager' ),
			'next_text' => __( 'Next', 'product-manager' ),
		) ) );
		echo '</div></div>';
	}
}
