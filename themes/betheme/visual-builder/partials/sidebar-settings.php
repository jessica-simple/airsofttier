<?php
if( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

//$post_id = intval( $_GET['post'] );

if( $this->template_type && in_array( $this->template_type, array('header', 'footer', 'megamenu', 'popup') ) ){
	$this->options['builder-blocks-disabled'] = true;
}

echo '<div class="panel panel-settings" style="display: none;">
	<div class="mfn-form">';

	echo '<ul class="settings-links">
        <li>
            <a href="#" class="shortcutsinfo-open">
                <span class="mfn-icon mfn-icon-shortcuts"></span>
                <p>'.esc_html__('Keyboard shortcuts', 'mfn-opts').'</p>
            </a>
        </li>
        <li>
            <a href="#" class="dynamicdatainfo-open">
                <span class="mfn-icon mfn-icon-dynamic-data"></span>
                <p>'.esc_html__('Dynamic data', 'mfn-opts').'</p>
            </a>
        </li>
    </ul>';

	if( ! empty($this->options['builder-blocks-disabled']) || empty($this->options['builder-blocks']) ){

		echo '<div class="mfn-form-row mfn-row">
		  <div class="row-column row-column-12">
		    <div class="form-content form-content-full-width">
		      <div class="form-group segmented-options single-segmented-option settings">

		        <span class="mfn-icon mfn-icon-navigation"></span>

		        <div class="setting-label">
		          <h5>'.esc_html__('Navigation', 'mfn-opts').'</h5>
		        </div>

		        <div class="form-control" data-option="mfn-modern-nav">
		          <ul>
		            <li class="active" data-value="1"><a href="#"><span class="text">'.esc_html__('Modern', 'mfn-opts').'</span></a></li>
		            <li data-value="0"><a href="#"><span class="text">'.esc_html__('Classic', 'mfn-opts').'</span></a></li>
		          </ul>
		        </div>

		      </div>
		    </div>
		  </div>
		</div>';

	}

	echo '<div class="mfn-form-row mfn-row mfn-reload-required">
	  <div class="row-column row-column-12">
	    <div class="form-content form-content-full-width">
	      <div class="form-group segmented-options single-segmented-option settings">

	        <span class="mfn-icon mfn-icon-column"></span>

	        <div class="setting-label">
	          <h5>'.esc_html__('Column text editor', 'mfn-opts').'</h5>
	          <p>'.esc_html__('CodeMirror or TinyMCE', 'mfn-opts').'</p>
	        </div>

	        <div class="form-control" data-option="column-visual">
	          <ul>
	            <li class="active" data-value="0"><a href="#"><span class="text">'.esc_html__('Code', 'mfn-opts').'</span></a></li>
	            <li data-value="1"><a href="#"><span class="text">'.esc_html__('Visual', 'mfn-opts').'</span></a></li>
	          </ul>
	        </div>

	      </div>
	    </div>
	  </div>
	</div>';

	// BeBuilder Blocks

	if( empty($this->options['builder-blocks-disabled']) ){

		echo '<div class="mfn-form-row mfn-row mfn-reload-required">
		  <div class="row-column row-column-12">
		    <div class="form-content form-content-full-width">
		      <div class="form-group segmented-options single-segmented-option settings">

		        <span class="mfn-icon mfn-icon-builder-mode"></span>

		        <div class="setting-label">
		          <h5>'.esc_html__('Builder Mode', 'mfn-opts').'</h5>
		          <p>'.esc_html__('Classic blocks builder or Live builder', 'mfn-opts').'</p>
		        </div>

		        <div class="form-control" data-option="builder-blocks">
		          <ul>
		            <li data-value="1"><a href="#"><span class="text">'.esc_html__('Blocks', 'mfn-opts').'</span></a></li>
								<li class="active" data-value="0"><a href="#"><span class="text">'.esc_html__('Live', 'mfn-opts').'</span></a></li>
		          </ul>
		        </div>

		      </div>
		    </div>
		  </div>
		</div>';

	}

	if( empty($this->options['builder-blocks-disabled']) && ! empty($this->options['builder-blocks']) ){

		echo '<div class="mfn-form-row mfn-row">
		  <div class="row-column row-column-12">
		    <div class="form-content form-content-full-width">
		      <div class="form-group segmented-options single-segmented-option settings">

		        <span class="mfn-icon mfn-icon-simple-view"></span>

		        <div class="setting-label">
		          <h5>'.esc_html__('Simple view', 'mfn-opts').'</h5>
		          <p>'.esc_html__('Simplified version of elements', 'mfn-opts').'</p>
		        </div>

		        <div class="form-control" data-option="simple-view">
		          <ul>
						<li class="active" data-value="0"><a href="#"><span class="text">'.esc_html__('Off', 'mfn-opts').'</span></a></li>
						<li data-value="1"><a href="#"><span class="text">'.esc_html__('On', 'mfn-opts').'</span></a></li>
		          </ul>
		        </div>

		      </div>
		    </div>
		  </div>
		</div>';

		echo '<div class="mfn-form-row mfn-row">
		  <div class="row-column row-column-12">
		    <div class="form-content form-content-full-width">
		      <div class="form-group segmented-options single-segmented-option settings">

		        <span class="mfn-icon mfn-icon-hover-effects"></span>

		        <div class="setting-label">
		          <h5>'.esc_html__('Hover effects', 'mfn-opts').'</h5>
		          <p>'.esc_html__('Builder element bar shows on hover', 'mfn-opts').'</p>
		        </div>

		        <div class="form-control" data-option="hover-effects">
		          <ul>
								<li data-value="1"><a href="#"><span class="text">'.esc_html__('Off', 'mfn-opts').'</span></a></li>
								<li class="active" data-value="0"><a href="#"><span class="text">'.esc_html__('On', 'mfn-opts').'</span></a></li>
		          </ul>
		        </div>

		      </div>
		    </div>
		  </div>
		</div>';

	}

	echo '<div class="mfn-form-row mfn-row">
	  <div class="row-column row-column-12">
	    <div class="form-content form-content-full-width">
	      <div class="form-group segmented-options single-segmented-option settings">

	        <span class="mfn-icon mfn-icon-navigator-position"></span>

	        <div class="setting-label">
	          <h5>'.esc_html__('Navigator', 'mfn-opts').'</h5>
	        </div>

	        <div class="form-control" data-option="navigator-position">
	          <ul>
	            <li class="active" data-value="0"><a href="#"><span class="text">'.esc_html__('Default', 'mfn-opts').'</span></a></li>
	            <li data-value="1"><a href="#"><span class="text">'.esc_html__('Side', 'mfn-opts').'</span></a></li>
	          </ul>
	        </div>

	      </div>
	    </div>
	  </div>
	</div>';

	echo '<div class="mfn-form-row mfn-row">
	  <div class="row-column row-column-12">
	    <div class="form-content form-content-full-width">
	      <div class="form-group segmented-options single-segmented-option settings">

	        <span class="mfn-icon mfn-icon-history-mode"></span>

	        <div class="setting-label">
	          <h5>'.esc_html__('History mode', 'mfn-opts').'</h5>
	          <p>'.esc_html__('Ajax is slower but has more storage', 'mfn-opts').'</p>
	        </div>

	        <div class="form-control" data-option="history-mode">
	          <ul>
	            <li class="active" data-value="0"><a href="#"><span class="text">'.esc_html__('Default', 'mfn-opts').'</span></a></li>
	            <li data-value="1"><a href="#"><span class="text">'.esc_html__('Ajax', 'mfn-opts').'</span></a></li>
	          </ul>
	        </div>

	      </div>
	    </div>
	  </div>
	</div>';

	// UI mode

	echo '<div class="mfn-form-row mfn-row mfn-reload-required">
	  <div class="row-column row-column-12">
	    <div class="form-content form-content-full-width">
	      <div class="form-group segmented-options single-segmented-option settings">

	        <span class="mfn-icon mfn-icon-user-interface"></span>

	        <div class="setting-label">
	          <h5>'.esc_html__('User interface', 'mfn-opts').'</h5>
	        </div>

	        <div class="form-control" data-option="user-interface">
	          <ul>
	          	<li class="active" data-value="dev"><a href="#"><span class="text">'.esc_html__('Developer', 'mfn-opts').'</span></a></li>
	            <li data-value="default"><a href="#"><span class="text">'.esc_html__('Classic', 'mfn-opts').'</span></a></li>
	          </ul>
	        </div>

	      </div>
	    </div>
	  </div>
	</div>';

	echo '<div class="mfn-form-row mfn-row">
	  <div class="row-column row-column-12">
	    <div class="form-content form-content-full-width">
	      <div class="form-group segmented-options single-segmented-option settings">

	        <span class="mfn-icon mfn-icon-scalable-preview"></span>

	        <div class="setting-label">
	          <h5>'.esc_html__('Scalable Preview', 'mfn-opts').'</h5>
	          <p>'.esc_html__('Adjust preview to your screen', 'mfn-opts').'</p>
	        </div>

	        <div class="form-control" data-option="scalable-preview">
	          <ul>
	            <li class="active" data-value=""><a href="#"><span class="text">'.esc_html__('Disable', 'mfn-opts').'</span></a></li>
	            <li data-value="enable"><a href="#"><span class="text">'.esc_html__('Enable', 'mfn-opts').'</span></a></li>
	          </ul>
	        </div>

	      </div>
	    </div>
	  </div>
	</div>';

	echo '<div class="mfn-form-row mfn-row">
	  <div class="row-column row-column-12">
	    <div class="form-content form-content-full-width">
	      <div class="form-group segmented-options single-segmented-option settings">

	        <span class="mfn-icon mfn-icon-preset"></span>

	        <div class="setting-label">
	          <h5>'.esc_html__('Presets', 'mfn-opts').'</h5>
	          <p>'.esc_html__('Enable presets', 'mfn-opts').'</p>
	        </div>

	        <div class="form-control" data-option="enable-presets">
	          <ul>
	            <li class="active" data-value=""><a href="#"><span class="text">'.esc_html__('Disable', 'mfn-opts').'</span></a></li>
	            <li data-value="enable"><a href="#"><span class="text">'.esc_html__('Enable', 'mfn-opts').'</span></a></li>
	          </ul>
	        </div>

	      </div>
	    </div>
	  </div>
	</div>';

	echo '<div class="mfn-form-row mfn-row">
	  <div class="row-column row-column-12">
	    <div class="form-content form-content-full-width">
	      <div class="form-group segmented-options single-segmented-option settings">

	        <span class="mfn-icon mfn-icon-dark-mode"></span>

	        <div class="setting-label">
	          <h5>'.esc_html__('Color scheme', 'mfn-opts').'</h5>
	        </div>

	        <div class="form-control" data-option="ui-theme">
	          <ul>
	            <li class="active" data-value="mfn-ui-auto"><a href="#"><span class="text">'.esc_html__('Auto', 'mfn-opts').'</span></a></li>
	            <li data-value="mfn-ui-light"><a href="#"><span class="text">'.esc_html__('Light', 'mfn-opts').'</span></a></li>
	            <li data-value="mfn-ui-dark"><a href="#"><span class="text">'.esc_html__('Dark', 'mfn-opts').'</span></a></li>
	          </ul>
	        </div>

	      </div>
	    </div>
	  </div>
	</div>';

echo '</div>
</div>';
