<?php

namespace BeYoastPremium;

use Yoast\WP\SEO\Integrations\Integration_Interface;

class Yoast_Premium_AI implements Integration_Interface {

	/** @var object|null */
	private $asset_manager;
	private $addon_manager;
	private $api_client;
	private $current_page_helper;
	private $options_helper;
	private $user_helper;
	private $introductions_seen_repository;

	public static function get_conditionals() {
		// Keep if you are actually registered through Yoast's Integration system.
		return [
			\Yoast\WP\SEO\Conditionals\AI_Conditional::class,
			\Yoast\WP\SEO\Conditionals\AI_Editor_Conditional::class,
		];
	}

	/**
	 * IMPORTANT: keep constructor empty / safe.
	 * No "new Yoast\..." here.
	 */
	public function __construct() {}

	public function register_hooks() {
		// If Yoast is not active / classes missing, don't hook anything.
		if ( ! $this->yoast_available() ) {
			return;
		}

		\add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		\add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_assets' ], 11 );
	}

	private function yoast_available(): bool {
		// Minimal sanity checks. Add more if needed.
		return class_exists( '\WPSEO_Admin_Asset_Manager' )
			&& class_exists( '\WPSEO_Addon_Manager' )
			&& class_exists( '\Yoast\WP\SEO\AI_HTTP_Request\Infrastructure\API_Client' )
			&& class_exists( '\Yoast\WP\SEO\Helpers\User_Helper' )
			&& class_exists( '\Yoast\WP\SEO\Helpers\Options_Helper' );
	}

	/**
	 * Initialize dependencies only when needed (and only if classes exist).
	 */
	private function init(): void {
		if ( $this->asset_manager ) {
			return;
		}

		// Guard each class so a missing class never fatals.
		if ( class_exists( '\WPSEO_Admin_Asset_Manager' ) ) {
			$this->asset_manager = new \WPSEO_Admin_Asset_Manager();
		}

		if ( class_exists( '\WPSEO_Addon_Manager' ) ) {
			$this->addon_manager = new \WPSEO_Addon_Manager();
		}

		if ( class_exists( '\Yoast\WP\SEO\AI_HTTP_Request\Infrastructure\API_Client' ) ) {
			$this->api_client = new \Yoast\WP\SEO\AI_HTTP_Request\Infrastructure\API_Client();
		}

		if ( class_exists( '\Yoast\WP\SEO\Helpers\Options_Helper' ) ) {
			$this->options_helper = new \Yoast\WP\SEO\Helpers\Options_Helper();
		}

		if ( class_exists( '\Yoast\WP\SEO\Helpers\User_Helper' ) ) {
			$this->user_helper = new \Yoast\WP\SEO\Helpers\User_Helper();
		}

		// These can be more fragile -> extra guards.
		if (
			class_exists( '\Yoast\WP\SEO\Wrappers\WP_Query_Wrapper' ) &&
			class_exists( '\Yoast\WP\SEO\Helpers\Current_Page_Helper' )
		) {
			$this->current_page_helper = new \Yoast\WP\SEO\Helpers\Current_Page_Helper(
				new \Yoast\WP\SEO\Wrappers\WP_Query_Wrapper()
			);
		}

		if (
			$this->user_helper &&
			class_exists( '\Yoast\WP\SEO\Introductions\Infrastructure\Introductions_Seen_Repository' )
		) {
			$this->introductions_seen_repository =
				new \Yoast\WP\SEO\Introductions\Infrastructure\Introductions_Seen_Repository( $this->user_helper );
		}
	}

	public function enqueue_assets() {
		$this->init();

		// Hard guard - don't fatal if asset manager is missing.
		if ( ! $this->asset_manager || ! method_exists( $this->asset_manager, 'enqueue_script' ) ) {
			return;
		}

		$this->asset_manager->enqueue_script( 'ai-generator' );

		if ( method_exists( $this->asset_manager, 'localize_script' ) ) {
			$this->asset_manager->localize_script(
				'ai-generator',
				'wpseoAiGenerator',
				$this->get_script_data()
			);
		}

		if ( method_exists( $this->asset_manager, 'enqueue_style' ) ) {
			$this->asset_manager->enqueue_style( 'ai-generator' );
		}
	}

	public function get_product_subscriptions(): array {
		$this->init();

		if (
			! $this->addon_manager ||
			! method_exists( $this->addon_manager, 'has_valid_subscription' ) ||
			! class_exists( '\WPSEO_Addon_Manager' )
		) {
			return [
				'premiumSubscription'     => false,
				'wooCommerceSubscription' => false,
			];
		}

		return [
			'premiumSubscription'     => $this->addon_manager->has_valid_subscription( \WPSEO_Addon_Manager::PREMIUM_SLUG ),
			'wooCommerceSubscription' => $this->addon_manager->has_valid_subscription( \WPSEO_Addon_Manager::WOOCOMMERCE_SLUG ),
		];
	}

	public function get_script_data(): array {
		$this->init();

		$user_id = ( $this->user_helper && method_exists( $this->user_helper, 'get_current_user_id' ) )
			? $this->user_helper->get_current_user_id()
			: get_current_user_id();

		$hasConsent = ( $this->user_helper && method_exists( $this->user_helper, 'get_meta' ) )
			? $this->user_helper->get_meta( $user_id, '_yoast_wpseo_ai_consent', true )
			: get_user_meta( $user_id, '_yoast_wpseo_ai_consent', true );

		$hasSeenIntro = false;
		if (
			$this->introductions_seen_repository &&
			method_exists( $this->introductions_seen_repository, 'is_introduction_seen' ) &&
			class_exists( '\Yoast\WP\SEO\Introductions\Application\Ai_Fix_Assessments_Upsell' )
		) {
			$hasSeenIntro = $this->introductions_seen_repository->is_introduction_seen(
				$user_id,
				\Yoast\WP\SEO\Introductions\Application\Ai_Fix_Assessments_Upsell::ID
			);
		}

		$requestTimeout = ( $this->api_client && method_exists( $this->api_client, 'get_request_timeout' ) )
			? $this->api_client->get_request_timeout()
			: 0;

		$isFreeSparks = ( $this->options_helper && method_exists( $this->options_helper, 'get' ) )
			? ( $this->options_helper->get( 'ai_free_sparks_started_on', null ) !== null )
			: false;

		return [
			'hasConsent'           => $hasConsent,
			'productSubscriptions' => $this->get_product_subscriptions(),
			'hasSeenIntroduction'  => $hasSeenIntro,
			'requestTimeout'       => $requestTimeout,
			'isFreeSparks'         => $isFreeSparks,
		];
	}
}
