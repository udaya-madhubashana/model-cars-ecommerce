<?php
require_once __DIR__ . '/php/config.php';
$message = '';
$success = false;
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) $message = 'This reset link is invalid. Please request a new one.';
    elseif (strlen($password) < 8) $message = 'Password must be at least 8 characters long.';
    elseif ($password !== $confirm) $message = 'The passwords do not match.';
    else {
        $pdo = getDbConnection();
        if (!$pdo) $message = 'Database connection unavailable. Start MySQL and try again.';
        else {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('SELECT id, user_id FROM password_reset_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW() LIMIT 1 FOR UPDATE');
                $stmt->execute([hash('sha256', $token)]); $row = $stmt->fetch();
                if (!$row) { $pdo->rollBack(); $message = 'This reset link is invalid or expired. Please request a new one.'; }
                else {
                    $u = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $u->execute([password_hash($password, PASSWORD_DEFAULT), (int)$row['user_id']]);
                    $mark = $pdo->prepare('UPDATE password_reset_tokens SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL');
                    $mark->execute([(int)$row['user_id']]);
                    $pdo->commit(); $success = true; $message = 'Your password has been reset. You can now log in with your new password.';
                }
            } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); $message = 'Could not reset the password. Please request a new link.'; }
        }
    }
}
$tokenValid = false;
if (!$success && preg_match('/^[a-f0-9]{64}$/', $token)) { $pdo=getDbConnection(); if($pdo){try{$s=$pdo->prepare('SELECT id FROM password_reset_tokens WHERE token_hash=? AND used_at IS NULL AND expires_at>NOW() LIMIT 1');$s->execute([hash('sha256',$token)]);$tokenValid=(bool)$s->fetch();}catch(Throwable $e){}} }
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Choose New Password - ModelCars Pro</title><link rel="stylesheet" href="css/style.css"></head><body><header><nav><a class="logo" href="index.php">🏎 ModelCars Pro</a><a href="login.php">Back to Login</a></nav></header><main class="main-content auth-section"><div class="container auth-container"><div class="auth-card"><h1>Choose a new password</h1>
<?php if ($message): ?><div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>"><?php echo sanitize($message); ?></div><?php endif; ?>
<?php if ($tokenValid): ?><form method="post" class="auth-form"><input type="hidden" name="token" value="<?php echo sanitize($token); ?>"><div class="form-group"><label for="password">New password *</label><input id="password" type="password" name="password" minlength="8" required autocomplete="new-password"></div><div class="form-group"><label for="confirm_password">Confirm new password *</label><input id="confirm_password" type="password" name="confirm_password" minlength="8" required autocomplete="new-password"></div><button class="btn btn-primary btn-block" type="submit">Update password</button></form><?php elseif (!$success): ?><p>Please request a new reset link.</p><a class="btn btn-primary" href="forgot-password.php">Request new link</a><?php else: ?><a class="btn btn-primary" href="login.php">Go to Login</a><?php endif; ?></div></div></main></body></html>
