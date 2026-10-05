<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

if( wp_get_referer() && strpos( wp_get_referer(), 'login' ) === false && strpos( wp_get_referer(), 'action=mfn-live-builder' ) === false ){
    $referrer = wp_get_referer();
}else{
    $referrer = admin_url( 'edit.php?post_type=page' );
}

$custom_replaced_logo = apply_filters('betheme_logo', '') ? 'style="background-image:url('. apply_filters('betheme_logo_nohtml', ''). ')"' : '';
$version = apply_filters('betheme_disable_theme_version', MFN_THEME_VERSION);
$be_dashboard_url = admin_url('admin.php?page='.apply_filters('betheme_slug', 'betheme'));

echo '<div class="sidebar-menu">
  <div class="sidebar-menu-inner">';

	  if( ! WHITE_LABEL ){
			echo '<a href="'.$be_dashboard_url.'" class="mfnb-logo" '.$custom_replaced_logo.'>Be Builder - Powered by Muffin Group <span class="mfnb-ver">'.($version ? 'V'.MFN_THEME_VERSION : "").'</span></a>';
		} else {
			echo '<a href="'.$be_dashboard_url.'" class="mfnb-logo" style="background-image:unset"></a>';
		}

    echo '<nav id="main-menu">
      <ul>
	      <li class="menu-items"><a data-tooltip="'.esc_html__('Elements', 'mfn-opts').'" data-position="right" href="#">Elements</a></li>';

	      if( $this->template_type && $this->template_type == 'header' ){
	          echo '<li class="menu-sections"><a data-tooltip="'.esc_html__('Pre-built headers', 'mfn-opts').'" data-position="right" href="#">Pre-built sections</a></li>';
	      }elseif( $this->template_type && $this->template_type == 'footer' ){
	          echo '<li class="menu-sections"><a data-tooltip="'.esc_html__('Pre-built footers', 'mfn-opts').'" data-position="right" href="#">Pre-built sections</a></li>';
	      }else{
	          echo '<li class="menu-sections"><a data-tooltip="'.esc_html__('Pre-built sections', 'mfn-opts').'" data-position="right" href="#">Pre-built sections</a></li>';
	      }

	      echo '<li class="menu-export"><a class="mfn-export-get" data-tooltip="'.esc_html__('Export / Import', 'mfn-opts') . ( ! is_admin() ? ' (Unavailable in Demo)' : '' ) .'" data-position="right" href="#">Export / Import</a></li>';

				if( ! apply_filters('betheme_disable_single', false) ){
					echo '<li class="menu-page"><a data-tooltip="'.esc_html__('Single page import', 'mfn-opts') . ( ! is_admin() ? ' (Unavailable in Demo)' : '' ) .'" data-position="right" href="#">Single page import</a></li>';
				}

	      if ( defined( 'WPSEO_FILE' ) && $this->post_type !== 'template' ) echo '<li class="menu-yoast"><a data-tooltip="'.esc_html__('Yoast SEO', 'mfn-opts') . ( ! is_admin() ? ' (Unavailable in Demo)' : '' ) .'" data-position="right" class="mfn-yoast-tab" href="#">Yoast SEO</a></li>';

      echo '</ul>
    </nav>

    <nav id="settings-menu">
      <ul>

     		<li class="menu-live-css"><a href="#" data-tooltip="'.esc_html__('Live CSS', 'mfn-opts').'" data-position="right" class="btn-live-css-switcher"><span class="mfn-icon mfn-icon-css-live-editor"></span></a></li>

	      <li class="menu-navigator"><a href="#" data-tooltip="'.esc_html__('Navigator', 'mfn-opts').'" data-position="right" class="btn-navigator-switcher"><span class="mfn-icon mfn-icon-navigator"></span></a></li>

	      <li class="menu-revisions"><a data-tooltip="'.esc_html__('History', 'mfn-opts') . ( ! is_admin() ? ' (Unavailable in Demo)' : '' ) .'" data-position="right" href="#">History'. ( ! is_admin() ? ' (Unavailable in Demo)' : '' ) .'</a></li>';

	      if( $this->template_type && $this->template_type == 'header' ) {
	          echo '<li class="menu-options"><a data-position="right" id="page-options-tab" class="mfn-view-options-tab" href="#" data-tooltip="'.esc_html__('Header options', 'mfn-opts').'">Options</a></li>';
	      }elseif( $this->template_type && $this->template_type == 'footer' ) {
	          echo '<li class="menu-options"><a data-position="right" id="page-options-tab" class="mfn-view-options-tab" href="#" data-tooltip="'.esc_html__('Footer options', 'mfn-opts').'">Options</a></li>';
	      }elseif( $this->template_type && $this->template_type == 'megamenu' ) {
	          echo '<li class="menu-options"><a data-position="right" id="page-options-tab" class="mfn-view-options-tab" href="#" data-tooltip="'.esc_html__('Mega menu options', 'mfn-opts').'">Options</a></li>';
	      }elseif( $this->template_type && $this->template_type == 'popup' ) {
	          echo '<li class="menu-options"><a data-position="right" id="page-options-tab" class="mfn-view-options-tab" href="#" data-tooltip="'.esc_html__('Popup options', 'mfn-opts').'">Options</a></li>';
	      }elseif( $this->template_type && $this->template_type == 'sidemenu' ) {
	          echo '<li class="menu-options"><a data-position="right" id="page-options-tab" class="mfn-view-options-tab" href="#" data-tooltip="'.esc_html__('Sidebar menu options', 'mfn-opts').'">Options</a></li>';
	      }elseif( $this->template_type ) {
	          echo '<li class="menu-options"><a data-position="right" id="page-options-tab" class="mfn-view-options-tab" href="#" data-tooltip="'.esc_html__('Template options', 'mfn-opts').'">Options</a></li>';
	      }else{
	          echo '<li class="menu-options"><a data-position="right" id="page-options-tab" class="mfn-view-options-tab" href="#" data-tooltip="'.esc_html__('Page options', 'mfn-opts') . ( ! is_admin() ? ' (Unavailable in Demo)' : '' ) .'">Options</a></li>';
	      }

	      if( current_user_can( 'edit_theme_options' ) ) echo '<li class="menu-themeoptions"><a data-tooltip="'.esc_html__('Theme options', 'mfn-opts') . ( ! is_admin() ? ' (Unavailable in Demo)' : '' ) .'" data-position="right" href="#">Theme options</a></li>';

	      echo '<li class="menu-settings"><a data-tooltip="'.esc_html__('Settings', 'mfn-opts') . ( ! is_admin() ? ' (Unavailable in Demo)' : '' ) .'" class="mfn-settings-tab" data-position="right" href="#">Settings</a></li>';

	      if( is_admin() ) {
	      	echo '<li class="menu-wordpress"><div class="mfn-option-dropdown mfn-option-dropdown-back-wordpress"><a href="'. ( is_admin() ? admin_url() : '#' ) .'">Back to WordPress</a><div class="dropdown-wrapper"><a class="mfn-dropdown-item" href="'.admin_url().'"><span class="mfn-icon mfn-icon-wordpress"></span> Back to WordPress</a><a class="mfn-dropdown-item" href="'.admin_url('post.php?post='.$this->post_id.'&action=edit').'"><span class="mfn-icon mfn-icon-edit"></span> Edit page</a></div></div></li>';
	      }else{
	      	echo '<li class="menu-wordpress"><a data-position="right" href="#" data-tooltip="'.esc_html__('Back to WordPress', 'mfn-opts').' (Unavailable in Demo)">Back to WordPress</a></li>';
	      }

      echo '</ul>
    </nav>

  </div>
</div>';
