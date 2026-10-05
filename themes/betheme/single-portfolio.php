<?php
/**
 * The Template for displaying all single posts.
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

get_header();
global $mfn_global;
$class = '';
if (get_post_meta(get_the_ID(), 'mfn-post-template', true) == 'builder') {
	$class .= 'no-padding';
}

$single_post_nav = array(
	'hide-header'	=> false,
	'hide-sticky'	=> false,
	'in-same-term'	=> false,
);

$opts_single_post_nav = mfn_opts_get('prev-next-nav');

if (is_array($opts_single_post_nav)) {
	if (isset($opts_single_post_nav['hide-header'])) {
		$single_post_nav['hide-header'] = true;
	}
	if (isset($opts_single_post_nav['hide-sticky'])) {
		$single_post_nav['hide-sticky'] = true;
	}
	if (isset($opts_single_post_nav['in-same-term'])) {
		$single_post_nav['in-same-term'] = true;
	}
}

$post_prev = get_adjacent_post($single_post_nav['in-same-term'], '', true, 'portfolio-types');
$post_next = get_adjacent_post($single_post_nav['in-same-term'], '', false, 'portfolio-types');

$portfolio_page_id = mfn_opts_get('portfolio-page');

?>

<div id="Content" class="<?php echo esc_attr($class); ?>">
	<div class="content_wrapper clearfix">

		<main class="sections_group">

			<?php

			if (! $single_post_nav['hide-sticky']) {
				echo mfn_post_navigation_sticky($post_prev, 'prev', 'icon-left-open-big');
				echo mfn_post_navigation_sticky($post_next, 'next', 'icon-right-open-big');
			}

			$tmp_id = $mfn_global['singular_template'];

			if( !empty($_GET['elementor-preview']) ){

				// elementor builder fix

				while (have_posts()) {

					the_post();
					the_content();

				}


			}else if( ( empty($_GET['visual']) || !empty($_GET['mfn-template-id']) ) && !empty( $tmp_id ) && is_numeric( $tmp_id ) && get_post_type( $tmp_id ) == 'template' && get_post_status( $tmp_id ) == 'publish' ) {

				$mfn_builder = new Mfn_Builder_Front( $tmp_id );
				$mfn_builder->show();

			}else{

				if (get_post_meta(get_the_ID(), 'mfn-post-template', true) == 'builder') {

					// template: builder -----

					// prev & next post navigation

					mfn_post_navigation_sort();

					while (have_posts()) {

						the_post();

						$mfn_builder = new Mfn_Builder_Front(get_the_ID());
						$mfn_builder->show();

					}

				} else {

					// template: default

					while (have_posts()) {
						the_post();
						get_template_part('includes/content', 'single-portfolio');
					}

					if (mfn_opts_get('portfolio-comments')) {
						echo '<section class="section section-page-comments">';
							echo '<div class="section_wrapper clearfix">';
								echo '<div class="column one comments">';
									echo '<div class="mcb-column-inner">';
										comments_template('', true);
										echo '</div>';
								echo '</div>';
							echo '</div>';
						echo '</section>';
					}
				}

			}

			?>

		</main>

		<?php get_sidebar(); ?>

	</div>
</div>

<?php get_footer();
