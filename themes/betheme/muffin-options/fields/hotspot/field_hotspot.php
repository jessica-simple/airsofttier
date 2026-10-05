<?php
class MFN_Options_hotspot extends Mfn_Options_field {

	/**
	 * Render
	 */

	public function render( $meta = false, $vb = false, $js = false ) {

    global $post;

    // print_r($this->value);

    echo '<p>'. apply_filters('betheme_label', 'Be') .'Builder Blocks Classic works in legacy mode so elements added in newer versions may not be supported. Please <a href="post.php?post='. $post->ID .'&amp;action='. apply_filters('betheme_slug', 'mfn') .'-live-builder">edit with '. apply_filters('betheme_label', 'Be') .'Builder</a>.</p>';

    $name = $this->field['id'];

    // output

    if( $this->value && is_array($this->value) ){
      foreach( $this->value as $i => $value ){
        if( is_array( $value ) ){
          foreach( $value as $v1 => $value1 ){
            if( is_array( $value1 ) ){
              foreach( $value1 as $v2 => $value2 ){
                if( is_array( $value2 ) ){
                  foreach( $value2 as $v3 => $value3 ){
                    echo '<input type="hidden" name="'. $name .'['. $i .']['. $v1 .']['. $v2 .']['. $v3 .']" value="'. $value3 .'">';
                  }
                } else {
                  echo '<input type="hidden" name="'. $name .'['. $i .']['. $v1 .']['. $v2 .']" value="'. $value2 .'">';
                }
              }
            } else {
              echo '<input type="hidden" name="'. $name .'['. $i .']['. $v1 .']" value="'. $value1 .'">';
            }
          }
        }
      }
    }

	}
}
