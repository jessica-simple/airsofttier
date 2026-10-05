<?php
class MFN_Options_preview extends Mfn_Options_field
{

  /**
	 * Constructor
	 */

	public function __construct( $field, $value = false, $prefix = false )
	{
    parent::__construct( $field, $value, $prefix );
	}

	/**
	 * Render
	 */

	public function render($meta = false)
	{
		// output -----

		echo '<div class="form-group mfn-preview">';

			echo '<div class="item" data-key="Default">';
				echo '<a href="#" class="button-preview default"><span class="button_icon"><i class="icon-air"></i></span><span class="button_label">Button text</span></a>';
			echo '</div>';

			echo '<div class="item" data-key="Highlighted">';
				echo '<a href="#" class="button-preview highlighted"><span class="button_icon"><i class="icon-air"></i></span><span class="button_label">Button text</span></a>';
			echo '</div>';

			echo '<div class="item" data-key="Shop">';
				echo '<a href="#" class="button-preview shop">Button text</a>';
			echo '</div>';

			echo '<div class="item" data-key="Action">';
				echo '<a href="#" class="button-preview action">Button text</a>';
			echo '</div>';
			
    echo '</div>';

		$this->enqueue();
	}

	/**
	 * Enqueue Function.
	 */

	public function enqueue()
	{
		if( ! mfn_opts_get('google-font-mode') ) {
			wp_enqueue_script( 'mfn-webfont', 'https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js', array( 'jquery' ), false, true );
		}

		wp_register_script('mfn-field-preview', MFN_OPTIONS_URI .'fields/preview/field_preview.js', array('jquery'), MFN_THEME_VERSION, true);

		$custom_fonts = mfn_fonts('custom');
		wp_localize_script( 'mfn-field-preview', 'mfn_fonts', $custom_fonts );

		wp_enqueue_script( 'mfn-field-preview' );
	}

}
