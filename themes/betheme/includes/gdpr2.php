<div id="mfn-consent-mode" class="mfn-cookies" data-tab="consent" data-expires="<?php echo mfn_opts_get('gdpr2-settings-cookie_expire',365); ?>" data-animation="<?php echo mfn_opts_get('gdpr2-settings-animation'); ?>">
  <div class="mfn-cookies-popup">
    <div class="mfn-cookies-wrapper">
      <ul class="cookies-tab-nav">
        <li class="tab is-active" data-id="consent"><a href="#"><?php echo mfn_opts_get('gdpr2-content-title','Consent'); ?></a></li>
        <li class="tab" data-id="details"><a href="#"><?php echo mfn_opts_get('gdpr2-details-title','Details'); ?></a></li>
        <li class="tab" data-id="about"><a href="#"><?php echo mfn_opts_get('gdpr2-about-title','About Cookies'); ?></a></li>
      </ul>
      <div data-id="consent" class="cookies-tab-content"><?php echo mfn_opts_get('gdpr2-consent-content'); ?></div>
      <div data-id="details" class="cookies-tab-content">
        <form class="cookie-consent">
          <div class="cookie-type">
            <header>
              <strong><?php echo mfn_opts_get('gdpr2-necessary-title','Necessary'); ?></strong>
              <div class="mfn-switch">
                <input class="mfn-switch-input" id="cookies_neccessary" type="checkbox" checked="" disabled="disabled">
                <label class="mfn-switch-label" for="cookies_neccessary"></label>
              </div>
            </header>
            <?php echo mfn_opts_get('gdpr2-necessary-consent'); ?>
          </div>
          <div class="cookie-type">
            <header>
              <strong><?php echo mfn_opts_get('gdpr2-analytics-title','Analytics & Performance'); ?></strong>
              <div class="mfn-switch">
                <input class="mfn-switch-input" id="cookies_analytics" type="checkbox">
                <label class="mfn-switch-label" for="cookies_analytics"></label>
              </div>
            </header>
            <?php echo mfn_opts_get('gdpr2-analytics-consent'); ?>
          </div>
          <div class="cookie-type">
            <header>
              <strong><?php echo mfn_opts_get('gdpr2-marketing-title','Marketing'); ?></strong>
              <div class="mfn-switch">
                <input class="mfn-switch-input" id="cookies_marketing" type="checkbox">
                <label class="mfn-switch-label" for="cookies_marketing"></label>
              </div>
            </header>
            <?php echo mfn_opts_get('gdpr2-marketing-consent'); ?>
          </div>
        </form>
      </div>
      <div data-id="about" class="cookies-tab-content">
        <?php echo mfn_opts_get('gdpr2-about-content'); ?>
      </div>
    </div>
    <footer class="mfn-cookies-footer">
      <a id="consent_deny" class="button button-outlined white" href="#"><?php echo mfn_opts_get('gdpr2-button-deny','Deny'); ?></a>
      <a id="consent_customize" class="button button-outlined white" href="#"><span><?php echo mfn_opts_get('gdpr2-button-customize','Customize'); ?></span></a>
      <a id="consent_selected" class="button button-outlined white" href="#"><?php echo mfn_opts_get('gdpr2-button-allow-selected','Allow selected'); ?></a>
      <a id="consent_allow" class="button secondary button_theme" href="#"><?php echo mfn_opts_get('gdpr2-button-allow-all','Allow all'); ?></a>
    </footer>
  </div>
</div>

<?php if( mfn_opts_get('gdpr2-button') ): ?>
<div class="mfn-cookies-reopen">

  <?php if( mfn_opts_get('gdpr2-reopen-icon') ): ?>
    <?php echo '<i class="'. esc_attr(mfn_opts_get('gdpr2-reopen-icon')) .'"></i>'; ?>
  <?php else: ?>
    <svg width="26" height="26" viewBox="0 0 26 26" version="1.1" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
      <path class="path" d="M13,2.864L21.8,4.793L21.8,13.6C21.8,16.084 20.356,18.596 17.881,20.081L13,23.057L8.101,20.069L8.083,20.058C5.614,18.453 4.2,16.068 4.2,13.6L4.2,4.793L13,2.864ZM5.7,6L5.7,13.6C5.7,15.6 6.9,17.5 8.9,18.8L13,21.3L17.1,18.8C19.1,17.6 20.3,15.6 20.3,13.6L20.3,6L13,4.4L5.7,6Z"/>
      <path class="path" d="M16.5,11.6L13.1,11.6L13.1,13L15.1,13C15.1,13.3 14.8,13.8 14.4,14.2C14.1,14.4 13.7,14.6 13.1,14.6C12.1,14.6 11.3,14 11,13.1C10.9,12.9 10.9,12.6 10.9,12.4C10.9,12.1 10.9,11.9 11,11.7C11.3,10.8 12.1,10.2 13.1,10.2C13.8,10.2 14.2,10.5 14.5,10.7L15.5,9.7C14.9,9.1 14,8.8 13.1,8.8C11.7,8.8 10.4,9.6 9.8,10.8C9.6,11.3 9.4,11.9 9.4,12.4C9.4,12.9 9.5,13.5 9.8,14C10.4,15.2 11.6,16 13.1,16C14.1,16 14.9,15.7 15.5,15.1C16.2,14.5 16.6,13.5 16.6,12.4C16.6,12 16.6,11.8 16.5,11.6Z" style="fill-rule:nonzero;"/>
    </svg>
  <?php endif; ?>

</div>
<?php endif; ?>
