/**
 * Client-side glyph snippets for dynamic DOM updates.
 */
(function (global) {
  const paths = {
    manage: '<path d="M12 8v8M8 12h8"/><circle cx="12" cy="12" r="8"/>',
    close: '<circle cx="12" cy="12" r="8"/><path d="m9 9 6 6M15 9l-6 6"/>',
    check: '<path d="m6 12 4 4 8-8"/>',
    'x-mark': '<path d="m8 8 8 8M16 8l-8 8"/>',
    alert: '<path d="M12 4 3 19h18L12 4z"/><path d="M12 10v4M12 17h.01"/>',
    eye: '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="2.5"/>',
  };

  function renderGlyph(key, className) {
    const body = paths[key] || paths.close;
    const cls = 'erph-glyph erph-glyph-' + key + (className ? ' ' + className : '');
    return '<svg class="' + cls + '" viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + body + '</svg>';
  }

  global.ERPHGlyph = { render: renderGlyph };
})(window);
