<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

$page_options_label = 'Page options';

if( $this->template_type && $this->template_type == 'header' ){
    $page_options_label = esc_html__('Header options', 'mfn-opts');
}else if( $this->template_type && $this->template_type == 'footer' ){
    $page_options_label = esc_html__('Footer options', 'mfn-opts');
}else if( $this->template_type && $this->template_type == 'megamenu' ){
    $page_options_label = esc_html__('Mega menu options', 'mfn-opts');
}else if( $this->template_type && $this->template_type == 'popup' ){
    $page_options_label = esc_html__('Popup options', 'mfn-opts');
}else if( $this->template_type && $this->template_type == 'sidemenu' ){
    $page_options_label = esc_html__('Sidebar options', 'mfn-opts');
}else if( $this->template_type ){
    $page_options_label = esc_html__('Template options', 'mfn-opts');
}

echo '<div class="sidebar-panel-header">';

if ( defined( 'WPSEO_FILE' ) ) {
    echo '<div class="header header-yoast-seo" style="display: none;">
        <div class="title-group">
        <span class="sidebar-panel-icon mfn-icon-predefined-sections"></span>
        <div class="sidebar-panel-desc">
            <h5 class="sidebar-panel-title">'.esc_html__('Yoast SEO', 'mfn-opts').'</h5>
        </div>
        </div>
        </div>';
    }



    echo '<div class="header header-global-sections" id="header-global-sections" style="display: none;">
    <div class="title-group">
    <span class="sidebar-panel-icon mfn-icon-predefined-sections"></span>
    <div class="sidebar-panel-desc">
        <h5 class="sidebar-panel-title">'.esc_html__('Global sections', 'mfn-opts').'</h5>
    </div>
    </div>
    </div>

	<div class="header header-edit-item" id="header-edit-item" style="display: none;">
        <div class="title-group">
            <span class="sidebar-panel-icon mfn-icon-column"></span>
            <div class="sidebar-panel-desc">
                <h5 class="sidebar-panel-title">'.esc_html__('Column', 'mfn-opts').'</h5>
            </div>
        </div>

        <div class="options-group">

            <div class="mfn-option-dropdown mfn-presets-list">
                <a title="Presets" href="#" class="mfn-option-btn btn-large btn-icon-right mfn-option-blank"><span class="mfn-icon mfn-icon-preset"></span><span style="width: 9px;" class="mfn-icon mfn-icon-unfold"></span></a>
                <ul class="dropdown-wrapper"></ul>
            </div>

            <a class="mfn-option-btn mfn-option-blank btn-large back-to-widgets" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a>
        </div>
    </div>

    <div class="header header-settings" id="header-settings" style="display: none;">';

    if( $this->ui_mode == 'dev' ){
        echo '<ul class="mfn-settings-nav">
            <li class="menu-options"><a href="#" id="page-options-tab" class="mfn-view-options-tab">'.$page_options_label.'</a></li>
            <li class="menu-themeoptions"><a href="#" class="mfn-themeoptions-tab">'.esc_html__('Theme options', 'mfn-opts').'</a></li>
            <li class="menu-settings active"><a href="#" class="mfn-settings-tab">'.esc_html__('Settings', 'mfn-opts').'</a></li>
        </ul>';
    }else{
        echo '<div class="title-group">
            <span class="sidebar-panel-icon mfn-icon-settings"></span>
            <div class="sidebar-panel-desc">
                <h5 class="sidebar-panel-title">'.esc_html__('Settings', 'mfn-opts').'</h5>
            </div>
        </div>
        <div class="options-group">
            <a class="mfn-option-btn mfn-option-blank btn-large back-to-widgets" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a>
        </div>
        ';
    }
    echo '</div>
    <div class="header header-view-options" id="header-view-options" style="display: none;">';

    if( $this->ui_mode == 'dev' ){
        echo '<ul class="mfn-settings-nav">
            <li class="menu-options active"><a href="#" id="page-options-tab" class="mfn-view-options-tab">'.$page_options_label.'</a></li>
            <li class="menu-themeoptions"><a href="#" class="mfn-themeoptions-tab">Theme options</a></li>
            <li class="menu-settings"><a href="#" class="mfn-settings-tab">Settings</a></li>
        </ul>';
    }else{

        echo '<div class="title-group">
            <span class="sidebar-panel-icon mfn-icon-options"></span>
            <div class="sidebar-panel-desc">
                <h5 class="sidebar-panel-title">'.$page_options_label.'</h5>
            </div>
        </div>
        <div class="options-group">
            <a class="mfn-option-btn mfn-option-blank btn-large back-to-widgets" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a>
        </div>';

    }
    echo '</div>

    <div class="header header-items" id="header-items">
        <div class="title-group">
        <span class="sidebar-panel-icon mfn-icon-add-big"></span>
        <div class="sidebar-panel-desc">
            <h5 class="sidebar-panel-title">'.esc_html__('Add Element', 'mfn-opts').'</h5>
        </div>
        </div>';

        if( !$this->template_type || ($this->template_type && $this->template_type != 'header' ) ){
        echo '<div class="options-group">
            <div class="mfn-option-dropdown">
                <a title="More" href="#" class="mfn-option-btn btn-large mfn-option-text btn-icon-right mfn-option-blank"><span class="text filter-items-current">'.esc_html__('All', 'mfn-opts').'</span><span class="mfn-icon mfn-icon-arrow-down"></span></a>
                <div class="dropdown-wrapper">
                    <h6>'.esc_html__('Filter by', 'mfn-opts').':</h6>
                <a class="mfn-dropdown-item mfn-filter-items active" data-filter="all" href="#"> '.esc_html__('All', 'mfn-opts').'</a>
                <div class="mfn-dropdown-divider"></div>';

                if( get_post_type($this->post_id) == 'template' && get_post_meta($this->post_id, 'mfn_template_type', true) == 'single-product' ){
                	echo '<a class="mfn-dropdown-item mfn-filter-items" data-filter="category-single-product" href="#"> '.esc_html__('Product', 'mfn-opts').'</a>';
                }elseif( get_post_type($this->post_id) == 'template' && get_post_meta($this->post_id, 'mfn_template_type', true) == 'shop-archive' ){
                	echo '<a class="mfn-dropdown-item mfn-filter-items" data-filter="category-shop-archive" href="#"> '.esc_html__('Shop', 'mfn-opts').'</a>';
                }

                echo '<a class="mfn-dropdown-item mfn-filter-items" data-filter="category-typography" href="#"> '.esc_html__('Typography', 'mfn-opts').'</a>
                <a class="mfn-dropdown-item mfn-filter-items" data-filter="category-boxes" href="#"> '.esc_html__('Boxes', 'mfn-opts').'</a>
                <a class="mfn-dropdown-item mfn-filter-items" data-filter="category-blocks" href="#"> '.esc_html__('Blocks', 'mfn-opts').'</a>
                <a class="mfn-dropdown-item mfn-filter-items" data-filter="category-elements" href="#"> '.esc_html__('Elements', 'mfn-opts').'</a>';
                if( !$this->template_type || ($this->template_type && $this->template_type != 'popup' ) ) echo '<a class="mfn-dropdown-item mfn-filter-items" data-filter="category-loops" href="#"> '.esc_html__('Loops', 'mfn-opts').'</a>';
                echo '<a class="mfn-dropdown-item mfn-filter-items" data-filter="category-plugins" href="#"> '.esc_html__('Plugins', 'mfn-opts').'</a>
                <a class="mfn-dropdown-item mfn-filter-items" data-filter="category-other" href="#"> '.esc_html__('Other', 'mfn-opts').'</a>

                </div>
            </div>
        </div>';
        }
    echo '</div>';


    echo '<div class="header header-prebuilt-sections" id="header-prebuilt-sections" style="display: none;">
        <div class="title-group">
        <span class="sidebar-panel-icon mfn-icon-predefined-sections"></span>
        <div class="sidebar-panel-desc">
            <h5 class="sidebar-panel-title">'.esc_html__('Pre-built', 'mfn-opts').'</h5>
        </div>
        </div>';

        if( !$this->template_type || $this->template_type != 'header' ){
   echo '<div class="options-group">
            <div class="mfn-option-dropdown">
                <a title="More" href="#" class="mfn-option-btn btn-large mfn-option-text btn-icon-right mfn-option-blank"><span class="text pre-built-current">'.esc_html__('All', 'mfn-opts').'</span><span class="mfn-icon mfn-icon-arrow-down"></span></a>
                <div class="dropdown-wrapper">
                    <h6>'.esc_html__('Filter by', 'mfn-opts').':</h6>';

                    $categories = Mfn_Pre_Built_Sections::get_categories();

                    foreach( $categories as $category_key => $category ){
                    	echo '<a class="mfn-dropdown-item pre-built-opt" data-filter="category-'. esc_attr( $category_key ) .'" href="#"> '. esc_html( $category ) .'</a>';
                    	if( $category_key == 'all'){
                    		echo '<div class="mfn-dropdown-divider"></div>';
                    	}
					}

                echo '</div>
            </div>
        </div>';
    }

    echo '</div><div class="header header-revisions" id="header-revisions" style="display: none;">
        <div class="title-group">
            <span class="sidebar-panel-icon mfn-icon-revisions"></span>
            <div class="sidebar-panel-desc">
                <h5 class="sidebar-panel-title">'.esc_html__('History', 'mfn-opts').'</h5>
            </div>
        </div>
        <div class="options-group">
            <div class="mfn-option-dropdown">
                <a title="More" href="#" class="mfn-option-btn btn-large mfn-option-text btn-icon-right mfn-option-blank"><span class="text revisions-current">'. ( empty(mfn_opts_get('builder-autosave')) ? esc_html__('Autosave', 'mfn-opts') : esc_html__('Update', 'mfn-opts') ) .'</span><span class="mfn-icon mfn-icon-arrow-down"></span></a>
                <div class="dropdown-wrapper">';
                    if( empty(mfn_opts_get('builder-autosave')) ) echo '<a class="mfn-dropdown-item mfn-revisions-opt active" data-filter="panel-revisions" href="#"> '.esc_html__('Autosave', 'mfn-opts').'</a>';
                    echo '<a class="mfn-dropdown-item mfn-revisions-opt" data-filter="panel-revisions-update" href="#"> '.esc_html__('Update', 'mfn-opts').'</a>
                    <a class="mfn-dropdown-item mfn-revisions-opt" data-filter="panel-revisions-revision" href="#"> '.esc_html__('Revision', 'mfn-opts').'</a>
                    <a class="mfn-dropdown-item mfn-revisions-opt" data-filter="panel-revisions-backup" href="#"> '.esc_html__('Backup', 'mfn-opts').'</a>
                </div>
            </div>
        </div>
    </div>

    <div class="header header-themeoptions" id="header-themeoptions" style="display: none;">';

    if( $this->ui_mode == 'dev' ){
        echo '<ul class="mfn-settings-nav">
            <li class="menu-options"><a href="#" id="page-options-tab" class="mfn-view-options-tab">'.$page_options_label.'</a></li>
            <li class="menu-themeoptions active"><a href="#" class="mfn-themeoptions-tab">'.esc_html__('Theme options', 'mfn-opts').'</a></li>
            <li class="menu-settings"><a href="#" class="mfn-settings-tab">'.esc_html__('Settings', 'mfn-opts').'</a></li>
        </ul>';
    }else{
        echo '<div class="title-group">
            <span class="sidebar-panel-icon mfn-icon-themeoptions"></span>
            <div class="sidebar-panel-desc">
                <h5 class="sidebar-panel-title">'.esc_html__('Theme options', 'mfn-opts').'</h5>
            </div>
        </div>
        <div class="options-group header-to-back" style="display: none;">
        <a title="More" href="#" class="mfn-option-btn btn-medium mfn-option-text btn-icon-left mfn-option-blank"><span class="mfn-icon mfn-icon-arrow-left"></span><span class="text">'.esc_html__('Back to list', 'mfn-opts').'</span></a>
        </div>
        ';
    }

    echo '</div>

    <div class="header header-export-import" id="header-export-import" style="display: none;">
        <div class="title-group">
        <span class="sidebar-panel-icon mfn-icon-export-import"></span>
        <div class="sidebar-panel-desc">
            <h5 class="sidebar-panel-title">'.esc_html__('Export / Import', 'mfn-opts').'</h5>
        </div>
        </div>
        <div class="options-group">
            <div class="mfn-option-dropdown">
                <a title="More" href="#" class="mfn-option-btn btn-large mfn-option-text btn-icon-right mfn-option-blank"><span class="text export-import-current">'.esc_html__('Export', 'mfn-opts').'</span><span class="mfn-icon mfn-icon-arrow-down"></span></a>
                <div class="dropdown-wrapper">
                    <a class="mfn-dropdown-item mfn-export-import-opt mfn-export-get active" data-filter="panel-export-import" href="#"> '.esc_html__('Export', 'mfn-opts').'</a>
                    <a class="mfn-dropdown-item mfn-export-import-opt" data-filter="panel-export-import-import" href="#"> '.esc_html__('Import', 'mfn-opts').'</a>
                    <a class="mfn-dropdown-item mfn-export-import-opt" data-filter="panel-export-import-templates" href="#"> '.esc_html__('Templates', 'mfn-opts').'</a>';

										if( ! apply_filters('betheme_disable_single', false) ){
											echo '<a class="mfn-dropdown-item mfn-export-import-opt" data-filter="panel-export-import-single-page" href="#"> '.esc_html__('Single page', 'mfn-opts').'</a>';
										}

										echo '<a class="mfn-dropdown-item mfn-export-import-opt" data-filter="panel-export-import-classes" href="#"> '.esc_html__('Global styles', 'mfn-opts').'</a>
                    <a class="mfn-dropdown-item mfn-export-import-opt mfn-export-import-opt-presets" data-filter="panel-export-import-presets" href="#"> '.esc_html__('Presets', 'mfn-opts').'</a>';
                    if( !$this->template_type ){ echo '<a class="mfn-dropdown-item mfn-export-import-opt" data-filter="panel-export-import-seo" href="#"> '.esc_html__('Builder &rarr; SEO', 'mfn-opts').'</a>'; }
                echo '</div>
            </div>
        </div>
    </div>

</div>';

?>
