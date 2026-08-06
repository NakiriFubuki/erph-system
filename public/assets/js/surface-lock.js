// Locks document surface to light palette only
(function () {
  'use strict';
  var SURFACE = 'light';
  document.documentElement.setAttribute('data-theme', SURFACE);
  try {
    localStorage.setItem('theme', SURFACE);
    sessionStorage.setItem('theme', SURFACE);
  } catch (ignored) {}
})();
