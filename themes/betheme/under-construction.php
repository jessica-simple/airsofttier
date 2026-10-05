<?php
/**
 * Template Name: Under Construction
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

get_header();

$customID = apply_filters( 'wpml_object_id', mfn_opts_get('construction-page'), 'page', TRUE  );
?>

<div id="Content" class="content-under-construction">
	<div class="content_wrapper clearfix">

		<?php if ($customID): ?>

			<main class="sections_group">
				<?php
					$mfn_builder = new Mfn_Builder_Front($customID, true);
					$mfn_builder->show();
				?>
			</main>

		<?php else: ?>

			<?php
				$translate['days'] = mfn_opts_get('translate') ? mfn_opts_get('translate-days', 'days') : __('days', 'betheme');
				$translate['hours'] = mfn_opts_get('translate') ? mfn_opts_get('translate-hours', 'hours') : __('hours', 'betheme');
				$translate['minutes']	= mfn_opts_get('translate') ? mfn_opts_get('translate-minutes', 'minutes') : __('minutes', 'betheme');
				$translate['seconds']	= mfn_opts_get('translate') ? mfn_opts_get('translate-seconds', 'seconds') : __('seconds', 'betheme');
			?>

			<main class="sections_group">

				<section class="section center section-uc-1">
					<div class="section_wrapper clearfix">

						<div class="column one">
							<div class="mcb-column-inner">
								<?php

									$logo = mfn_opts_get('logo-img', get_theme_file_uri('/images/logo/logo.png'));
									$logo_under_construction = mfn_opts_get('logo-under-construction');
									$logo_text = mfn_opts_get('logo-text');

									if( $logo_text ){

										echo '<span class="text-logo">'. esc_html($logo_text) .'</span>';

									} else {

										echo '<a id="logo" href="'. esc_url(get_home_url()) .'" title="'. esc_attr(get_bloginfo('name')) .'">';
											if( $logo_under_construction ){
												echo '<img class="scale-with-grid" src="'. esc_url($logo_under_construction) .'" alt="'. esc_attr(get_bloginfo('name')) .'" />';
											} else {
												echo '<img class="scale-with-grid" src="'. esc_url($logo) .'" alt="'. esc_attr(get_bloginfo('name')) .'" />';
											}
										echo '</a>';

									}

								?>
							</div>
						</div>

					</div>
				</section>

				<section class="section section-border-top section-uc-2">
					<div class="section_wrapper clearfix">

						<div class="column one column_fancy_heading">
							<div class="mcb-column-inner">

								<div class="fancy_heading fancy_heading_icon">
									<div data-anim-type="bounceIn" class="animate bounceIn">
										<span class="icon_top"><i class="icon-clock" aria-hidden="true"></i></span>
										<h2><?php echo wp_kses(mfn_opts_get('construction-title'), mfn_allowed_html()); ?></h2>
										<div class="inside">
											<span class="big"><?php echo wp_kses_post(mfn_opts_get('construction-text')); ?></span>
										</div>
									</div>
								</div>

							</div>
						</div>

						<?php if (mfn_opts_get('construction-date')): ?>

							<div class="column one column_downcount">
								<div class="mcb-column-inner clearfix">

									<div class="downcount" data-date="<?php echo esc_attr(mfn_opts_get('construction-date')); ?>" data-offset="<?php echo esc_attr(mfn_opts_get('construction-offset')); ?>">

										<div class="column one-fourth column_quick_fact">
											<div class="mcb-column-inner">
												<div class="quick_fact">
													<div data-anim-type="zoomIn" class="animate zoomIn">
														<div class="number-wrapper">
															<div class="number days">00</div>
														</div>
														<h3 class="title"><?php echo esc_html($translate['days']); ?></h3>
														<hr class="hr_narrow">
													</div>
												</div>
											</div>
										</div>

										<div class="column one-fourth column_quick_fact">
											<div class="mcb-column-inner">
												<div class="quick_fact">
													<div data-anim-type="zoomIn" class="animate zoomIn">
														<div class="number-wrapper">
															<div class="number hours">00</div>
														</div>
														<h3 class="title"><?php echo esc_html($translate['hours']); ?></h3>
														<hr class="hr_narrow">
													</div>
												</div>
											</div>
										</div>

										<div class="column one-fourth column_quick_fact">
											<div class="mcb-column-inner">
												<div class="quick_fact">
													<div data-anim-type="zoomIn" class="animate zoomIn">
														<div class="number-wrapper">
															<div class="number minutes">00</div>
														</div>
														<h3 class="title"><?php echo esc_html($translate['minutes']); ?></h3>
														<hr class="hr_narrow">
													</div>
												</div>
											</div>
										</div>

										<div class="column one-fourth column_quick_fact">
											<div class="mcb-column-inner">
												<div class="quick_fact">
													<div data-anim-type="zoomIn" class="animate zoomIn">
														<div class="number-wrapper">
															<div class="number seconds">00</div>
														</div>
														<h3 class="title"><?php echo esc_html($translate['seconds']); ?></h3>
														<hr class="hr_narrow">
													</div>
												</div>
											</div>
										</div>

									</div>

								</div>
							</div>

						<?php endif; ?>

					</div>
				</section>

				<section class="section section-border-top section-uc-3">
					<div class="section_wrapper clearfix">

						<div class="column one-fourth column_column">
							<div class="mcb-column-inner"></div>
						</div>

						<div class="column one-second column_column">
							<div class="mcb-column-inner">
								<div data-anim-type="fadeInUpLarge" class="animate fadeInUpLarge">
									<?php echo do_shortcode(mfn_opts_get('construction-contact')); ?>
								</div>
							</div>
						</div>

						<div class="column one-fourth column_column">
							<div class="mcb-column-inner"></div>
						</div>

					</div>
				</section>

			</main>

		<?php endif; ?>

	</div>
</div>

<?php
wp_enqueue_script('mfn-waypoints', get_theme_file_uri('/js/plugins/waypoints.min.js'), ['jquery'], MFN_THEME_VERSION, true);
wp_enqueue_script('mfn-countdown', get_theme_file_uri('/js/plugins/countdown.min.js'), ['jquery'], MFN_THEME_VERSION, true);

get_footer();
