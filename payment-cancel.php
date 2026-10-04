<?php
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/orders.php';
require_once __DIR__ . '/php/auth.php';

requireAuth('payment-cancel.php');

$orderNumber = $_GET['order_id'] ?? '';
$order = $orderNumber ? getOrderDetails($orderNumber) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - <?php echo sanitize(APP_NAME); ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="top-bar"><div class="container"><p><?php echo APP_NAME; ?> | PayHere Sandbox</p></div></div>
    <nav>
        <a href="index.php" class="logo"><div class="logo-icon">🏎</div><?php echo APP_NAME; ?></a>
        <ul class="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="shop.php">Shop Catalog</a></li>
            <li><a href="cart.php">Shopping Cart</a></li>
            <li><a href="profile.php">My Profile</a></li>
        </ul>
    </nav>
</header>
<div class="breadcrumb"><div class="container"><a href="index.php">Home</a><span>/</span><span class="active">Payment Cancelled</span></div></div>
<div class="main-content">
    <div class="container checkout-section">
        <div class="order-success-card">
            <div class="success-icon">!</div>
            <h1 style="font-size: 28px; margin-bottom: 8px;">Payment Cancelled</h1>
            <p style="color: var(--text-muted); margin-bottom: 24px;">
                The PayHere Sandbox payment was cancelled or not completed.
                <?php if ($order): ?>
                    Order <strong>#<?php echo sanitize($order['order_number']); ?></strong> remains in the system with its current pending status.
                <?php endif; ?>
            </p>
            <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
                <a href="shop.php" class="btn btn-primary btn-lg">Continue Shopping</a>
                <a href="cart.php" class="btn btn-outline-dark">View Cart</a>
            </div>
        </div>
    </div>
</div>
<script src="js/script.js"></script>
</body>
</html>
