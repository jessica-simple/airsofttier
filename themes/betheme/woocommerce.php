<?php
/**
 * The template for displaying woocommerce.
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

get_header( 'shop' );

global $wp_filter;

?>

<div id="Content">
	<div class="content_wrapper clearfix">

		<main class="sections_group">
			<div class="entry-content" itemprop="mainContentOfPage">

				<?php if( ! mfn_is_blocks() ): ?>
				<section class="section woocommerce_before_main_content">
					<div class="section_wrapper no-bebuilder-section">
						<div class="column column-margin-0px one">
							<?php
								/**
								 * Hook: woocommerce_before_main_content.
								 *
								 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
								 * @hooked woocommerce_breadcrumb - 20
								 * @hooked WC_Structured_Data::generate_website_data() - 30
								 */
								do_action( 'woocommerce_before_main_content' );
							?>
						</div>
					</div>
				</section>
				<?php endif; ?>

				<?php

					if( is_product() ){
						get_template_part( 'template-single-product' );
					}else{
						get_template_part( 'template-shop-archive' );
					}

				?>

				<?php if( ! mfn_is_blocks() ): ?>
				<section class="section woocommerce_after_main_content">
					<div class="section_wrapper no-bebuilder-section">
						<div class="column column-margin-0px one">
							<?php
								/**
								 * Hook: woocommerce_after_main_content.
								 */
								do_action( 'woocommerce_after_main_content' );
							?>
						</div>
					</div>
				</section>
			<?php endif; ?>

			</div>

		</main>

		<?php get_sidebar(); ?>

	</div>
</div>

<?php get_footer();
