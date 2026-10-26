<?php
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/auth.php';

if (!isLoggedIn()) {
    redirect('../login.php?redirect=' . rawurlencode('admin/index.php'));
}

$user = getCurrentUser();
if (!$user || ($user['role'] ?? 'customer') !== 'admin') {
    setFlashMessage('error', 'Admin access is restricted to administrator accounts.');
    redirect('../index.php');
}

$pdo = getDbConnection();
if (!$pdo) {
    die('Database connection is unavailable. Start MySQL in XAMPP and try again.');
}

// Week 8 migration helper: add a dedicated active flag if the existing Week 7
// database does not have it yet. This preserves old data and avoids requiring
// a full database re-import.
try {
    $check = $pdo->query("SHOW COLUMNS FROM products LIKE 'is_active'");
    if (!$check->fetch()) {
        $pdo->exec("ALTER TABLE products ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER is_featured");
    }
} catch (Throwable $e) {
    // If the column cannot be created, admin pages will still work except for
    // deactivate/activate operations, which will show a clear database error.
}

if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

function adminCsrfToken() {
    return $_SESSION['admin_csrf'];
}

function verifyAdminCsrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['admin_csrf'] ?? '', $token)) {
        http_response_code(403);
        exit('Invalid security token. Please go back and try again.');
    }
}

function adminFlash() {
    return getFlashMessage();
}

function adminRedirect($page = 'index.php') {
    redirect($page);
}
