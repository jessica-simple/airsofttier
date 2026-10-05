<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

echo '<div class="panel panel-ie panel-export-import-presets" style="display: none;">
    <span class="mfn-icon mfn-icon-preset-export-import"></span>
    <h3>'.esc_html__('Presets', 'mfn-opts').'</h3>
    <h5>'.esc_html__('Export presets', 'mfn-opts').':</h5>
    <div class="mfn-form">
        <div class="form-content form-content-full-width">
            <div class="form-group">
                <div class="form-control">
                    <textarea id="export-presets-data-textarea" class="mfn-form-control mfn-import-field mfn-form-textarea"></textarea>
                </div>
            </div>
        </div>
        <a class="mfn-btn mfn-btn-blue mfn-export-presets-button" href="#"><span class="btn-wrapper">'.esc_html__('Export', 'mfn-opts').'</span></a>
    </div>

    <hr>

    <h5>'.esc_html__('Import presets', 'mfn-opts').'</h5>
    <div class="mfn-form">
        <div class="form-content form-content-full-width">
            <div class="form-group">
                <div class="form-control">
                    <textarea id="import-presets-data-textarea" class="mfn-form-control mfn-import-field mfn-form-textarea" placeholder="'.esc_html__('Paste exported presets here', 'mfn-opts').'"></textarea>
                </div>
            </div>
        </div>
        <a class="mfn-btn mfn-btn-blue mfn-import-presets-button" href="#"><span class="btn-wrapper">'.esc_html__('Import', 'mfn-opts').'</span></a>
    </div>

</div>';

echo '<div class="panel panel-ie panel-export-import-classes" style="display: none;">
    <span class="mfn-icon mfn-icon-global-styles-list"></span>
    <h3>'.esc_html__('Global styles', 'mfn-opts').'</h3>
    <h5>'.esc_html__('Export', 'mfn-opts').':</h5>
    <div class="mfn-form">
        <div class="form-content form-content-full-width">
            <div class="form-group">
                <div class="form-control">
                    <textarea id="export-classes-data-textarea" class="mfn-form-control mfn-import-field mfn-form-textarea"></textarea>
                </div>
            </div>
        </div>
        <a class="mfn-btn mfn-btn-blue mfn-export-classes-button" href="#"><span class="btn-wrapper">'.esc_html__('Export', 'mfn-opts').'</span></a>
    </div>

    <hr>

    <h5>'.esc_html__('Import', 'mfn-opts').'</h5>
    <div class="mfn-form">
        <div class="form-content form-content-full-width">
            <div class="form-group">
                <div class="form-control">
                    <textarea id="import-classes-data-textarea" class="mfn-form-control mfn-import-field mfn-form-textarea" placeholder="'.esc_html__('Paste exported global styles here', 'mfn-opts').'"></textarea>
                </div>
            </div>
        </div>
        <a class="mfn-btn mfn-btn-blue mfn-import-classes-button" href="#"><span class="btn-wrapper">'.esc_html__('Import', 'mfn-opts').'</span></a>
    </div>

</div>';

echo '<div class="panel panel-ie panel-export-import" style="display: none;">

    <div class="mfn-form">
        <div class="form-content form-content-full-width">
            <div class="form-group">
                <div class="form-control">
                    <textarea class="mfn-form-control mfn-export-field mfn-form-textarea"></textarea>
                </div>
            </div>
        </div>
    </div>

    <p>'.esc_html__('Copy to clipboard: Ctrl+C (Cmd+C for Mac)', 'mfn-opts').'</p>

    <a class="mfn-btn mfn-btn mfn-export-cancel" href="#"><span class="btn-wrapper">'.esc_html__('Cancel', 'mfn-opts').'</span></a>
    <a class="mfn-btn mfn-btn-blue mfn-export-button" href="#"><span class="btn-wrapper">'.esc_html__('Copy to clipboard', 'mfn-opts').'</span></a>

</div>';



echo '<div class="panel panel-ie panel-export-import-import" style="display: none;">

    <div class="mfn-form">
        <div class="form-content form-content-full-width">
            <div class="form-group">
                <div class="form-control">
                    <textarea id="import-data-textarea" class="mfn-form-control mfn-import-field mfn-form-textarea" placeholder="'.esc_html__('Paste import data here', 'mfn-opts').'"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="mfn-form import-options">
    <select id="mfn-import-type" class="mfn-form-control mfn-form-select mfn-import-type">
        <option value="before">'.esc_html__('Insert BEFORE current builder content', 'mfn-opts').'</option>
        <option value="after">'.esc_html__('Insert AFTER current builder content', 'mfn-opts').'</option>
        <option value="replace">'.esc_html__('Replace current builder content', 'mfn-opts').'</option>
    </select>

    <p class="global-sections-import-info">'.esc_html__('Important: The first section only will be imported from the copied content. Please note that the current section will be replaced by a new one. Instead, we recommend to copy a single section by using “Import & Replace” option located under three vertical dots on the right side at section green bar.', 'mfn-opts').'</p>

    <a data-id="'.get_the_ID().'" class="mfn-btn mfn-btn-blue mfn-import-button" href="#"><span class="btn-wrapper">'.esc_html__('Import', 'mfn-opts').'</span></a>
    </div>


