<?php
/**
 * Dynamic render callback.
 *
 * @param array $attributes Block attributes.
 * @param string $content Inner blocks content (unused here).
 */
return function( $attributes, $content ) {

	ob_start();
	mfn_tell_free_delivery();
	$output = ob_get_clean();

	return $output;
};
