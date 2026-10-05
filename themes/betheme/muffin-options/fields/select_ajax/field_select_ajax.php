<?php
class MFN_Options_select_ajax extends Mfn_Options_field
{

	/**
	 * Render
	 * @deprecated $vb
	 * @deprecated $js
	 */

	public function render( $meta = false, $vb = false, $js = false )
	{

		// output -----

		$title = !empty($this->value) && is_numeric($this->value) ? get_the_title($this->value) : '';

		echo '<div class="form-group">';
			echo '<div class="form-control">';

				echo '<div class="mfn-select-ajax">';
					echo '<input type="hidden" value="'. esc_attr( $this->value ) .'" '. $this->get_name( $meta ) .' class="mfn-field-value"><input type="text" value="'.$title.'" placeholder="Search page..." class="mfn-form-control mfn-select-ajax-input">';
				echo '</div>';

			echo '</div>';
		echo '</div>';

		echo $this->get_description();

	}
}
