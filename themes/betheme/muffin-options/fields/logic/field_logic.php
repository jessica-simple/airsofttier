<?php
class MFN_Options_logic extends Mfn_Options_field {

	/**
	 * Render
	 */

	public function render( $meta = false, $vb = false, $js = false ) {

		if( !empty($this->value) ){
			foreach ($this->value as $i=>$val) {
				//print_r($val);
				if( is_array($val) && !empty($val) ){
					foreach ($val as $v=>$value) {
						echo '<input type="text" '.str_replace('conditions]', 'conditions]['.$i.']['.$v.'][key]', $this->get_name( $meta )).' value="'.$value['key'].'">';
						echo '<input type="text" '.str_replace('conditions]', 'conditions]['.$i.']['.$v.'][var]', $this->get_name( $meta )).' value="'.$value['var'].'">';
						echo '<input type="text" '.str_replace('conditions]', 'conditions]['.$i.']['.$v.'][value]', $this->get_name( $meta )).' value="'.$value['value'].'">';
					}
				}
			}
		}

	}
}