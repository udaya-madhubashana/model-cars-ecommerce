<?php
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/auth.php';
$message = 'If the email address is registered, password reset instructions will be available for this account.';
$localResetUrl = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pdo = getDbConnection();
        if ($pdo) {
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS password_reset_tokens (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, token_hash CHAR(64) NOT NULL UNIQUE, expires_at DATETIME NOT NULL, used_at DATETIME NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX (user_id), CONSTRAINT fk_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $hash = hash('sha256', $token);
                    $ins = $pdo->prepare('INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))');
                    $ins->execute([(int)$user['id'], $hash]);
                    // Email transport is not configured in this local XAMPP project. Expose the link only on localhost for testing.
                    $host = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
                    if (in_array(explode(':', $host)[0], ['localhost', '127.0.0.1'], true)) {
                        $localResetUrl = 'reset-password.php?token=' . urlencode($token);
                    }
                }
            } catch (Throwable $e) { /* Keep the same public message to avoid account enumeration. */ }
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Forgot Password - ModelCars Pro</title><link rel="stylesheet" href="css/style.css"></head><body>
<header><nav><a class="logo" href="index.php">🏎 ModelCars Pro</a><a href="login.php">Back to Login</a></nav></header>
<main class="main-content auth-section"><div class="container auth-container"><div class="auth-card"><h1>Reset your password</h1><p>Enter your registered email address. Reset links expire after 30 minutes.</p>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?><div class="alert alert-info"><?php echo sanitize($message); ?></div><?php endif; ?>
<form method="post" class="auth-form"><div class="form-group"><label for="email">Email address *</label><input id="email" name="email" type="email" required autocomplete="email"></div><button class="btn btn-primary btn-block" type="submit">Request reset link</button></form>
<?php if ($localResetUrl): ?><div class="alert alert-warning" style="margin-top:18px"><strong>Local testing link:</strong><br><a href="<?php echo sanitize($localResetUrl); ?>"><?php echo sanitize($localResetUrl); ?></a><p>Email delivery is not configured in this XAMPP project; this link is shown only on localhost.</p></div><?php endif; ?>
</div></div></main></body></html>
