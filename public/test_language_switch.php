<?php
// test_language_switch.php - Test language switching
require_once __DIR__ . '/inc/session_config.php';
require_once __DIR__ . '/inc/language_config.php';

// Show current language status
echo "<h1>Language switch test page</h1>";
echo "<p>Current language: " . ($_SESSION['lang'] ?? 'Not set') . "</p>";
echo "<p>Current language code: " . t('common.language_code') . "</p>";
echo "<p>Welcome message: " . t('dashboard.welcome_back') . "</p>";
echo "<p>Admin description: " . t('dashboard.admin_description') . "</p>";

// Show language switch buttons
?>
<div style="margin: 20px 0;">
    <h3>Language switch buttons</h3>
    <button onclick="changeLanguage('zh')">Switch to Chinese</button>
    <button onclick="changeLanguage('en')">Switch to English</button>
</div>

<div style="margin: 20px 0;">
    <h3>Current session info</h3>
    <pre><?php print_r($_SESSION); ?></pre>
</div>

<script>
function changeLanguage(lang) {
    console.log('Switch language to:', lang);
    
    fetch('change_language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'lang=' + lang
    })
    .then(response => response.json())
    .then(data => {
        console.log('Language switch response:', data);
        if (data.success) {
            console.log('Language switch succeeded; refreshing page');
            location.reload();
        } else {
            console.error('Language switch failed:', data.error);
        }
    })
    .catch(error => {
        console.error('Language switch request failed:', error);
    });
}
</script>

