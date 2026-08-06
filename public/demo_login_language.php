<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page language switch demo</title>
    <style>
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            background: linear-gradient(135deg, #fff 10%, #ffecec 60%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .demo-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        .demo-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        .demo-title {
            color: #2563eb;
            margin-bottom: 15px;
        }
        .demo-link {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px;
            transition: all 0.3s ease;
        }
        .demo-link:hover {
            background: #3b82f6;
            transform: translateY(-2px);
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .feature-list li:before {
            content: "✅ ";
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="demo-container">
        <h1 class="demo-title">Login page language switching demo</h1>
        
        <div class="demo-section">
            <h2>Feature overview</h2>
            <p>The login page now supports Chinese/English switching so users can choose the UI language before signing in.</p>
            
            <h3>Key features:</h3>
            <ul class="feature-list">
                <li>Supports Chinese and English</li>
                <li>Language switch buttons are at the top of the login form</li>
                <li>Language choice is saved in the session</li>
                <li>Language setting persists after refresh</li>
                <li>All text content displays in the selected language</li>
            </ul>
        </div>
        
        <div class="demo-section">
            <h2>Test links</h2>
            <p>Use the links below to test language switching:</p>
            
            <a href="login_roles.php" class="demo-link">Open the login page</a>
            <a href="test_login_language.php" class="demo-link">Test language switch</a>
        </div>
        
        <div class="demo-section">
            <h2>How to use</h2>
            <ol>
                <li>Open the login page</li>
                <li>Find the language switch buttons at the top of the page</li>
                <li>Click the "Chinese" or "English" button</li>
                <li>The page refreshes and shows the selected language</li>
                <li>All form labels and button text update</li>
            </ol>
        </div>
        
        <div class="demo-section">
            <h2>Technical implementation</h2>
            <ul class="feature-list">
                <li>Uses PHP sessions for language settings</li>
                <li>Passes language choice via GET parameter</li>
                <li>Translation files live in the translations directory</li>
                <li>Responsive design with mobile support</li>
                <li>Styles match the login page theme</li>
            </ul>
        </div>
    </div>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

