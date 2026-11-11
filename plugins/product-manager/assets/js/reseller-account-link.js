(function () {
 'use strict';
 var selector = 'a.myaccount_button, a.toggle-login-modal';
 function updateLinks() {
  document.querySelectorAll(selector).forEach(function (link) {
   link.href = window.airsoftResellerPortalUrl;
  });
 }
 updateLinks();
 // Capture clicks before the theme opens its account modal, including mobile headers.
 document.addEventListener('click', function (event) {
  var link = event.target.closest(selector);
  if (!link) { return; }
  link.href = window.airsoftResellerPortalUrl;
  event.stopImmediatePropagation();
 }, true);
}());
