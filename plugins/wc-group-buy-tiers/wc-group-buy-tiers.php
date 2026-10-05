<?php
/**
 * Plugin Name: WooCommerce Group Buy & Tiered Pricing
 * Description: Adds countdown timers and sales-based tiered pricing to WooCommerce products.
 * Version: 1.0.0
 * Author: Antigravity
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

/**
 * Main Class for the Plugin
 */
class WC_Group_Buy_Tiers
{

	public function __construct()
	{
		// Admin Product Tabs/Fields
		add_action('woocommerce_product_options_general_product_data', array($this, 'add_group_buy_fields'));
		add_action('woocommerce_process_product_meta', array($this, 'save_group_buy_fields'));
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_assets'));

		// Price Filtering Logic
		add_filter('woocommerce_product_get_price', array($this, 'get_dynamic_price'), 10, 2);
		add_filter('woocommerce_product_variation_get_price', array($this, 'get_dynamic_price'), 10, 2);
		add_action('woocommerce_before_calculate_totals', array($this, 'apply_dynamic_price_to_cart'), 10, 1);

		// Frontend Displays
		add_action('woocommerce_single_product_summary', array($this, 'display_group_buy_info'), 25);
		add_shortcode('gb_deal_info', array($this, 'render_shortcode'));
		add_shortcode('gb_deal_list', array($this, 'render_list_shortcode'));
		add_shortcode('gb_deal_banner', array($this, 'render_banner_shortcode'));

		// Enqueue Scripts/Styles
		add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));

		// Fresh timer data for cached product pages/shortcodes.
		add_action('wp_ajax_gb_timer_data', array($this, 'ajax_timer_data'));
		add_action('wp_ajax_nopriv_gb_timer_data', array($this, 'ajax_timer_data'));
	}

	/**
	 * Add custom fields to WooCommerce Product Data > General tab
	 */
	public function add_group_buy_fields()
	{
		echo '<div class="options_group">';

		woocommerce_wp_checkbox(array(
			'id' => '_gb_enabled',
			'label' => __('Enable Group Buy', 'woocommerce'),
			'description' => __('Check this to enable tiered pricing and countdown.', 'woocommerce'),
		));

		woocommerce_wp_text_input(array(
			'id' => '_gb_expiry',
			'label' => __('Group Buy End Date', 'woocommerce'),
			'placeholder' => 'YYYY-MM-DD HH:MM',
			'description' => __('Select the date and time when the group buy ends.', 'woocommerce'),
			'class' => 'gb-flatpickr-input'
		));

		echo '<hr/>';
		echo '<h4>' . __('Pricing Tiers', 'woocommerce') . '</h4>';
		echo '<p>' . __('Define prices based on total sales count. If sales surpass a threshold, the corresponding price applies.', 'woocommerce') . '</p>';

		// Tier 1
		woocommerce_wp_text_input(array(
			'id' => '_gb_tier_1_qty',
			'label' => __('Tier 1 Min Sales', 'woocommerce'),
			'type' => 'number',
			'description' => __('Apply this price when sales >= this value.', 'woocommerce'),
		));
		woocommerce_wp_text_input(array(
			'id' => '_gb_tier_1_price',
			'label' => __('Tier 1 Price (RM)', 'woocommerce'),
			'type' => 'text',
		));

		// Tier 2
		woocommerce_wp_text_input(array(
			'id' => '_gb_tier_2_qty',
			'label' => __('Tier 2 Min Sales', 'woocommerce'),
			'type' => 'number',
		));
		woocommerce_wp_text_input(array(
			'id' => '_gb_tier_2_price',
			'label' => __('Tier 2 Price (RM)', 'woocommerce'),
			'type' => 'text',
		));

		// Tier 3
		woocommerce_wp_text_input(array(
			'id' => '_gb_tier_3_qty',
			'label' => __('Tier 3 Min Sales', 'woocommerce'),
			'type' => 'number',
		));
		woocommerce_wp_text_input(array(
			'id' => '_gb_tier_3_price',
			'label' => __('Tier 3 Price (RM)', 'woocommerce'),
			'type' => 'text',
		));

		echo '</div>';
	}

	/**
	 * Save the custom fields
	 */
	public function save_group_buy_fields($post_id)
	{
		$gb_enabled = isset($_POST['_gb_enabled']) ? 'yes' : 'no';
		update_post_meta($post_id, '_gb_enabled', $gb_enabled);

		$fields = array('_gb_expiry', '_gb_tier_1_qty', '_gb_tier_1_price', '_gb_tier_2_qty', '_gb_tier_2_price', '_gb_tier_3_qty', '_gb_tier_3_price');
		foreach ($fields as $field) {
			if (isset($_POST[$field])) {
				update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
			}
		}

		$this->purge_group_buy_cache($post_id);
	}

	/**
	 * Get the Current Tier Price based on Sales Count
	 */
	public function get_dynamic_price($price, $product)
	{
		if (!$product)
			return $price;

		$is_enabled = get_post_meta($product->get_id(), '_gb_enabled', true);
		if ($is_enabled !== 'yes')
			return $price;

		$expiry = get_post_meta($product->get_id(), '_gb_expiry', true);
		if ($this->is_deal_expired($expiry)) {
			return $price; // Group buy expired, revert to regular price
		}

		return $this->get_lowest_tier_price($product);
	}

	/**
	 * Force the dynamic price in the cart
	 */
	public function apply_dynamic_price_to_cart($cart)
	{
		if (is_admin() && !defined('DOING_AJAX'))
			return;

		foreach ($cart->get_cart() as $cart_item) {
			$product = $cart_item['data'];
			$dynamic_price = $this->get_dynamic_price($product->get_regular_price(), $product);
			$cart_item['data']->set_price($dynamic_price);
		}
	}

	public function ajax_timer_data()
	{
		$ids = isset($_GET['ids']) ? sanitize_text_field(wp_unslash($_GET['ids'])) : '';
		$ids = array_filter(array_map('absint', explode(',', $ids)));
		$ids = array_values(array_unique($ids));

		$data = array();
		foreach ($ids as $product_id) {
			if ('yes' !== get_post_meta($product_id, '_gb_enabled', true)) {
				continue;
			}

			$expiry = get_post_meta($product_id, '_gb_expiry', true);
			$expiry_ts = $this->get_expiry_timestamp($expiry);

			$data[$product_id] = array(
				'expiry' => $expiry,
				'expiry_ts' => $expiry_ts,
				'expired' => $this->is_deal_expired($expiry),
			);
		}

		wp_send_json_success(array(
			'server_ts' => time(),
			'products' => $data,
		));
	}

	private function purge_group_buy_cache($post_id)
	{
		clean_post_cache($post_id);

		if (function_exists('wc_delete_product_transients')) {
			wc_delete_product_transients($post_id);
		}

		if (function_exists('rocket_clean_post')) {
			rocket_clean_post($post_id);
		}

		if (function_exists('w3tc_flush_post')) {
			w3tc_flush_post($post_id);
		}

		if (function_exists('wpfc_clear_post_cache_by_id')) {
			wpfc_clear_post_cache_by_id($post_id);
		}

		do_action('litespeed_purge_post', $post_id);
		do_action('sg_cachepress_purge_post', $post_id);
		do_action('breeze_clear_all_cache');
	}

	/**
	 * Display Group Buy info on Single Product page
	 */
	public function display_group_buy_info()
	{
		global $product;
		if (!$product)
			return;
		echo $this->get_deal_html($product->get_id());
	}

	/**
	 * Shortcode Handler: [gb_deal_info id="123"]
	 */
	public function render_shortcode($atts)
	{
		$atts = shortcode_atts(array(
			'id' => get_the_ID(),
		), $atts, 'gb_deal_info');

		return $this->get_deal_html($atts['id']);
	}

	/**
	 * Shortcode Handler: [gb_deal_list limit="10" columns="3" theme="theme-1"]
	 */
	public function render_list_shortcode($atts)
	{
		$atts = shortcode_atts(array(
			'limit' => 10,
			'columns' => 3,
			'cat' => '',
			'theme' => 'theme-1',
			'timezone' => 'Asia/Singapore',
		), $atts, 'gb_deal_list');

		$theme = $this->normalize_list_theme($atts['theme']);
		$list_timezone = $this->get_valid_timezone($atts['timezone']);
		$limit = absint($atts['limit']);

		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'meta_query' => $this->get_enabled_deal_meta_query(),
		);

		if (!empty($atts['cat'])) {
			$args['product_cat'] = $atts['cat'];
		}

		$products = new WP_Query($args);
		if (!$products->have_posts())
			return '<p class="gb-no-deals">' . __('No Deal at the moment', 'woocommerce') . '</p>';

		ob_start();
		$rendered = 0;
		echo '<div class="gb-deal-grid gb-deal-grid-loading gb-cols-' . esc_attr($atts['columns']) . ' gb-list-theme ' . esc_attr($theme) . '" style="visibility:hidden;">';
		while ($products->have_posts()) {
			$products->the_post();
			global $product;

			$expiry = get_post_meta(get_the_ID(), '_gb_expiry', true);
			if ($this->is_deal_expired_for_timezone($expiry, $list_timezone)) {
				continue;
			}

			$is_ending = $this->is_deal_ending($product, $expiry);
			echo $this->get_deal_list_item_html(get_the_ID(), $theme, $is_ending);
			$rendered++;

			if ($limit > 0 && $rendered >= $limit) {
				break;
			}
		}
		echo '</div>';
		wp_reset_postdata();

		$html = ob_get_clean();

		if (0 === $rendered) {
			return '<p class="gb-no-deals">' . __('No Deal at the moment', 'woocommerce') . '</p>';
		}

		return $html;
	}

	/**
	 * Shortcode Handler: [gb_deal_banner show="thumbnail,title,countdown,price,discount_price"]
	 *
	 * Useful for Revolution Slider layers. Use `show` or `fields` to choose
	 * exactly which pieces to render.
	 */
	public function render_banner_shortcode($atts)
	{
		$atts = shortcode_atts(array(
			'id' => '',
			'cat' => '',
			'show' => 'thumbnail,title,countdown,price,discount_price',
			'fields' => '',
			'image_size' => 'full',
			'link' => 'no',
			'class' => '',
		), $atts, 'gb_deal_banner');

		$product_id = $this->get_first_active_deal_id($atts['cat'], $atts['id']);
		if (!$product_id) {
			return '';
		}

		$product = wc_get_product($product_id);
		if (!$product) {
			return '';
		}

		$fields = !empty($atts['fields']) ? $atts['fields'] : $atts['show'];
		$fields = array_filter(array_map('trim', explode(',', strtolower($fields))));
		if (empty($fields)) {
			return '';
		}

		$allowed_fields = array(
			'thumbnail',
			'image',
			'title',
			'countdown',
			'timer',
			'price',
			'regular_price',
			'discount_price',
			'sale_price',
			'discount',
			'discount_badge',
		);
		$fields = array_values(array_intersect($fields, $allowed_fields));
		if (empty($fields)) {
			return '';
		}

		$expiry = get_post_meta($product_id, '_gb_expiry', true);
		$has_live_countdown = !empty($expiry) && !$this->is_deal_expired($expiry);
		$regular_price = (float) $product->get_regular_price();
		$lowest_price = $this->get_lowest_tier_price($product);
		$discount_pc = $this->get_discount_percentage($product);
		$should_link = in_array(strtolower((string) $atts['link']), array('1', 'yes', 'true'), true);

		ob_start();
		echo '<div class="gb-deal-banner ' . esc_attr($atts['class']) . '" data-product-id="' . esc_attr($product_id) . '">';

		foreach ($fields as $field) {
			switch ($field) {
				case 'thumbnail':
				case 'image':
					$image_html = get_the_post_thumbnail($product_id, sanitize_key($atts['image_size']), array('class' => 'gb-deal-banner-thumbnail-img'));
					if (empty($image_html)) {
						break;
					}

					echo '<div class="gb-deal-banner-thumbnail">';
					if ($should_link) {
						echo '<a href="' . esc_url(get_permalink($product_id)) . '">';
					}
					echo $image_html;
					if ($should_link) {
						echo '</a>';
					}
					echo '</div>';
					break;

				case 'title':
					echo '<div class="gb-deal-banner-title">';
					if ($should_link) {
						echo '<a href="' . esc_url(get_permalink($product_id)) . '">';
					}
					echo esc_html($product->get_title());
					if ($should_link) {
						echo '</a>';
					}
					echo '</div>';
					break;

				case 'countdown':
				case 'timer':
					if ($has_live_countdown) {
						echo '<div class="gb-deal-banner-countdown">';
						$this->render_timer_html($expiry, true, $product_id);
						echo '</div>';
					}
					break;

				case 'price':
					$price_html = $has_live_countdown ? $product->get_price_html() : $this->get_original_price_html($product);
					if (!empty($price_html)) {
						echo '<div class="gb-deal-banner-price">' . wp_kses_post($price_html) . '</div>';
					}
					break;

				case 'regular_price':
					if ($regular_price > 0) {
						$regular_price_classes = array('gb-deal-banner-regular-price');
						if (!$has_live_countdown) {
							$regular_price_classes[] = 'countdown-ended';
						}

						echo '<div class="' . esc_attr(implode(' ', $regular_price_classes)) . '">' . wc_price($regular_price) . '</div>';
					}
					break;

				case 'discount_price':
				case 'sale_price':
					if ($has_live_countdown && $lowest_price > 0) {
						echo '<div class="gb-deal-banner-discount-price">' . wc_price($lowest_price) . '</div>';
					}
					break;

				case 'discount':
				case 'discount_badge':
					if ($has_live_countdown && $discount_pc > 0) {
						echo '<div class="gb-deal-banner-discount gb-discount-badge">-' . esc_html($discount_pc) . '%</div>';
					}
					break;
			}
		}

		echo '</div>';

		return ob_get_clean();
	}

	private function get_first_active_deal_id($cat = '', $product_id = '')
	{
		$product_id = absint($product_id);
		if ($product_id > 0) {
			$product = wc_get_product($product_id);
			if (!$product) {
				return 0;
			}

			return $product_id;
		}

		$args = array(
			'post_type' => 'product',
			'posts_per_page' => 20,
			'fields' => 'ids',
			'meta_query' => $this->get_countdown_deal_meta_query(),
		);

		if (!empty($cat)) {
			$args['product_cat'] = $cat;
		}

		$products = new WP_Query($args);
		if ($products->have_posts()) {
			foreach ($products->posts as $deal_id) {
				$expiry = get_post_meta($deal_id, '_gb_expiry', true);
				if (!empty($expiry) && !$this->is_deal_expired($expiry)) {
					return (int) $deal_id;
				}
			}
		}

		$args = array(
			'post_type' => 'product',
			'posts_per_page' => 20,
			'fields' => 'ids',
			'meta_query' => $this->get_active_deal_meta_query(),
		);

		if (!empty($cat)) {
			$args['product_cat'] = $cat;
		}

		$products = new WP_Query($args);
		if (!$products->have_posts()) {
			return 0;
		}

		foreach ($products->posts as $deal_id) {
			$expiry = get_post_meta($deal_id, '_gb_expiry', true);
			if (!$this->is_deal_expired($expiry)) {
				return (int) $deal_id;
			}
		}

		return 0;
	}

	private function get_original_price_html($product)
	{
		if ($product->is_type('variable')) {
			$min_price = (float) $product->get_variation_regular_price('min', true);
			$max_price = (float) $product->get_variation_regular_price('max', true);

			if ($min_price > 0 && $max_price > 0 && $min_price !== $max_price) {
				return wc_format_price_range($min_price, $max_price);
			}

			if ($min_price > 0) {
				return wc_price($min_price);
			}
		}

		$regular_price = (float) $product->get_regular_price();
		if ($regular_price > 0) {
			return wc_price($regular_price);
		}

		return $product->get_price_html();
	}

	private function normalize_list_theme($theme)
	{
		$theme = strtolower(trim((string) $theme));

		if (in_array($theme, array('2', 'theme-2', 'theme_2', 'theme2'), true)) {
			return 'theme-2';
		}

		return 'theme-1';
	}

	private function get_active_deal_meta_query()
	{
		return array(
			'relation' => 'AND',
			$this->get_enabled_deal_meta_clause(),
			array(
				'relation' => 'OR',
				array(
					'key' => '_gb_expiry',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key' => '_gb_expiry',
					'value' => '',
					'compare' => '=',
				),
				array(
					'key' => '_gb_expiry',
					'value' => current_time('mysql'),
					'compare' => '>=',
					'type' => 'DATETIME',
				),
			),
		);
	}

	private function get_countdown_deal_meta_query()
	{
		return array(
			'relation' => 'AND',
			$this->get_enabled_deal_meta_clause(),
			array(
				'key' => '_gb_expiry',
				'value' => '',
				'compare' => '!=',
			),
			array(
				'key' => '_gb_expiry',
				'value' => current_time('mysql'),
				'compare' => '>=',
				'type' => 'DATETIME',
			),
		);
	}

	private function get_enabled_deal_meta_query()
	{
		return array(
			$this->get_enabled_deal_meta_clause(),
		);
	}

	private function get_enabled_deal_meta_clause()
	{
		return array(
			'key' => '_gb_enabled',
			'value' => 'yes',
			'compare' => '=',
		);
	}

	private function get_valid_timezone($timezone)
	{
		$timezone = trim((string) $timezone);
		if ('' === $timezone) {
			return wp_timezone();
		}

		try {
			return new DateTimeZone($timezone);
		} catch (Exception $e) {
			return wp_timezone();
		}
	}

	private function is_deal_expired_for_timezone($expiry, DateTimeZone $timezone)
	{
		$expiry = trim((string) $expiry);
		if ('' === $expiry) {
			return false;
		}

		try {
			$expiry_date = new DateTimeImmutable($expiry, $timezone);
			$now = new DateTimeImmutable('now', $timezone);
			return $expiry_date < $now;
		} catch (Exception $e) {
			return false;
		}
	}

	private function get_expiry_timestamp($expiry)
	{
		$expiry = trim((string) $expiry);
		if ('' === $expiry) {
			return 0;
		}

		try {
			$date = new DateTimeImmutable($expiry, wp_timezone());
			return $date->getTimestamp();
		} catch (Exception $e) {
			return 0;
		}
	}

	private function get_formatted_expiry_date($expiry)
	{
		$expiry = trim((string) $expiry);
		if ('' === $expiry) {
			return '';
		}

		return $expiry;
	}

	private function get_formatted_current_date()
	{
		$format = get_option('date_format') . ' ' . get_option('time_format');
		return wp_date($format, time(), wp_timezone());
	}

	private function is_deal_expired($expiry)
	{
		$expiry_ts = $this->get_expiry_timestamp($expiry);

		return $expiry_ts > 0 && $expiry_ts < time();
	}

	private function get_deal_list_item_html($product_id, $theme, $is_ending = false)
	{
		if ('theme-2' === $theme) {
			return $this->get_deal_list_item_theme_two_html($product_id, $is_ending);
		}

		return $this->get_deal_list_item_theme_one_html($product_id, $is_ending);
	}

	/**
	 * Theme 1 card HTML for [gb_deal_list].
	 * Edit this method if you want to customize theme 1 markup.
	 */
	private function get_deal_list_item_theme_one_html($product_id, $is_ending = false)
	{
		$item_class = 'gb-deal-item gb-deal-item-theme-1' . ($is_ending ? ' ending' : '');
		$expiry = get_post_meta($product_id, '_gb_expiry', true);
		$is_expired = $this->is_deal_expired($expiry);

		ob_start();
		echo '<div class="' . esc_attr($item_class) . '" data-product-id="' . esc_attr($product_id) . '">';
		echo '<a href="' . esc_url(get_permalink($product_id)) . '" class="gb-deal-link gb-deal-link-theme-1">';
		echo '<div class="gb-deal-image gb-deal-image-theme-1">' . get_the_post_thumbnail($product_id, 'woocommerce_thumbnail') . '</div>';
		echo $this->get_compact_deal_html_theme_one($product_id);
		echo '</a>';

		echo '<div class="gb-add-to-cart-wrapper gb-add-to-cart-wrapper-theme-1">';
		if ($is_expired) {
			echo '<span class="gb-expired-label" style="display:block; padding:10px; background:#ccc; color:#333; text-align:center;">' . __('EXPIRED - No Add to Cart', 'woocommerce') . '</span>';
		} else {
			woocommerce_template_loop_add_to_cart();
		}
		echo '</div>';

		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Theme 2 card HTML for [gb_deal_list].
	 * Edit this method if you want to customize theme 2 markup.
	 */
	private function get_deal_list_item_theme_two_html($product_id, $is_ending = false)
	{
		$item_class = 'gb-deal-item gb-deal-item-theme-2' . ($is_ending ? ' ending' : '');
		$expiry = get_post_meta($product_id, '_gb_expiry', true);
		$is_expired = $this->is_deal_expired($expiry);

		ob_start();
		echo '<div class="gbListTheme2 ' . esc_attr($item_class) . '" data-product-id="' . esc_attr($product_id) . '">';
		echo '<a href="' . esc_url(get_permalink($product_id)) . '" class="gb-deal-link gb-deal-link-theme-2">';
		echo '<div class="gb-deal-image gb-deal-image-theme-2">' . get_the_post_thumbnail($product_id, 'woocommerce_thumbnail') . '</div>';
		echo '<div class="gb-deal-content gb-deal-content-theme-2">';
		echo $this->get_compact_deal_html_theme_two($product_id);
		echo '</a>';

		echo '<div class="gb-add-to-cart-wrapper gb-add-to-cart-wrapper-theme-2">';
		if ($is_expired) {
			echo '<span class="gb-expired-label" style="display:block; padding:10px; background:#ccc; color:#333; text-align:center;">' . __('EXPIRED - No Add to Cart', 'woocommerce') . '</span>';
		} else {
			woocommerce_template_loop_add_to_cart();
		}
		echo '</div>';

		echo '</div>';
		return ob_get_clean();
	}

	/**
	 * Theme 1 compact content for [gb_deal_list theme="theme-1"].
	 * Edit this method if you want to customize theme 1 inner HTML.
	 */
	private function get_compact_deal_html_theme_one($product_id)
	{
		$product = wc_get_product($product_id);
		if (!$product)
			return '';

		$expiry = get_post_meta($product_id, '_gb_expiry', true);
		$sales_count = (int) $product->get_total_sales();

		ob_start();
		echo '<div class="gb-compact-info gb-compact-info-theme-1">';
		echo '<span class="gb-limited-deals">LIMITED DEALS</span>';
		echo '<h3 class="gb-deal-title">' . esc_html($product->get_title()) . '</h3>';
		echo '<div class="gb-deal-price-wrapper">';
		$regular_price = (float) $product->get_regular_price();
		$lowest_price = $this->get_lowest_tier_price($product);

		if ($lowest_price < $regular_price) {
			echo '<div class="gb-deal-price"><del>' . wc_price($regular_price) . '</del> ' . wc_price($lowest_price) . '</div>';
		} else {
			echo '<div class="gb-deal-price">' . $product->get_price_html() . '</div>';
		}

		$discount_pc = $this->get_discount_percentage($product);
		if ($discount_pc > 0) {
			echo '<span class="gb-discount-badge">-' . esc_html($discount_pc) . '%</span>';
		}
		echo '</div>';
		if ($expiry) {
			echo '<div class="gb-compact-info-timer">';
			$this->render_timer_html($expiry, true, $product_id);
			echo '</div>';
			// echo '<div class="gb-deal-current-date">' . esc_html__('Current:', 'woocommerce') . ' <span class="gb-current-date-value" data-current-ts="' . esc_attr(time()) . '">' . esc_html($this->get_formatted_current_date()) . '</span></div>';
			// $end_date = $this->get_formatted_expiry_date($expiry);
			// if ($end_date) {
			// 	echo '<div class="gb-deal-end-date">' . esc_html__('Ends:', 'woocommerce') . ' <span class="gb-end-date-value" data-expiry="' . esc_attr($expiry) . '" data-expiry-ts="' . esc_attr($this->get_expiry_timestamp($expiry)) . '">' . esc_html($end_date) . '</span></div>';
			// }
		}

		$this->render_tier_progress($product, $sales_count, true);
		$this->render_time_progress($product, $expiry, true);
		echo '<div class="gb-sales-count">' . sprintf(esc_html__('%d sold', 'woocommerce'), $sales_count) . '</div>';
		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Theme 2 compact content for [gb_deal_list theme="theme-2"].
	 * Edit this method if you want to customize theme 2 inner HTML.
	 */
	private function get_compact_deal_html_theme_two($product_id)
	{
		$product = wc_get_product($product_id);
		if (!$product)
			return '';

		$expiry = get_post_meta($product_id, '_gb_expiry', true);
		$sales_count = (int) $product->get_total_sales();
		$regular_price = (float) $product->get_regular_price();
		$lowest_price = $this->get_lowest_tier_price($product);
		$discount_pc = $this->get_discount_percentage($product);

		ob_start();
		echo '<div class="gb-compact-info gb-compact-info-theme-2">';
		echo '<div class="gb-deal-meta-row gb-deal-meta-row-theme-2">';
		echo '<span class="gb-limited-deals">LIMITED DEALS</span>';
		if ($discount_pc > 0) {
			echo '<span class="gb-discount-badge">-' . esc_html($discount_pc) . '%</span>';
		}
		echo '</div>';

		echo '<h3 class="gb-deal-title">' . esc_html($product->get_title()) . '</h3>';
		echo '<div class="gb-deal-price-wrapper gb-deal-price-wrapper-theme-2">';
		if ($lowest_price < $regular_price) {
			echo '<div class="gb-deal-price"><del>' . wc_price($regular_price) . '</del> ' . wc_price($lowest_price) . '</div>';
		} else {
			echo '<div class="gb-deal-price">' . $product->get_price_html() . '</div>';
		}
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="gb-compact-info-full">';

		if ($expiry) {
			echo '<div class="gb-compact-info-timer gb-compact-info-timer-theme-2">';
			$this->render_timer_html($expiry, true, $product_id);
			echo '</div>';
			// echo '<div class="gb-deal-current-date gb-deal-current-date-theme-2">' . esc_html__('Current:', 'woocommerce') . ' <span class="gb-current-date-value" data-current-ts="' . esc_attr(time()) . '">' . esc_html($this->get_formatted_current_date()) . '</span></div>';
			// $end_date = $this->get_formatted_expiry_date($expiry);
			// if ($end_date) {
			// 	echo '<div class="gb-deal-end-date gb-deal-end-date-theme-2">' . esc_html__('Ends:', 'woocommerce') . ' <span class="gb-end-date-value" data-expiry="' . esc_attr($expiry) . '" data-expiry-ts="' . esc_attr($this->get_expiry_timestamp($expiry)) . '">' . esc_html($end_date) . '</span></div>';
			// }
		}

		

		echo '<div class="gb-sales-count gb-sales-count-theme-2">' . sprintf(esc_html__('%d sold', 'woocommerce'), $sales_count) . '</div>';
		$this->render_tier_progress($product, $sales_count, true);
		$this->render_time_progress($product, $expiry, true);
		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Get the Lowest Possible Tier Price (The Goal)
	 */
	private function get_lowest_tier_price($product)
	{
		$product_id = $product->get_id();
		$regular_price = (float) $product->get_regular_price();
		$tier_prices = array(
			(float) get_post_meta($product_id, '_gb_tier_1_price', true),
			(float) get_post_meta($product_id, '_gb_tier_2_price', true),
			(float) get_post_meta($product_id, '_gb_tier_3_price', true)
		);

		$tier_prices = array_filter($tier_prices); // Remove zeros/empty

		if (empty($tier_prices)) {
			return $regular_price;
		}

		return min($tier_prices);
	}

	/**
	 * Calculate Discount Percentage based on Lowest Possible Price
	 */
	private function get_discount_percentage($product)
	{
		$regular_price = (float) $product->get_regular_price();
		$lowest_price = $this->get_lowest_tier_price($product);

		if ($regular_price > 0 && $lowest_price < $regular_price) {
			$discount = 1 - ($lowest_price / $regular_price);
			return round($discount * 100);
		}
		return 0;
	}

	/**
	 * Check if the Deal is near expiration (80% elapsed)
	 */
	private function is_deal_ending($product, $expiry)
	{
		if (!$expiry)
			return false;

		$start_time = $product->get_date_created() ? $product->get_date_created()->getTimestamp() : time() - (7 * 24 * 60 * 60);
		$expiry_time = $this->get_expiry_timestamp($expiry);
		if (!$expiry_time) {
			return false;
		}

		$total_duration = $expiry_time - $start_time;

		if ($total_duration > 0) {
			$elapsed = time() - $start_time;
			$percent = ($elapsed / $total_duration) * 100;
			return ($percent >= 80);
		}

		return false;
	}

	/**
	 * Core HTML Generator for the Deal Info
	 */
	private function get_deal_html($product_id)
	{
		$product = wc_get_product($product_id);
		if (!$product)
			return '';

		$is_enabled = get_post_meta($product_id, '_gb_enabled', true);
		if ($is_enabled !== 'yes')
			return '';

		$expiry = get_post_meta($product_id, '_gb_expiry', true);
		if ($this->is_deal_expired($expiry)) {
			return '';
		}

		$sales_count = (int) $product->get_total_sales();

		ob_start();
		echo '<div class="gb-info-box" data-product-id="' . esc_attr($product_id) . '">';

		if ($expiry) {
			echo '<div class="gb-deal-timer-wrapper">';
			echo '<strong>' . __('Offer Ends In:', 'woocommerce') . '</strong>';
			$this->render_timer_html($expiry, false, $product_id);
			echo '</div>';
		}

		$discount_pc = $this->get_discount_percentage($product);
		if ($discount_pc > 0) {
			echo '<div class="gb-discount-badge">-' . $discount_pc . '%</div>';
		}

		echo '<div class="gb-sales-info">';
		echo '<p>' . sprintf(__('🔥 <strong>%d</strong> items have already been sold!', 'woocommerce'), $sales_count) . '</p>';
		echo '</div>';

		// Next tier hint & Progress Bar
		$this->render_tier_progress($product, $sales_count);

		echo '<div style="margin-top:25px;">';
		echo '<p style="font-size:0.9em; font-weight:600; color:#666; margin-bottom:8px;">' . __('⏳ Time Progress', 'woocommerce') . '</p>';
		$this->render_time_progress($product, $expiry);
		echo '</div>';

		echo '</div>';
		return ob_get_clean();
	}

	private function render_tier_progress($product, $sales_count, $compact = false)
	{
		$product_id = $product->get_id();
		$tier_1_qty = (int) get_post_meta($product_id, '_gb_tier_1_qty', true);
		$tier_2_qty = (int) get_post_meta($product_id, '_gb_tier_2_qty', true);
		$tier_3_qty = (int) get_post_meta($product_id, '_gb_tier_3_qty', true);

		$tiers = array_filter(array($tier_1_qty, $tier_2_qty, $tier_3_qty));
		if (empty($tiers))
			return;

		$max_tier = max($tiers);
		$next_qty = 0;
		if ($sales_count < $tier_1_qty)
			$next_qty = $tier_1_qty;
		elseif ($sales_count < $tier_2_qty)
			$next_qty = $tier_2_qty;
		elseif ($sales_count < $tier_3_qty)
			$next_qty = $tier_3_qty;

		// Progress Bar
		$percent = min(100, ($sales_count / $max_tier) * 100);
		$height = $compact ? '8px' : '12px';
		$margin = $compact ? '10px 0 5px' : '20px 0 10px';

		/*
		echo '<div class="gb-progress-container" style="background:#f0f0f0; height:' . $height . '; border-radius:6px; margin:' . $margin . '; overflow:hidden; border:1px solid #1E2438;">';
		echo '<div class="gb-progress-bar" style="width:' . $percent . '%; background:linear-gradient(90deg, #e44d26, #f16529); height:100%; border-radius:6px; transition: width 1s ease-out;"></div>';
		echo '</div>';
		*/

		if (!$compact) {
			if ($next_qty > 0) {
				$remaining = $next_qty - $sales_count;
				echo '<p style="color:#e44d26; font-size:1em; font-weight:700;">' . sprintf(__('Only %d more buyers needed for the next price drop!', 'woocommerce'), $remaining) . '</p>';
			} else {
				echo '<p style="color:#008a00; font-size:1em; font-weight:700;">' . __('All tiers unlocked! Best price active!', 'woocommerce') . '</p>';
			}
		}
	}

	private function render_time_progress($product, $expiry, $compact = false)
	{
		if (!$expiry)
			return;

		$start_time = $product->get_date_created() ? $product->get_date_created()->getTimestamp() : time() - (7 * 24 * 60 * 60);
		$expiry_time = $this->get_expiry_timestamp($expiry);
		if (!$expiry_time) {
			return;
		}

		$now = time();

		$total_duration = $expiry_time - $start_time;
		if ($total_duration <= 0)
			return;

		$elapsed = $now - $start_time;
		$percent = max(0, min(100, ($elapsed / $total_duration) * 100));

		$height = $compact ? '4px' : '10px';
		$margin = $compact ? '5px 0' : '0 0 10px';
		$color = $percent > 85 ? '#e44d26' : '#2ecc71'; // Red when close to expiry, green otherwise

		echo '<div class="gb-time-progress-container" data-start="' . esc_attr($start_time) . '" data-expiry="' . esc_attr($expiry_time) . '" style="background:#1E2438; height:' . $height . '; border-radius:10px; margin:' . $margin . '; overflow:hidden;">';
		echo '<div class="gb-time-progress-bar" style="width:' . $percent . '%; background:' . $color . '; height:100%; transition: width 1s linear;"></div>';
		echo '</div>';
	}

	private function render_timer_html($expiry, $compact = false, $product_id = 0)
	{
		$class = $compact ? 'gb-timer-compact' : 'gb-timer-full';
		$expiry_ts = $this->get_expiry_timestamp($expiry);
		?>
		<div class="gb-timer <?php echo esc_attr($class); ?>" data-product-id="<?php echo esc_attr(absint($product_id)); ?>" data-expiry="<?php echo esc_attr($expiry); ?>" data-expiry-ts="<?php echo esc_attr($expiry_ts); ?>">
			<div class="gb-time-unit">
				<span class="gb-unit-value gb-days">00</span>
				<span class="gb-unit-label"><?php _e('Days', 'woocommerce'); ?></span>
			</div>
			<div class="gb-time-separator">:</div>
			<div class="gb-time-unit">
				<span class="gb-unit-value gb-hours">00</span>
				<span class="gb-unit-label"><?php _e('Hrs', 'woocommerce'); ?></span>
			</div>
			<div class="gb-time-separator">:</div>
			<div class="gb-time-unit">
				<span class="gb-unit-value gb-mins">00</span>
				<span class="gb-unit-label"><?php _e('Mins', 'woocommerce'); ?></span>
			</div>
			<div class="gb-time-separator">:</div>
			<div class="gb-time-unit">
				<span class="gb-unit-value gb-secs">00</span>
				<span class="gb-unit-label"><?php _e('Secs', 'woocommerce'); ?></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Removed Archive timer as per user request (Now available via shortcode or single product only)
	 */
	// public function display_archive_timer() { ... }

	/**
	 * Enqueue Assets (Styles in head, Script in footer)
	 */
	public function enqueue_assets()
	{
		wp_enqueue_style('gb-tiers-style', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.0.0');
		add_action('wp_footer', array($this, 'output_js'));
	}

	/**
	 * Admin Assets for Datepicker
	 */
	public function admin_enqueue_assets($hook)
	{
		if ('post.php' !== $hook && 'post-new.php' !== $hook)
			return;

		wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
		wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr');

		wc_enqueue_js("
			flatpickr('.gb-flatpickr-input', {
				enableTime: true,
				dateFormat: 'Y-m-d H:i',
				calendarSeconds: false,
				time_24hr: true
			});
		");
	}



	public function output_js()
	{
		?>
		<script>
			(function () {
				document.addEventListener('DOMContentLoaded', function () {
					const timers = document.querySelectorAll('.gb-timer');
					if (timers.length === 0) return;

					let serverLoadedAt = <?php echo esc_js(time() * 1000); ?>;
					let browserLoadedAt = Date.now();
					const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
					const localDateFormatter = new Intl.DateTimeFormat(undefined, {
						year: 'numeric',
						month: 'short',
						day: 'numeric',
						hour: 'numeric',
						minute: '2-digit',
						second: '2-digit',
						timeZoneName: 'short'
					});

					const formatLocalDate = timestamp => localDateFormatter.format(new Date(timestamp));

					const getLocalExpiryTime = expiryValue => {
						if (!expiryValue) return 0;

						const parts = expiryValue.trim().split(/[- :]/).map(Number);
						if (parts.length < 5 || parts.some(Number.isNaN)) {
							return 0;
						}

						return new Date(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], parts[5] || 0).getTime();
					};

					const updateLocalDateLabels = now => {
						document.querySelectorAll('.gb-current-date-value').forEach(label => {
							label.textContent = formatLocalDate(now);
						});

						document.querySelectorAll('.gb-end-date-value').forEach(label => {
							let expiryDate = getLocalExpiryTime(label.dataset.expiry);

							if (!expiryDate) {
								const expiryTs = parseInt(label.dataset.expiryTs, 10);
								expiryDate = expiryTs ? expiryTs * 1000 : 0;
							}

							if (!expiryDate) return;

							label.textContent = formatLocalDate(expiryDate);
						});
					};

					const showEmptyDealListMessage = grid => {
						if (!grid || grid.querySelector('.gb-deal-item')) return;

						grid.hidden = true;
						grid.style.display = 'none';

						if (grid.nextElementSibling && grid.nextElementSibling.classList.contains('gb-no-deals')) return;

						const message = document.createElement('p');
						message.className = 'gb-no-deals';
						message.textContent = <?php echo wp_json_encode(__('No Deal at the moment', 'woocommerce')); ?>;
						grid.insertAdjacentElement('afterend', message);
					};

					const hideExpiredDealItem = timer => {
						const dealItem = timer.closest('.gb-deal-item');
						if (!dealItem) return false;

						const grid = dealItem.closest('.gb-deal-grid');
						dealItem.remove();
						showEmptyDealListMessage(grid);
						return true;
					};

					const revealCheckedDealLists = () => {
						document.querySelectorAll('.gb-deal-grid-loading').forEach(grid => {
							if (!grid.querySelector('.gb-deal-item')) {
								showEmptyDealListMessage(grid);
								return;
							}

							grid.classList.remove('gb-deal-grid-loading');
							grid.style.visibility = '';
						});
					};

					const updateTimers = () => {
						const now = serverLoadedAt + (Date.now() - browserLoadedAt);
						updateLocalDateLabels(now);

						timers.forEach(timer => {
							let expiryDate = getLocalExpiryTime(timer.dataset.expiry);

							if (!expiryDate) {
								const expiryTs = parseInt(timer.dataset.expiryTs, 10);
								expiryDate = expiryTs ? expiryTs * 1000 : 0;
							}

							if (!expiryDate) return;

							const distance = expiryDate - now;

							if (distance < 0) {
								if (hideExpiredDealItem(timer)) {
									return;
								}

								timer.innerHTML = '<div class="gb-expired-notice">' + "EXPIRED" + '</div>';
								return;
							}

							const days = Math.floor(distance / (1000 * 60 * 60 * 24));
							const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
							const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
							const seconds = Math.floor((distance % (1000 * 60)) / 1000);

							// Update Units
							const dEl = timer.querySelector('.gb-days');
							const hEl = timer.querySelector('.gb-hours');
							const mEl = timer.querySelector('.gb-mins');
							const sEl = timer.querySelector('.gb-secs');

							if (dEl) dEl.innerText = String(days).padStart(2, '0');
							if (hEl) hEl.innerText = String(hours).padStart(2, '0');
							if (mEl) mEl.innerText = String(minutes).padStart(2, '0');
							if (sEl) sEl.innerText = String(seconds).padStart(2, '0');

							// Update Time Progress Bar
							const parentBox = timer.closest('.gb-info-box, .gb-compact-info, .gb-deal-item');
							if (parentBox) {
								const progressBar = parentBox.querySelector('.gb-time-progress-bar');
								const progressContainer = parentBox.querySelector('.gb-time-progress-container');
								if (progressBar && progressContainer) {
									const start = parseInt(progressContainer.dataset.start) * 1000;
									const end = expiryDate;
									const total = end - start;
									const elapsed = now - start;
									const percent = Math.min(100, Math.max(0, (elapsed / total) * 100));
									progressBar.style.width = percent + '%';
									if (percent > 85) progressBar.style.background = '#e44d26';
								}
							}
						});
					};

					const refreshTimerData = () => {
						const productIds = Array.from(timers)
							.map(timer => {
								const productBox = timer.closest('[data-product-id]');
								return parseInt(timer.dataset.productId || (productBox && productBox.dataset.productId), 10);
							})
							.filter(Boolean);

						if (productIds.length === 0) {
							return Promise.resolve();
						}

						const ids = Array.from(new Set(productIds)).join(',');
						return fetch(ajaxUrl + '?action=gb_timer_data&ids=' + encodeURIComponent(ids), {
							credentials: 'same-origin',
							cache: 'no-store'
						})
							.then(response => response.json())
							.then(response => {
								if (!response || !response.success || !response.data) return;

								if (response.data.server_ts) {
									serverLoadedAt = parseInt(response.data.server_ts, 10) * 1000;
									browserLoadedAt = Date.now();
								}

								timers.forEach(timer => {
									const productBox = timer.closest('[data-product-id]');
									const productId = timer.dataset.productId || (productBox && productBox.dataset.productId);
									const product = response.data.products && response.data.products[productId];
									if (!product) return;

									timer.dataset.productId = productId;
									timer.dataset.expiry = product.expiry || '';
									timer.dataset.expiryTs = product.expiry_ts || '';

									if (productBox) {
										const endDateLabel = productBox.querySelector('.gb-end-date-value');
										if (endDateLabel) {
											endDateLabel.dataset.expiry = product.expiry || '';
											endDateLabel.dataset.expiryTs = product.expiry_ts || '';
										}
									}
								});
							})
							.catch(() => {});
					};

					updateTimers();
					revealCheckedDealLists();

					refreshTimerData().then(() => {
						updateTimers();
						revealCheckedDealLists();
						setInterval(updateTimers, 1000);
					});
				});
			})();
		</script>
		<?php
	}
}

new WC_Group_Buy_Tiers();
