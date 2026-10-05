<?php
	defined( 'ABSPATH' ) || exit;

	global $current_user;

	$is_custom_content = apply_filters('betheme_dashboard_content', 'filter_me') !== 'filter_me';
?>

<div id="mfn-dashboard" class="mfn-ui mfn-dashboard" data-page="dashboard">

	<input type="hidden" name="mfn-builder-nonce" value="<?php echo wp_create_nonce( 'mfn-builder-nonce' ); ?>">

	<?php
		// header
		include_once get_theme_file_path('/functions/admin/templates/parts/header.php');
	?>

	<div class="mfn-wrapper">

		<?php
			// subheader
			$current = 'dashboard';
			include_once get_theme_file_path('/functions/admin/templates/parts/subheader.php');
		?>

		<div class="mfn-dashboard-wrapper">

			<?php if ( $is_custom_content ): ?>

				<?php echo stripslashes(apply_filters('betheme_dashboard_content', '')); ?>

			<?php else: ?>

				<?php if( ! mfn_is_registered() ): ?>

					<div class="mfn-row">

						<div class="row-column row-column-8">

							<div class="mfn-card mfn-shadow-1" data-card="theme-register">

								<div class="card-header">
									<div class="card-title-group">
										<span class="card-icon mfn-icon-register-light"></span>
										<div class="card-desc">
											<h4 class="card-title">Theme Registration</h4>
										</div>
									</div>
									<?php if( ! WHITE_LABEL ): ?>
									<div class="card-links-group">
										<a href="#" class="data-collection" data-modal="data-collection"><span class="mfn-icon mfn-icon-support-light"></span> Data collection</a>
										<a target="_blank" href="https://api.muffingroup.com/licenses/"><span class="mfn-icon mfn-icon-folder-open-light"></span> Check your licenses</a>
									</div>
									<?php endif; ?>
								</div>

								<div class="card-content">
									<form class="form-register mfn-form mfn-form-reg" method="post">

										<input type="hidden" name="mfn-setup-nonce" value="<?php echo wp_create_nonce( 'mfn-setup-register' ); ?>">
						        <input type="hidden" name="mfn-builder-nonce" value="<?php echo wp_create_nonce( 'mfn-builder-nonce' ); ?>">
						        <input type="hidden" name="action" value="mfn_setup_register">
						        <input type="submit" name="submit" value="mfn_setup_register" style="display:none">

										<div class="form-register-input">

											<span class="mfn-icon mfn-icon-password-light"></span>
											<input type="text" name="code" placeholder="Paste your purchase code here" class="mfn-form-control of-input" size="36">

											<div class="mfn-tooltip-box where-is">
												<a>Where is code?</a>
												<div class="tooltip-box-inner">
													<p><strong>Where can I find my purchase code?</strong></p>
													<ol>
														<li>Please go to <a target="_blank" href="https://themeforest.net/downloads">ThemeForest.net/downloads</a></li>
														<li>Click the <strong>Download</strong> button in Betheme row</li>
														<li>Select <strong>License Certificate &amp; Purchase code</strong></li>
														<li>Copy <strong>Item Purchase Code</strong></li>
													</ol>
												</div>
											</div>

											<span class="form-message">aaa</span>

										</div>

										<a id="register" class="mfn-btn mfn-btn-fw mfn-btn-green"><span class="btn-wrapper">Register theme</span></a>

									</form>

									<?php
										// new license
										include get_theme_file_path('/functions/admin/templates/parts/new-license.php');
									?>

								</div>
							</div>




              <!-- 1 -->
							<div class="mfn-card mfn-shadow-1" data-card="theme-register">

								<div class="card-header">
									<div class="card-title-group">
										<span class="card-icon mfn-icon-register-light"></span>
										<div class="card-desc">
											<h4 class="card-title">License Key Details</h4>
										</div>
									</div>
									<?php if( ! WHITE_LABEL ): ?>
									<div class="card-links-group">
										<a target="_blank" href="#"><span class="mfn-icon mfn-icon-support-light"></span> Need help with registration?</a>
									</div>
									<?php endif; ?>
								</div>

								<div class="card-content">

                    <div class="license-wrapper unregistered">
                      <div class="license-wrapper-left">
                        <div class="license-image"><img src="<?php echo get_theme_file_uri('functions/admin/assets/svg/be.svg'); ?>" alt="" /></div>
                        <div class="license-details">
                          <div><span class="license-title">Betheme Subscription</span></div>
                          <div class="license-status-wrapper">
                            <span class="license-status unregistered">UNREGISTERED</span>
                            <span class="license-info">Register domain in you account</span>
                          </div>
                        </div>
                      </div>
                      <div class="license-wrapper-right">
                        <a class="card-link check-status" href="#">
                          <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_2850_454)">
                              <path d="M14 2.36253V4.85625C14 5.3395 13.6082 5.73125 13.125 5.73125H10.6313C10.148 5.73125 9.75628 5.3395 9.75628 4.85625C9.75628 4.373 10.148 3.98125 10.6313 3.98125H11.2998C10.3336 2.59643 8.75019 1.75 7 1.75C4.82237 1.75 2.90259 3.06012 2.10913 5.08766C1.93298 5.53768 1.42538 5.75969 0.975406 5.58359C0.525383 5.40747 0.303352 4.89989 0.479473 4.44989C0.986945 3.15317 1.86162 2.04621 3.00893 1.24868C4.18409 0.431785 5.56415 0 7 0C8.43585 0 9.81591 0.431785 10.9911 1.24865C11.458 1.57322 11.8798 1.94909 12.25 2.36811V2.36255C12.25 1.87931 12.6418 1.48755 13.125 1.48755C13.6082 1.48755 14 1.87928 14 2.36253ZM13.0246 8.41643C12.5746 8.24029 12.067 8.46237 11.8909 8.91237C11.0974 10.9399 9.17763 12.25 7 12.25C5.287 12.25 3.73368 11.4391 2.76254 10.1063H3.36872C3.85197 10.1063 4.24372 9.7145 4.24372 9.23125C4.24372 8.748 3.85197 8.35625 3.36872 8.35625H0.875C0.391754 8.35625 0 8.748 0 9.23125V11.725C0 12.2082 0.391754 12.6 0.875 12.6C1.35825 12.6 1.75 12.2082 1.75 11.725V11.6319C2.12023 12.0509 2.54201 12.4268 3.00893 12.7513C4.18409 13.5682 5.56415 14 7 14C8.43585 14 9.81591 13.5682 10.9911 12.7513C12.1384 11.9538 13.0131 10.8469 13.5205 9.55013C13.6966 9.10014 13.4746 8.59255 13.0246 8.41643Z" fill="#FDF2DA"/>
                            </g>
                            <defs>
                              <clipPath id="clip0_2850_454">
                                <rect width="14" height="14" fill="currentColor"/>
                              </clipPath>
                            </defs>
                          </svg> Check license status
                        </a>
                        <div class="license-options-wrapper">
                          <a class="card-link my-account" target="_blank" href="#">My Account</a>
                        </div>
                      </div>
                    </div>

                    <p class="buy-new-license">Need a new license? <a href="#">Browse our flexible plans</a></p>

                  <div class="new-license">
                    <div class="toggle-header">
                      <img src="<?php echo get_theme_file_uri('/muffin-options/svg/logo-envato-light.svg'); ?>" alt="Envato" />
                      <h5>Did you buy on Envato?</h5>
                      <span class="new-license-arrow"><i class="icon-down-open-big"></i></span>
                    </div>
                    <div class="toggle-content" style="display: none;">
                      <form class="form-register mfn-form mfn-form-reg" method="post">
                        <input type="hidden" name="mfn-setup-nonce" value="<?php echo wp_create_nonce( 'mfn-setup-register' ); ?>">
                        <input type="hidden" name="mfn-builder-nonce" value="<?php echo wp_create_nonce( 'mfn-builder-nonce' ); ?>">
                        <input type="hidden" name="action" value="mfn_setup_register">
                        <input type="submit" name="submit" value="mfn_setup_register" style="display:none">
                        <div class="form-register-input">
                          <span class="mfn-icon mfn-icon-password-light"></span>
                          <input type="text" name="code" placeholder="Paste your purchase code here" class="mfn-form-control of-input" size="36">
                          <div class="mfn-tooltip-box where-is">
                            <a>Where is code?</a>
                            <div class="tooltip-box-inner">
                              <p><strong>Where can I find my purchase code?</strong></p>
                              <ol>
                                <li>Please go to <a target="_blank" href="https://themeforest.net/downloads">ThemeForest.net/downloads</a></li>
                                <li>Click the <strong>Download</strong> button in Betheme row</li>
                                <li>Select <strong>License Certificate &amp; Purchase code</strong></li>
                                <li>Copy <strong>Item Purchase Code</strong></li>
                              </ol>
                            </div>
                          </div>
                          <span class="form-message">aaa</span>
                        </div>
                        <a id="register" class="mfn-btn mfn-btn-fw mfn-btn-green"><span class="btn-wrapper">Register theme</span></a>
                      </form>
                      <div class="pricing-banner">
                        Need a new license? <a href="#">Browse new flexible plans</a>
                      </div>
                      <div class="pricing-plans">
                        <!-- Basic -->
                        <div class="pricing-plan">
                          <div class="plan-name">Basic</div>
                          <div class="plan-sub">for 1 website</div>
                          <div>
                            <span class="price"><sup>$</sup>10</span>
                            <span class="price-period">/year</span>
                          </div>
                        </div>
                        <!-- Advanced (highlighted) -->
                        <div class="pricing-plan highlighted">
                          <span class="badge-popular">Most Popular</span>
                          <div class="plan-name">Advanced</div>
                          <div class="plan-sub">for 3 websites</div>
                          <div>
                            <span class="price"><sup>$</sup>24</span>
                            <span class="price-period">/year</span>
                          </div>
                          <div class="price-meta">
                            <span class="per-site">$8 per site</span>
                            <span class="discount">-20%</span>
                          </div>
                        </div>
                        <!-- Pro -->
                        <div class="pricing-plan">
                          <div class="plan-name">Pro</div>
                          <div class="plan-sub">for 25 websites</div>
                          <div>
                            <span class="price"><sup>$</sup>199</span>
                            <span class="price-period">/year</span>
                          </div>
                          <div class="price-meta">
                            <span class="per-site">$8 per site</span>
                            <span class="discount">-40%</span>
                          </div>
                        </div>
                        <!-- Ultimate -->
                        <div class="pricing-plan">
                          <div class="plan-name">Ultimate</div>
                          <div class="plan-sub">Unlimited websites</div>
                          <div>
                            <span class="price"><sup>$</sup>999</span>
                            <span class="price-period">/year</span>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>

								</div>
							</div>







             <!-- Basic subscription -->
							<div class="mfn-card mfn-shadow-1" data-card="theme-register">

								<div class="card-header">
									<div class="card-title-group">
										<span class="card-icon mfn-icon-register-light"></span>
										<div class="card-desc">
											<h4 class="card-title">License Key Details</h4>
										</div>
									</div>
									<?php if( ! WHITE_LABEL ): ?>
									<div class="card-links-group">
										<a target="_blank" href="#"><span class="mfn-icon mfn-icon-support-light"></span> Need help with registration?</a>
									</div>
									<?php endif; ?>
								</div>

								<div class="card-content">

                    <div class="license-wrapper">
                      <div class="license-wrapper-left">
                        <div class="license-image"><img src="<?php echo get_theme_file_uri('functions/admin/assets/svg/be.svg'); ?>" alt="" /></div>
                        <div class="license-details">
                          <div><span class="license-title">Basic Subscription</span> <span class="license-desc">· for 1 website</span></div>
                          <div class="license-status-wrapper">
                            <span class="license-status active">ACTIVE</span>
                            <span class="license-info">Registered on this domain</span>
                          </div>
                        </div>
                      </div>
                      <div class="license-wrapper-right">
                        <a class="card-link check-status" href="#">
                          <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_2850_454)">
                              <path d="M14 2.36253V4.85625C14 5.3395 13.6082 5.73125 13.125 5.73125H10.6313C10.148 5.73125 9.75628 5.3395 9.75628 4.85625C9.75628 4.373 10.148 3.98125 10.6313 3.98125H11.2998C10.3336 2.59643 8.75019 1.75 7 1.75C4.82237 1.75 2.90259 3.06012 2.10913 5.08766C1.93298 5.53768 1.42538 5.75969 0.975406 5.58359C0.525383 5.40747 0.303352 4.89989 0.479473 4.44989C0.986945 3.15317 1.86162 2.04621 3.00893 1.24868C4.18409 0.431785 5.56415 0 7 0C8.43585 0 9.81591 0.431785 10.9911 1.24865C11.458 1.57322 11.8798 1.94909 12.25 2.36811V2.36255C12.25 1.87931 12.6418 1.48755 13.125 1.48755C13.6082 1.48755 14 1.87928 14 2.36253ZM13.0246 8.41643C12.5746 8.24029 12.067 8.46237 11.8909 8.91237C11.0974 10.9399 9.17763 12.25 7 12.25C5.287 12.25 3.73368 11.4391 2.76254 10.1063H3.36872C3.85197 10.1063 4.24372 9.7145 4.24372 9.23125C4.24372 8.748 3.85197 8.35625 3.36872 8.35625H0.875C0.391754 8.35625 0 8.748 0 9.23125V11.725C0 12.2082 0.391754 12.6 0.875 12.6C1.35825 12.6 1.75 12.2082 1.75 11.725V11.6319C2.12023 12.0509 2.54201 12.4268 3.00893 12.7513C4.18409 13.5682 5.56415 14 7 14C8.43585 14 9.81591 13.5682 10.9911 12.7513C12.1384 11.9538 13.0131 10.8469 13.5205 9.55013C13.6966 9.10014 13.4746 8.59255 13.0246 8.41643Z" fill="#FDF2DA"/>
                            </g>
                            <defs>
                              <clipPath id="clip0_2850_454">
                                <rect width="14" height="14" fill="currentColor"/>
                              </clipPath>
                            </defs>
                          </svg> Check license status
                        </a>
                        <div class="license-options-wrapper">
                          <a class="card-link my-account" target="_blank" href="#">My Account</a>
                          <a class="card-link deregister" href="#">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clip-path="url(#clip0_2850_67)">
                                <path d="M5.25024 8.84794C5.16374 8.84794 5.07574 8.83295 4.99024 8.80145C4.54674 8.63745 4.15224 8.38444 3.81674 8.04894C3.52424 7.75594 3.52424 7.28094 3.81674 6.98844C4.10974 6.69544 4.58474 6.69544 4.87774 6.98844C5.05824 7.16944 5.27074 7.30594 5.50924 7.39394C5.89774 7.53744 6.09674 7.96894 5.95324 8.35744C5.84124 8.65994 5.55474 8.84744 5.24974 8.84744L5.25024 8.84794ZM0.953738 6.44244C-0.315762 7.71194 -0.315762 9.77694 0.953738 11.0464C1.58874 11.6814 2.42224 11.9984 3.25574 11.9984C4.08924 11.9984 4.92324 11.6809 5.55774 11.0464C5.57324 11.0309 5.58874 11.0144 5.60274 10.9974L5.82524 10.7314C6.09074 10.4139 6.04874 9.94094 5.73124 9.67494C5.41274 9.40894 4.94024 9.45094 4.67474 9.76894L4.47574 10.0074C3.78924 10.6699 2.69274 10.6639 2.01424 9.98644C1.32974 9.30194 1.32974 8.18794 2.01424 7.50344L3.00824 6.50894C3.30124 6.21594 3.30124 5.74094 3.00824 5.44844C2.71524 5.15544 2.24074 5.15544 1.94774 5.44844L0.953738 6.44244ZM0.219738 1.28044L10.7197 11.7804C10.8662 11.9269 11.0582 11.9999 11.2502 11.9999C11.4422 11.9999 11.6342 11.9269 11.7807 11.7804C12.0737 11.4879 12.0737 11.0124 11.7807 10.7199L8.83274 7.77194L11.0467 5.55794C12.3162 4.28844 12.3162 2.22344 11.0467 0.953945C9.77824 -0.315555 7.71324 -0.316555 6.44274 0.953945C6.14974 1.24644 6.14974 1.72194 6.44274 2.01444C6.73574 2.30744 7.21024 2.30744 7.50324 2.01444C8.18824 1.32994 9.30224 1.33044 9.98624 2.01444C10.6702 2.69844 10.6707 3.81294 9.98624 4.49744L7.77224 6.71144L5.56374 4.50294C6.07574 4.44744 6.60224 4.61944 6.99224 5.00894C7.13874 5.15544 7.33074 5.22844 7.52274 5.22844C7.71474 5.22844 7.90674 5.15544 8.05324 5.00894C8.34624 4.71644 8.34624 4.24094 8.05324 3.94844C7.06124 2.95594 5.58874 2.72294 4.36474 3.30394L1.28074 0.219445C0.987738 -0.0735552 0.513238 -0.0735552 0.220238 0.219445C-0.0727617 0.511945 -0.0727617 0.987445 0.220238 1.27994L0.219738 1.28044Z" fill="currentColor"/>
                              </g>
                            </svg>Deregister
                          </a>
                        </div>
                      </div>
                    </div>

                    <div class="license-wrapper">
                      <div class="license-wrapper-left">
                        <div class="license-image"><img src="<?php echo get_theme_file_uri('functions/admin/assets/svg/be.svg'); ?>" alt="" /></div>
                        <div class="license-details">
                          <div><span class="license-title">Envato License</div>
                          <div class="license-status-wrapper">
                            <span class="license-status active">ACTIVE</span>
                            <span class="license-info">c67b2db3-f3e5-****-****-************</span>
                          </div>
                        </div>
                      </div>
                      <div class="license-wrapper-right">
                        <div class="license-options-wrapper">
                          <a class="card-link my-account" target="_blank" href="#">My licenses</a>
                          <a class="card-link deregister" href="#">
                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                              <g clip-path="url(#clip0_2850_67)">
                                <path d="M5.25024 8.84794C5.16374 8.84794 5.07574 8.83295 4.99024 8.80145C4.54674 8.63745 4.15224 8.38444 3.81674 8.04894C3.52424 7.75594 3.52424 7.28094 3.81674 6.98844C4.10974 6.69544 4.58474 6.69544 4.87774 6.98844C5.05824 7.16944 5.27074 7.30594 5.50924 7.39394C5.89774 7.53744 6.09674 7.96894 5.95324 8.35744C5.84124 8.65994 5.55474 8.84744 5.24974 8.84744L5.25024 8.84794ZM0.953738 6.44244C-0.315762 7.71194 -0.315762 9.77694 0.953738 11.0464C1.58874 11.6814 2.42224 11.9984 3.25574 11.9984C4.08924 11.9984 4.92324 11.6809 5.55774 11.0464C5.57324 11.0309 5.58874 11.0144 5.60274 10.9974L5.82524 10.7314C6.09074 10.4139 6.04874 9.94094 5.73124 9.67494C5.41274 9.40894 4.94024 9.45094 4.67474 9.76894L4.47574 10.0074C3.78924 10.6699 2.69274 10.6639 2.01424 9.98644C1.32974 9.30194 1.32974 8.18794 2.01424 7.50344L3.00824 6.50894C3.30124 6.21594 3.30124 5.74094 3.00824 5.44844C2.71524 5.15544 2.24074 5.15544 1.94774 5.44844L0.953738 6.44244ZM0.219738 1.28044L10.7197 11.7804C10.8662 11.9269 11.0582 11.9999 11.2502 11.9999C11.4422 11.9999 11.6342 11.9269 11.7807 11.7804C12.0737 11.4879 12.0737 11.0124 11.7807 10.7199L8.83274 7.77194L11.0467 5.55794C12.3162 4.28844 12.3162 2.22344 11.0467 0.953945C9.77824 -0.315555 7.71324 -0.316555 6.44274 0.953945C6.14974 1.24644 6.14974 1.72194 6.44274 2.01444C6.73574 2.30744 7.21024 2.30744 7.50324 2.01444C8.18824 1.32994 9.30224 1.33044 9.98624 2.01444C10.6702 2.69844 10.6707 3.81294 9.98624 4.49744L7.77224 6.71144L5.56374 4.50294C6.07574 4.44744 6.60224 4.61944 6.99224 5.00894C7.13874 5.15544 7.33074 5.22844 7.52274 5.22844C7.71474 5.22844 7.90674 5.15544 8.05324 5.00894C8.34624 4.71644 8.34624 4.24094 8.05324 3.94844C7.06124 2.95594 5.58874 2.72294 4.36474 3.30394L1.28074 0.219445C0.987738 -0.0735552 0.513238 -0.0735552 0.220238 0.219445C-0.0727617 0.511945 -0.0727617 0.987445 0.220238 1.27994L0.219738 1.28044Z" fill="currentColor"/>
                              </g>
                            </svg>Deregister
                          </a>
                        </div>
                      </div>
                    </div>

                  <div class="new-license">
                    <div class="toggle-header">
                      <h5>Need a new license? See our flexible plans</h5>
                      <span class="new-license-arrow"><i class="icon-down-open-big"></i></span>
                    </div>
                    <div class="toggle-content" style="display: none;">
                      <div class="pricing-plans">
                        <!-- Basic -->
                        <div class="pricing-plan">
                          <div class="plan-name">Basic</div>
                          <div class="plan-sub">for 1 website</div>
                          <div>
                            <span class="price"><sup>$</sup>10</span>
                            <span class="price-period">/year</span>
                          </div>
                        </div>
                        <!-- Advanced (highlighted) -->
                        <div class="pricing-plan highlighted">
                          <span class="badge-popular">Most Popular</span>
                          <div class="plan-name">Advanced</div>
                          <div class="plan-sub">for 3 websites</div>
                          <div>
                            <span class="price"><sup>$</sup>24</span>
                            <span class="price-period">/year</span>
                          </div>
                          <div class="price-meta">
                            <span class="per-site">$8 per site</span>
                            <span class="discount">-20%</span>
                          </div>
                        </div>
                        <!-- Pro -->
                        <div class="pricing-plan">
                          <div class="plan-name">Pro</div>
                          <div class="plan-sub">for 25 websites</div>
                          <div>
                            <span class="price"><sup>$</sup>199</span>
                            <span class="price-period">/year</span>
                          </div>
                          <div class="price-meta">
                            <span class="per-site">$8 per site</span>
                            <span class="discount">-40%</span>
                          </div>
                        </div>
                        <!-- Ultimate -->
                        <div class="pricing-plan">
                          <div class="plan-name">Ultimate</div>
                          <div class="plan-sub">Unlimited websites</div>
                          <div>
                            <span class="price"><sup>$</sup>999</span>
                            <span class="price-period">/year</span>
                          </div>
                        </div>
                      </div>
                      <a class="mfn-btn mfn-btn-blue mfn-btn-fw" href="#"><span class="btn-wrapper">Browse all plans</span></a>
                    </div>
                  </div>

								</div>
							</div>









						</div>

						<div class="row-column row-column-4">

							<?php
								// mini system status
								include get_theme_file_path('/functions/admin/templates/parts/mini-status.php');

								// suggestion
								include get_theme_file_path('/functions/admin/templates/parts/suggestion.php');
							?>

						</div>

					</div>

				<?php endif; ?>

				<?php
					$disable = mfn_opts_get('theme-disable');

					if( ! WHITE_LABEL && ! isset($disable['demo-data']) ):
				?>

	      <div class="mfn-row">

	        <div class="row-column row-column-4">
	          <div class="mfn-card mfn-shadow-1" data-card="setup-wizard">
	            <div class="card-content">
	              <h3>Step by step<br /> website creator</h3>
	              <p>Let us guide you through this process. Promise, it won't take more than a couple of seconds.</p>
	              <a class="mfn-btn" href="admin.php?page=<?php echo apply_filters('betheme_slug', 'be'); ?>-setup"><span class="btn-wrapper">Let’s get started</span></a>
	            </div>
	          </div>
	        </div>

	        <div class="row-column row-column-8">
	          <div class="mfn-card mfn-shadow-1" data-card="news-carousel">
	            <div class="card-content">
	              <!-- <ul class="slider-promo">
	                <li><a href="#"><img src="https://api.muffingroup.com/promo/images/26.jpg" alt="" /></a></li>
	                <li><a href="#"><img src="https://api.muffingroup.com/promo/images/26.jpg" alt="" /></a></li>
	              </ul> -->
								<?php $this->promo(); ?>
	            </div>
	          </div>
	        </div>

	      </div>

				<?php
					// latest websites
					include_once get_theme_file_path('/functions/admin/templates/parts/websites.php');
				?>

				<?php endif; ?>

				<?php if( mfn_is_registered() ): ?>

					<div class="mfn-row">

						<div class="row-column row-column-8">

							<div class="mfn-card mfn-shadow-1" data-card="theme-register">

								<div class="card-header">
									<div class="card-title-group">
										<span class="card-icon mfn-icon-register-light"></span>
										<div class="card-desc">
											<h4 class="card-title">Theme Registration</h4>
										</div>
									</div>
									<div class="card-links-group">
										<a href="#" class="data-collection" data-modal="data-collection"><span class="mfn-icon mfn-icon-support-light"></span> Data collection</a>
										<a target="_blank" href="https://api.muffingroup.com/licenses/"><span class="mfn-icon mfn-icon-folder-open-light"></span> Check your licenses</a>
									</div>
								</div>

								<div class="card-content">

									<form class="form-register mfn-form" method="post">
										<div class="form-register-input">

											<span class="mfn-icon mfn-icon-password-light"></span>
											<input type="text" value="<?php echo esc_html( mfn_get_purchase_code_hidden() ); ?>" class="mfn-form-control of-input" size="36" readonly="readonly">

											<a id="deregister" class="mfn-btn mfn-btn-green deregister-theme"><span class="btn-wrapper">Deregister theme</span></a>

										</div>
									</form>

									<?php
										// new license
										include get_theme_file_path('/functions/admin/templates/parts/new-license.php');
									?>

								</div>

							</div>

						</div>

						<div class="row-column row-column-4">

							<?php
								// mini system status
								include get_theme_file_path('/functions/admin/templates/parts/mini-status.php');

								// suggestion
								include get_theme_file_path('/functions/admin/templates/parts/suggestion.php');
							?>

						</div>

					</div>

				<?php endif; ?>

				<?php if( ! WHITE_LABEL ): ?>

				<div class="mfn-row">
					<div class="row-column row-column-12">

						<div class="mfn-card mfn-shadow-1" data-card="performance">
							<div class="card-header">
								<div class="card-title-group">
									<span class="card-icon mfn-icon-performance"></span>
									<div class="card-desc">
										<h4 class="card-title">Performance settings</h4>
									</div>
								</div>
								<div class="card-logos-group">
                  <img class="logo-pagespeed" src="<?php echo get_theme_file_uri('/functions/admin/assets/svg/logo-pagespeed-insights.svg'); ?>" width="35" alt="PageSpeed Insights" />
                  <img class="logo-gtmetrix" src="<?php echo get_theme_file_uri('/functions/admin/assets/svg/logo-gtmetrix.svg'); ?>" width="100" alt="GTmetrix" />
								</div>
								<div class="card-buttons-group">
                  <a class="mfn-btn mfn-btn-blue" href="admin.php?page=<?php echo apply_filters('betheme_slug', 'be'); ?>-options#performance-general">
                    <span class="btn-wrapper">Improve site performance</span>
                  </a>
								</div>
							</div>
						</div>

					</div>
				</div>

	      <div class="mfn-row">
	        <div class="row-column row-column-12">
	          <div class="mfn-card mfn-shadow-1" data-card="customization">
	            <div class="card-content">
                <div class="banner-title">
                  <div class="new-service"><img src="<?php echo get_theme_file_uri('/functions/admin/assets/svg/be.svg'); ?>" alt="" /> Customization</span></div>
                  <h3>Professional customizations <span class="text-gradient-primary">of Betheme</span></h3>
                </div>
                <div class="banner-content">
                  <p>Get a quote from the creators of Betheme for customizations that perfectly fit your project.</p>
                  <div class="pills-wrapper">
                    <span class="pill pill-filled white pill1">
                      <span class="pill-icon">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.4 10L8.8 12.4L13.6 7.6M18 10C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10C2 5.58172 5.58172 2 10 2C14.4183 2 18 5.58172 18 10Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                      </span>
                      <span class="pill-text">Secure deployment</span>
                    </span>
                    <span class="pill pill-filled white pill2">
                      <span class="pill-icon" style="background-color: #FACBFC;">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.4 10L8.8 12.4L13.6 7.6M18 10C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10C2 5.58172 5.58172 2 10 2C14.4183 2 18 5.58172 18 10Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                      </span>
                      <span class="pill-text">Express mode</span>
                    </span>
                    <span class="pill pill-filled white pill3">
                      <span class="pill-icon" style="background-color: #CBE4FC;">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.4 10L8.8 12.4L13.6 7.6M18 10C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10C2 5.58172 5.58172 2 10 2C14.4183 2 18 5.58172 18 10Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                      </span>
                      <span class="pill-text">Fast delivery</span>
                    </span>
                    <span class="pill pill-filled white pill4">
                      <span class="pill-icon" style="background-color: #DDCBFC;">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.4 10L8.8 12.4L13.6 7.6M18 10C18 14.4183 14.4183 18 10 18C5.58172 18 2 14.4183 2 10C2 5.58172 5.58172 2 10 2C14.4183 2 18 5.58172 18 10Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                      </span>
                      <span class="pill-text">Direct contact</span>
                    </span>
                  </div>
                  <a class="mfn-btn mfn-btn-blue" href="https://muffingroup.com/betheme/customization/" target="_blank">Get a Quote</a>
                </div>
                <img class="be-customization" src="<?php echo get_theme_file_uri('/functions/admin/assets/images/becustomization.png'); ?>" alt="" />
	            </div>
	          </div>
	        </div>
        </div>

	      <div class="mfn-row">
	        <div class="row-column row-column-12">
	          <div class="mfn-card mfn-shadow-1" data-card="siteground">
	            <div class="card-content">
                <div class="banner-content">
                  <h3>Web Hosting Built for Your Success</h3>
                  <a target="_blank" href="https://www.siteground.com/go/d0idqfaakf">Sign Up Now - Up to 81% Off</a>
                </div>
                <img class="be-siteground" src="<?php echo get_theme_file_uri('/functions/admin/assets/images/siteground.webp'); ?>" alt="Siteground" />
	            </div>
	          </div>
	        </div>
        </div>

				<div class="mfn-row">
					<div class="row-column row-column-12">

						<div class="mfn-card mfn-shadow-1" data-card="integrations">
							<div class="card-header">
								<div class="card-title-group">
									<span class="card-icon mfn-icon-plugins"></span>
									<div class="card-desc">
										<h4 class="card-title">Betheme integrations</h4>
									</div>
								</div>
							</div>
							<div class="card-content">
								<div class="mfn-row">

									<div class="row-column row-column-4 plugin-item">
										<img class="icon-light" src="<?php echo get_theme_file_uri('/functions/admin/assets/svg/plugins/integration-wprocket.svg'); ?>" alt="WPRocket" />
										<img class="icon-dark" src="<?php echo get_theme_file_uri('/functions/admin/assets/svg/_dark/plugins/integration-wprocket.svg'); ?>" alt="WPRocket" />
										<h5>WP Rocket</h5>
										<p>WP Rocket is much more than just a WordPress caching plugin. It’s the most powerful solution to boost your loading time.</p>
										<a class="mfn-btn btn-wide" target="_blank" href="https://shareasale.com/r.cfm?b=1075949&u=3636944&m=74778&urllink=&afftrack=">
											<span class="btn-wrapper">Get WP Rocket Now</span>
										</a>
									</div>

									<div class="row-column row-column-4 plugin-item">
										<img class="icon-light" src="<?php echo get_theme_file_uri('/functions/admin/assets/svg/plugins/integration-wpml.svg'); ?>" alt="WPML" />
										<img class="icon-dark" src="<?php echo get_theme_file_uri('/functions/admin/assets/svg/_dark/plugins/integration-wpml.svg'); ?>" alt="WPML" />
										<h5>Multilingual sites</h5>
										<p>Plugin that makes over a million WordPress sites multilingual. It’s powerful enough for corporate sites, yet simple for blogs.</p>
										<a class="mfn-btn btn-wide" target="_blank" href="https://wpml.org/?aid=29349&affiliate_key=aCEsSE0ka33p">
											<span class="btn-wrapper">Buy and download</span>
										</a>
									</div>

									<div class="row-column row-column-4 plugin-item">
										<img class="icon-light" src="<?php echo get_theme_file_uri('/functions/admin/assets/svg/plugins/integration-hubspot.svg'); ?>" alt="HubSpot" />
										<img class="icon-dark" src="<?php echo get_theme_file_uri('/functions/admin/assets/svg/_dark/plugins/integration-hubspot.svg'); ?>" alt="HubSpot" />
										<h5>CRM, Marketing and Sales</h5>
										<p>CRM platform contains the marketing, sales, service, operations, and SEO friendly software you need to grow your business.</p>
										<a class="mfn-btn btn-wide" target="_blank" href="https://hubspot.sjv.io/c/1289117/1389270/12893">
											<span class="btn-wrapper">Sign up for free</span>
										</a>
									</div>

								</div>
							</div>
						</div>

					</div>
				</div>

				<?php endif; ?>

			<?php endif; ?>

    </div>

		<?php
			// footer
			include get_theme_file_path('/functions/admin/templates/parts/footer.php');
		?>

	</div>

	<!-- modal: data collection -->

  <div class="mfn-modal modal-medium modal-data-collection">
    <div class="mfn-modalbox mfn-form mfn-shadow-1">

			<div class="modalbox-header">

				<div class="options-group">
					<div class="modalbox-title-group">
						<span class="modalbox-icon mfn-icon-card"></span>
						<div class="modalbox-desc">
							<h4 class="modalbox-title"><?php esc_html_e('Data collection', 'mfn-opts'); ?></h4>
						</div>
					</div>
				</div>

				<div class="options-group">
					<a class="mfn-option-btn mfn-option-blank btn-large btn-modal-close" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a>
				</div>

			</div>

			<div class="modalbox-content">

				<span class="mfn-icon mfn-icon-support"></span>
				<h3><?php esc_html_e('Data collection', 'mfn-opts'); ?></h3>

				<p>Betheme does not collect any personal data. However, we gather some basic information about your website to validate your license and product registration. These are:</p>

				<ul class="default">
					<li>The purchase code that was used for product registration</li>
					<li>The domain name that your website uses</li>
				</ul>

				<p>In order to serve and check for updates, from time to time, your WordPress installation establishes an anonymous connection to our servers.</p>

			</div>

    </div>

  </div>

</div>
