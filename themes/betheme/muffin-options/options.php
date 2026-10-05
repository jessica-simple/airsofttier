<?php
if ( ! class_exists( 'MFN_Options' ) ) {

	if ( ! defined( 'MFN_OPTIONS_DIR' ) ) {
		define( 'MFN_OPTIONS_DIR', get_template_directory() .'/muffin-options/' );
	}

	if ( ! defined( 'MFN_OPTIONS_URI' ) ) {
		define( 'MFN_OPTIONS_URI', get_template_directory_uri() .'/muffin-options/' );
	}

	class MFN_Options
	{
		public $dir = MFN_OPTIONS_DIR;
		public $url = MFN_OPTIONS_URI;
		public $page = '';

		public $args = array();
		public $sections = array();

		public $errors = array();
		public $warnings = array();

		public $options = array();

		public $menu = array();

		/**
		 * Class Constructor. Defines the args for the theme options class
		 */

		public function __construct($menu = array(), $sections = array())
		{
			$this->menu = apply_filters('mfn-opts-menu', $menu);

			$defaults = array();

			$defaults['opt_name'] = 'betheme';

			$defaults['menu_icon'] = MFN_OPTIONS_URI .'/img/menu_icon.png';
			$defaults['menu_title'] = __('Theme Options', 'mfn-opts');
			$defaults['page_title'] = __('Theme Options', 'mfn-opts');
			$defaults['page_slug'] = apply_filters('betheme_slug', 'be').'-options';
			$defaults['page_cap'] = 'edit_theme_options';
			$defaults['page_type'] = 'menu';
			$defaults['page_parent'] = '';
			$defaults['page_position'] = 100;

			// get args

			$this->args = $defaults;
			$this->args = apply_filters( 'mfn-opts-args', $this->args );
			$this->args = apply_filters( 'mfn-opts-args-'. $this->args['opt_name'], $this->args );

			// get sections

			$this->sections = apply_filters( 'mfn-opts-sections', $sections );
			$this->sections = apply_filters( 'mfn-opts-sections-'. $this->args['opt_name'], $this->sections );

			// set option with defaults
			add_action( 'init', array( $this, '_set_default_options' ) );
			add_action( 'init', array( $this, '_backward_compatibility' ) );
			add_action( 'admin_init', array( $this, '_backward_compatibility_admin' ) );

			// save new custom fonts
			add_action( 'init', array( $this, '_register_custom_social' ), 11 );
			add_action( 'init', array( $this, '_register_custom_fonts' ), 12 );

			// options page
			add_action( 'admin_menu', array( $this, '_options_page' ), 4 );
			add_filter( 'admin_body_class', array( $this, '_admin_body_class' ) );

			// register setting
			add_action( 'admin_init', array( $this, '_register_setting' ) );

			// get the options for use later on
			$this->options = get_option( $this->args['opt_name'] );

			if( empty( $_GET['action'] ) || $_GET['action'] != apply_filters('betheme_slug', 'mfn') .'-live-builder' ){
				// first action hooked into the admin scripts actions
				add_action( 'admin_enqueue_scripts', array( $this, '_enqueue' ) );
			}

			// hook into the wp feeds for downloading the exported settings
			add_action( 'do_feed_mfn-opts-'. $this->args['opt_name'], array( $this, '_download_options' ), 1, 1 );

			// add actions before form
			add_action( 'mfn-opts-page-before-form', array( $this, '_static_CSS' ), 10 );
			add_action( 'mfn-opts-page-before-form', array( $this, '_cache_manager' ), 11 );
			add_action( 'mfn-opts-page-before-form', array( $this, '_flush_cache' ), 12 );

			// ajax
			add_action( 'wp_ajax_mfn_options_revision_save', array( $this, '_revision_save' ) );
		}

		/**
		 * Ajax | SET revision
		 */

		public function _revision_save() {

			// function verifies the AJAX request, to prevent any processing of requests which are passed in by third-party sites or systems

			check_ajax_referer( 'mfn-builder-nonce', 'mfn-builder-nonce' );

			if( ! current_user_can( 'manage_options' ) ){
				wp_die();
			}

			if( empty( $_POST['betheme'] ) ){
				return false;
			}

			$options = $_POST['betheme'];
			$type = htmlspecialchars(trim($_POST['revision-type']));

			unset( $options['last_tab'] );
			unset( $options['import_code'] );
			unset( $options['import_link'] );

			$revisions = $this->set_revision( $type, $options );

			echo $this->get_revisions_json( $revisions );

			exit;
		}

		/**
		 * SET revision
		 */

		public function set_revision( $type, $options ){

			if( ! $type || ! $options ){
				return false;
			}

			$options = call_user_func('base'.'64_encode', serialize($options));

			$limit = 5; // max number of revisions of specified type

			$option_key = 'betheme_revision_'. $type;
			$revisions = get_option( $option_key );

			if( $revisions && is_array($revisions) ) {

				if( count( $revisions ) >= $limit ) {
					reset( $revisions );
					$rev_key = key( $revisions );
					unset( $revisions[$rev_key] );
				}

			} else {

				$revisions = [];

			}

			$time = current_time('timestamp');

			$revisions[$time] = $options;

			update_option( $option_key, $revisions );

			return $revisions;
		}

		/**
		 * GET revisions in json format
		 */

		public function get_revisions_json( $revisions ){

			if( ! is_array( $revisions ) ){
				return false;
			}

			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );

			$json = [];

			foreach( $revisions as $rev_key => $rev_val ){
				$json[$rev_key] = date( $date_format .' '. $time_format , $rev_key );
			}

			return json_encode($json);
		}

		/**
		 * Get all revisions for the post
		 */

		public function get_revisions(){

			$array = [
				'update' => [],
				'revision' => [],
				'backup' => [],
			];

			$date_format = get_option( 'date_format' );
			$time_format = get_option( 'time_format' );

			foreach( $array as $type => $value ){

				$option_key = 'betheme_revision_'. $type;

				$revisions = get_option( $option_key );

				if( is_array( $revisions ) ){
					foreach( $revisions as $rev_key => $rev_val ){
						$array[$type][$rev_key] = date( $date_format .' '. $time_format , $rev_key );
					}
				}

			}

			return $array;

		}

		/**
		 * Print revisions list
		 */

		public function the_revisions_list( $revisions ){

			if( ! empty( $revisions ) ){
				foreach( $revisions as $rev_key => $rev_val ){
					echo '<li data-time="'. esc_attr( $rev_key ) .'">';
				    echo '<span class="revision-icon mfn-icon-clock"></span>';
				    echo '<div class="revision">';
			        echo '<h6>'. esc_attr( $rev_val ) .'</h6>';
			        echo '<a class="mfn-option-btn mfn-option-text mfn-option-blue mfn-btn-restore revision-restore" href="#"><span class="text">'. esc_html__('Restore','mfn-opts') .'</span></a>';
				    echo '</div>';
					echo '</li>';
				}
			}

		}

		/**
		 * Get darker color
		 * Helper function for old button styles
		 */

		public function get_darker_color($hex, $percentage) {

	    // Ensure hex is formatted correctly
	    $hex = str_replace('#', '', $hex);

	    // If shorthand notation, expand it
	    if (strlen($hex) == 3) {
	      $hex = str_repeat(substr($hex, 0, 1), 2) .
	             str_repeat(substr($hex, 1, 1), 2) .
	             str_repeat(substr($hex, 2, 1), 2);
	    }

	    // Convert hex to RGB
	    $r = hexdec(substr($hex, 0, 2));
	    $g = hexdec(substr($hex, 2, 2));
	    $b = hexdec(substr($hex, 4, 2));

	    // Calculate the step value based on the percentage
	    $steps = round(255 * ($percentage / 100));

	    // Adjust brightness to make the color darker
	    $r = max(0, min(255, $r - $steps));
	    $g = max(0, min(255, $g - $steps));
	    $b = max(0, min(255, $b - $steps));

	    // Convert RGB back to hex
	    $r = str_pad(dechex($r), 2, '0', STR_PAD_LEFT);
	    $g = str_pad(dechex($g), 2, '0', STR_PAD_LEFT);
	    $b = str_pad(dechex($b), 2, '0', STR_PAD_LEFT);

	    return "#$r$g$b";
		}

		/**
		 * Backward compatibility with older Betheme versions
		 * @since 21.9.1
		 */

		public function _backward_compatibility(){

			if( ! is_array($this->options) ){
				$this->options = [];
			}

			// Minimalist header @since 21.9.1

			if( isset( $this->options['minimalist-header'] ) ) {

				if( 'no' === $this->options['minimalist-header'] ) {

					$this->options['header-height'] = '0'; // use string not integer
					$this->options['mobile-subheader-padding'] = '80px 0';

				} elseif( '1' === $this->options['minimalist-header'] ) {

					$this->options['header-height'] = '0';

					if( 'modern' == $this->options['header-style'] ){
						$this->options['header-height'] = '147';
					}

					if( 'simple' == $this->options['header-style'] ){
						$this->options['header-height'] = '130';
					}

					if( 'fixed' == $this->options['header-style'] ){
						$this->options['header-height'] = '60';
					}

				} else {

					$this->options['header-height'] = '250';

				}

			}

			// Custom Variation Swatches @since 25.0.3

			if( ! isset( $this->options['variable-swatches'] ) ) {
				$this->options['variable-swatches'] = '1';
			}

			// Social icons order @since 25.1.6

			$socials = ['skype','whatsapp','facebook','twitter','vimeo','youtube','flickr','linkedin','pinterest','dribbble','instagram','snapchat','behance','tumblr','tripadvisor','vkontakte','viadeo','xing','custom','rss'];

			if( empty( $this->options['social-link']['order'] ) ) {
				$this->options['social-link'] = [];
				$this->options['social-link']['order'] = implode( ',', $socials );
			}

			foreach( $socials as $social ){
				if( isset( $this->options['social-'. $social] ) ) {
					$this->options['social-link'][$social] = $this->options['social-'. $social];
				}
			}

			// Buttons 2.0 @since 27.4.2

			if( isset( $this->options['button-style'] ) ) {

				$this->options['button-animation'] = 'fade';
				$this->options['button-animation-time'] = 0.2;

				// custom

				if( 'custom' == $this->options['button-style'] ){

					$this->options['button-icon-color'] = $this->options['button-color'];
					$this->options['button-highlighted-icon-color'] = $this->options['button-highlighted-color'];
					$this->options['button-action-icon-color'] = $this->options['button-action-color'];

					$this->options['button-shop-color'] = $this->options['button-highlighted-color'];
					$this->options['button-shop-background'] = $this->options['button-highlighted-background'];
					$this->options['button-shop-border-color'] = $this->options['button-highlighted-border-color'];
					$this->options['button-shop-box-shadow'] = $this->options['button-highlighted-box-shadow'] ?? '';
				}

				// all buttons, except 'custom' - used content font

				if( 'custom' != $this->options['button-style'] ){

					$this->options['button-font-family'] = '';
					$this->options['button-font'] = $this->options['font-size-content'];
					$this->options['button-padding'] = [
						'top' => '16px',
						'right' => '20px',
						'bottom' => '16px',
						'left' => '20px',
					];
					$this->options['button-border-width'] = '';
					$this->options['button-border-color'] = '';
					$this->options['button-box-shadow'] = '';
					$this->options['button-highlighted-border-color'] = '';
					$this->options['button-highlighted-box-shadow'] = '';
					$this->options['button-shop-border-color'] = '';
					$this->options['button-shop-box-shadow'] = '';
					$this->options['button-action-border-color'] = '';
					$this->options['button-action-box-shadow'] = '';

					// classic, flat, round

					if( in_array( $this->options['button-style'], ['', 'flat', 'round'] ) ){

						if( '' == $this->options['button-style'] ){
							$this->options['button-border-radius'] = '5px';
						} elseif( 'flat' == $this->options['button-style'] ){
							$this->options['button-border-radius'] = '0';
						} elseif( 'round' == $this->options['button-style'] ){
							$this->options['button-border-radius'] = '50px';
							$this->options['button-padding']['right'] = '35px';
							$this->options['button-padding']['left'] = '35px';
						}

						// default

						$this->options['button-color'] = [
							'normal' => $this->options['color-button'] ?? '#747474',
							'hover' => $this->options['color-button'] ?? '#747474',
						];
						$this->options['button-background'] = [
							'normal' => $this->options['background-button'] ?? '#f7f7f7',
							'hover' => $this->options['background-button'] ?? '#f7f7f7',
						];
						$this->options['button-icon-color'] = $this->options['button-color'];

						// theme

						$this->options['button-highlighted-color'] = [
							'normal' => $this->options['color-button-theme'] ?? '#FFFFFF',
							'hover' => $this->options['color-button-theme'] ?? '#FFFFFF',
						];
						$this->options['button-highlighted-background'] = [
							'normal' => $this->options['color-theme'] ?? '#0089F7',
							'hover' => $this->options['color-theme'] ?? '#0089F7',
						];
						$this->options['button-highlighted-icon-color'] = $this->options['button-highlighted-color'];

						// shop

						$this->options['button-shop-color'] = $this->options['button-highlighted-color'];
						$this->options['button-shop-background'] = $this->options['button-highlighted-background'];

						// action

						$this->options['button-action-color'] = [
							'normal' => $this->options['color-action-button'] ?? '#FFFFFF',
							'hover' => $this->options['color-action-button'] ?? '#FFFFFF',
						];
						$this->options['button-action-background'] = [
							'normal' => $this->options['background-action-button'] ?? '#0089F7',
							'hover' => $this->options['background-action-button'] ?? '#0089F7',
						];
						$this->options['button-action-icon-color'] = $this->options['button-action-color'];

						// auto hover darker colors

						$this->options['button-background']['hover'] = $this->get_darker_color($this->options['button-background']['normal'], 5);
						$this->options['button-highlighted-background']['hover'] = $this->get_darker_color($this->options['button-highlighted-background']['normal'], 5);
						$this->options['button-shop-background']['hover'] = $this->get_darker_color($this->options['button-shop-background']['normal'], 5);
						$this->options['button-action-background']['hover'] = $this->get_darker_color($this->options['button-action-background']['normal'], 5);

					}

					// default - old hover style

					if( '' == $this->options['button-style'] ){

						$this->options['button-animation'] = 'slide slide-right';

					}

					// stroke

					if( 'stroke' == $this->options['button-style'] ){

						$this->options['button-border-radius'] = '3px';
						$this->options['button-border-width'] = '2px';

						// default

						$this->options['button-color'] = [
							'normal' => $this->options['color-button'] ?? '#747474',
							'hover' => '#FFFFFF',
						];
						$this->options['button-background'] = [
							'normal' => '',
							'hover' => $this->options['background-button'] ?? '#f7f7f7',
						];
						$this->options['button-border'] = [
							'normal' => $this->options['background-button'] ?? '#f7f7f7',
							'hover' => $this->options['background-button'] ?? '#f7f7f7',
						];
						$this->options['button-icon-color'] = $this->options['button-color'];

						// theme

						$this->options['button-highlighted-color'] = [
							'normal' => $this->options['color-theme'] ?? '#0089F7',
							'hover' => '#000000',
						];
						$this->options['button-highlighted-background'] = [
							'normal' => '',
							'hover' => $this->options['color-theme'] ?? '#0089F7',
						];
						$this->options['button-highlighted-border'] = [
							'normal' => $this->options['color-theme'] ?? '#0089F7',
							'hover' => $this->options['color-theme'] ?? '#0089F7',
						];
						$this->options['button-highlighted-icon-color'] = $this->options['button-highlighted-color'];

						// shop

						$this->options['button-shop-color'] = $this->options['button-highlighted-color'];
						$this->options['button-shop-background'] = $this->options['button-highlighted-background'];
						$this->options['button-shop-border'] = $this->options['button-highlighted-border'];

						// action

						$this->options['button-action-color'] = [
							'normal' => $this->options['color-action-button'] ?? '#0089F7',
							'hover' => '#000000',
						];
						$this->options['button-action-background'] = [
							'normal' => '',
							'hover' => $this->options['background-action-button'] ?? '#0089F7',
						];
						$this->options['button-action-border'] = [
							'normal' => $this->options['background-action-button'] ?? '#0089F7',
							'hover' => $this->options['background-action-button'] ?? '#0089F7',
						];
						$this->options['button-action-icon-color'] = $this->options['button-action-color'];

					}

				}

			}

			// Lead text @since 27.5.8

			if( ! isset( $this->options['font-lead'] ) ) {
				$this->options['font-lead'] = $this->options['font-content'] ?? '';
			}

			// Header Search Form @since 28.3.1

			if( ! isset( $this->options['header-search-form'] ) ) {
				if( isset( $this->options['header-search'] ) ){
					if( 'shop' != $this->options['header-search'] ){
						$this->options['header-search'] = '';
					}
				} else {
					$this->options['header-search'] = '';
					$this->options['header-search-form'] = '0';
				}
			}

			// Remove unwanted SEO tags @since 28.1.9

			$domain_to_check = 'muffingroup.com';
			$current_domain = $_SERVER['SERVER_NAME'] ?? '';
			$seo_conflict = false;

			if ( strpos( $current_domain, $domain_to_check ) === false ) {

		    $seo_tags = [
	        'google-gtag-id',
	        'google-gtag-js',
	        'google-gtag-html',
	        'google-remarketing',
	        // 'facebook-pixel',
	        'google-analytics'
		    ];

		    $seo_ids = [
	        'AW-353326160',
	        'G-EQ0837VQVT',
	        'UA-31305350-1'
		    ];

		    foreach ( $seo_tags as $tag ) {
	        if ( ! empty($this->options[$tag]) ) {
            foreach ( $seo_ids as $id ) {
              if ( strpos( $this->options[$tag], $id ) !== false ) {
                $this->options[$tag] = '';
								$seo_conflict = true;
                break;
              }
            }
	        }
		    }

				if( $seo_conflict ){

					// backup options
					$backup = get_option( 'betheme' );
					$this->set_revision( 'revision', $backup);

					// save options
					update_option( 'betheme', $this->options );
				}

			}

		}

		/**
		 * Backward compatibility with older Betheme versions
		 * @since 28.0.4
		 */

		public function _backward_compatibility_admin(){

			if( ! is_array($this->options) ){
				$this->options = [];
			}

			// Static CSS includes Custom CSS @since 28.0.4

			if( ! get_site_option( 'betheme_static' ) && ! empty( $this->options['static-css'] ) ){
				$this->_static_CSS( true );
				update_site_option( 'betheme_static', '28' );
			}

		}

		/**
		 * This is used to return and option value from the options array
		 */

		public function get( $opt_name, $default = null ) {
 			if( ! is_array( $this->options ) ){
 				return $default;
 			}

 			if( ! key_exists( $opt_name, $this->options ) ) {
 				return $default;
 			}

 			if( empty( $this->options[$opt_name] ) && ( '0' !== $this->options[$opt_name] ) ) {
 				return $default;
 			}

 			return $this->options[$opt_name];
 		}

		/**
		 * Get default options into an array suitable for the settings API
		 */

		public function _default_values()
		{
			$defaults = array();

			foreach ( $this->sections as $k => $section ) {
				if ( isset( $section['fields'] ) ) {
					foreach ( $section['fields'] as $fieldk => $field ) {

						if ( empty( $field['id'] ) ){
							continue;
						}

						if ( ! isset( $field['std'] ) ) {
							$field['std'] = '';
						}

						$defaults[ $field['id'] ] = $field['std'];

					}
				}
			}

			$defaults['last_tab'] = false;
			return $defaults;
		}

		/**
		 * Set default options on admin_init if option doesnt exist (theme activation hook caused problems, so admin_init it is)
		 */

		public function _set_default_options()
		{
			if ( ! get_option($this->args['opt_name']) ) {
				add_option($this->args['opt_name'], $this->_default_values());
			}
			$this->options = get_option($this->args['opt_name']);

			// be setup wizard
			if( isset( $_GET['mfn-setup-preview'] ) ){
				$this->options = $this->_default_values();
			}
		}

		/**
		 * Class Theme Options Page Function, creates main options page.
		 */

		public function _options_page()
		{
			$this->page = add_submenu_page(
				apply_filters('betheme_dynamic_slug', 'betheme'),
				$this->args['page_title'],
				$this->args['page_title'],
				$this->args['page_cap'],
				apply_filters('betheme_slug', 'be').'-options',
				array( $this, '_options_page_html' )
			);

			// Fires when styles are printed for a specific admin page based on $hook_suffix.
			add_action( 'admin_print_styles-'. $this->page, array( $this, '_enqueue_options_page' ) );
		}

		/**
		 * Admin body class
		 * @param string $classes
		 */

		public function _admin_body_class( $classes )
		{
			$classes .= ' theme_page_be-options';

			// elementor active - show some of theme options
			if ( class_exists( 'Elementor\Plugin' ) ){
				$classes .= ' elementor-active';
			}

			// load google fonts
			if ( mfn_opts_get('google-font-mode') !== 'disabled' ) {
				$classes .= ' has-googlefonts';
			}

			// hide wordpress editor
			if ( mfn_opts_get('hide_editor') ) {
				$classes .= ' hide-wp-editor';
			}

			// UI options

			$ui_options = Mfn_Builder_Helper::get_options();
			$screen = get_current_screen();
			$screen_base = '';

			if( is_object( $screen ) ){

				$screen_base = $screen->base;

				// templates edit screen
				if( 'edit' == $screen_base && 'template' == $screen->post_type ){
					$screen_base = 'template_edit';
				}

			}

			$slug_betheme = apply_filters('betheme_slug', 'betheme');
			$slug_be = apply_filters('betheme_slug', 'be');

			$be_dark = array(
				'toplevel_page_'. $slug_betheme,
				$slug_betheme .'_page_'. $slug_be .'-setup',
				$slug_betheme .'_page_'. $slug_be .'-plugins',
				$slug_betheme .'_page_'. $slug_be .'-websites',
				$slug_betheme .'_page_'. $slug_be .'-options',
				$slug_betheme .'_page_'. $slug_be .'-status',
				$slug_betheme .'_page_'. $slug_be .'-support',
				$slug_betheme .'_page_'. $slug_be .'-changelog',
				$slug_betheme .'_page_'. $slug_be .'-tools',
				'template_edit',
			);

			// dark mode
			if( ! empty( $ui_options['dashboard-ui'] ) && 'dark' == $ui_options['dashboard-ui'] && in_array( $screen_base, $be_dark ) ){
				$classes .= ' mfn-ui-dark';
			} else {
				$classes .= ' mfn-ui-light';
			}

			return $classes;
		}

		/**
		 * Enqueue styles/js GLOBAL
		 */

		public function _enqueue($hook)
 		{
 			// styles

			if ( ! mfn_opts_get('google-font-mode') ) {
				wp_enqueue_style('mfn-opts-font', 'https://fonts.googleapis.com/css?family=Poppins:300,400,500,600', false, MFN_THEME_VERSION, 'all');
			} elseif ( 'local' === mfn_opts_get( 'google-font-mode' ) ) {
				$path_fonts = mfn_uploads_dir('baseurl', 'fonts');
				wp_enqueue_style('mfn-opts-font', $path_fonts.'/mfn-local-fonts.css', false, MFN_THEME_VERSION, 'all');
			}

			wp_enqueue_style('mfn-opts-fontawesome', get_theme_file_uri('/fonts/fontawesome/fontawesome.css'), false, MFN_THEME_VERSION, 'all');

 			wp_enqueue_style('mfn-opts-icons', get_theme_file_uri('/fonts/mfn/icons.css'), false, MFN_THEME_VERSION, 'all');
 			wp_enqueue_style('mfn-opts', $this->url .'css/options.css', false, MFN_THEME_VERSION, 'all');

			// magnific popup

			wp_enqueue_style( 'mfn-magnific-popup', get_theme_file_uri('/functions/admin/assets/plugins/magnific-popup/magnific-popup.css'), array(), MFN_THEME_VERSION );
			wp_enqueue_script( 'mfn-magnific-popup', get_theme_file_uri('/functions/admin/assets/plugins/magnific-popup/magnific-popup.min.js'), false, MFN_THEME_VERSION, true );

			// scripts

			wp_enqueue_script( 'mfn-opts-plugins', $this->url .'js/plugins.js', array('jquery'), MFN_THEME_VERSION, true );
			wp_register_script( 'mfn-opts-js', $this->url .'js/options.js', array('jquery'), MFN_THEME_VERSION, true );

			if( ! empty( $hook ) && strpos( $hook, '-options' ) ){

				// syntax highlight

	 			$cm_args = array(
	 				'autoRefresh' => true,
	 			  'lint' => true,
	 				'indentUnit' => 2,
	 				'tabSize' => 2,
	 			);

	 			$codemirror['css']['codeEditor'] = wp_enqueue_code_editor(array(
	 				'type' => 'text/css',
	 				'codemirror' => $cm_args,
	 			));

	 			$codemirror['html']['codeEditor'] = wp_enqueue_code_editor(array(
	 				'type' => 'text/html',
	 				'codemirror' => $cm_args,
	 			));

				$codemirror['javascript']['codeEditor'] = wp_enqueue_code_editor(array(
	        'type'       => 'application/javascript',
	        'codemirror' => $cm_args,
		    ));

	 			wp_localize_script( 'mfn-opts-js', 'mfn_cm', $codemirror );

			}

			// color palette

			$palette = [];

			for ( $i=1; $i <= 14; $i++ ) {
				if( ! empty( $this->options['color-palette-'. $i] ) ){
					$palette[] = $this->options['color-palette-'. $i];
				}
			}

			if( empty($palette) ){
				$palette = ["#e91e63","#9c27b0","#673ab7","#3f51b5","#2196f3","#03a9f4","#00bcd4","#009688","#4caf50","#8bc34a","#cddc39","#ffeb3b","#ffc107"];
			}

			wp_localize_script( 'mfn-opts-js', 'mfn_palette', $palette );

			// enqueue

 			wp_enqueue_script( 'mfn-opts-js' );

 		}

		/**
		 * Enqueue styles/js THEME OPTIONS only
		 */

		public function _enqueue_options_page()
 		{
			wp_enqueue_style( 'mfn-dashboard', get_theme_file_uri('/functions/admin/assets/dashboard.css'), array(), MFN_THEME_VERSION );
			wp_enqueue_script('mfn-dashboard', get_theme_file_uri('/functions/admin/assets/dashboard.js'), false, MFN_THEME_VERSION, true);
 		}

		/**
		 * Download the options file, or display it
		 */

		public function _download_options()
		{
			if (! isset($_GET['secret']) || $_GET['secret'] != md5(AUTH_KEY.SECURE_AUTH_KEY)) {
				wp_die('Invalid Secret for options use');
				exit;
			}
			if (! isset($_GET['feed'])) {
				wp_die('No Feed Defined');
				exit;
			}

			$backup_options = get_option(str_replace('mfn-opts-', '', $_GET['feed']));
			$backup_options['mfn-opts-backup'] = '1';
			$backup_options = json_encode($backup_options);

			if (isset($_GET['action']) && $_GET['action'] == 'download_options') {
				header('Content-Description: File Transfer');
				header('Content-type: application/txt');
				header('Content-Disposition: attachment; filename="'. str_replace('mfn-opts-', '', $_GET['feed']) .'_options_'. date('d-m-Y') .'.txt"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				echo $backup_options;
				exit;
			} else {
				echo $backup_options;
				exit;
			}
		}

		/**
		 * Static CSS
		 */

		public function _static_CSS( $force = false )
		{
			if( ! $force && ( empty($_GET['settings-updated']) || empty($this->options['static-css']) ) ){
				return false;
			};

			$wp_filesystem = Mfn_Helper::filesystem();

			$upload_dir = wp_upload_dir();
			$path_be = wp_normalize_path( $upload_dir['basedir'] .'/betheme' );
			$path_css = wp_normalize_path( $path_be .'/css' );
			$path = wp_normalize_path( $path_css .'/static.css' );

			if( ! file_exists( $path_be ) ){
				wp_mkdir_p( $path_be );
			}

			if( ! file_exists( $path_css ) ){
				wp_mkdir_p( $path_css );
			}

			$css = "/* theme options */\n";
			$css .= mfn_styles_dynamic();

			$css .= "\n\n";

			$css .= "/* custom CSS */\n";
			$css .= mfn_styles_custom();

			$wp_filesystem->put_contents( $path, $css, FS_CHMOD_FILE );
		}

		/**
		 * Flush W3 Total Cache
		 */

		public function _flush_cache()
		{
			if( empty($_GET['settings-updated']) ){
				return false;
			};

			if ( function_exists('w3tc_flush_all') ){
				w3tc_flush_all();
			}

			// generate bebuilder file

			if( empty( $this->options['builder-visibility'] ) || 'hide' !== $this->options['builder-visibility'] ){
				Mfn_Helper::generate_bebuilder_items();
			}

		}

		/**
		 * Caching
		 */

		public function _cache_manager()
		{
			$cache_created = get_transient('betheme_hold-cache');
			$cache_activate = intval(mfn_opts_get('hold-cache'));

			if( 'changed' == $cache_created ){

				if ( $cache_activate ) {

					$this->_setup_cache();
					delete_transient('betheme_hold-cache');
					@clearstatcache();

				} else {

					$this->_remove_cache();
					delete_transient('betheme_hold-cache');
					@clearstatcache();

				}

			}

		}

		public function _setup_cache()
		{
			if( empty($_GET['settings-updated'] ) || ! mfn_opts_get('hold-cache') ) {
				return false;
			}

			$wp_filesystem = Mfn_Helper::filesystem();
			$htaccess_path = get_home_path() .'.htaccess';

			$htaccess_content = $wp_filesystem->get_contents($htaccess_path);
			$htaccess_new_content = $htaccess_content . Mfn_Helper::get_cache_text();

			$wp_filesystem->put_contents($htaccess_path, $htaccess_new_content, 0644);
		}

		public function _remove_cache()
		{
			$wp_filesystem = Mfn_Helper::filesystem();
			$htaccess_path = get_home_path() .'.htaccess';

			$htaccess_content = $wp_filesystem->get_contents($htaccess_path);
			$htaccess_new_content = preg_replace('/(# BEGIN BETHEME)(.|\n)*?(# END BETHEME)/', '', $htaccess_content);

			$wp_filesystem->put_contents($htaccess_path, $htaccess_new_content, 0644);
		}

		/**
		 * Theme Options -> Social -> Custom
		 * Save only filled fields (dynamically added)
		 */

		public function _register_custom_social()
		{
			if( empty( $this->options['custom-icon-count'] ) ){
				return false;
			};

			$number_loaded = 2; // we start from 2, because 1 custom icon is default
			$custom_amount = intval( $this->options['custom-icon-count'] ) + $number_loaded;

			$loop = 2; // we start from 2, because 1 custom icon is default

			for( $loop; $loop < $custom_amount; $loop++ ){

				if ( ! empty( $this->options['social-custom-icon-'. $loop] ) ) {

					$this->options['social-custom-icon-'. $number_loaded] = $this->options['social-custom-icon-'. $loop];
					$this->options['social-custom-link-'. $number_loaded] = $this->options['social-custom-link-'. $loop];
					$this->options['social-custom-title-'. $number_loaded] = $this->options['social-custom-title-'. $loop];

					// increase flag, it means one custom dynamically social is loaded

					$number_loaded++;

				} else {

					// Empty ALL values if icon is not provided

					$this->options['social-custom-icon-'. $loop] = '';
					$this->options['social-custom-link-'. $loop] = '';
					$this->options['social-custom-title-'. $loop] = '';

					// remove icon from social order field

					if( ! empty( $this->options['social-link']['order'] ) ){
						$name = 'custom-'. $number_loaded;
						$names = [
							$name .',',
							','. $name,
						]; // removes also "," sign
						$this->options['social-link']['order'] = str_replace($names, '', $this->options['social-link']['order']);
					}

				}

			}

			// update the new fields amount, to know how many fields have to appear
			$this->options['custom-icon-count'] = $number_loaded - 2;
		}

		/**
		 * Theme Options -> Fonts -> Custom
		 * Save only filled fields (dynamically added)
		 */

		public function _register_custom_fonts()
		{
			if( ! mfn_opts_get('font-custom-fields') ){
				return false;
			};

			$font_number_loaded = 3; // we start from 3, because 2 custom fonts are default ones
			$loop = 3; // we start from 3, because 2 custom fonts are default ones
			$custom_amount = intval(mfn_opts_get('font-custom-fields')) + $loop;

			for( $loop; $loop < $custom_amount; $loop++ ){

				if ( ! empty( mfn_opts_get( 'font-custom'. $loop ) ) ) {

					// Overwrite the most early font field, with values from field
					$this->options['font-custom'. $font_number_loaded] = mfn_opts_get( 'font-custom'. $loop );
					$this->options['font-custom'. $font_number_loaded .'-woff'] = mfn_opts_get( 'font-custom'. $loop .'-woff' );
					$this->options['font-custom'. $font_number_loaded .'-ttf' ] = mfn_opts_get( 'font-custom'. $loop .'-ttf' );

					// increase flag, it means one custom dynamically font is loaded.
					$font_number_loaded++;

				} else {

					// Empty ALL values if title is not provided
					$this->options['font-custom'. $loop] = '';
					$this->options['font-custom'. $loop .'-woff']	= '';
					$this->options['font-custom'. $loop .'-ttf'] = '';

				}

			}

			// update the new fields amount, to know how many fields have to appear
			$this->options['font-custom-fields'] = $font_number_loaded - 3;
		}

		/**
		 * Validate the Options options before insertion
		 */

		public function _validate_options($plugin_options)
		{
			set_transient('mfn-opts-saved', '1', 1000);

			// options | revision restore

			if( ! empty( $_POST['revision-time'] ) ){

				$time = htmlspecialchars(trim($_POST['revision-time']));
				$type = htmlspecialchars(trim($_POST['revision-type']));

				if( $time && $type ){

					$option_key = 'betheme_revision_'. $type;
					$revisions = get_option( $option_key );

					if( ! empty( $revisions[$time] ) ){
						$options = $revisions[$time];
						$options = unserialize(base64_decode($options), ['allowed_classes' => false]);
						return $options;
					}

				}

			}

			// options | import

			if (! empty($plugin_options['import'])) {

				if ($plugin_options['import_code'] != '') {

					// import from file
					$import = $plugin_options['import_code'];

				} elseif ($plugin_options['import_link'] != '') {

					// import from URL
					$import = wp_remote_retrieve_body(wp_safe_remote_get($plugin_options['import_link']));

				}

				$imported_options = json_decode($import, true);

				// FIX | Import 1-click Demo Data encoded options file

				if ($imported_options === false) {
					$import_tmp_fn = 'base'.'64_decode';
					$import = call_user_func($import_tmp_fn, trim($import));
					$imported_options = unserialize($import, ['allowed_classes' => false]);
				}

				if (is_array($imported_options)) {
					$imported_options['imported'] = 1;
					$imported_options['last_tab'] = false;
					return $imported_options;
				}

			}

			// options | defaults

			if (isset($plugin_options['defaults']) && ($plugin_options['defaults'] == 'Resetting...')) {
				$plugin_options = $this->_default_values();
				return $plugin_options;
			}

			// validate fields (if needed)

			$plugin_options = $this->_validate_values($plugin_options, $this->options);

			// save revision

			$this->set_revision( 'update', $plugin_options);

			// JS error handling

			if ( $this->errors ) {
				set_transient( 'mfn-opts-errors', $this->errors, 1000 );
			}

			if ( $this->warnings ) {
				set_transient( 'mfn-opts-warnings', $this->warnings, 1000 );
			}

			// after validate hooks

			do_action('mfn-opts-options-validate', $plugin_options, $this->options);
			do_action('mfn-opts-options-validate-'.$this->args['opt_name'], $plugin_options, $this->options);

			// unset unwanted attributes

			if( ! wp_doing_ajax() ){
				unset($plugin_options['defaults']);
				unset($plugin_options['import']);
				unset($plugin_options['import_code']);
				unset($plugin_options['import_link']);
			}













			// Create dedicated header to checkout

			if( function_exists('is_woocommerce') ) {

				$checkout_page_id = wc_get_page_id('checkout');

				if(!empty($checkout_page_id) && empty($plugin_options['gutenberg-checkout-header']) ){
					delete_post_meta($checkout_page_id, 'mfn_dedicated_header_template');
				}

				if( !empty($plugin_options['gutenberg-checkout-header']) ) {

					if( $plugin_options['gutenberg-checkout-header'] == 'create_checkout_header' ) {

						$header_object = json_decode('[{"icon":"section","uid":"dxflicd8","jsclass":"section","title":"Section","attr":{"scroll-visibility":"show","closeable-x":"left","tabber_nav_position":{"desktop":"left"},"query_post_orderby":"date","query_post_order":"DESC","query_terms_taxonomy":"category","query_terms_orderby":"ID","query_terms_order":"ASC","css_tabber_gap":{"desktop":"30"},"tabber_icon_position":{"desktop":"left"},"query_slider_columns":{"desktop":"1"},"background_switcher_ql":"default","query_display_slider_arrow_prev":"icon-left-open-big","query_display_slider_arrow_next":"icon-right-open-big","background_switcher":"default","background_switcher_hover":"default","background_switcher_scroll":"default","background_overlay_switcher":"default","css_advanced_align_items":{"val":{"desktop":"center"},"selector":".mcb-section-mfnuidelement .section_wrapper","style":"align-items"},"css_advanced_padding":{"val":{"desktop":{"top":"15px","bottom":"15px","right":"15px","left":"15px"}},"selector":".mcb-section-mfnuidelement","style":"padding"},"css_advanced_border_style":{"val":"solid","selector":".mcb-section-mfnuidelement","style":"border-style"},"css_advanced_border_width":{"val":{"desktop":"0 0 1px 0"},"selector":".mcb-section-mfnuidelement","style":"border-width"},"css_advanced_border_color":{"val":"rgba(0,0,0,0.1)","selector":".mcb-section-mfnuidelement","style":"border-color"},"vb_postid":"523","vb":1,"rwd":"desktop"},"ver":"default","wraps":[{"icon":"wrap","uid":"9aesvl74","size":"1/2","tablet_size":"1/2","mobile_size":"1/1","jsclass":"wrap","attr":{"css_grid_columns":{"val":{"desktop":"repeat(3, 1fr)"},"selector":".mcb-section .mcb-wrap-grid.mcb-wrap-mfnuidelement > .mcb-wrap-inner","style":"grid-template-columns"},"query_post_type":"post","query_post_orderby":"date","query_post_order":"DESC","query_terms_taxonomy":"category","query_terms_orderby":"ID","query_terms_order":"ASC","query_slider_columns":{"desktop":"1"},"background_switcher_ql":"default","query_display_slider_arrow_prev":"icon-left-open-big","query_display_slider_arrow_next":"icon-right-open-big","background_switcher":"default","background_switcher_hover":"default","background_overlay_switcher":"default","custom-responsive":"hide","css_advanced_flex_grow":{"val":{"desktop":"unset","laptop":"unset","tablet":"unset","mobile":"unset"},"selector":".mcb-section .mcb-wrap-mfnuidelement","style":"flex-grow"},"css_advanced_align_items":{"val":{"desktop":"center","laptop":"center","tablet":"center","mobile":"center"},"selector":".mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner","style":"align-items"},"vb_postid":"523","vb":1,"rwd":"desktop"},"title":"Wrap","laptop_size":"1/2","tablet_resized":"0","items":[{"type":"header_logo","jsclass":"header_logo","title":"Logo","icon":"header-logo","attr":{"image":"{featured_image:site}","css_logo_align":{"val":{"desktop":"center"},"selector":".mcb-section .mcb-wrap .mcb-item-mfnuidelement .logo-wrapper","style":"align-items"},"width_switcher":"custom","css_advanced_flex":{"val":{"desktop":"200px"},"selector":".mcb-section .mcb-wrap .mcb-item-mfnuidelement","style":"width"},"vb_postid":"523","vb":1,"rwd":"desktop"},"uid":"j2g64ir","size":"1/1","tablet_size":"1/1","laptop_size":"1/1","mobile_size":"1/1","tablet_resized":"0"}]},{"icon":"wrap","uid":"t24a0lx","size":"1/2","tablet_size":"1/2","mobile_size":"1/1","jsclass":"wrap","attr":{"css_grid_columns":{"val":{"desktop":"repeat(3, 1fr)"},"selector":".mcb-section .mcb-wrap-grid.mcb-wrap-mfnuidelement > .mcb-wrap-inner","style":"grid-template-columns"},"query_post_type":"post","query_post_orderby":"date","query_post_order":"DESC","query_terms_taxonomy":"category","query_terms_orderby":"ID","query_terms_order":"ASC","query_slider_columns":{"desktop":"1"},"background_switcher_ql":"default","query_display_slider_arrow_prev":"icon-left-open-big","query_display_slider_arrow_next":"icon-right-open-big","background_switcher":"default","background_switcher_hover":"default","background_overlay_switcher":"default","custom-responsive":"hide","css_advanced_flex_grow":{"val":{"desktop":"1","laptop":"1","tablet":"1","mobile":"1"},"selector":".mcb-section .mcb-wrap-mfnuidelement","style":"flex-grow"},"css_advanced_align_items":{"val":{"desktop":"center","laptop":"center","tablet":"center","mobile":"center"},"selector":".mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner","style":"align-items"},"css_advanced_justify_content":{"val":{"desktop":"flex-end"},"selector":".mcb-section .mcb-wrap-mfnuidelement > .mcb-wrap-inner","style":"justify-content"},"vb_postid":"523","vb":1,"rwd":"desktop"},"title":"Wrap","laptop_size":"1/2","tablet_resized":"0","items":[{"type":"header_icon","jsclass":"header_icon","title":"Icon","icon":"header-icon","attr":{"type":"cart","cart_total":"0","count":"0","icon_position":{"desktop":"top"},"icon_align":{"desktop":"center"},"css_icon_count_top":{"val":"-9px","selector":".mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-icon-box .icon-wrapper .header-cart-count,.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-icon-box .icon-wrapper .header-wishlist-count","style":"top"},"icon_count_posh":"1","css_icon_count_right":{"val":"-11px","selector":".mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-icon-box .icon-wrapper .header-cart-count,.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-icon-box .icon-wrapper .header-wishlist-count","style":"right"},"css_live-search-box-live-search-heading_opacity":{"val":"0.6","selector":".mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-live-search-box .mfn-live-search-heading","style":"opacity"},"width_switcher":"inline","vb_postid":"523","vb":1,"rwd":"desktop"},"uid":"cyh2pbr","size":"1/1","tablet_size":"1/1","laptop_size":"1/1","mobile_size":"1/1","tablet_resized":"0"}]}]}]', true);

						$header_css =json_decode('[{"jsclass":"header_logo","uid":"j2g64ir","attr":{"image":"{featured_image:site}","css_logo_align":{"val":{"desktop":"center"},"selector":".mcb-section .mcb-wrap .mcb-item-mfnuidelement .logo-wrapper","style":"align-items"},"width_switcher":"custom","css_advanced_flex":{"val":{"desktop":"200px"},"selector":".mcb-section .mcb-wrap .mcb-item-mfnuidelement","style":"width"}}},{"jsclass":"wrap","uid":"9aesvl74","attr":{"css_grid_columns":{"val":{"desktop":"repeat(3, 1fr)"},"selector":".mcb-section .mcb-wrap-grid.mcb-wrap-mfnuidelement > .mcb-wrap-inner","style":"grid-template-columns"},"query_post_type":"post","query_post_orderby":"date","query_post_order":"DESC","query_terms_taxonomy":"category","query_terms_orderby":"ID","query_terms_order":"ASC","query_slider_columns":{"desktop":"1"},"query_display_slider_arrow_prev":"icon-left-open-big","query_display_slider_arrow_next":"icon-right-open-big","css_advanced_flex_grow":{"val":{"desktop":"unset","laptop":"unset","tablet":"unset","mobile":"unset"},"selector":".mcb-section .mcb-wrap-mfnuidelement","style":"flex-grow"},"css_advanced_align_items":{"val":{"desktop":"center","laptop":"center","tablet":"center","mobile":"center"},"selector":".mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner","style":"align-items"}}},{"jsclass":"header_icon","uid":"cyh2pbr","attr":{"type":"cart","cart_total":"0","count":"0","icon_position":{"desktop":"top"},"icon_align":{"desktop":"center"},"css_icon_count_top":{"val":"-9px","selector":".mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-icon-box .icon-wrapper .header-cart-count,.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-icon-box .icon-wrapper .header-wishlist-count","style":"top"},"icon_count_posh":"1","css_icon_count_right":{"val":"-11px","selector":".mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-icon-box .icon-wrapper .header-cart-count,.mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-icon-box .icon-wrapper .header-wishlist-count","style":"right"},"css_live-search-box-live-search-heading_opacity":{"val":"0.6","selector":".mcb-section .mcb-wrap .mcb-item-mfnuidelement .mfn-live-search-box .mfn-live-search-heading","style":"opacity"},"width_switcher":"inline"}},{"jsclass":"wrap","uid":"t24a0lx","attr":{"css_grid_columns":{"val":{"desktop":"repeat(3, 1fr)"},"selector":".mcb-section .mcb-wrap-grid.mcb-wrap-mfnuidelement > .mcb-wrap-inner","style":"grid-template-columns"},"query_post_type":"post","query_post_orderby":"date","query_post_order":"DESC","query_terms_taxonomy":"category","query_terms_orderby":"ID","query_terms_order":"ASC","query_slider_columns":{"desktop":"1"},"query_display_slider_arrow_prev":"icon-left-open-big","query_display_slider_arrow_next":"icon-right-open-big","css_advanced_flex_grow":{"val":{"desktop":"1","laptop":"1","tablet":"1","mobile":"1"},"selector":".mcb-section .mcb-wrap-mfnuidelement","style":"flex-grow"},"css_advanced_align_items":{"val":{"desktop":"center","laptop":"center","tablet":"center","mobile":"center"},"selector":".mcb-section .mcb-wrap-mfnuidelement .mcb-wrap-inner","style":"align-items"},"css_advanced_justify_content":{"val":{"desktop":"flex-end"},"selector":".mcb-section .mcb-wrap-mfnuidelement > .mcb-wrap-inner","style":"justify-content"}}},{"jsclass":"section","uid":"dxflicd8","attr":{"scroll-visibility":"show","closeable-x":"left","tabber_nav_position":{"desktop":"left"},"query_post_orderby":"date","query_post_order":"DESC","query_terms_taxonomy":"category","query_terms_orderby":"ID","query_terms_order":"ASC","css_tabber_gap":{"desktop":"30"},"tabber_icon_position":{"desktop":"left"},"query_slider_columns":{"desktop":"1"},"background_switcher_ql":"default","query_display_slider_arrow_prev":"icon-left-open-big","query_display_slider_arrow_next":"icon-right-open-big","background_switcher":"default","background_switcher_hover":"default","background_switcher_scroll":"default","background_overlay_switcher":"default","css_advanced_align_items":{"val":{"desktop":"center"},"selector":".mcb-section-mfnuidelement .section_wrapper","style":"align-items"},"css_advanced_padding":{"val":{"desktop":{"top":"15px","bottom":"15px","right":"15px","left":"15px"}},"selector":".mcb-section-mfnuidelement","style":"padding"},"css_advanced_border_style":{"val":"solid","selector":".mcb-section-mfnuidelement","style":"border-style"},"css_advanced_border_width":{"val":{"desktop":"0 0 1px 0"},"selector":".mcb-section-mfnuidelement","style":"border-width"},"css_advanced_border_color":{"val":"rgba(0,0,0,0.1)","selector":".mcb-section-mfnuidelement","style":"border-color"},"vb_postid":"523","vb":1,"rwd":"desktop"}}]', true);

						$newheader = array(
						  'post_title'    	=> 'Checkout Header',
						  'post_status'   	=> 'publish',
						  'post_author'   	=> get_current_user_id(),
						  'post_type' 		=> 'template'
						);

						// Insert the post into the database
						$new_id = wp_insert_post( $newheader );

						update_post_meta( $new_id, 'mfn_template_type', 'header');
						update_post_meta( $new_id, 'mfn-page-object', $header_object );
						update_post_meta( $new_id, 'mfn-is-checkout-header', '1' ); // flag is auto created

						$mfn_styles = Mfn_Helper::preparePostUpdate( $header_css, $new_id, false );

						$save = wp_unslash( $header_object );

						if ( 'encode' == mfn_opts_get('builder-storage') ) {
							$new = call_user_func('base'.'64_encode', serialize($save));
						}else{
							$new = wp_slash( $save );
						}

						update_post_meta($new_id, 'mfn-page-items', $new);

						if( !empty($checkout_page_id) ){
							update_post_meta($checkout_page_id, 'mfn_dedicated_header_template', $new_id);
						}

						$plugin_options['gutenberg-checkout-header'] = $new_id;
					}else if( is_numeric($plugin_options['gutenberg-checkout-header']) && get_post_status($plugin_options['gutenberg-checkout-header']) == 'publish' ){
						update_post_meta($checkout_page_id, 'mfn_dedicated_header_template', $plugin_options['gutenberg-checkout-header']);
					}

				}

			}





			return $plugin_options;
		}

		/**
		 * Validate values from options form (used in settings api validate function)
		 * calls the custom validation class for the field so authors can override with custom classes
		 */

		public function _validate_values($plugin_options, $options)
		{
			foreach ($this->sections as $k => $section) {
				if (isset($section['fields'])) {
					foreach ($section['fields'] as $fieldk => $field) {

						$field['section_id'] = $k;

						if ( empty( $field['id'] ) || empty( $plugin_options[$field['id']] ) ) {
							continue;
						}

						// force validate of specified filed types

						/*
						if (isset($field[ 'type' ]) && ! isset($field[ 'validate' ])) {
							if ($field[ 'type' ] == 'color' || $field[ 'type' ] == 'color_multi') {
								$field[ 'validate' ] = 'color';
							}
						}
						*/

						// validate fields

						if ( isset($field['validate']) ) {

							$validate = 'MFN_Validation_'.$field['validate'];

							if (! class_exists($validate)) {
								require_once($this->dir .'validation/'. $field['validate'] .'/validation_'. $field[ 'validate' ] .'.php');
							}

							if (class_exists($validate)) {

								$validation = new $validate($field, $plugin_options[ $field['id'] ], $options[ $field['id'] ]);

								$plugin_options[ $field['id'] ] = $validation->value;

								if (isset($validation->error)) {
									$this->errors[] = $validation->error;
								}

								if (isset($validation->warning)) {
									$this->warnings[] = $validation->warning;
								}

								continue;
							}
						}

						if (isset($field['validate_callback']) && function_exists($field['validate_callback'])) {
							$callbackvalues = call_user_func($field['validate_callback'], $field, $plugin_options[$field['id']], $options[$field['id']]);

							$plugin_options[$field['id']] = $callbackvalues['value'];

							if (isset($callbackvalues['error'])) {
								$this->errors[] = $callbackvalues['error'];
							}

							if (isset($callbackvalues['warning'])) {
								$this->warnings[] = $callbackvalues['warning'];
							}
						}

					}
				}
			}

			return $plugin_options;
		}

		/**
		 * Register settings
		 * Add setting section
		 */

		public function _register_setting()
		{
			require_once( $this->dir .'fields/class-mfn-options-field.php' );

			register_setting( $this->args['opt_name'] .'_group', $this->args['opt_name'], array( $this, '_validate_options' ) );

			foreach ( $this->sections as $k => $section ) {

				add_settings_section( $k .'_section', $section['title'], false, $k .'_page' );

				if ( isset( $section['fields'] ) ) {
					foreach ( $section['fields'] as $field_key => $field ) {

						if ( isset( $field['title'] ) ) {
							// Muffin -> Custom label
							$field['title'] = apply_filters('betheme_options_filed_title', $field['title']);
							$th = isset( $field['sub_desc'] ) ? $field['title'] .'<span class="description">'. $field['sub_desc'] .'</span>' : $field['title'];
						} else {
							$th = '';
						}

						// Muffin -> Custom label
						if ( isset($field['options']) ) {
							$field['options'] = apply_filters('betheme_options_filed_options', $field['options']);
						}

						// both below for removing links and changing anchors
						if ( isset($field['sub_desc']) ) {
							$field['sub_desc'] = apply_filters('betheme_options_filed_desc', $field['sub_desc']);
						}

						if ( isset($field['desc']) ) {
							$field['desc'] = apply_filters('betheme_options_filed_desc', $field['desc']);
						}

						add_settings_field( $field_key .'_field', $th, array( $this, '_field_input' ), $k .'_page', $k .'_section', $field );
					}
				}

			}
		}

		/**
		 * Add setting field
		 */

		function _field_input( $field ){

			if( empty( $field['type'] ) || $field['type'] == 'header' ){
				return false;
			}

			$field_class = 'MFN_Options_'. $field['type'];

			if ( ! class_exists( $field_class ) ) {
				require_once( $this->dir .'fields/'. $field['type'] .'/field_'. $field['type'] .'.php' );
			}

			if( class_exists( $field_class ) ){

				if( isset( $this->options[$field['id']] ) ){
					$value = $this->options[$field['id']];
				} else {
					$value = isset($field['std']) ? $field['std'] : '';
				}

				$field_object = new $field_class( $field, $value, $this->args['opt_name'] );
				$field_object->render();

				// enqueue field JS and optional CSS

				if( method_exists( $field_class, 'enqueue' ) ){
					$field_object->enqueue();
				}

			}

		}

		/**
		 * Open options card HTML
		 */

		function card_open( $title, $args = [] ){

			$class = false;
			$param = false;

			$id = str_replace( ' ', '-', strtolower( $title ) );
			$id = str_replace( '&-', '', strtolower( $id ) );

			if( ! empty($args['prefix']) ){
				$id .= '-'. $args['prefix'];
			}

			$title = str_replace( '_', '', $title ); // duplicated names

			// class

			if( ! empty( $args['class'] ) ){
				$class = $args['class'];
			}

			// parameters

			if( ! empty( $args['attr'] ) ){
				$param = 'data-attr="'. $args['attr'] .'"';
			}

			// output -----

			echo '<div '
			    . ( isset($args['id']) ? "id='{$args['id']}' " : '' )
			    . ( isset($args['condition'])
			        ? 'class="mfn-card mfn-shadow-1 ' . $class . ' activeif activeif-' . $args['condition']['id'] . '" '
			          . "data-condition='" . htmlspecialchars(json_encode($args['condition']), ENT_QUOTES, 'UTF-8') . "' "
			        : 'class="mfn-card mfn-shadow-1 ' . $class . '" '
			      )
			    . 'data-card="' . $id . '" '
			    . $param
			    . '>';


        echo '<div class="card-header" data-search="'. strtolower($title) .'">';
          echo '<div class="card-title-group">';
            echo '<span class="card-icon mfn-icon-card"></span>';
            echo '<div class="card-desc">';
              echo '<h4 class="card-title">'. $title .'</h4>';
              if( ! empty( $args['sub_desc'] ) ){
								echo '<p class="card-subtitle">'. $args['sub_desc'] .'</p>';
							}
            echo '</div>';
          echo '</div>';
        echo '</div>';

        echo '<div class="card-content">';
        	echo '<div class="mfn-form mfn-form-horizontal">';

		}

		/**
		 * Close options card HTML
		 */

		function card_close(){

					echo '</div>';
				echo '</div>';
			echo '</div>';

		}

		/**
		 * Form row HTML
		 */

		function form_row( $field, $class = false ){

			$conditions = '';
			$row_class = '';

			if( isset( $field['args']['responsive'] ) ) {
				$row_class .= 'mfn_field_'. $field['args']['responsive'];
			}

			if( isset( $field['args']['condition'] ) ) {
				$row_class .= ' activeif';
				if( isset($field['args']['condition']['id']) ){
					$row_class .= ' activeif-'. $field['args']['condition']['id'];
				}else{
					foreach($field['args']['condition'] as $cond){
						if( isset($cond['id']) ){
							$row_class .= ' activeif-'. $cond['id'];
						}
					}
				}
				//$row_class .= ' activeif activeif-'. $field['args']['condition']['id'];
				$conditions = "data-condition='". json_encode($field['args']['condition']) ."'";
			}

			echo '<div class="mfn-form-row mfn-row '. esc_attr( $row_class ) .'" id="'. $field['args']['id'] .'" data-search="'. strtolower($field['args']['title']) .'" '. $conditions .'>';

				echo '<div class="row-column row-column-2">';
					echo '<label class="form-label">'. $field['args']['title'] .'</label>';
					if( ! empty($field['args']['responsive']) ) {
						Mfn_Options_field::get_responsive_swither($field['args']['responsive'],[ 'skip' => 'laptop' ]);
					}
				echo '</div>';

				echo '<div class="row-column row-column-10">';
					echo '<div class="form-content '. esc_attr( $class ) .'">';

						call_user_func( $field['callback'], $field['args'] );

					echo '</div>';
				echo '</div>';

			echo '</div>';

		}

		/**
		 * Custom do_settings_sections HTML
		 */

		function do_settings_sections( $page ) {

      global $wp_settings_sections, $wp_settings_fields;

      if ( ! isset( $wp_settings_sections[ $page ] ) ) {
        return;
      }

      foreach ( (array) $wp_settings_sections[ $page ] as $section ) {

        if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
          continue;
        }

				$section_tab_id = str_replace( '_section', '', $section['id'] );

				echo '<div class="mfn-card-group" data-tab="'. $section_tab_id .'">';

					$this->do_settings_fields( $page, $section );

					$this->card_close();

				echo '</div>';

      }

  	}

		/**
		 * Custom do_settings_fields HTML
		 */

		function do_settings_fields( $page, $section ) {
	    global $wp_settings_fields;

			$section_id = $section['id'];

	    if ( ! isset( $wp_settings_fields[ $page ][ $section_id ] ) ) {
        return;
	    }

			// search results heading

			$section_tab_id = str_replace( '_section', '', $section_id );
			foreach ( $this->menu as $k => $item ) {
		    if (array_search( $section_tab_id, $item['sections']) !== false ) {
	        $parent_key = $k;
	        $parent_title = $item['title'];
	        break;
		    }
			}

			$title = $section['title'];
			$link = 'admin.php?page=be-options#'. $section_tab_id;

			echo '<div class="search-card-heading" class="search-card-'. esc_attr($parent_key) .'"><h3><span class="page-title">'. esc_html($parent_title) .'</span><span class="sep">»</span><a href="'. esc_url($link) .'" class="subpage-title">'. esc_html($title) .'</a></h3></div>';

			// loop thru fields

	    foreach ( (array) $wp_settings_fields[ $page ][ $section_id ] as $field ) {
        $class = '';

        if ( ! empty( $field['args']['class'] ) ) {
          $class = $field['args']['class'];
        }

				if( empty( $field['args']['type'] ) || $field['args']['type'] == 'header' ){

					// card wrapper

					if( isset( $field['args']['join'] ) ){
						$this->card_close();
					}

					if( ! isset( $field['args']['sub_desc'] ) ){
						$field['args']['sub_desc'] = false;
					}

					/* Custom Custom Fonts -> Kinda like dynamic load */

					if ( ! empty($field['args']['class']) && 'mfn_new_font' == $field['args']['class'] )  {
						$this->custom_font_loader($field);
					}

					if ( ! empty($field['args']['class']) && 'custom-icon-card' == $field['args']['class'] )  {
						$this->custom_social_loader($field);
					}

					$this->card_open( $field['args']['title'], $field['args'] );

				} elseif( 'info' == $field['args']['type'] ) {

					// info field

					if( isset( $field['args']['join'] ) ){
						$this->card_close();
					}

					call_user_func( $field['callback'], $field['args'] );

				} else {

					// fields

	        $this->form_row( $field, $class );

				}

	    }
		}

		public function mfn_display_tmpl_conditions($conditions, $post_id = false, $tmpl_type = false) {
			$html = '';
			if( !empty($conditions) && is_iterable($conditions) ){
				foreach($conditions as $c=>$con){
					if($con->rule == 'include'){ $html .= '<span class="mfn-tmpl-conditions-incude">+ '; }else{ $html .= '<span class="mfn-tmpl-conditions-exclude">- '; }

					//print_r($con);

					if($con->var == 'everywhere'){
						$html .= 'Entire Site';
					}elseif($con->var == 'archives'){
						if( empty($con->archives) ){
							$html .= 'All archives';
						}else{

							if( strpos($con->archives, ':') !== false){
								$expl = explode(':', $con->archives);
								$pt = get_post_type_object( $expl[0] );
								$term = get_term( $expl[1] );
							}elseif( !empty($con->archives) ){
								$pt = get_post_type_object( $con->archives );
							}

							$html .= 'Archive: '.$pt->label;

							if( !empty($term->name) ) $html .= '/'.$term->name;

						}
					}elseif($con->var == 'singular'){
						if( empty($con->singular) ){

							$html .= 'All singulars';

						}else{

							if( strpos($con->singular, ':') !== false){
								$expl = explode(':', $con->singular);
								$pt = get_post_type_object( $expl[0] );
								$term = get_term( $expl[1] );
							}elseif( !empty($con->singular) && $con->singular == 'front-page' ){
								$html .= 'Front Page</span><br>';
								continue;
							}elseif( !empty($con->singular) ){
								$pt = get_post_type_object( $con->singular );
							}

							$html .= 'Singular: '.$pt->label;

							if( !empty($term->name) ) $html .= '/'.$term->name;

						}
					}elseif($con->var == 'shop'){
						if( get_post_meta($post_id, 'mfn_template_type', true) == 'single-product' ){
							$html .= ' All products';
						}else{
							$html .= ' Shop';
						}
					}elseif($con->var == 'productcategory'){
						if($con->productcategory == 'all'){
							$html .= ' All categories';
						}else{
							$term = get_term_by('term_id', $con->productcategory, 'product_cat');
							if( !empty($term->name) ) $html .= 'Category: '.$term->name;
						}
					}elseif($con->var == 'producttag'){
						if($con->producttag == 'all'){
							$html .= ' All tags';
						}else{
							$term = get_term_by('term_id', $con->producttag, 'product_tag');
							if( !empty($term->name) ) $html .= 'Tag: '.$term->name;
						}
					}elseif($con->var == 'all'){
						if( $tmpl_type == 'blog' ){
							$html .= 'Entire blog';
						}elseif( $tmpl_type == 'portfolio' ){
							$html .= 'Entire portfolio';
						}else{
							$html .= 'All singulars';
						}
					}elseif($con->var == 'category'){
						$term = get_term_by('term_id', $con->category, 'category');
						if( !empty($term->name) ) $html .= 'Category: '.$term->name;
					}elseif($con->var == 'post_tag'){
						$term = get_term_by('term_id', $con->post_tag, 'post_tag');
						if( !empty($term->name) ) $html .= 'Tag: '.$term->name;
					}elseif($con->var == 'portfolio-types'){
						$term = get_term_by('term_id', $con->{'portfolio-types'}, 'portfolio-types');
						if( !empty($term->name) ) $html .= 'Category: '.$term->name;
					}
					$html .= '</span><br>';
				}
			}else{
				$html .= '<span class="mfn-tmpl-conditions-na">n/a</span>';
			}

			return $html;
		}

		/**
		 * HTML OUTPUT
		 */

		function _options_page_html(){

			global $wpdb;

			$form_class = '';
			$mfn_content_classes = array('mfn-content');

			$header_tmpls = $wpdb->get_results( "SELECT p.ID, p.post_date, p.post_title, m.meta_value FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as m on m.post_id = p.ID JOIN {$wpdb->prefix}postmeta as t on t.post_id = p.ID WHERE p.post_type = 'template' and p.post_status = 'publish' and m.meta_key = 'mfn_template_conditions' and t.meta_key = 'mfn_template_type' and t.meta_value = 'header' LIMIT 5" );
			$footer_tmpls = $wpdb->get_results( "SELECT p.ID, p.post_date, p.post_title, m.meta_value FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as m on m.post_id = p.ID JOIN {$wpdb->prefix}postmeta as t on t.post_id = p.ID WHERE p.post_type = 'template' and p.post_status = 'publish' and m.meta_key = 'mfn_template_conditions' and t.meta_key = 'mfn_template_type' and t.meta_value = 'footer' LIMIT 5" );
			$shop_tmpls = $wpdb->get_results( "SELECT p.ID, p.post_date, p.post_title, m.meta_value FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as m on m.post_id = p.ID JOIN {$wpdb->prefix}postmeta as t on t.post_id = p.ID WHERE p.post_type = 'template' and p.post_status = 'publish' and m.meta_key = 'mfn_template_conditions' and t.meta_key = 'mfn_template_type' and t.meta_value IN ('shop-archive', 'archive-product') LIMIT 5" );
			$product_tmpls = $wpdb->get_results( "SELECT p.ID, p.post_date, p.post_title, m.meta_value FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as m on m.post_id = p.ID JOIN {$wpdb->prefix}postmeta as t on t.post_id = p.ID WHERE p.post_type = 'template' and p.post_status = 'publish' and m.meta_key = 'mfn_template_conditions' and t.meta_key = 'mfn_template_type' and t.meta_value = 'single-product' LIMIT 5" );
			$blog_tmpls = $wpdb->get_results( "SELECT p.ID, p.post_date, p.post_title, m.meta_value FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as m on m.post_id = p.ID JOIN {$wpdb->prefix}postmeta as t on t.post_id = p.ID WHERE p.post_type = 'template' and p.post_status = 'publish' and m.meta_key = 'mfn_template_conditions' and t.meta_key = 'mfn_template_type' and t.meta_value IN ('blog', 'archive-post') LIMIT 5" );
			$portfolio_tmpls = $wpdb->get_results( "SELECT p.ID, p.post_date, p.post_title, m.meta_value FROM {$wpdb->prefix}posts as p JOIN {$wpdb->prefix}postmeta as m on m.post_id = p.ID JOIN {$wpdb->prefix}postmeta as t on t.post_id = p.ID WHERE p.post_type = 'template' and p.post_status = 'publish' and m.meta_key = 'mfn_template_conditions' and t.meta_key = 'mfn_template_type' and t.meta_value IN ('portfolio', 'archive-portfolio') LIMIT 5" );

			$mfn_templates_html = '';

			if( !empty($header_tmpls) ) {
	    		$mfn_templates_html .= '<div class="mfn-content-tmpls mfn-content-header-tmpls">';
		    		$mfn_templates_html .= '<div class="mfn-alert">';
						$mfn_templates_html .= '<div class="alert-icon mfn-icon-information"></div>';
						$mfn_templates_html .= '<div class="alert-content">';
							$mfn_templates_html .= '<p>Your site uses <a href="edit.php?post_type=template&tab=header">Templates</a> for <strong>Header</strong>.<br />';
							$mfn_templates_html .= 'Some options from the Theme Options may not work because they don\'t affect Templates.</p>';
						$mfn_templates_html .= '</div>';
					$mfn_templates_html .= '</div>';

					$mfn_templates_html .= '<div class="mfn-content-templates-counter"><p class="mfn-content-templates-counter-left">You have <span class="num">'.count($header_tmpls).'</span> '.(count($header_tmpls) == 1 ? 'used template:' : 'used templates:').'</p><a class="mfn-content-templates-counter-right" href="edit.php?post_type=template&tab=header">Go to templates</a></div>';

					foreach( $header_tmpls as $i=>$item ) {

						$mfn_templates_html .= '<div class="mfn-content-tmpl-row mfn-card">';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-1"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder">'.$item->post_title.'</a></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-2"><span>'.$item->post_date.'</span></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-3 conditions">';
								$conditions = !empty($item->meta_value) ? json_decode($item->meta_value) : array();
								$header_conditions = $this->mfn_display_tmpl_conditions($conditions, $item->ID);
								$mfn_templates_html .= $header_conditions;
								if( strpos($header_conditions, 'Entire Site') !== false ) $mfn_content_classes[] = 'mfn-content-has-header-tmpls';
							$mfn_templates_html .= '</div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-4"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder" class="mfn-btn mfn-btn-navy">Manage template</a></div>';
						$mfn_templates_html .= '</div>';
					}

				$mfn_templates_html .= '</div>';
	    	}


	    	if( !empty($shop_tmpls) ) {
	    		$mfn_templates_html .= '<div class="mfn-content-tmpls mfn-content-shop-tmpls">';
		    		$mfn_templates_html .= '<div class="mfn-alert">';
						$mfn_templates_html .= '<div class="alert-icon mfn-icon-information"></div>';
						$mfn_templates_html .= '<div class="alert-content">';
							$mfn_templates_html .= '<p>Your site uses <a href="edit.php?post_type=template&tab=shop-archive">Templates</a> for <strong>Shop</strong>.<br />';
							$mfn_templates_html .= 'Some options from the Theme Options may not work because they don\'t affect Templates.</p>';
						$mfn_templates_html .= '</div>';
					$mfn_templates_html .= '</div>';

					$mfn_templates_html .= '<div class="mfn-content-templates-counter"><p class="mfn-content-templates-counter-left">You have <span class="num">'.count($shop_tmpls).'</span> '.(count($shop_tmpls) == 1 ? 'used template:' : 'used templates:').'</p><a class="mfn-content-templates-counter-right" href="edit.php?post_type=template&tab=shop-archive">Go to templates</a></div>';

					foreach( $shop_tmpls as $i=>$item ){

						$mfn_templates_html .= '<div class="mfn-content-tmpl-row mfn-card">';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-1"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder">'.$item->post_title.'</a></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-2"><span>'.$item->post_date.'</span></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-3 conditions">';
								$conditions = !empty($item->meta_value) ? json_decode($item->meta_value) : array();
								$shop_conditions = $this->mfn_display_tmpl_conditions($conditions, $item->ID);
								$mfn_templates_html .= $shop_conditions;
								if( strpos($shop_conditions, 'Shop') !== false ) $mfn_content_classes[] = 'mfn-content-has-shop-tmpls';
							$mfn_templates_html .= '</div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-4"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder" class="mfn-btn mfn-btn-navy">Manage template</a></div>';
						$mfn_templates_html .= '</div>';
					}

				$mfn_templates_html .= '</div>';
	    	}


	    	if( !empty($product_tmpls) ) {
	    		$mfn_templates_html .= '<div class="mfn-content-tmpls mfn-content-product-tmpls">';
		    		$mfn_templates_html .= '<div class="mfn-alert">';
						$mfn_templates_html .= '<div class="alert-icon mfn-icon-information"></div>';
						$mfn_templates_html .= '<div class="alert-content">';
							$mfn_templates_html .= '<p>Your site uses <a href="edit.php?post_type=template&tab=single-product">Templates</a> for <strong>Product</strong>.<br />';
							$mfn_templates_html .= 'Some options from the Theme Options may not work because they don\'t affect Templates.</p>';
						$mfn_templates_html .= '</div>';
					$mfn_templates_html .= '</div>';

					$mfn_templates_html .= '<div class="mfn-content-templates-counter"><p class="mfn-content-templates-counter-left">You have <span class="num">'.count($product_tmpls).'</span> '.(count($product_tmpls) == 1 ? 'used template:' : 'used templates:').'</p><a class="mfn-content-templates-counter-right" href="edit.php?post_type=template&tab=single-product">Go to templates</a></div>';

					foreach( $product_tmpls as $i=>$item ){

						$mfn_templates_html .= '<div class="mfn-content-tmpl-row mfn-card">';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-1"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder">'.$item->post_title.'</a></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-2"><span>'.$item->post_date.'</span></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-3 conditions">';
								$conditions = !empty($item->meta_value) ? json_decode($item->meta_value) : array();
								$product_conditions = $this->mfn_display_tmpl_conditions($conditions, $item->ID);
								$mfn_templates_html .= $product_conditions;
								if( strpos($product_conditions, 'All products') !== false ) $mfn_content_classes[] = 'mfn-content-has-product-tmpls';
							$mfn_templates_html .= '</div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-4"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder" class="mfn-btn mfn-btn-navy">Manage template</a></div>';
						$mfn_templates_html .= '</div>';
					}

				$mfn_templates_html .= '</div>';
	    	}


	    	if( !empty($footer_tmpls) ) {
	    		$mfn_templates_html .= '<div class="mfn-content-tmpls mfn-content-footer-tmpls">';
		    		$mfn_templates_html .= '<div class="mfn-alert">';
						$mfn_templates_html .= '<div class="alert-icon mfn-icon-information"></div>';
						$mfn_templates_html .= '<div class="alert-content">';
							$mfn_templates_html .= '<p>Your site uses <a href="edit.php?post_type=template&tab=footer">Templates</a> for <strong>Footer</strong>.<br />';
							$mfn_templates_html .= 'Some options from the Theme Options may not work because they don\'t affect Templates.</p>';
						$mfn_templates_html .= '</div>';
					$mfn_templates_html .= '</div>';

					$mfn_templates_html .= '<div class="mfn-content-templates-counter"><p class="mfn-content-templates-counter-left">You have <span class="num">'.count($footer_tmpls).'</span> '.(count($footer_tmpls) == 1 ? 'used template:' : 'used templates:').'</p><a class="mfn-content-templates-counter-right" href="edit.php?post_type=template&tab=footer">Go to templates</a></div>';

					foreach( $footer_tmpls as $i=>$item ){

						$mfn_templates_html .= '<div class="mfn-content-tmpl-row mfn-card">';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-1"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder">'.$item->post_title.'</a></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-2"><span>'.$item->post_date.'</span></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-3 conditions">';
								$conditions = !empty($item->meta_value) ? json_decode($item->meta_value) : array();
								$footer_conditions = $this->mfn_display_tmpl_conditions($conditions, $item->ID);
								$mfn_templates_html .= $footer_conditions;
								if( strpos($footer_conditions, 'Entire Site') !== false ) $mfn_content_classes[] = 'mfn-content-has-footer-tmpls';
							$mfn_templates_html .= '</div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-4"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder" class="mfn-btn mfn-btn-navy">Manage template</a></div>';
						$mfn_templates_html .= '</div>';
					}

				$mfn_templates_html .= '</div>';
	    	}

	    	if( !empty($blog_tmpls) ) {
	    		$mfn_templates_html .= '<div class="mfn-content-tmpls mfn-content-blog-tmpls">';
		    		$mfn_templates_html .= '<div class="mfn-alert">';
						$mfn_templates_html .= '<div class="alert-icon mfn-icon-information"></div>';
						$mfn_templates_html .= '<div class="alert-content">';
							$mfn_templates_html .= '<p>Your site uses <a href="edit.php?post_type=template&tab=blog">Templates</a> for <strong>Blog</strong>.<br />';
							$mfn_templates_html .= 'Some options from the Theme Options may not work because they don\'t affect Templates.</p>';
						$mfn_templates_html .= '</div>';
					$mfn_templates_html .= '</div>';

					$mfn_templates_html .= '<div class="mfn-content-templates-counter"><p class="mfn-content-templates-counter-left">You have <span class="num">'.count($blog_tmpls).'</span> '.(count($blog_tmpls) == 1 ? 'used template:' : 'used templates:').'</p><a class="mfn-content-templates-counter-right" href="edit.php?post_type=template&tab=blog">Go to templates</a></div>';

					foreach( $blog_tmpls as $i=>$item ){

						$mfn_templates_html .= '<div class="mfn-content-tmpl-row mfn-card">';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-1"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder">'.$item->post_title.'</a></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-2"><span>'.$item->post_date.'</span></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-3 conditions">';
								$conditions = !empty($item->meta_value) ? json_decode($item->meta_value) : array();
								$blog_conditions = $this->mfn_display_tmpl_conditions($conditions, $item->ID, 'blog');
								$mfn_templates_html .= $blog_conditions;
								if( strpos($blog_conditions, 'Entire blog') !== false ) $mfn_content_classes[] = 'mfn-content-has-blog-tmpls';
							$mfn_templates_html .= '</div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-4"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder" class="mfn-btn mfn-btn-navy">Manage template</a></div>';
						$mfn_templates_html .= '</div>';
					}

				$mfn_templates_html .= '</div>';
	    	}

	    	if( !empty($portfolio_tmpls) ) {
	    		$mfn_templates_html .= '<div class="mfn-content-tmpls mfn-content-portfolio-tmpls">';
		    		$mfn_templates_html .= '<div class="mfn-alert">';
						$mfn_templates_html .= '<div class="alert-icon mfn-icon-information"></div>';
						$mfn_templates_html .= '<div class="alert-content">';
							$mfn_templates_html .= '<p>Your site uses <a href="edit.php?post_type=template&tab=portfolio">Templates</a> for <strong>Portfolio</strong>.<br />';
							$mfn_templates_html .= 'Some options from the Theme Options may not work because they don\'t affect Templates.</p>';
						$mfn_templates_html .= '</div>';
					$mfn_templates_html .= '</div>';

					$mfn_templates_html .= '<div class="mfn-content-templates-counter"><p class="mfn-content-templates-counter-left">You have <span class="num">'.count($portfolio_tmpls).'</span> '.(count($portfolio_tmpls) == 1 ? 'used template:' : 'used templates:').'</p><a class="mfn-content-templates-counter-right" href="edit.php?post_type=template&tab=portfolio">Go to templates</a></div>';

					foreach( $portfolio_tmpls as $i=>$item ){

						$mfn_templates_html .= '<div class="mfn-content-tmpl-row mfn-card">';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-1"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder">'.$item->post_title.'</a></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-2"><span>'.$item->post_date.'</span></div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-3 conditions">';
								$conditions = !empty($item->meta_value) ? json_decode($item->meta_value) : array();
								$portfolio_conditions = $this->mfn_display_tmpl_conditions($conditions, $item->ID, 'portfolio');
								$mfn_templates_html .= $portfolio_conditions;
								if( strpos($portfolio_conditions, 'Portfolio') !== false ) $mfn_content_classes[] = 'mfn-content-has-portfolio-tmpls';
							$mfn_templates_html .= '</div>';
							$mfn_templates_html .= '<div class="mfn-content-tmpl-col mfn-content-tmpl-col-4"><a href="post.php?post='.$item->ID.'&action='.apply_filters('betheme_slug', 'mfn').'-live-builder" class="mfn-btn mfn-btn-navy">Manage template</a></div>';
						$mfn_templates_html .= '</div>';
					}

				$mfn_templates_html .= '</div>';
	    	}

			// theme skin

			if( ! empty($this->options['skin']) && 'custom' != $this->options['skin'] ){
				$form_class .= ' skin-selected';
			}

			// output

			echo '<div id="mfn-options" class="mfn-ui mfn-options loading '. esc_attr( $form_class ) .'" data-page="options">';

				// dashboard header
				$is_theme_options = true;
				include_once get_theme_file_path('/functions/admin/templates/parts/header.php');

				do_action('mfn-opts-page-before-form');

				echo '<form method="post" action="options.php" enctype="multipart/form-data" >';

				echo '<input type="hidden" name="mfn-builder-nonce" value="'. wp_create_nonce( 'mfn-builder-nonce' ) .'">';

					settings_fields( $this->args['opt_name'] .'_group' );

					$this->options['last_tab'] = isset( $this->options['last_tab'] ) ? $this->options['last_tab'] : false;
					echo '<input type="hidden" id="last_tab" name="'. $this->args['opt_name'] .'[last_tab]" value="'. $this->options['last_tab'] .'" />';

					// menu

				  echo '<div class="mfn-overlay"></div>';

				  echo '<div class="mfn-menu">';

			      echo '<nav>';
							echo '<ul>';

								foreach( $this->menu as $menu_key => $menu_item ){

									echo '<li class="mfn-menu-'. $menu_key .'">';

										echo '<a href="#"><span class="mfn-icon"></span>'. $menu_item['title']. '</a>';

										if( is_array( $menu_item['sections'] ) ){
											echo '<ul class="mfn-submenu">';
												foreach( $menu_item['sections'] as $sub_item ){
						              echo '<li data-id="'. $sub_item .'"><a href="#'. $sub_item .'"><span>'. $this->sections[$sub_item]['title'] .'</span></a></li>';
												}
					            echo '</ul>';
										}

									echo '</li>';

								}

								// import

								echo '<li class="mfn-menu-backup">';
									echo '<a href="#"><span class="mfn-icon"></span>'. __('Backup & Reset', 'mfn-opts'). '</a>';
									echo '<ul class="mfn-submenu">';
										echo '<li data-id="backup-history"><a href="#history"><span>'. __('History', 'mfn-opts'). '</span></a></li>';
										echo '<li data-id="backup-reset"><a href="#backup-reset"><span>'. __('Backup & Reset', 'mfn-opts'). '</span></a></li>';
									echo '</ul>';
								echo '</li>';

							echo '</ul>';
			      echo '</nav>';

				  echo '</div>';

					// content

					echo '<div class="mfn-wrapper device-wrapper" data-device="desktop">';

				    echo '<div class="mfn-top">';

					    echo '<span class="mfn-icon mfn-icon-card"></span>';

					    echo '<div class="mfn-topbar">';
					      echo '<div class="subheader-options-group topbar-title topbar-breadcrumbs">';
					        echo '<h3><span class="page-title"></span> <span class="sep">&raquo;</span> <span class="subpage-title"></span></h3>';
					      echo '</div>';
					    echo '</div>';

							echo '<div class="mfn-subheader-placeholder"></div>';

					    echo '<div class="mfn-subheader">';
					      echo '<div class="subheader-options-group subheader-tabber">';
					        echo '<ul class="subheader-tabs">';
					        echo '</ul>';
					      echo '</div>';

					      echo '<div class="subheader-options-group subheader-buttons">';

									echo '<div class="search-wrapper">';
										echo '<a class="mfn-btn mfn-btn-blank btn-only-icon search-open" href="#" data-tooltip="'. __('Search options', 'mfn-opts'). '"><span class="btn-wrapper"><span class="mfn-icon mfn-icon-search"></span></span></a>';
										echo '<input id="options-search" type="text" value="" placeholder="Enter option name.."/>';
										echo '<a class="mfn-btn mfn-btn-blank btn-only-icon search-close" href="#" data-tooltip="'. __('Close', 'mfn-opts'). '"><span class="btn-wrapper"><span class="mfn-icon mfn-icon-close"></span></span></a>';
									echo '</div>';

					        echo '<a class="mfn-btn mfn-btn-blank btn-only-icon" target="_blank" href="https://support.muffingroup.com/" data-tooltip="'. __('Help Center', 'mfn-opts'). '"><span class="btn-wrapper"><span class="mfn-icon mfn-icon-support"></span></span></a>';
									echo '<a class="mfn-btn mfn-btn-blank btn-only-icon" target="_blank" href="https://support.muffingroup.com/changelog/" data-tooltip="'. __('Changelog', 'mfn-opts'). '"><span class="btn-wrapper"><span class="mfn-icon mfn-icon-changelog"></span></span></a>';
					        // echo '<a class="mfn-btn mfn-btn-blank btn-only-icon" href="#" data-tooltip="Settings"><span class="btn-wrapper"><span class="mfn-icon mfn-icon-settings"></span></span></a>';

									if( mfn_is_registered() ){
										echo '<input type="submit" value="'. __('Save changes', 'mfn-opts') .'" class="mfn-btn mfn-btn-green btn-save-changes"/>';
									} else {
										echo '<a href="admin.php?page=betheme" class="mfn-btn btn-save-changes">Register now</a>';
									}

								echo '</div>';
					    echo '</div>';

					   echo '</div>';

				    echo '<div class="'.implode(' ', $mfn_content_classes).'">';

							// old button styles + static CSS

							if( isset( $this->options['button-style'] ) && ! empty( $this->options['static-css'] ) ) {
								echo '<div class="mfn-alert mfn-alert-info">';
									echo '<div class="alert-icon mfn-icon-information"></div>';
									echo '<div class="alert-content">';
										echo '<p><strong>Button settings</strong> changed in recent theme update. Please check <a href="admin.php?page=be-options#buttons">button colors</a> and <strong>Save changes</strong> to regenerate static CSS file.</p>';
									echo '</div>';
								echo '</div>';
							}

				    	echo $mfn_templates_html;

							foreach( $this->sections as $section_key => $section ){
								$this->do_settings_sections( $section_key .'_page' );
								echo " \n";
							}

							echo '<div class="mfn-card-group modal-revisions" data-tab="backup-history">';

								$this->card_open( __('History', 'mfn-opts') );

								echo '<div class="mfn-form-row mfn-row">';

									$revisions = $this->get_revisions();

									echo '<div class="row-column row-column-4">';
										echo '<div class="form-content form-content-full-width backup-revisions">';

											echo '<h5>Update:</h5>';

		                  echo '<ul class="mfn-revisions-list" data-type="update">';
												$this->the_revisions_list( $revisions['update'] );
											echo '</ul>';

											echo '<p class="info">Created every <b>Save Changes</b> click</p>';

		                echo '</div>';
	                echo '</div>';

									echo '<div class="row-column row-column-4">';
										echo '<div class="form-content form-content-full-width backup-revisions">';

											echo '<h5>Revision:</h5>';

		                  echo '<ul class="mfn-revisions-list" data-type="revision">';
												$this->the_revisions_list( $revisions['revision'] );
											echo '</ul>';

											echo '<p class="info">Saved using <b>Save revision</b> button</p>';

											echo '<a class="mfn-btn mfn-btn-blue mfn-save-revision" href="#"><span class="btn-wrapper">'. __('Save revision', 'mfn-opts') .'</span></a>&nbsp;';

		                echo '</div>';
	                echo '</div>';

									echo '<div class="row-column row-column-4">';
										echo '<div class="form-content form-content-full-width backup-revisions">';

											echo '<h5>Backup:</h5>';

		                  echo '<ul class="mfn-revisions-list" data-type="backup">';
												$this->the_revisions_list( $revisions['backup'] );
											echo '</ul>';

											echo '<p class="info">Backups are being created before restoring any revision</p>';

		                echo '</div>';
	                echo '</div>';

								echo '</div>';

								$this->card_close();

							echo '</div>';

							echo '<div class="mfn-card-group" data-tab="backup-reset">';

								$this->card_open( __('Export', 'mfn-opts') );

								echo '<div class="mfn-form-row mfn-row">';

									echo '<div class="row-column row-column-12">';
										echo '<div class="form-content form-content-full-width backup-export">';

											echo '<div class="desc-group">';

												echo '<p>'. __('Here, you can copy/download your theme’s option settings. Keep this safe, as you can use it as a backup. You can also use it to restore your settings on this site (or any other). You also have the handy option to copy the link to yours sites settings which you can then use to duplicate on another site.', 'mfn-opts') .'</p>';

												echo '<a class="mfn-btn backup-export-show-textarea" href="#"><span class="btn-wrapper">'. __('Copy', 'mfn-opts') .'</span></a>&nbsp;';
												echo '<a class="mfn-btn backup-export-show-input" href="#"><span class="btn-wrapper">'. __('Copy link', 'mfn-opts') .'</span></a>&nbsp;';

												if( defined('AUTH_KEY') && defined('SECURE_AUTH_KEY') ){
													echo '<a class="mfn-btn mfn-btn-blue" href="'. esc_url( add_query_arg( array( 'feed' => 'mfn-opts-'. $this->args['opt_name'], 'action' => 'download_options', 'secret' => md5( AUTH_KEY.SECURE_AUTH_KEY ) ), site_url() ) ) .'"><span class="btn-wrapper">'. __('Download', 'mfn-opts') .'</span></a>';
												} else {
													echo '<p>AUTH_KEY or SECURE_AUTH_KEY undefined. Please check your wp-config.php file.</p>';
												}

												$options = $this->options;
												$options['mfn-opts-backup'] = '1';
												$options = '###'. serialize( $options ) .'###';

												echo '<textarea class="mfn-form-control mfn-form-textarea backup-export-textarea" rows="8">'. $options .'</textarea>';

												if( defined('AUTH_KEY') && defined('SECURE_AUTH_KEY') ){
													echo '<input class="mfn-form-control mfn-form-input backup-export-input" type="text"  value="'. esc_url( add_query_arg( array( 'feed' => 'mfn-opts-'.$this->args['opt_name'], 'secret' => md5( AUTH_KEY.SECURE_AUTH_KEY ) ), site_url() ) ) .'" />';
												}
											echo '</div>';

										echo '</div>';
									echo '</div>';

								echo '</div>';

								$this->card_close();

								$this->card_open( __('Import', 'mfn-opts') );

								echo '<div class="mfn-form-row mfn-row">';

									echo '<div class="row-column row-column-12">';
										echo '<div class="form-content form-content-full-width backup-import">';

											echo '<div class="desc-group">';

												echo '<a class="mfn-btn backup-import-show-textarea" href="#"><span class="btn-wrapper">'. __('Import from file', 'mfn-opts') .'</span></a>&nbsp;';
												echo '<a class="mfn-btn backup-import-show-input" href="#"><span class="btn-wrapper">'. __('Import from link', 'mfn-opts') .'</span></a>';

												echo '<div class="backup-import-group backup-import-textarea">';

													echo '<p>'. __('Paste content of your backup file below and hit <b>Import</b> to restore your site’s options from a backup.', 'mfn-opts') .'</p>';
													echo '<textarea class="mfn-form-control mfn-form-textarea" name="'. $this->args['opt_name'] .'[import_code]" rows="8"></textarea>';

													echo '<input type="submit" class="mfn-btn mfn-btn-blue" name="'. $this->args['opt_name'] .'[import]" value="'. __( 'Import', 'mfn-opts' ) .'">';
													echo '<span class="warning">'. __('WARNING! This will overwrite all existing options, please proceed with caution!', 'mfn-opts') .'</span>';

												echo '</div>';

												echo '<div class="backup-import-group backup-import-input">';

													echo '<p>'. __('Paste the link to another site’s options set and hit <b>Import</b> to load the options from that site.', 'mfn-opts') .'</p>';
													echo '<input type="text" class="mfn-form-control mfn-form-input" name="'. $this->args['opt_name'] .'[import_link]" value="" />';

													echo '<input type="submit" class="mfn-btn mfn-btn-blue" name="'. $this->args['opt_name'] .'[import]" value="'. __( 'Import', 'mfn-opts' ) .'">';
													echo '<span class="warning">'. __('WARNING! This will overwrite all existing options, please proceed with caution!', 'mfn-opts') .'</span>';

												echo '</div>';

											echo '</div>';

										echo '</div>';
									echo '</div>';

								echo '</div>';

								$this->card_close();

								$this->card_open( __('Reset', 'mfn-opts') );

								echo '<div class="mfn-form-row mfn-row">';

									echo '<div class="row-column row-column-12">';
										echo '<div class="form-content form-content-full-width backup-reset">';

											echo '<div class="desc-group">';

												echo '<div class="backup-reset-step step-1">';
													echo '<a href="#" class="mfn-btn mfn-btn-primary backup-reset-pre-confirm">'. __( 'Reset to default', 'mfn-opts' ) .'</a>';
													echo '<span class="warning">'. __('WARNING! This will overwrite all existing options, please proceed with caution!', 'mfn-opts') .'</span>';
												echo '</div>';

												echo '<div class="backup-reset-step step-2">';
													echo 'Insert security code: <b>r3s3t</b>';
													echo '<input class="mfn-form-control mfn-form-input backup-reset-security-code" type="text" value="" autocomplete="off" />';
													echo '<input type="submit" class="mfn-btn mfn-btn-blue backup-reset-confirm" name="'. $this->args['opt_name'] .'[defaults]" value="'. __( 'Confirm reset ALL options', 'mfn-opts' ). '" />';
												echo '</div>';

											echo '</div>';

										echo '</div>';
									echo '</div>';

								echo '</div>';

								$this->card_close();

							echo '</div>';

						echo '</div>';

					echo '</div>';

				echo '</form>';

				// modal: restore revision

				echo '<div class="mfn-modal modal-confirm modal-confirm-revision">';
					echo '<div class="mfn-modalbox mfn-form mfn-shadow-1">';

		        echo '<div class="modalbox-header">';

	            echo '<div class="options-group">';
                echo '<div class="modalbox-title-group">';
                  echo '<span class="modalbox-icon mfn-icon-undo"></span>';
                  echo '<div class="modalbox-desc">';
                    echo '<h4 class="modalbox-title">'. esc_html('Restore revision', 'mfn-opts') .'</h4>';
                  echo '</div>';
                echo '</div>';
	            echo '</div>';

	            echo '<div class="options-group">';
                echo '<a class="mfn-option-btn mfn-option-blank btn-large btn-modal-close" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a>';
	            echo '</div>';

		        echo '</div>';

		        echo '<div class="modalbox-content">';

	            echo '<img class="icon" alt="" src="'. get_theme_file_uri( '/muffin-options/svg/warning.svg' ) .'">';
	            echo '<h3>'. esc_html('Restore revision?', 'mfn-opts') .'</h3>';
	            echo '<p>'. esc_html('Please confirm. There is no undo.', 'mfn-opts') .'<br />'. esc_html('Backup revision will be created.', 'mfn-opts') .'</p>';
	            echo '<a class="mfn-btn mfn-btn-blue btn-wide btn-modal-confirm-revision" href="#"><span class="btn-wrapper">'. esc_html('Restore', 'mfn-opts') .'</span></a>';

					 	echo '</div>';

			    echo '</div>';
		    echo '</div>';

				// modal | icon select

				Mfn_Icons::the_modal();

				// modal | icon select

				if( ! mfn_is_registered() ){
					Mfn_Helper::the_modal_register();
				}

			echo '</div>';
		}

		/**
		 * Custom social loader
		 */

		public function custom_social_loader($field){
			$loop = 2;
			$custom_amount = intval(mfn_opts_get('custom-icon-count')) + $loop;
			$callback = $field['callback'];
			$class = '';

			for( $loop; $loop < $custom_amount; $loop++ ){

				$this->card_open( __('Custom '.$loop, 'mfn-opts'), array(
					'args' => 'Custom '.$loop,
					'join' => true,
				) );

				$this->form_row( array(
					'args' => array(
						'id' => 'social-custom-icon-'.$loop,
						'type' => 'icon',
						'title' => __('Icon', 'mfn-opts'),
					),
					'callback' => $callback,
				), $class );

				$this->form_row( array(
					'args' => array(
						'id' => 'social-custom-link-'.$loop,
						'type' => 'text',
						'title' => __('Link', 'mfn-opts'),
					),
					'callback' => $callback,
				), $class );

				$this->form_row( array(
					'args' => array(
						'id' => 'social-custom-title-'.$loop,
						'type' => 'text',
						'title' => __( 'Title', 'mfn-opts' ),
					),
					'callback' => $callback,
				), $class );

				$this->card_close();
			}
		}

		/**
		 * Custom font loader
		 */

		public function custom_font_loader($field){
			$loop = 3;
			$custom_amount = intval(mfn_opts_get('font-custom-fields')) + $loop;
			$callback = $field['callback'];
			$class = '';

			for( $loop; $loop < $custom_amount; $loop++ ){

				$this->card_open( __('Font '.$loop, 'mfn-opts'), array(
					'args' => 'Font '.$loop,
					'join' => true,
				) );

				$this->form_row( array(
					'args' => array(
						'id' => 'font-custom'.$loop,
						'type' => 'text',
						'title' => __('Name', 'mfn-opts'),
						'desc' => __( 'Name for Custom Font uploaded below.<br />Font will show on fonts list after <b>click the Save Changes</b> button.' , 'mfn-opts' ),
					),
					'callback' => $callback,
				), $class );

				$this->form_row( array(
					'args' => array(
						'id' => 'font-custom'.$loop.'-woff',
						'type' => 'upload',
						'title' => __('.woff', 'mfn-opts'),
						'desc' => __( 'WordPress 5.0 blocks .woff upload. Please use <a target="_blank" href="plugin-install.php?s=Disable+Real+MIME+Check&tab=search&type=term">Disable Real MIME Check</a> plugin.', 'mfn-opts' ),
						'data' => 'font',
					),
					'callback' => $callback,
				), $class );

				$this->form_row( array(
					'args' => array(
						'id' => 'font-custom'.$loop.'-ttf',
						'type' => 'upload',
						'title' => __( '.ttf', 'mfn-opts' ),
						'desc' => __( 'WordPress 5.0 blocks .ttf upload. Please use <a target="_blank" href="plugin-install.php?s=Disable+Real+MIME+Check&tab=search&type=term">Disable Real MIME Check</a> plugin.', 'mfn-opts' ),
						'data' => 'font',
					),
					'callback' => $callback,
				), $class );

				$this->card_close();
			}
		}

	}

}
