<?php
/**
 * Search template file.
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

get_header();
?>

<div id="Content">
	<div class="content_wrapper clearfix">

		<main class="sections_group">

			<section class="section">
				<div class="section_wrapper clearfix">

					<div class="column one">
						<div class="mcb-column-inner">
							<?php
								while (have_posts()) {
									the_post(); ?>
										<article id="post-<?php the_ID(); ?>" <?php post_class('clearfix'); ?>>
											<?php the_content(false); ?>
										</article>
									<?php
								}
								mfn_pagination();
							?>
						</div>
					</div>

				</div>
			</section>

		</main>

	</div>
</div>

<?php get_footer();
