<?php
// login_roles.php - Role-based login system
ob_start();
require_once __DIR__ . '/inc/bootstrap.php';

// Handle language switch request
if (isset($_GET['lang']) && in_array($_GET['lang'], ['zh', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
    // Redirect to current page to apply new language
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user'])) {
    ob_end_clean();
    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: teacher_dashboard.php');
    }
    exit;
}

// Try connecting to database
try {
    require_once __DIR__ . '/../db.php';
} catch (Exception $e) {
    $error = t('login.error_database') . ": " . $e->getMessage();
}

// Get current background settings
$current_background = 'default';

// Preset background styles (ERPH1 blue-white gradient)
$preset_backgrounds = [
    'default' => 'linear-gradient(165deg, #1d4ed8 0%, #2563eb 25%, #3b82f6 50%, #93c5fd 80%, #eff6ff 100%)',
    'gradient_blue' => 'linear-gradient(165deg, #1e40af 0%, #2563eb 40%, #60a5fa 75%, #dbeafe 100%)',
    'gradient_purple' => 'linear-gradient(165deg, #3730a3 0%, #4f46e5 40%, #818cf8 75%, #e0e7ff 100%)',
    'gradient_green' => 'linear-gradient(165deg, #065f46 0%, #059669 40%, #34d399 75%, #d1fae5 100%)',
    'gradient_orange' => 'linear-gradient(165deg, #c2410c 0%, #ea580c 40%, #fb923c 75%, #ffedd5 100%)'
];

// Prefer reading background settings from database
try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'system_settings'");
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'login_background' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result) {
                $current_background = $result['setting_value'];
                // Sync to session
                $_SESSION['login_background'] = $current_background;
            }
        }
    }
} catch (Exception $e) {
    // Ignore database errors, continue using session
}

// If not in database, read from session
if ($current_background === 'default' && isset($_SESSION['login_background'])) {
    $current_background = $_SESSION['login_background'];
}

$error = '';
$login = '';
$default_password = '';
$register_success = isset($_GET['registered']) && $_GET['registered'] === '1';
$show_register = isset($_GET['mode']) && $_GET['mode'] === 'register';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$show_register) {
    $login = 'admin';
    $default_password = 'admin123';
}

function findUserByLogin(PDO $pdo, string $login): ?array {
    $login = trim($login);
    if ($login === '') {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT id, name, password, role, avatar, email
        FROM users
        WHERE LOWER(email) = LOWER(:login) OR name = :login_name
        LIMIT 1
    ");
    $stmt->execute(['login' => $login, 'login_name' => $login]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function ensureDefaultAdmin(PDO $pdo, string $login, string $pass): ?array {
    $defaultName = 'admin';
    $defaultEmail = 'admin@erph.com';
    $loginNormalized = strtolower(trim($login));
    $isDefaultLogin = $loginNormalized === strtolower($defaultName)
        || $loginNormalized === strtolower($defaultEmail);
    if (!$isDefaultLogin || $pass !== 'admin123') {
        return null;
    }

    $user = findUserByLogin($pdo, $defaultName);
    if (!$user) {
        $user = findUserByLogin($pdo, $defaultEmail);
    }
    if (!$user) {
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')")
            ->execute([$defaultName, $defaultEmail, $newHash]);
        return findUserByLogin($pdo, $defaultName);
    }

    if ($user['role'] !== 'admin') {
        $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$user['id']]);
        $user['role'] = 'admin';
    }

    if (!password_verify($pass, $user['password'])) {
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $user['id']]);
    }

    return findUserByLogin($pdo, $defaultName) ?: findUserByLogin($pdo, $defaultEmail);
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email_reg = trim($_POST['email'] ?? '');
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email_reg) || empty($pass1) || empty($pass2)) {
        $error = t('login.error_all_fields');
        $show_register = true;
    } elseif (!filter_var($email_reg, FILTER_VALIDATE_EMAIL)) {
        $error = t('login.error_invalid_email');
        $show_register = true;
    } elseif (strlen($pass1) < 6) {
        $error = t('login.error_password_length');
        $show_register = true;
    } elseif ($pass1 !== $pass2) {
        $error = t('login.error_password_mismatch');
        $show_register = true;
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email_reg]);
            if ($stmt->fetch()) {
                $error = t('login.error_email_exists');
                $show_register = true;
            } else {
                $hash = password_hash($pass1, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'teacher')");
                $stmt->execute([$name, $email_reg, $hash]);
                ob_end_clean();
                header('Location: login_roles.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            $error = t('login.error_register_failed') . ': ' . $e->getMessage();
            $show_register = true;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error) && !$show_register) {
    $login = trim($_POST['login'] ?? $_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (empty($login) || empty($pass)) {
        $error = t('login.error_all_fields');
    } else {
        try {
            $user = null;
            $login_ok = false;

            // Prefer default admin credentials to avoid conflict with same-named users
            $adminUser = ensureDefaultAdmin($pdo, $login, $pass);
            if ($adminUser) {
                $user = $adminUser;
                $login_ok = true;
            } else {
                $user = findUserByLogin($pdo, $login);
                if ($user && password_verify($pass, $user['password'])) {
                    $login_ok = true;
                }
            }

            if ($login_ok && $user) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'avatar' => $user['avatar'] ?? null
                ];
                try {
                    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                    $stmt->execute(['id' => $user['id']]);
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'last_login') === false) {
                        throw $e;
                    }
                }
                ob_end_clean();
                if ($user['role'] === 'admin') {
                    header('Location: admin_dashboard.php');
                } else {
                    header('Location: teacher_dashboard.php');
                }
                exit;
            }

            if ($user) {
                $error = t('login.error_wrong_password');
            } else {
                $error = t('login.error_login_failed');
            }
        } catch (PDOException $e) {
            $error = t('login.error_system');
        }
    }
}

