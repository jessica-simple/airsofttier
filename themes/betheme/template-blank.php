<?php
/**
 * Template Name: Blank Page
 *
 * @package Betheme
 * @author Muffin Group
 * @link https://muffingroup.com
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js<?php echo esc_attr(mfn_user_os()); ?>"<?php mfn_tag_schema(); ?>>

<head>

<meta charset="<?php bloginfo('charset'); ?>" />
<?php wp_head(); ?>

</head>

<body <?php body_class('template-blank'); ?>>

	<?php do_action('mfn_hook_top'); ?>

	<?php do_action('mfn_hook_content_before'); ?>

	<div id="Content">
		<div class="content_wrapper clearfix">

			<main class="sections_group">
				<?php
					while (have_posts()) {

						the_post();

						$mfn_builder = new Mfn_Builder_Front(get_the_ID());
						$mfn_builder->show();

					}
				?>
			</main>

			<?php get_sidebar(); ?>

		</div>
	</div>

	<?php do_action('mfn_hook_content_after'); ?>

	<?php do_action('mfn_hook_bottom'); ?>

	<?php do_action('mfn_wp_footer_before'); ?>



	<?php 


	if( empty($_GET['visual']) ){
		$mfn_popups = mfn_addons_ID('popup');

		if( isset($mfn_popups) && is_array($mfn_popups) && count($mfn_popups) > 0){
			foreach ($mfn_popups as $popup_tmpl_id) {
				if( get_post_status($popup_tmpl_id) == 'publish' && get_post_type($popup_tmpl_id) == 'template' ) {
					$popup = new MfnPopup($popup_tmpl_id);
					$popup->render();
				}
			}
		}
	}

	if( apply_filters('bebuilder_preview', false) ) {
		get_footer();
	}else{
		wp_footer();
	}

	?>

</body>
</html>
