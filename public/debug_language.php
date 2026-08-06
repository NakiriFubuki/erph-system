<?php
// debug_language.php - Debug language switching
require_once __DIR__ . '/inc/session_config.php';
require_once __DIR__ . '/inc/language_config.php';

// Show debug info
echo "<h1>Language switch debug page</h1>";
echo "<h2>Session info</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Data:\n";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Language config info</h2>";
echo "<p>Current language: " . ($_SESSION['lang'] ?? 'Not set') . "</p>";
echo "<p>Language code: " . t('common.language_code') . "</p>";

echo "<h2>Translation test</h2>";
echo "<p>Welcome message: " . t('dashboard.welcome_back') . "</p>";
echo "<p>Admin description: " . t('dashboard.admin_description') . "</p>";

echo "<h2>Language switch test</h2>";
?>
<div style="margin: 20px 0;">
    <button onclick="testLanguageSwitch('zh')">Test switch to Chinese</button>
    <button onclick="testLanguageSwitch('en')">Test Switch to English</button>
</div>

<div id="debug-output" style="margin: 20px 0; padding: 10px; background: #f0f0f0; border: 1px solid #ccc;">
    <h3>Debug output</h3>
    <div id="debug-content"></div>
</div>

<script>
function testLanguageSwitch(lang) {
    const debugContent = document.getElementById('debug-content');
    debugContent.innerHTML = '<p>Starting language switch to: ' + lang + '</p>';
    
    // Record current time
    const startTime = new Date();
    debugContent.innerHTML += '<p>Start time: ' + startTime.toLocaleString() + '</p>';
    
    // Send request
    fetch('change_language.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'lang=' + lang
    })
    .then(response => {
        debugContent.innerHTML += '<p>Response received, status: ' + response.status + '</p>';
        return response.json();
    })
    .then(data => {
        debugContent.innerHTML += '<p>Response data: ' + JSON.stringify(data, null, 2) + '</p>';
        
        if (data.success) {
            debugContent.innerHTML += '<p style="color: green;">Language switch succeeded!</p>';
            debugContent.innerHTML += '<p>Refreshing page in 3 seconds...</p>';
            setTimeout(() => {
                location.reload();
            }, 3000);
        } else {
            debugContent.innerHTML += '<p style="color: red;">Language switch failed: ' + data.error + '</p>';
        }
    })
    .catch(error => {
        debugContent.innerHTML += '<p style="color: red;">Request failed: ' + error.message + '</p>';
        debugContent.innerHTML += '<p>Error details: ' + error + '</p>';
    });
    
    // Record end time
    const endTime = new Date();
    debugContent.innerHTML += '<p>Request sent at: ' + endTime.toLocaleString() + '</p>';
}
</script>

