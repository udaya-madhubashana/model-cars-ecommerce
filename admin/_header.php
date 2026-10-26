<?php
require_once __DIR__ . '/bootstrap.php';
$pageTitle = $pageTitle ?? 'Admin Dashboard';
$flash = adminFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo sanitize($pageTitle); ?> - <?php echo sanitize(APP_NAME); ?></title>
<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="admin.css">
</head>
<body class="admin-body">
<header class="admin-header">
    <div class="admin-brand"><span class="admin-logo">🏎</span><div><strong><?php echo sanitize(APP_NAME); ?></strong><small>Store Admin</small></div></div>
    <div class="admin-top-actions">
        <a href="../index.php">View Store</a>
        <a href="../logout.php" class="admin-logout">Logout</a>
    </div>
</header>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-nav-link <?php echo ($activePage ?? '') === 'dashboard' ? 'active' : ''; ?>" href="index.php">📊 Dashboard</a>
        <a class="admin-nav-link <?php echo ($activePage ?? '') === 'products' ? 'active' : ''; ?>" href="products.php">🚗 Products</a>
        <a class="admin-nav-link <?php echo ($activePage ?? '') === 'orders' ? 'active' : ''; ?>" href="orders.php">📦 Orders</a>
        <a class="admin-nav-link <?php echo ($activePage ?? '') === 'inventory' ? 'active' : ''; ?>" href="inventory.php">📋 Inventory</a>
        <a class="admin-nav-link <?php echo ($activePage ?? '') === 'users' ? 'active' : ''; ?>" href="users.php">👥 Users</a>
        <div class="admin-user-box"><strong><?php echo sanitize($user['full_name'] ?? 'Admin'); ?></strong><span><?php echo sanitize($user['email'] ?? ''); ?></span><em>Administrator</em></div>
    </aside>
    <main class="admin-main">
        <?php if ($flash): ?>
            <div class="admin-alert <?php echo sanitize($flash['type']); ?>"><?php echo sanitize($flash['message']); ?></div>
        <?php endif; ?>
