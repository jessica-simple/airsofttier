<?php
/**
 * Template Name: Sitemap
 *
 * @package Betheme
 * @author Muffin Group
 * @link https://muffingroup.com
 */

get_header();
?>

<div id="Content">
	<div class="content_wrapper clearfix">
		<main class="sections_group">
			<section class="section">
				<div class="section_wrapper clearfix">

					<?php
						if (have_posts()) {
							the_post();
						}
					?>

					<div class="column one">
						<div class="mcb-column-inner">
							<ul class="list">
								<?php wp_list_pages('title_li='); ?>
							</ul>
						</div>
					</div>

				</div>
			</section>
		</main>
	</div>
</div>

<?php get_footer();
