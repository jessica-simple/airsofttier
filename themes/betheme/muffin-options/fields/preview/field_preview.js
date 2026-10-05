(function($) {

  /* globals jQuery */

  "use strict";

  var MfnFieldPreview = (function() {

    var $head = $('head'),
      $preview = $('.mfn-preview .button-preview'),
      $group = $preview.closest('.mfn-card-group'),
      $condition = $('.condition', $group);

    var text = $preview.first().text();

    var systemFonts = [
      'Arial',
      'Georgia',
      'Tahoma',
      'Times',
      'Trebuchet',
      'Verdana'
    ];

    var font = {
      'family' : '',
      'style' : ''
    };

    /**
     * Multiple
     */

    function multiple( el, key ){

      var $fields = el.closest('.form-control').find('input');
      var value = [];

      $fields.each(function(){

        var val = $(this).val();

        // auto px

        if( val == parseInt(val, 10) ){
          val = val + 'px';
        }

        if( ! val ){
          val = 0;
        }

        value.push(val);

      });

      value = value.join(' ');

      $head.find('#mfn-button-'+ key).remove();
      $head.append('<style id="mfn-button-'+ key +'">.mfn-ui{--mfn-button-'+ key +':'+ value +'}</style>');

    }

    /**
     * Font family
     */

    function fontFamily( el ){

      var val = el.val();

      font['family'] = '';

      if( -1 === $.inArray( val, systemFonts ) ){
        if( typeof WebFont !== 'undefined' ){

          font['family'] = val;

          if( font['family'] ){
            WebFont.load({
              google: {
                families: [font['family'] + ':400,' + font['style']],
                text: text
              }
            });
          }
        }
      }

      $head.find('#mfn-button-font-family').remove();

      if( val ){
        $head.append('<style id="mfn-button-font-family">.mfn-ui{--mfn-button-font-family:"'+ val +'"}</style>');
      }

    }

    /**
     * Font
     */

    function fontStyle( el ){

      var val = el.val(),
        key = el.data('key'),
        weight, style;

      // weight & style

      if( 'weight-style' == key ){

        font['style'] = val;

        $head.find('#mfn-button-font-weight').remove();
        $head.append('<style id="mfn-button-font-weight">.mfn-ui{--mfn-button-font-weight:'+ val.replace('italic', '') +'}</style>');

        $head.find('#mfn-button-font-style').remove();
        if( -1 === val.indexOf('italic') ){
          $head.append('<style id="mfn-button-font-style">.mfn-ui{--mfn-button-font-style:normal}</style>');
        } else {
          $head.append('<style id="mfn-button-font-style">.mfn-ui{--mfn-button-font-style:italic}</style>');
        }

        if( font['family'] ){
          WebFont.load({
            google: {
              families: [font['family'] + ':400,' + font['style']],
              text: text
            }
          });
        }

        return true;
      }

      // auto px

      if( val == parseInt(val, 10) ){
        val = val + 'px';
      }

      $head.find('#mfn-button-'+ key).remove();
      $head.append('<style id="mfn-button-'+ key +'">.mfn-ui{--mfn-button-'+ key +':'+ val +'}</style>');
    }

    /**
     * Animation time
     */

    function animationType( el ){

      var val = el.val();

      $('.mfn-ui').attr('data-animation',val);

    }

    /**
     * Animation time
     */

    function animationTime( el ){

      var val = el.val();

      if( val == parseFloat(val) ){
        val = val + 's';
      }

      $head.find('#mfn-button-transition').remove();

      if( val ){
        $head.append('<style id="mfn-button-transition">.mfn-ui{--mfn-button-transition:'+ val +'}</style>');
      }

    }

    /**
     * Gap
     */

    function gap( el ){

      var val = el.val();

      if( val == parseFloat(val) ){
        val = val + 'px';
      }

      $head.find('#mfn-button-gap').remove();

      if( val ){
        $head.append('<style id="mfn-button-gap">.mfn-ui{--mfn-button-gap:'+ val +'}</style>');
      }

    }

    /**
     * Color
     */

    function color( el, val ){

      var key = el.data('key'),
        id = el.closest('.mfn-form-row').attr('id');

      if( 'normal' != key ){
        id += '-' + key;
      }

      // console.log([id, val]);

      $head.find('#mfn-'+ id).remove();

      if( val ){
        $head.append('<style id="mfn-'+ id +'">.mfn-ui{--mfn-'+ id +':'+ val +'}</style>');
      }

    }

    /**
     * Gradient
     */

    function gradient( el, val ){

      var id = el.closest('.mfn-form-row').attr('id');

      // console.log([id, val]);

      $head.find('#mfn-'+ id).remove();

      if( val ){
        $head.append('<style id="mfn-'+ id +'">.mfn-ui{--mfn-'+ id +':'+ val +'}</style>');
      }

    }

    /**
     * Type
     */

    function type( el, val ){

      var id = el.closest('.mfn-form-row').attr('id');

      id = id.replace('-background-type','-gradient');

      $head.find('#mfn-'+ id +'-type').remove();
      $head.find('#mfn-'+ id +'-hover-type').remove();

      if( ! val ) {
        $head.append('<style id="mfn-'+ id +'-type">.mfn-ui{--mfn-'+ id +':unset}</style>');
        $head.append('<style id="mfn-'+ id +'-hover-type">.mfn-ui{--mfn-'+ id +'-hover:unset}</style>');
      }

    }

    /**
     * Box shadow
     */

    function boxShadow( el, val ){

      var key = el.data('key'),
        id = el.closest('.mfn-form-row').attr('id');

      $head.find('#mfn-'+ id).remove();

      if( val ){
        $head.append('<style id="mfn-'+ id +'">.mfn-ui{--mfn-'+ id +':'+ val +'}</style>');
      }

    }

    /**
     * Attach events to buttons
     */

    function bind() {

      $preview.on('click', function(e){
        e.preventDefault();
      })

      $('.preview-font-family select').on('change', function() {
        fontFamily( $(this) );
      });

      $('.preview-font input, .preview-font select').on('change', function(e) {
        fontStyle( $(this) );
      });

      $('.preview-padding input').on('change', function() {
        multiple( $(this), 'padding' );
      });

      $('.preview-border-width input').on('change', function() {
        multiple( $(this), 'border-width' );
      });

      $('.preview-border-radius input').on('change', function() {
        multiple( $(this), 'border-radius' );
      });

      $('.preview-gap input').on('change', function() {
        gap( $(this) );
      });

      $('.preview-animation-time input').on('change', function() {
        animationTime( $(this) );
      });

      $('.preview-animation-type select').on('change', function() {
        animationType( $(this) );
      });

      $('.preview-color .has-colorpicker').on('change', function(e, value) {
        color( $(this), $(this).val() );
      });

      $('.preview-gradient .gradient-hidden').on('change', function() {
        gradient( $(this), $(this).val() );
      });

      $('.preview-type input').on('change', function() {
        if( $(this).prop('checked') ){
          type( $(this), $(this).val() );
        }

      });

      $('.preview-box-shadow .mfn-field-value').on('change', function(e, value) {
        boxShadow( $(this), $(this).val() );
      });

    }

    /**
     * Preview state on document ready
     */

    function ready(){

      if( mfn_fonts ){
        systemFonts = systemFonts.concat( mfn_fonts );
      }

      $('.preview-font-family select').trigger('change');
      $('.preview-font input, .preview-font select').trigger('change');
      $('.preview-padding input').first().trigger('change');
      $('.preview-border-width input').trigger('change');
      $('.preview-border-radius input').trigger('change');
      $('.preview-animation-type select').trigger('change');
      $('.preview-animation-time input').trigger('change');
      $('.preview-gap input').trigger('change');
      $('.preview-color input.mfn-form-input').trigger('change');
      $('.preview-gradient input.gradient-hidden').trigger('change');
      $('.preview-type input').trigger('change');

    }

    /**
     * Runs whole script.
     */

    function init() {
      bind();
      ready();
    }

    /**
     * Return
     * Method to start the closure
     */

    return {
      init: init
    };

  })();

  /**
   * $(document).ready
   * Specify a function to execute when the DOM is fully loaded.
   */

  $(function() {
    MfnFieldPreview.init();
  });

})(jQuery);
