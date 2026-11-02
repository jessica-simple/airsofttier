<?php
/** Standalone reseller document; the dashboard supplies its own main landmark. */
defined( 'ABSPATH' ) || exit;
$is_dashboard = Airsoft_Product_Manager_Reseller_Portal::dashboard_view();
// Read-only compatibility check: BeTheme's hooked metadata may supply a viewport.
$theme_viewport = function_exists( 'mfn_opts_get' ) && false !== has_action( 'wp_head', 'mfn_meta' ) && mfn_opts_get( 'responsive' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
<?php if ( ! $theme_viewport ) : ?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'airsoft-reseller-standalone' . ( $is_dashboard ? ' airsoft-reseller-dashboard' : '' ) ); ?>>
<?php wp_body_open(); ?>
<div class="airsoft-standalone">
<?php if ( $is_dashboard ) : ?>
<div class="airsoft-standalone-content">
<?php else : ?>
<main id="airsoft-reseller-main" class="airsoft-standalone-main">
<?php endif; ?>
<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
<?php if ( $is_dashboard ) : ?>
</div>
<?php else : ?>
</main>
<?php endif; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
