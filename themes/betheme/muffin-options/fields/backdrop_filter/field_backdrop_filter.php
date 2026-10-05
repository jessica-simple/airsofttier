<?php
class MFN_Options_backdrop_filter extends Mfn_Options_field {

	/**
	 * Render
	*/

	public function render( $meta = false, $vb = false, $js = false ) {

		if( !empty($this->value) ){
			foreach ($this->value as $v => $value) {
				echo '<input type="hidden" '.str_replace('inner:backdrop-filter]', 'inner:backdrop-filter]['.$v.']', $this->get_name( $meta )).' value="'.$value.'">';
			}
		}

	}

}