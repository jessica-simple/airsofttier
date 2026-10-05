<?php
class MFN_Options_group extends Mfn_Options_field
{

	/**
	 * Render
	 */

	public function render($meta = false)
	{

    $desc = $this->field['desc'] ?? '';

		// output -----

		echo '<div class="mfn-group">'. $desc .'</div>';

	}
}
