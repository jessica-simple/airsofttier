(function($) {

  /* globals jQuery, ajaxurl, wp */

  "use strict";

  var MfnOptions = (function($) {

    var $options = $('#mfn-options'),

      $menu = $('.mfn-menu', $options),
      $content = $('.mfn-wrapper', $options),
      $subheader = $('.mfn-subheader', $options),
      $modal = $('.mfn-modal', $options),
      $currentModal = false,

      $title = $('.topbar-title .page-title', $options),
      $subtitle = $('.topbar-title .subpage-title', $options),
      $tabs = $('.subheader-tabs', $options),

      $last_tab = $('#last_tab', $options);

    var loading = true, // prevent some functions until window load
      scrollLock = false; // prevent active on scroll after click

    /**
     * Main menu
     */

    var menu = {

      init: function() {

        var last_tab = $last_tab.val();

        if( window.location.hash.replace('#','') ){
          return false;
        }

        if( ! last_tab ){
          last_tab = 'general';
        }

        this.click( $('li[data-id="'+ last_tab +'"] a', $menu) );

      },

      // menu.click()

      click: function($el) {

        var $li = $el.closest('li'),
          tab = $li.data('id'),
          title, subtitle;

        if( $li.hasClass('active') ){
          // return false;
        }

        if ( ! validateCard() ){
          return;
        }

        responsive.reset();

        $menu.find('li').removeClass('active');

        $li.addClass('active');

        if( $li.is('[data-id]') ){

          // second level

          $li.parents('li').addClass('active');

          title = $li.parent().closest('li').children('a').text();
          subtitle = $li.children('a').text();

        } else {

          // first level

          $li.find('li:first').addClass('active');

          tab = $li.find('li:first').data('id');

          title = $li.children('a').text();
          subtitle = $li.find('li:first a').text();

        }

        $('.mfn-card-group', $options).removeClass('active');
        $('.mfn-card-group[data-tab="'+ tab +'"]', $options).addClass('active');

        $last_tab.val( tab );

        if( $options.hasClass('skin-selected') ){
          $options.attr('class', 'mfn-ui mfn-options skin-selected mfn-tab-'+tab);
        } else {
          $options.attr('class', 'mfn-ui mfn-options mfn-tab-'+tab);
        }

        $title.text( title );
        $subtitle.text( subtitle );

        subheader.init();

        $('html, body').animate({
          scrollTop: 0
        }, 300);

      },

      hash: function( link ){

        var tab, card;

        link = ( typeof link !== 'undefined' ) ?  link : window.location.hash;
        link = link.replace('#','');

        if( ! link ){
          return false;
        }

        tab = link.split('&')[0];
        card = link.split('&')[1];

        this.click( $('li[data-id="'+ tab +'"] a', $menu) );

        if( card ){
          subheader.click( $('li[data-card-id="'+ card +'"] a', $subheader) );
        }

      }

    };

    /**
     * HTML fields validation, ie. [input min="2"], etc.
     */

    var validateCard = function(){

      var $card = $('.mfn-card-group.active');
      let isValid = true;

      $card.find('input').each(function() {
        if (!this.checkValidity()) {
          this.reportValidity(); // Show the default browser error message
          isValid = false;
        }
      });

      return isValid;

    };

    /**
     * Subheader
     */

    var subheader = {

      startY: 0,
      topY: 0,
      bodyY: 0,
      width: 0,
      headerH: 0,
      dashboardMenuH: 0,
      $placeholder: $('.mfn-subheader-placeholder', $content),

      init: function(){

        var $tab = $('.mfn-card-group.active', $options);

        var link = $tab.data('tab');

        if( ! link ){
          return false;
        }

        $tabs.empty();

        $('.mfn-card', $tab).each(function( index ){

          var title = $(this).find('.card-title').text(),
            id = $(this).data('card'),
            attr = $(this).data('attr'),
            cssClass = '';

          if( ! index ){
            cssClass = 'active';
          }

          if( attr ){
            attr = ' data-attr="'+ attr +'"' ;
          } else {
            attr = '';
          }

          $tabs.append( '<li class="'+ cssClass +'" data-card-id="'+ id +'"'+ attr +'><a href="#'+ link +'&'+ id +'">'+ title +'</a></li>' );

        });

        window.location.hash = '#'+ link;

        this.set();

      },

      click: function($el){

        var $li = $el.closest('li');

        var id = $li.data('card-id'),
          adminH = $('#wpadminbar').height();

        $li.addClass('active').siblings('li').removeClass('active');

        this.setScrollLock();

        $('html, body').animate({
          scrollTop: $('.mfn-card-group.active .mfn-card[data-card="'+ id +'"]').offset().top - ( adminH + this.headerH + 20 )
        }, 300);

      },

      set: function(){

        this.topY = $content.offset().top;
        this.dashboardMenuH = $('.mfn-dashboard-menu').height() || 0;
        this.bodyY = $('#wpbody').offset().top + this.dashboardMenuH;

        this.width = $content.innerWidth();
        this.headerH = $subheader.height();

        this.startY = this.topY + $('.mfn-topbar', $content).height();

        // add dashboard sticky menu height if window height > 800
        if( window.innerHeight > 800 ){
          this.startY -= this.dashboardMenuH
        }

        this.$placeholder.height( $subheader.height() );

      },

      setScrollLock: function(){

        scrollLock = true;

        setTimeout(function(){
          scrollLock = false;
        }, 300);

      },

      sticky: function(){

        var windowY = $(window).scrollTop();

        if( windowY >= this.startY ){

          this.$placeholder.show(0);
          $subheader.addClass('sticky').css({
            position: 'fixed',
            top: this.bodyY,
            width: this.width
          });

        } else {

          this.$placeholder.hide(0);
          $subheader.removeClass('sticky').css({
            position: 'static',
            top: 0,
            width: 'unset'
          });

        }

      },

      scrollActive: function(){

        if( scrollLock ){
          return false;
        }

        var $tab = $('.mfn-card-group.active', $options);

        var first = false;

        $('.mfn-card:visible', $tab).each(function() {

          var windowY = $(window).scrollTop();
          var cardY = $(this).offset().top + $(this).height();

          cardY = cardY - subheader.bodyY - subheader.headerH;

          if( first ){
            return false;
          }

          if ( cardY > windowY ) {
            first = $(this).data('card');
          }

        });

        if ( first ) {

          $tabs.find('li[data-card-id="'+ first +'"]').addClass('active')
            .siblings('li').removeClass('active');

        }

      }

    };

    /**
     * Mobile
     */

    var mobile = {

      // mobile.menu()

      menu: function(){

        var $overlay = $('.mfn-overlay', $options);

        if( $menu.hasClass('show') ){

          $overlay.fadeOut(300);

        } else {

          $overlay.fadeIn(300);
          $menu.scrollTop(0);

        }

        $menu.toggleClass('show');
        $('body').toggleClass('mfn-modal-open');

      }

    };

    /**
     * Responsive
     */

    var responsive = {

      // responsive.switch()

      switch: function( $el ){

        var device = $el.data('device');

        $content.attr('data-device', device);

      },

      // responsive.reset()

      reset: function( $el ){

        $content.attr('data-device', 'desktop');

      },

      // responsive.enableFonts()

      enableFonts: function( $el ){

        var val = $el.val();

        console.log(val);

        if( val == 1 ){
          $content.addClass('auto-font-size');
        } else {
          $content.removeClass('auto-font-size');
        }

      },

      // responsive.checkFonts()

      checkFonts: function(){

        var val = $('#font-size-responsive').find('input:checked').val();

        if( val == 1 ){
          $content.addClass('auto-font-size');
        } else {
          $content.removeClass('auto-font-size');
        }

      }

    };

    /**
     * Revisions
     */

    var revisions = {

      senderRevision: false, // revision to restore after confirm

      modal: {

        // revisions.modal.restore()

        restore: function( $el ){

          revisions.senderRevision = $el;

          // open modal

          modal.open( $('.modal-confirm-revision') );

        },

        // revisions.modal.confirm()

        confirm: function(){

          // close modal | do NOT change order, change of $currentModal required

          // modal.close();

          // restore revision

          revisions.restore( revisions.senderRevision );

          revisions.senderRevision = false;

        }

      },

      // revisions.set()
      // types: revision, update, autosave, backup

      set: function( type ){

        var form = $content.closest('form').serialize();

        form += '&action=mfn_options_revision_save';
        form += '&revision-type=' + type;

        return $.ajax( ajaxurl, {
          type : "POST",
          data : form
        });

      },

      // revisions.save()

      save: function( $el ){

        var $list = $el.closest('.backup-revisions').find('ul[data-type="revision"]');

        var btnText = $el.text(),
          revision;

        $el.addClass('disabled loading');

        revision = revisions.set( 'revision' );
        revision.then(function(data) {

          if( data ) {

            $list.empty();

            $.each(JSON.parse(data), function(i, item) {
              $list.append('<li data-time="'+ i +'"><span class="revision-icon mfn-icon-clock"></span><div class="revision"><h6>'+ item +'</h6><a class="mfn-option-btn mfn-option-text mfn-option-blue mfn-btn-restore revision-restore" href="#"><span class="text">Restore</span></a></div></li>');
            });

            // enable buttons and close

            $el.removeClass('loading')
              .find('span').text('Saved, closing...');

            setTimeout(function(){
              $el.removeClass('disabled')
                .find('span').text(btnText);
              modal.close();
            },1000);

          }

        });

      },

      // revisions.restore()

      restore: function( $el ){

        var time = $el.closest('li').attr('data-time'),
          type = $el.closest('ul').attr('data-type'),
          revision;

        var $form = $content.closest('form');

        $currentModal.find('a').addClass('disabled');
        $currentModal.find('.btn-modal-confirm-revision').addClass('loading');

        // save backup revision before restore

        if( 'backup' != type ){
          revision = revisions.set( 'backup' ); // do NOT move it up
        }

        // restore revision

        $form.prepend('<input type="hidden" name="revision-type" value="'+ type +'" />');
        $form.prepend('<input type="hidden" name="revision-time" value="'+ time +'" />');

        setTimeout(function(){
          $form.submit();
        },1000);

      }

    };

    /**
     * Backup & Reset
     */

    var backup = {

      export: function(){

        $( '.backup-export-textarea', $content ).toggle();
        $( '.backup-export-input', $content ).hide();

      },

      exportLink: function(){

        $( '.backup-export-input', $content ).toggle();
        $( '.backup-export-textarea', $content ).hide();

      },

      import: function(){

        $( '.backup-import-textarea', $content ).toggle()
          .find('textarea').val('');
        $( '.backup-import-input', $content ).hide();

      },

      importLink: function(){

        $( '.backup-import-input', $content ).toggle()
          .find('.mfn-form-input').val('');
        $( '.backup-import-textarea', $content ).hide();

      },

      resetPre: function(){

        $( '.backup-reset-step.step-1', $content ).hide().next().show();

      },

      reset: function( $el ){

        if ( $( '.backup-reset-security-code', $content ).val() != 'r3s3t' ) {
          alert( 'Please insert correct security code: r3s3t' );
          return false;
        }

        if ( confirm( "Are you sure?\n\nAll custom values across your entire Theme Options panel will be reset" ) ) {
          $el.val( 'Resetting...' );
          return true;
        } else {
          return false;
        }

      }

    };

    /**
     * Modal, icon select etc
     */

    var modal = {

      // modal.open()

      open: function( $senderModal ){

        $currentModal = $senderModal;

        $currentModal.addClass('show');

        $('body').addClass('mfn-modal-open');

      },

      // modal.close()

      close: function(){

        if( ! $currentModal ){
          return false;
        }

        $currentModal.removeClass('show');

        $('body').removeClass('mfn-modal-open');

        $currentModal = false;

      }

    };

    /**
     * Performance
     * Uses 'perf' name because 'preformance' is reserved in JS
     */

    var perf = {

      // perf.enable()

      enable: function( $el ){

        if ( confirm( "Apply recommended settings?" ) ) {

          enableBeforeUnload();

          var button_text = $el.text();

          $el.addClass('loading');

          // change options

          $('#google-font-mode .form-control li:eq(1) a').trigger('click');

          $('#lazy-load .form-control li:eq(1) a').trigger('click');
          $('#srcset-limit .form-control li:eq(1) a').trigger('click');

          $('#performance-assets-disable .form-control li:eq(0).active').trigger('click');
          $('#performance-assets-disable .form-control li:eq(1).active').trigger('click');
          $('#performance-assets-disable .form-control li:eq(2):not(.active)').trigger('click');
          $('#performance-wp-disable .form-control li:not(.active)').trigger('click');

          $('#jquery-location .form-control li:eq(1) a').trigger('click');
          $('#css-location .form-control li:eq(0) a').trigger('click');
          $('#local-styles-location .form-control li:eq(1) a').trigger('click');

          $('#minify-css .form-control li:eq(1) a').trigger('click');
          $('#minify-js .form-control li:eq(1) a').trigger('click');

          $('#static-css .form-control li:eq(1) a').trigger('click');
          $('#hold-cache .form-control li:eq(0) a').trigger('click');

          // trigger ajax actions

          setTimeout(function(){

            $('#google-font-mode-regenerate .mfn-btn').attr('data-confirm',1).trigger('click');

          },100);

          // button

          setTimeout(function(){

            $el.removeClass('loading');
            $('.btn-wrapper', $el).text('Downloading Google Fonts...');

            setTimeout(function(){
              $el.addClass('loading');

              setTimeout(function(){
                $el.removeClass('loading');
                $('.btn-wrapper', $el).text('All done');

                setTimeout(function(){
                  $('.btn-wrapper', $el).text(button_text);
                },2000);

              },2000);

            },2000);

          },2000);

        } else {
          return false;
        }

      },

      // perf.disable()

      disable: function( $el ){

        if ( confirm( "Disable all performance settings?" ) ) {

          enableBeforeUnload();

          var button_text = $el.text();

          $el.addClass('loading');

          // change options

          $('#google-font-mode .form-control li:eq(0) a').trigger('click');

          $('#lazy-load .form-control li:eq(0) a').trigger('click');
          $('#srcset-limit .form-control li:eq(0) a').trigger('click');

          $('#performance-assets-disable .form-control li.active').trigger('click');
          $('#performance-wp-disable .form-control li.active').trigger('click');

          $('#jquery-location .form-control li:eq(0) a').trigger('click');
          $('#css-location .form-control li:eq(0) a').trigger('click');
          $('#local-styles-location .form-control li:eq(0) a').trigger('click');

          $('#minify-css .form-control li:eq(0) a').trigger('click');
          $('#minify-js .form-control li:eq(0) a').trigger('click');

          $('#static-css .form-control li:eq(0) a').trigger('click');
          $('#hold-cache .form-control li:eq(0) a').trigger('click');

          // button

          setTimeout(function(){

            $el.removeClass('loading');
            $('.btn-wrapper', $el).text('All done');

            setTimeout(function(){
              $('.btn-wrapper', $el).text(button_text);
            },2000);

          },1000);

        } else {
          return false;
        }

      }

    };

    /**
     * Elementor
     */

    var elementor = {

      // perf.enable()

      enable: function( $el ){

        if ( confirm( "Apply recommended settings?" ) ) {

          enableBeforeUnload();

          $el.addClass('loading');

          // change options

          $('#mobile-grid-width .mfn-form-control').val(700).trigger('blur');
          $('#mobile-site-padding .mfn-form-control').val(0).trigger('blur');
          $('#responsive-overflow-x .mfn-form-control').val('tablet');

          // button

          setTimeout(function(){
            $el.removeClass('loading');
          },1000);

        } else {
          return false;
        }

      },

      // perf.disable()

      disable: function( $el ){

        if ( confirm( "Apply default settings?" ) ) {

          enableBeforeUnload();

          $el.addClass('loading');

          // change options

          $('#mobile-grid-width .mfn-form-control').val(480).trigger('blur');
          $('#mobile-site-padding .mfn-form-control').val(33).trigger('blur');
          $('#responsive-overflow-x .mfn-form-control').val('');

          // button

          setTimeout(function(){
            $el.removeClass('loading');
          },1000);

        } else {
          return false;
        }

      }

    };

    /**
     * Custom icons
     */

    var icons = {

      // icons.add()

      add: function(){

        var number = $('.mfn-card-group[data-tab="social"] .mfn-card[data-card^="custom"]').length + 1;

        // count

        $('#custom-icon-count input').val( number - 1 );

        // card

        var $card = $('.mfn-card-group[data-tab="social"] .mfn-card[data-card="custom"]:first'),
          $clone = $card.clone();

        icons.number.card($clone, number);

        $('.mfn-card[data-card="new-icon"]').before($clone);

        // sorter

        var $sortClone = $('#social-link li[data-key="custom"]').clone();

        icons.number.sorter($sortClone, number);

        $('#social-link .social-wrapper').append($sortClone);
        $('#social-link .social-order').val(function(i,val){
          return val + ',custom-' + number;
        });

      },

      // icons.number

      number: {

        // icons.number.card()

        card: function( $el, number ){

          $el.attr('data-card', function(i,val){
            return val + '-' + number;
          });

          $el.find('.card-title').html(function(i,val){
            return val + ' ' + number;
          });

          $el.find('input').each(function(){
            $(this).attr('name', function(i,val){
              return val.replace( ']', '-'+ number +']' );
            }).val('');
          });

        },

        // icons.number.sorter()

        sorter: function( $el, number ){

          $el.attr('data-key',function(i,val){
            return val + '-' + number;
          });

          $el.find('.label').html(function(i,val){
            return val + ' ' + number;
          });

          $el.find('.label i').attr('class','fas fa-question');

        },

      },

    };

    /**
     * Cards hash navigation
     */

    var goToCard = function( el, e ){

      var locationURL = location.href.replace(/\/#.*|#.*/, ''),
        thisURL = el.href.replace(/\/#.*|#.*/, ''),
        hash = el.hash;

      if ( locationURL == thisURL ) {
        e.preventDefault();
      } else {
        return false;
      }

      menu.hash( hash );

    };

    /**
     * Shop | Custom Attributes
     * WooCommerce: Product > Attributes
     */

    var mfnattributes = {

      // mfnattributes.run()

      run: function() {
        if($('.mfn_tax_field_color').length){
          $('.mfn_tax_field_color').wpColorPicker();
        }

        if($('.mfn-tax-image').length){

          var frame,
            metaBox = $('.mfn-tax-image'),
            addImgLink = metaBox.find('.upload-custom-img'),
            delImgLink = metaBox.find( '.delete-custom-img'),
            imgContainer = metaBox.find( '.mfn-custom-img-container'),
            imgIdInput = metaBox.find( '#mfn_tax_field' ),
            placeholder = metaBox.find( '.mfn-custom-img-container img').attr('data-src');

          addImgLink.on( 'click', function( event ){
            event.preventDefault();

            if ( frame ) {
              frame.open();
              return;
            }

            frame = wp.media({
              title: 'Select or Upload Media Of Your Chosen Persuasion',
              button: {
                text: 'Use this media'
              },
              multiple: false
            });

            frame.on( 'select', function() {
              var attachment = frame.state().get('selection').first().toJSON();
              imgContainer.find('img').attr('src', attachment.url);
              imgIdInput.val( attachment.id );
              //addImgLink.addClass( 'hidden' );
              delImgLink.removeClass( 'hidden' );
            });
            frame.open();
          });

          delImgLink.on( 'click', function( event ){
            event.preventDefault();
            imgContainer.find('img').attr('src', placeholder);
            //addImgLink.removeClass( 'hidden' );
            delImgLink.addClass( 'hidden' );
            imgIdInput.val( "" );

          });
        }
      }
    };

    var select_ajax = {
      val: false,
      wrapper: false,
      init: function() {

        if( !$('.mfn-select-ajax').length ) return;

        $(document).on('keyup focus', '.mfn-select-ajax .mfn-select-ajax-input', function() {
          select_ajax.wrapper = $(this).closest('.mfn-select-ajax');
          select_ajax.val = $(this).val();

          if( $(this).val().length &&  $(this).val().length > 2 ) {
            select_ajax.ajax();
            $(document).bind('click', select_ajax.blur);
          }else{
            select_ajax.wrapper.find('.mfn-field-value').val('').trigger('change');
            select_ajax.exit();
          }
        });

        $(document).on('click', '.mfn-select-ajax-hints li:not(.disabled)', function() {
          let id = $(this).attr('data-id');
          select_ajax.wrapper = $(this).closest('.mfn-select-ajax');
          select_ajax.wrapper.find('.mfn-field-value').val(id).trigger('change');
          select_ajax.wrapper.find('.mfn-select-ajax-input').val($(this).text());
          select_ajax.exit();
        });

      },

      ajax: function() {

        if( select_ajax.wrapper.hasClass('loading') ) return;
        select_ajax.wrapper.addClass('loading');

        $.ajax({
            url: ajaxurl,
            data: {
              'mfn-builder-nonce': $('input[name="mfn-builder-nonce"]').val(),
              action: 'mfn_dynamic_get_items',
              type: 'posts',
              post_type: 'page',
              search: select_ajax.val,
            },
            type: 'POST',
            success: function(response){

              if( !select_ajax.wrapper ) return;

              select_ajax.wrapper.removeClass('loading');
              let html = '<ul class="mfn-select-ajax-hints">';

              if( _.has(response, 'page') && _.has(response.page, 'options') && response.page['options'].length ){

                response.page['options'].map( item => {
                  html += `<li data-id="${item.id}">${item.title}</li>`;
                });

              }else{
                html += '<li class="disabled">Empty list</li>';
              }

              html += '</ul>';

              select_ajax.wrapper.append(html);

              console.log(response);
            }
          });
      },

      blur: function(e) {
        var div = $('.mfn-select-ajax');
        if( !div.is(e.target) && div.has(e.target).length === 0 ) select_ajax.exit();
      },

      exit: function() {
        if( $('.mfn-select-ajax .mfn-select-ajax-hints').length ) $('.mfn-select-ajax .mfn-select-ajax-hints').remove();
        if( $('.mfn-select-ajax').hasClass('loading') ) $('.mfn-select-ajax').removeClass('loading');
        select_ajax.val = false;
        select_ajax.wrapper = false;
      }
    }

    /**
     * Search theme options
     */

    var search = {

      // search.find

      find: function($el){

        var $rows = $content.find('.mfn-form-row');

        var val = $el.val().toLowerCase();

        $('.mfn-form-row', $options).removeClass('searched-row');
        $('.mfn-card', $options).removeClass('searched-card');
        $('.mfn-card-group', $options).removeClass('searched-group');

        if( ! val ){
          $options.removeClass('search-active');
          return;
        }

        $options.addClass('search-active');

        // search fields

        $content.find('.mfn-form-row[data-search*="'+ val +'"]').each(function(){

          // skip conditionally hidden fields
          if( $(this).is('[style]') ){
            return;
          }

          $(this).addClass('searched-row')
            .closest('.mfn-card').addClass('searched-card')
            .closest('.mfn-card-group').addClass('searched-group');

        });

        // search headings

        $content.find('.card-header[data-search*="'+ val +'"]').each(function(){

          $(this).closest('.mfn-card').addClass('searched-card')
            .closest('.mfn-card-group').addClass('searched-group');

          $(this).closest('.mfn-card')
            .find('.mfn-form-row').not('[style=""]').addClass('searched-row');

          $(this).closest('.mfn-card')
            .find('.mfn-form-row[style=""]').addClass('searched-row');

        });

      },

      // search.reset

      reset: function(){

        $('#options-search').val('');

        $options.removeClass('search-active');

        $('.mfn-form-row', $options).removeClass('searched-row');
        $('.mfn-card', $options).removeClass('searched-card');
        $('.mfn-card-group', $options).removeClass('searched-group');

      },

      // search.hash

      hash: function($el){

        var $card = $el.closest('.searched-card'),
          $heading = $card.siblings('.search-card-heading').find('.subpage-title');

        var id = $card.data('card'),
          href = $heading.attr('href');

        href += '&' + id;

        $heading.attr('href',href).click();

      },

    };

    /**
     * window.onbeforeunload
     * Warn user before leaving web page with unsaved changes
     */

    var enableBeforeUnload = function() {
      window.onbeforeunload = function(e) {
        return 'The changes you made will be lost if you navigate away from this page';
      };
    };

    /**
     * Survey
     * WordPress dashboard and Betheme dashboard
     */

    var survey = function(){

      $('.mfn-survey').on( 'click', '.close', function(e){
        e.preventDefault();

        var $el = $(this);

        $.ajax({
          url: ajaxurl,
          data: {
            action: 'mfn_survey',
          },
          success: function(response){
            // console.log(response);
          },
          complete: function(){
            $el.closest('.mfn-survey').hide();
          }
        });

      });

    };

    /**
     * Portfolio slug
     */

    var portfolioSlug = function( $el ) {

      var $sibling = $el.closest('.mfn-form-row').siblings('[data-search*="slug"]').find('input');

      var val = $el.val().toLowerCase(),
        siblingVal = '';

      if( ! $sibling.length ){
        return;
      }

      siblingVal = $sibling.val().toLowerCase();

      $el.removeClass('error');
      $sibling.removeClass('error');

      if( ! val ){
        alert( 'Slug can not be empty' );
        $el.addClass('error');
      }

      if( 'portfolio' == val ){
        alert( 'Slug must be different from the Portfolio site title' );
        $el.addClass('error');
      }

      if( val == siblingVal ){
        alert( 'Both slugs must be different' );
        $el.addClass('error');
        $sibling.addClass('error');
      }

    };

    /**
     * Bind on load
     */

    var bindOnLoad = function() {

      // onbeforeunload

      setTimeout(function(){
        $options.on( 'change', '.form-control input, .form-control select, .form-control textarea', function(){
          enableBeforeUnload();
        });
      },100);

    };

    /**
     * Bind
     */

    var bind = function() {

      // click

      // main menu

      $menu.on( 'click', 'a', function(e){
        e.preventDefault();
        search.reset();
        menu.click( $(this) );
      });

      // subheader tabs

      $tabs.on( 'click', 'a', function(e){
        subheader.click( $(this) );
      });

      // link in description to another tab

      $( '.mfn-card-group, .mfn-alert', $options ).on( 'click', 'a', function(e){
        goToCard( this, e );
      });

      // mobile menu

      $( '.responsive-menu, .mfn-overlay', $options ).on( 'click', function(e){
        e.preventDefault();
        mobile.menu();
      });

      // responsive

      $( '.responsive-switcher li', $options ).on( 'click', function(e){
        responsive.switch($(this));
      });

      $( '#font-size-responsive input', $options ).on( 'change', function(){
        responsive.enableFonts($(this));
      });

      // history

      $( '.modal-revisions .mfn-save-revision').on( 'click', function(e) {
        e.preventDefault();
        revisions.save( $(this) );
      });

      $( '.modal-revisions .revision-restore').on( 'click', function(e) {
        e.preventDefault();
        revisions.modal.restore( $(this) );
      });

      $modal.on( 'click', '.btn-modal-confirm-revision', function(e) {
        e.preventDefault();
        revisions.modal.confirm();
      });

      // backup

      $( '.backup-export-show-textarea', $content ).on( 'click', function(e){
        e.preventDefault();
        backup.export();
      });

      $( '.backup-export-show-input', $content ).on( 'click', function(e){
        e.preventDefault();
        backup.exportLink();
      });

      $( '.backup-import-show-textarea', $content ).on( 'click', function(e){
        e.preventDefault();
        backup.import();
      });

      $( '.backup-import-show-input', $content ).on( 'click', function(e){
        e.preventDefault();
        backup.importLink();
      });

      $( '.backup-reset-pre-confirm', $content ).on( 'click', function(e){
        e.preventDefault();
        backup.resetPre();
      });

      $( '.backup-reset-confirm', $content ).on( 'click', function(e){
        return backup.reset( $(this) );
      });

      // portfolio slug

      $( 'input[name="betheme[portfolio-slug]"], input[name="betheme[portfolio-tax]"]', $content ).on( 'blur',function(e){
        portfolioSlug( $(this) );
      });

      // performance

      $( '.performance-apply-enable', $content ).on( 'click', function(e){
        e.preventDefault();
        perf.enable( $(this) ); // performance name is reverved
      });

      $( '.performance-apply-disable', $content ).on( 'click', function(e){
        e.preventDefault();
        perf.disable( $(this) ); // performance name is reverved
      });

      // elementor

      $( '.elementor-apply-enable', $content ).on( 'click', function(e){
        e.preventDefault();
        elementor.enable( $(this) );
      });

      $( '.elementor-apply-disable', $content ).on( 'click', function(e){
        e.preventDefault();
        elementor.disable( $(this) );
      });

      // custom icons

      $( '.custom-icon-add', $content ).on( 'click', function(e){
        e.preventDefault();
        icons.add( $(this) );
      });

      // modal close

      $modal.on( 'click', '.btn-modal-close', function(e) {
        e.preventDefault();
        modal.close();
      });

      $modal.on( 'click', function(e) {
        if ( $(e.target).hasClass('mfn-modal') ) {
          modal.close();
        }
      });

      $( 'body' ).on( 'keydown', function(event) {
        if ( 27 == event.keyCode ) {
          modal.close();
        }
      });

      // search

      $options.on( 'click', '.searched-card .card-title', function(e) {
        search.hash($(this));
      });

      $( '#options-search' ).on( 'keyup', function(e) {
        search.find($(this));
      });

      $( '.search-wrapper .search-open' ).on( 'click', function(e) {
        e.preventDefault();
        $('#options-search').val('').focus();
        $options.addClass('search-open search-opening');
      });

      $( '.search-wrapper .search-close' ).on( 'click', function(e) {
        e.preventDefault();
        $options.removeClass('search-opening');
        setTimeout(function(){
          $options.removeClass('search-open');
          search.reset();
        },200);

      });

      // external modal

      $(document).on('mfn:modal:open', function( $this, el ){
        modal.open( $(el) );
      });

      $(document).on('mfn:modal:close', function(){
        modal.close();
      });

      // disable onbeforeunload

      $('form').on('submit', function() {
        window.onbeforeunload = null;
      });

      // window.scroll

      $(window).on('scroll', function() {

        subheader.sticky();
        subheader.scrollActive();

      });

      // window resize

      $(window).on('debouncedresize', function() {

        subheader.set();
        subheader.sticky();

      });

    };

    /**
     * Conditions
     * mfnoptsinputs()
     */





    var mfnoptsinputs = {

      condition: {},
      relation: 'AND',

    start: function() {

      var prepareValues = false;

      let items = $('#mfn-options form .activeif:not(.mfn-initialized)');

      if( items.length ) {
          items.each(function() {


              mfnoptsinputs.condition = JSON.parse( $(this).attr('data-condition') );

              if( Array.isArray(mfnoptsinputs.condition) && typeof mfnoptsinputs.condition[0] == 'string' ) {
                  mfnoptsinputs.relation = mfnoptsinputs.condition[0];
                  mfnoptsinputs.condition.splice(0, 1);
              }

              if (Array.isArray(mfnoptsinputs.condition)) {

                  mfnoptsinputs.condition.map( (c) => {
                      if( !$('#mfn-options form #'+c.id+'.watchChanges').length ){
                          $(this).addClass('conditionally-hide');
                          $('#mfn-options form  #'+c.id).addClass('watchChanges');
                          prepareValues = true;
                      }
                  });

              }else{
                  if( !$('#mfn-options form #'+mfnoptsinputs.condition.id+'.watchChanges').length ){
                      $(this).addClass('conditionally-hide');
                      $('#mfn-options form  #'+mfnoptsinputs.condition.id).addClass('watchChanges');
                      prepareValues = true;
                  }
              }

              $(this).addClass('mfn-initialized');
          });
      }

      if( prepareValues ) mfnoptsinputs.startValues();
      mfnoptsinputs.watchChanges();

    },

    startValues: function() {
      $('#mfn-options form .watchChanges').each(function() {
          var id = $(this).attr('id');
          mfnoptsinputs.getField(id);
      });
    },

    watchChanges: function() {
      // segmented options is in segmented click function 
      $(document).on('change', '.watchChanges input, .watchChanges select, .watchChanges textarea', function() {
          var formrow = $(this).closest('.watchChanges');
          var id = formrow.attr('id');
          mfnoptsinputs.getField(id);
      });

       /*$('#mfn-options form .watchChanges').each(function() {
          var id = $(this).attr('id');
          if( $(this).find('.segmented-options').length ){
            $(this).on('click', '.segmented-options li', function() {
              //var val = $(this).find('input').val();
              mfnoptsinputs.getField(id);
            });
          }else{
            $(this).on('change', 'input, select, textarea', function() {
              //var val = $(this).val();
              mfnoptsinputs.getField(id);
            });
          }
        });*/
    },

    getField: function(id) {
      $('#mfn-options form .activeif-'+id).each(function() {
        var formrow = $(this);
        mfnoptsinputs.showhidefields(formrow);
      });
    },

    showhidefields: function(formrow) {

      var val = '';

      mfnoptsinputs.condition = JSON.parse( formrow.attr('data-condition') );

      if( Array.isArray(mfnoptsinputs.condition) && typeof mfnoptsinputs.condition[0] == 'string' ) {
          mfnoptsinputs.relation = mfnoptsinputs.condition[0];
          mfnoptsinputs.condition.splice(0, 1);
      }

      const regex = /\{featured_image:(\d+):badge\}/;

      if (Array.isArray(mfnoptsinputs.condition)) {

          let show = 0;
          let c_l = mfnoptsinputs.condition.length;

          mfnoptsinputs.condition.map( (c) => {

              if( $('#'+c.id+' input[type="checkbox"]').length ) {
                  val = $('#'+c.id+' input:checked').val();
              }else{
                  val = $('#'+c.id+'.mfn-form-row input, #'+c.id+'.mfn-form-row textarea, #'+c.id+'.mfn-form-row select').val();
              }

              if( c.opt == 'is' && ( (val != '' && (c.val.includes(val) || regex.test(val) )) || (val == '' && c.val == '') ) ) {
                  show++;
              }else if( c.opt == 'isnt' && ( (c.val == '' && val != '') || (val == '' && c.val != '') || val != c.val ) ) {
                  show++;
              }else{
                  if( mfnoptsinputs.relation == 'OR' ){
                      show--;
                  }else{
                      show = 0;
                      return true;
                  }
              }
          });

          if( mfnoptsinputs.relation == 'OR' && show >= 0 ){
              formrow.addClass('conditionally-show').removeClass('conditionally-hide');
          }else if( show == c_l ){
              formrow.addClass('conditionally-show').removeClass('conditionally-hide');
          }else{
              formrow.addClass('conditionally-hide').removeClass('conditionally-show');
          }

      }else{

          if( $('#'+mfnoptsinputs.condition.id+'.mfn-form-row .single-segmented-option.segmented-options').length || $('#'+mfnoptsinputs.condition.id+'.mfn-form-row .visual-options').length || $('#'+mfnoptsinputs.condition.id+'.mfn-form-row .mfn-switch').length ){
              val = $('#'+mfnoptsinputs.condition.id+'.mfn-form-row input:checked').val();
          }else{
              val = $('#'+mfnoptsinputs.condition.id+'.mfn-form-row .mfn-field-value, #'+mfnoptsinputs.condition.id+'.mfn-form-row .condition-field, #'+mfnoptsinputs.condition.id+'.mfn-form-row .field-to-object').val();
          }

          if( mfnoptsinputs.condition.opt == 'is' && ( (val != '' && (mfnoptsinputs.condition.val.includes(val) || regex.test(val) )) || (val == '' && mfnoptsinputs.condition.val == '') ) ) {
              formrow.addClass('conditionally-show').removeClass('conditionally-hide');
          }else if( mfnoptsinputs.condition.opt == 'isnt' && ( (mfnoptsinputs.condition.val == '' && val != '') || (val == '' && mfnoptsinputs.condition.val != '') || val != mfnoptsinputs.condition.val ) ) {
              formrow.addClass('conditionally-show').removeClass('conditionally-hide');
          }else{
              formrow.addClass('conditionally-hide').removeClass('conditionally-show');
          }

      }    

    },

  };





/*



    var mfnoptsinputs = {

      start: function() {
        $('#mfn-options .activeif').each(function() {
          if( !$('#mfn-options form #'+$(this).attr('data-id')+'.watchChanges').length ){
            $('#mfn-options form #'+$(this).attr('data-id')).addClass('watchChanges');
          }
          $(this).hide();
        });
        mfnoptsinputs.startValues();
        mfnoptsinputs.watchChanges();
      },

      startValues: function() {
        $('#mfn-options form .watchChanges').each(function() {
          var id = $(this).attr('id');
          var val;
          if( $(this).find('.segmented-options, .visual-options').length ){
            val = $(this).find('input:checked').val();
          }else{
            val = $(this).find('input, select, textarea').val();
          }
          mfnoptsinputs.getField(id, val);
        });
      },

      watchChanges: function() {
        $('#mfn-options form .watchChanges').each(function() {
          var id = $(this).attr('id');
          if( $(this).find('.segmented-options').length ){
            $(this).on('click', '.segmented-options li', function() {
              var val = $(this).find('input').val();
              mfnoptsinputs.getField(id, val);
            });
          }else{
            $(this).on('change', 'input, select, textarea', function() {
              var val = $(this).val();
              mfnoptsinputs.getField(id, val);
            });
          }
        });
      },

      getField: function(id, val){
        $('#mfn-options form .activeif-'+id).each(function() {
          var $formrow = $(this);
          var opt = $formrow.attr('data-opt');
          var optval = $formrow.attr('data-val');
          mfnoptsinputs.showhidefields($formrow, opt, optval, val);
        });
      },

      showhidefields: function($formrow, opt, optval, val){
        if( opt == 'is' && ( val == optval ) ){
          $formrow.show();
          if( $formrow.hasClass('mfn-card') ){ mfnoptsinputs.showhidetab( $formrow.attr('data-card'), 'list-item' ); }
        }else if( opt == 'isnt' && (val != optval ) ){
          $formrow.show();
          if( $formrow.hasClass('mfn-card') ){ mfnoptsinputs.showhidetab( $formrow.attr('data-card'), 'list-item' ); }
        }else{
          $formrow.hide();
          if( $formrow.hasClass('mfn-card') ){ mfnoptsinputs.showhidetab( $formrow.attr('data-card'), 'none' ); }
        }
      },

      showhidetab: function( tab, style ){
        // if( $('#mfn-options .subheader-tabs li[data-card-id="'+tab+'"]').length ){
          var styleid = tab+'-style';
          if( $('style#'+styleid).length ){ $('style#'+styleid).remove(); }
          $('body').append('<style id="'+styleid+'">#mfn-options .subheader-tabs li[data-card-id="'+tab+'"] { display: '+style+' }</style>');
        // }
      }

    };*/

    /**
     * Unlimited custom fonts
     * mfnNewFont()
     */

    var mfnNewFont = {

      el: $('.mfn_new_font a'),

      hiddenInput: $('#font-custom-fields input'),

      getCardsAmount: () => $('.mfn-card-group[data-tab="font-custom"]').children().length - 2,

      getDOMContent: () => $('.mfn-card[data-card="font-1"]').clone(),

      getTabContent: () => $('.subheader-tabs li[data-card-id="font-1"]').clone(),

      assignProperNumber: function(clonedEl, skip = 0) {
        //change number in new card + in hidden input
        let newCardNumber = this.getCardsAmount() - skip ;

        //HIDDEN INPUT
        this.hiddenInput.attr('value', newCardNumber - 2); //it must be always - 2, we have two first basic custom fonts fields

        //CARD
        let htmlToPrepare = clonedEl[0].outerHTML;
        htmlToPrepare = htmlToPrepare.replaceAll('font-1', `font-${newCardNumber}`);
        htmlToPrepare = htmlToPrepare.replaceAll('Font 1', `Font ${newCardNumber}`);
        htmlToPrepare = htmlToPrepare.replaceAll('font-custom', `font-custom${newCardNumber}`);

        return htmlToPrepare;
      },

      cleanInputs: function(clonedEl) {
        let inputs = $(clonedEl).find('input');
        inputs.each(function(){
          $(this).attr('value', '');
        })

        return clonedEl;
      },

      appendTab: function(){
        const preparedElement = this.assignProperNumber( this.getTabContent() , 1);

        $('.subheader-tabs li[data-card-id="create-font"]').before( preparedElement );
      },

      appendCard: function() {
        const preparedElement = this.assignProperNumber( this.cleanInputs( this.getDOMContent() ), 0 );

        $('.mfn_new_font').before( preparedElement );
      },

      watcher: function() {
        $(this.el).on('click', () => {
          this.appendCard();
          this.appendTab();
        })
      },

      init: function() {
        this.watcher();
      }
    }

    /**
     *
     * Regenerate thumbnails
     *
     */

    var regenerateThumbnails = {

      init: function() {

        $(document).on('click', '.mfn-regenerate-thumbnails', function(e) {
          e.preventDefault();
          var $button = $(this);
          if( $button.hasClass('loading') ) return;

          $button.addClass('loading').text('Processing 0%');

          regenerateThumbnails.process($button);

        });

      },

      process: function($button) {

        var $statusupdater = setInterval( function() {
          ajaxProgress('regenerate_thumbnails');
        }, 10000);

        $.ajax({
          url: ajaxurl,
          data: {
            'action': 'mfn_regenerate_thumbnails',
            'mfn-builder-nonce': $button.attr('data-nonce'),
          },
          // dataType: 'JSON',
          type: 'POST',
          statusCode: {
            524: function() {
              console.log('A timeout occurred. Trying again.');
              regenerateThumbnails.process($button);
            },
            500: function() {
              console.log('Error');
              regenerateThumbnails.process($button);
            }
          }
        }).done(function(response) {
          $('.mfn-regenerate-thumbnails').text('All done').removeClass('loading');
          clearInterval($statusupdater);
        });

      },

    }

    /**
     * Progress ajax check
     */

    function ajaxProgress(type){

      $.ajax({
        url: ajaxurl,
        data: {
          'action': 'mfn_ajax_progress',
          'type': type,
          //'mfn-setup-nonce': $('input[name="mfn-setup-nonce"]', $importer).val()
        },
        // dataType: 'JSON',
        type: 'POST',
      }).done(function(response) {
        // regenerate thumbnails
        if( type == 'regenerate_thumbnails' && $('.mfn-regenerate-thumbnails').hasClass('loading') ){
          var percent = (parseInt( response.current ) / parseInt( response.total ))*100;
          if(isNaN(percent)){
            percent = 1;
          }
          $('.mfn-regenerate-thumbnails').text( 'Processing '+ Math.round(percent)+'%' );
        }
      });

    }

    let mfn_product_cat_readmore = {
      field: false,
      val: false,
      init: function() {

        if( !$('.mfn-productcat-readmore-switcher').length ) return;

        if( $('.mfn-productcat-readmore-switcher').val().length ){
          mfn_product_cat_readmore.field = $('.mfn-productcat-readmore-switcher');
          mfn_product_cat_readmore.val = $('.mfn-productcat-readmore-switcher').val();
          mfn_product_cat_readmore.set();
        }

        $('.mfn-productcat-readmore-switcher').on('change', function() {
          mfn_product_cat_readmore.field = $(this);
          mfn_product_cat_readmore.val = $(this).val();
          mfn_product_cat_readmore.set();
        });
      },

      set: function() {
        if( mfn_product_cat_readmore.val.length ) {
          mfn_product_cat_readmore.field.closest('.mfn-wp-form-multifields').addClass('active');
          $('.mfn-desc-readmore-additional-fields').addClass('active').slideDown(300);
        }else{
          mfn_product_cat_readmore.field.closest('.mfn-wp-form-multifields').removeClass('active');
          $('.mfn-desc-readmore-additional-fields').addClass('active').slideUp(300);
        }
      }
    }

    let bebuilder_data_updater = {
      button: false,
      nonce: false,
      wrapper: false,
      init: function() {
        $(document).on('click', '.mfn_new_css_rewrite', function(e) {
          e.preventDefault();
          bebuilder_data_updater.button = $(this);
          if( bebuilder_data_updater.button.hasClass('loading') ) return;

          if ( bebuilder_data_updater.button.hasClass('confirm') && ! confirm( "Are you sure you want to run this tool?" ) ) {
            return false;
          }

          bebuilder_data_updater.nonce = bebuilder_data_updater.button.attr('data-nonce')

          bebuilder_data_updater.button.addClass('loading');
          bebuilder_data_updater.button.find('.btn-wrapper').text('Processing...');

          if( bebuilder_data_updater.button.closest('.notice').length ){
            bebuilder_data_updater.wrapper = bebuilder_data_updater.button.closest('.notice');
            bebuilder_data_updater.wrapper.removeClass('notice-warning').addClass('notice-info');
            bebuilder_data_updater.wrapper.find('.bebuilder-notice-content').html('BeBuilder: The database update has started. We will inform you when it is finished.');
          }

          bebuilder_data_updater.process();

        });
      },

      process: function() {

        $.ajax({
          url: ajaxurl,
          data: {
            'action': 'mfn_new_css_rewrite',
            'mfn-builder-nonce': bebuilder_data_updater.nonce,
          },
          // dataType: 'JSON',
          type: 'POST',
          statusCode: {
            524: function() {
              console.log('A timeout occurred. Trying again.');
              bebuilder_data_updater.process();
            },
            500: function() {
              console.log('Resuming');
              bebuilder_data_updater.process();
            },
            502: function() {
              console.log('Resuming');
              bebuilder_data_updater.process();
            },
            503: function() {
              console.log('Resuming');
              bebuilder_data_updater.process();
            },
            504: function() {
              console.log('Resuming');
              bebuilder_data_updater.process();
            }
          }
        }).done(function(response) {

          if( bebuilder_data_updater.wrapper ){
            bebuilder_data_updater.wrapper.find('.bebuilder-notice-content').html('BeBuilder: Database updated successfully! Thank you for using Betheme!');
          }else{
            bebuilder_data_updater.button.find('.btn-wrapper').text('Done');
            bebuilder_data_updater.button.removeClass('loading');
          }

        });

      },
    }

    /**
     * Ready
     * document.ready
     */

    var ready = function() {

      $(document).on('click', '#mfn-options .mfn-apply-shopify-checkout a', function(e) {
        e.preventDefault();

        $('#mfn-options #gutenberg-checkout ul li:last-child a').trigger('click');
        $('#mfn-options #gutenberg-checkout-options li').each(function () {
          const $cb = $(this);
          if (!$cb.hasClass('active')) $cb.trigger('click');
        });

        const $select = $('#mfn-options #gutenberg-checkout-header select');
        const current = $select.val(); // can be null or string

        if (!current || current === '0') {

          if ($select.find('option[value="create_checkout_header"]').length) {
            $select.val('create_checkout_header').trigger('change');
          } else {

            const $opt = $select.find('option').filter(function () {
              return ($(this).text() || '').toLowerCase().includes('checkout');
            });

            if ($opt.length) {
              $select.val($opt.first().val()).trigger('change');
            }
          }
        }



      });

      $(document).on('click', '.mfn-templates-doesnt-exists', function(e) {
        e.preventDefault();
        modal.open($('.mfn-modal.mfn-modal-templates-disabled'));
      });

      $(document).on('click', '.mfn-templates-doesnt-exists-link', function(e) {
        e.preventDefault();
        window.location.href = $(this).attr('href');
        location.reload();
      });

      let been_notified_css_changes = false;
      let now_time = new Date();

      document.addEventListener('visibilitychange', function () {
        if( document.visibilityState === 'visible' && $('#mfn-options').length ) {
          let ls_h = localStorage.getItem("be_live_css_history_time") ? localStorage.getItem("be_live_css_history_time") : false;

          if( !ls_h ) return;

          if( ls_h > now_time.getTime() && !been_notified_css_changes ) {
            been_notified_css_changes = true;
            $('.mfn-ui').append(`<div class="mfn-modal modal-confirm modal-confirm-css-changes show"><div class="mfn-modalbox mfn-form mfn-shadow-1"><div class="modalbox-header"><div class="options-group"><div class="modalbox-title-group"><span class="modalbox-icon mfn-icon-card"></span><div class="modalbox-desc"><h4 class="modalbox-title">CSS changes detected</h4></div></div></div><div class="options-group"><a class="mfn-option-btn mfn-option-blank btn-large btn-modal-close" title="Close" href="#"><span class="mfn-icon mfn-icon-close"></span></a></div></div><div class="modalbox-content"><img class="icon" alt="" src="https://muffingroup.com/dev8624/lukas/woo/wp-content/themes/betheme/muffin-options/svg/warning.svg"><h3>Custom CSS changes detected</h3><p>The current CSS version in Theme Options differs from the one saved in the database. Please review the differences before saving Theme Options, or refresh the page to load the latest version.</p><a class="mfn-btn mfn-btn-blue btn-wide btn-modal-close" href="#"><span class="btn-wrapper">Ok, got it</span></a></div></div></div>`);
          }
        }
      });

      $(document).on('click', '.modal-confirm-css-changes .btn-modal-close', function(e) {
        e.preventDefault();
        $('.modal-confirm-css-changes').remove();
      });

      survey();
      mfnattributes.run();
      regenerateThumbnails.init();
      bebuilder_data_updater.init();
      mfn_product_cat_readmore.init();

      if( ! $('#mfn-options').length ){
        return false;
      }

      menu.init();
      mfnNewFont.init();
      responsive.checkFonts();
      select_ajax.init();

      bind();

    };

    /**
     * Load
     * window.load
     */

    var load = function() {

      if( ! $('#mfn-options').length ){
        return false;
      }

      loading = false;
      $options.removeClass('loading');
      menu.hash();

      mfnoptsinputs.start();

      $(window).trigger('resize');

      bindOnLoad();

    };

    /**
     * Return
     */

    return {
      ready: ready,
      load: load
    };

  })(jQuery);

  /**
   * $(document).ready
   */

  $(function() {

    MfnOptions.ready();

    /* visual builder */

    wp.domReady(function() {
    	setTimeout(function() {
    		if( $('.mfn-live-edit-page-button').length ){
    			$('.mfn-live-edit-page-button').clone().appendTo('.admin-ui-navigable-region .editor-header .editor-header__toolbar');
    		}
    	}, 500);
    });

    /**
     * Template choose on create
     * templateCreate()
     */

    var templateCreate = {
      init: function() {
        $('.mfn-modal.modal-template-type .input-template-type-name').focus();
        $('.mfn-modal.modal-template-type .btn-save-template-type').on('click', function(e) {
          e.preventDefault();
          var $btn = $(this),
              $tmpl = $('.mfn-modal.modal-template-type .select-template-type'),
              $name = $('.mfn-modal.modal-template-type .input-template-type-name'),
              slug = $(this).attr('data-builder'),
              id = $('input#post_ID').val();

          $tmpl.removeClass('error');
          $name.removeClass('error');

          if(!$btn.hasClass('loading') && $tmpl.val().length && $name.val().length ){
            $btn.addClass('loading');
            $.ajax({
              url: ajaxurl,
              data: {
                'mfn-builder-nonce': $('input[name="mfn-builder-nonce"]').val(),
                action: 'mfncreatetemplate',
                tmpl: $tmpl.val(),
                name: $name.val(),
                id: id,
              },
              type: 'POST',
              success: function(response){
                window.history.pushState("data", "Templates", 'edit.php?post_type=template');
                window.location.href = 'post.php?post='+id+'&action='+slug+'-live-builder';
              }
            });
          }else{
            if( !$tmpl.val().length ) $tmpl.addClass('error');
            if( !$name.val().length ) $name.addClass('error');
          }

        });
      }
    }

    if( $('.mfn-modal.modal-template-type .btn-save-template-type').length ) templateCreate.init();

    if( $('body').hasClass('post-new-php') ){

      $('.mfn-switch-live-editor').on('click', function(e) {
        e.preventDefault();

        var $btn = $(this);

        var tmpl_type = '';

        if( $('.mfn-ui .mfn-form .mfn_template_type .mfn-field-value').length ){
          tmpl_type = $('.mfn-ui .mfn-form .mfn_template_type .mfn-field-value').val();
        }

        if(!$btn.hasClass('loading')){
          $btn.addClass('loading');
          $.ajax({
            url: ajaxurl,
            data: {
              'mfn-builder-nonce': $('input[name="mfn-builder-nonce"]').val(),
              action: 'mfnvbsavedraft',
              posttype: $('input#post_type').val(),
              id: $('input#post_ID').val(),
              tmpl: tmpl_type
            },
            type: 'POST',
            success: function(response){
              window.history.pushState("data", "Edit Page", 'post.php?post='+$('input#post_ID').val()+'&action=edit');
              window.location.href = $btn.attr('href');
            }
          });
        }

      });

    }

    /* END visual builder */

  });

  /**
   * $(window).load
   */

  $(window).on('load', function(){
    MfnOptions.load();
  });

})(jQuery);
