# Theme Sync Usage Guide

## Overview
Theme sync keeps dark/light mode consistent across pages and syncs theme state without a refresh.

## File structure
- `theme-sync.js` - Main theme sync script
- Include this script on pages that need theme sync

## How to use

### 1. Include the script
Add to the HTML `<head>`:
```html
<script src="assets/js/theme-sync.js"></script>
```

### 2. Add a theme toggle button
Add a theme toggle button on the page:
```html
<button class="theme-toggle-btn" onclick="toggleTheme()" title="Toggle theme">
  🌙
</button>
```

### 3. Set the HTML root element
Ensure the HTML root has a `data-theme` attribute:
```html
<html lang="zh" data-theme="light">
```

## Features

### Automatic theme initialization
- Detects and applies the saved theme on page load
- Priority: localStorage > session > default (light)

### Cross-page sync
- Uses localStorage to store theme state
- Supports CustomEvent for in-page communication
- Supports BroadcastChannel for cross-tab communication

### Server sync
- Automatically sends theme changes to `change_theme.php`
- Supports error handling and logging

## API

### Global functions
- `toggleTheme()` - Toggle theme (dark ↔ light)
- `ThemeManager.initializeTheme()` - Manually initialize theme
- `ThemeManager.syncThemeToOtherPages(theme)` - Sync theme to other pages

### Event listeners
Pages can listen for theme change events:
```javascript
window.addEventListener('themeChanged', function(event) {
  const newTheme = event.detail.theme;
  console.log('Theme changed to:', newTheme);
  // Run the corresponding theme switch logic
});
```

## Compatibility
- Modern browsers: full feature support
- Older browsers: falls back to localStorage
- No JavaScript: falls back to server-side theme setting

## Troubleshooting

### Theme not syncing
1. Verify `theme-sync.js` is included correctly
2. Confirm localStorage is available
3. Check the console for errors

### Styles not applying
1. Confirm dark mode CSS rules exist
2. Check that `data-theme` is set correctly
3. Verify CSS selector specificity

## Example page
See `admin_dashboard.php` for a full example of theme sync usage.

