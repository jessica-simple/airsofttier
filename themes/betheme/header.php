<?php
/**
 * The Header for our theme.
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */
?><!DOCTYPE html>
<?php
	if ($_GET && key_exists('mfn-rtl', $_GET)):
		echo '<html class="no-js" lang="ar" dir="rtl">';
	else:
?>
<html <?php language_attributes(); ?> class="no-js <?php echo esc_attr(mfn_html_classes()); ?>"<?php mfn_tag_schema(); ?> >
<?php endif; ?>

<head>

<meta charset="<?php bloginfo('charset'); ?>" />
<?php wp_head();

$style = '';
if( mfn_opts_get('preloader') ){
	$style = 'style="opacity:0"';
}

global $mfn_global;

if( empty($_GET['visual']) && !empty($mfn_global['sidemenu']) ){
	// global sidemenu
	$sidemenu = new MfnSideMenu($mfn_global['sidemenu']);
	$sidemenu->css();
}

if( !empty( get_post_meta(get_the_ID(), 'mfn-post-js', true) ) ) echo get_post_meta(get_the_ID(), 'mfn-post-js', true);

?>

</head>

<body <?php body_class(); ?> <?php echo $style; ?>>

	<?php if( mfn_is_blocks() ): ?>

		<div id="Wrapper">

	<?php else: // mfn_is_blocks() ?>

		<?php

			if( !empty(get_post_meta(get_the_ID(), 'mfn-post-one-page', true)) && get_post_meta(get_the_ID(), 'mfn-post-one-page', true) == '1' ){
				echo '<div id="home"></div>';
			}
		?>

		<?php do_action('mfn_hook_top'); ?>

		<?php get_template_part('includes/header', 'sliding-area'); ?>

		<?php
			if( mfn_header_style(true) == 'header-creative' && !$mfn_global['header'] ) {
				get_template_part('includes/header', 'creative');
			}
		?>

		<div id="Wrapper">

	<?php

		if (mfn_header_style(true) == 'header-below') {
			echo mfn_slider();
		}

		// be setup wizard
		if( isset( $_GET['mfn-setup-preview'] ) ) {
			$mfn_global['header'] = false;
		}

		if( !empty($mfn_global['header']) ){
			$is_visual = false;
			if( !empty($_GET['visual']) ) $is_visual = true;
			get_template_part( 'includes/header', 'template', array( 'id' => $mfn_global['header'], 'visual' => $is_visual ) );
		}else{
			get_template_part( 'includes/header', 'classic' );
		}

		if ( 'intro' == get_post_meta( mfn_ID(), 'mfn-post-template', true ) ) {
			get_template_part( 'includes/header', 'single-intro' );
		}
	?>

		<?php do_action( 'mfn_hook_content_before' ); ?>

	<?php endif; // mfn_is_blocks() ?>

<?php // omit closing php tag
