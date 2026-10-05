<?php
class MFN_Options_dimensions extends Mfn_Options_field
{

	/**
	 * Render
	 */

	public function render( $meta = false )
	{
		$class = '';
		$value = '';
		$explode_val = false;
		$readonly = false;
		$input_class = false;

 		// inputs and labels

  	$inputs = [
      'top' => 'Top',
      'right' => 'Right',
      'bottom' => 'Bottom',
      'left' => 'Left',
    ];

    if( str_replace(['border-radius','border_radius'], '', $this->field['id']) != $this->field['id'] ){ // strpos in array
    	$inputs = [
	      'top' => '&#8598;',
	      'right' => '&#8599;',
	      'bottom' => '&#8600;',
	      'left' => '&#8601;',
	    ];
    }

		// value

    if ( $this->value ) {

			$value = $this->value;

			if( is_array($value) ){
				$explode_val = $value;
			} else {
				$explode_val = explode(' ', $value);

				$explode_val = [
					'top' => $explode_val[0],
					'right' => isset($explode_val[1]) ? $explode_val[1] : $explode_val[0],
					'bottom' => isset($explode_val[2]) ? $explode_val[2] : $explode_val[0],
					'left' => isset($explode_val[3]) ? $explode_val[3] : $explode_val[0],
				];

			}

		} elseif( ! empty( $this->field['std'] ) ) {

			$value = $this->field['std'];

		} else {

			$value = '';

		}

		// classes

		if( $explode_val && (count($explode_val) == 4 ) && ( count(array_unique($explode_val) ) == 1) ){
			$class .= ' isLinked';
			$readonly = 'readonly';
		}

		if( isset($this->field['version']) ){
			$class .= ' '. $this->field['version'];
		} else {
			$class .= ' pseudo';
		}

		// output -----

		echo '<div class="form-group multiple-inputs has-addons has-addons-append '. esc_attr( $class ) .'">';
			echo '<div class="form-control">';

				if( ! isset($this->field['version']) ) {
					echo '<input class="pseudo-field mfn-form-control mfn-field-value" type="hidden" '. $this->get_name( $meta ) .' value="'. $value .'" autocomplete="off"/>';
				}

				foreach( $inputs as $i=>$input ){

					// value

					$sub_value = $explode_val[$i] ?? '';

					// classes

					$input_class = 'field-'.esc_attr( $i );

					if( isset( $this->field['version'] ) ){
						$input_class .= ' mfn-field-value';
					}

					$field_class = false;

					if( 'top' != $i ){
						$field_class = 'disableable';
						if( $readonly ){
							$input_class .= ' readonly';
						}
					}

					// field output ---

					echo '<div class="field '. esc_attr( $field_class ) .'" data-key="'. $inputs[$i] .'">';
						echo '<input type="text" class="mfn-form-control mfn-form-input numeral '. esc_attr( $input_class ) .' " data-key="'. esc_attr( $i ) .'" ';

							if( isset($this->field['version']) && $this->field['version'] == 'separated-fields' ){
								echo $this->get_name( $meta, $i ) .' value="'. esc_attr( $sub_value ) .'"';
							}else{
								echo 'value="'. esc_attr( $sub_value ).'"';
							}
							echo ( ('top' != $i) ? $readonly : '' );

						echo '>';
					echo '</div>';

				}

			echo '</div>';

			echo '<div class="form-addon-append">';
				echo '<a href="#" class="link">';
					echo '<span class="label"><i class="icon-link"></i></span>';
				echo '</a>';
			echo '</div>';

		echo '</div>';

	}


	/**
	 * Enqueue Function.
	 */

	public function enqueue()
	{
		wp_enqueue_script( 'mfn-field-dimensions', MFN_OPTIONS_URI .'fields/dimensions/field_dimensions.js', ['jquery'], MFN_THEME_VERSION, true );
	}

}