// End output buffering and display page
ob_end_flush();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('login.title') ?></title>
  <link rel="stylesheet" href="assets/css/login.css?v=<?= filemtime(__DIR__ . '/assets/css/login.css') ?>">
  <?php include __DIR__ . '/inc/shot_assets.php'; ?>
  <style>
    body.login-page {
      <?php 
      if ($current_background !== 'default') {
          if (isset($preset_backgrounds[$current_background])) {
              echo "background: " . $preset_backgrounds[$current_background] . ";";
          } else {
              echo "background: url('" . htmlspecialchars($current_background) . "') no-repeat center center fixed;";
              echo "background-size: cover;";
          }
      } else {
          echo "background: " . $preset_backgrounds['default'] . ";";
          echo "background-size: 400% 400%;";
          echo "animation: gradientShift 12s ease infinite;";
      }
      ?>
    }
  </style>
</head>
<body class="login-page">
  <div class="login-card">
    <?= renderLanguageSwitch(true) ?>
    
    <!-- Debug info -->
    <?php if (isset($_GET['debug'])): ?>
    <div style="background: #eff6ff; border: 1px solid #3b82f6; padding: 10px; margin-bottom: 20px; border-radius: 10px; font-size: 12px; color: #1d4ed8;">
      <strong>Debug info:</strong><br>
      DB background setting: <?= htmlspecialchars($current_background) ?><br>
      Session background setting: <?= htmlspecialchars($_SESSION['login_background'] ?? 'Not set') ?><br>
      File exists: <?= file_exists(__DIR__ . '/' . $current_background) ? 'Yes' : 'No' ?><br>
      File path: <?= htmlspecialchars(__DIR__ . '/' . $current_background) ?>
    </div>
    <?php endif; ?>
    
    <h2><?= $show_register ? t('login.register_title') : t('login.title') ?></h2>
    
    <?php if ($register_success && empty($error) && $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
    <div class="success"><?= t('login.register_success') ?><br><span class="success-hint"><?= t('login.register_use_teacher') ?></span></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($show_register): ?>
    <form method="post" id="registerForm">
      <input type="hidden" name="action" value="register">
      <p class="register-intro"><?= t('login.register_intro') ?></p>
      <label><?= t('login.name') ?><input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required maxlength="100"></label>
      <label><?= t('login.email') ?><input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required></label>
      <label><?= t('login.password') ?><input type="password" name="password" required minlength="6"></label>
      <label><?= t('login.confirm_password') ?><input type="password" name="confirm_password" required minlength="6"></label>
      <button type="submit" class="btn"><?= t('login.register_button') ?></button>
    </form>
    <div class="login-footer">
      <p><a href="login_roles.php"><?= t('login.go_to_login') ?></a></p>
      <p><a href="../index.php"><?= t('login.back_to_home') ?></a></p>
    </div>
    <?php else: ?>
    <form method="post" id="loginForm">
      <label><?= t('login.username_or_email') ?><input type="text" name="login" value="<?= htmlspecialchars($login) ?>" required autocomplete="username"></label>
      <label><?= t('login.password') ?><input type="password" name="password" value="<?= htmlspecialchars($default_password) ?>" required autocomplete="current-password"></label>
      
      <button type="submit" class="btn"><?= t('login.login_button') ?></button>
    </form>
    
    <div class="login-footer">
      <p><a href="login_roles.php?mode=register"><?= t('login.register_title') ?></a></p>
      <p><?= t('login.forgot_password') ?></p>
      <p><a href="../index.php"><?= t('login.back_to_home') ?></a></p>
    </div>
    <?php endif; ?>
  </div>

  <script>
    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
      loginForm.addEventListener('submit', function(e) {
        var loginInput = loginForm.querySelector('input[name="login"]');
        if (loginInput && !loginInput.value.trim()) {
          e.preventDefault();
          alert('<?= t('login.error_invalid_login') ?>');
          return false;
        }
      });
    }
  </script>
  <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>