</div>';

echo '<div class="panel panel-ie panel-export-import-single-page" style="display: none;">

    <div class="mfn-form">
        <div class="form-content form-content-full-width">
            <div class="form-group">
                <div class="form-control" style="">

                        <span class="mfn-single-page-icon"></span>

                        <h3>'.esc_html__('Single page import', 'mfn-opts').'</h3>
                        <p>'.__('Paste a <code>link</code> from one of <a target="_blank" href="https://muffingroup.com/betheme/websites/">pre-built websites</a>', 'mfn-opts').'</p>

                        <input id="mfn-items-import-page" class="mfn-form-control mfn-form-input" placeholder="https://themes.muffingroup.com/betheme/about/">

                        <p class="hint">'.esc_html__('Only builder content will be imported. Does not import archives. Single pages and posts only. Theme options, sliders and images will not be imported.', 'mfn-opts').'</p>

                    </div>
            </div>
        </div>
    </div>

    <div class="mfn-form import-options">
    <select class="mfn-form-control mfn-form-select mfn-import-type">
        <option value="before">'.esc_html__('Insert BEFORE current builder content', 'mfn-opts').'</option>
        <option value="after">'.esc_html__('Insert AFTER current builder content', 'mfn-opts').'</option>
        <option value="replace">'.esc_html__('Replace current builder content', 'mfn-opts').'</option>
    </select>

    <a data-id="'.get_the_ID().'" class="mfn-btn mfn-btn-blue mfn-import-single-page-button" href="#"><span class="btn-wrapper">'.esc_html__('Import', 'mfn-opts').'</span></a>
    </div>


</div>';

if( !$this->template_type ){
echo '<div class="panel panel-ie panel-export-import-seo" style="display: none;">

    <div class="mfn-form">
        <div class="form-content form-content-full-width">
            <div class="form-group">
                <div class="form-control" style="">

                    <span class="mfn-icon mfn-icon-builder-to-seo"></span>
                    <h3>'.esc_html__('Builder &rarr; SEO', 'mfn-opts').'</h3>
                    <p>'.esc_html__('This option is useful for plugins like Yoast SEO to analyze BeBuilder content. It will collect content from BeBuilder and copy it to new Content Block.', 'mfn-opts').'</p>
                    <p>'.__('You can hide the content if you set <code>"The content"</code> option to Hide.', 'mfn-opts').'</p>

                </div>
            </div>
        </div>
    </div>

    <div class="mfn-form import-options">
        <a data-id="'.get_the_ID().'" class="mfn-btn mfn-btn-blue mfn-builder-export-to-seo-button" href="#"><span class="btn-wrapper">'.esc_html__('Generate', 'mfn-opts').'</span></a>
    </div>

</div>';
}


echo '<div class="panel panel-ie panel-export-import-templates" style="display: none;">

    <h5>'.esc_html__('Select a template from the list', 'mfn-opts').':</h5>';

$page_id = $this->post_id;

$args = array(
    'post_type' => 'template',
    'posts_per_page'=> -1,
);

$templates = get_posts( $args );

if ( is_array( $templates ) && count($templates) > 0 ) {
    $classes = '';

    echo '<ul class="mfn-items-list mfn-items-import-template">';
    foreach ( $templates as $t=>$template ) {

        $tmpl_type = get_post_meta($template->ID, 'mfn_template_type', true);

        $t == 0 ? $classes = 'active' : $classes = '';
        if( empty($tmpl_type) || $tmpl_type == 'default' ){
            echo '<li class="'.$classes.'" data-id="'. esc_attr($template->ID) .'"><a href="#"><h5>'. esc_html($template->post_title) .'</h5><p>'. esc_html($template->post_modified) .'</p></a></li>';
        }

    }
    echo '</ul>';
}


echo '<div class="mfn-form templates-options">
    <select id="mfn-import-template-type" class="mfn-form-control mfn-form-select mfn-import-template-type">
        <option value="before">'.esc_html__('Insert BEFORE current builder content', 'mfn-opts').'</option>
        <option value="after">'.esc_html__('Insert AFTER current builder content', 'mfn-opts').'</option>
        <option value="replace">'.esc_html__('Replace current builder content', 'mfn-opts').'</option>
    </select>

		<p class="global-sections-import-info">'.esc_html__('Important: The first section only will be imported from the template content. Please note that the current section will be replaced by a new one.', 'mfn-opts').'</p>

    <a data-id="'.get_the_ID().'" class="mfn-btn mfn-btn-blue mfn-import-template-button" href="#"><span class="btn-wrapper">'.esc_html__('Import', 'mfn-opts').'</span></a>
    </div>


</div>';
