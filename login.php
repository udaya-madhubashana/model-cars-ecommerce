<?php
/**
 * ModelCars Pro - Customer Login Page
 * File: login.php
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/cart.php';

$redirectParam = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
$redirectUrl = getSafeRedirectUrl($redirectParam, 'index.php');

// If user is already logged in, redirect to destination
if (isLoggedIn()) {
    redirect($redirectUrl);
}

$errorMessage = '';
$emailValue = '';
$cartCount = getCartCount();
$flash = getFlashMessage();

// Process Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $emailValue = sanitize($email);

    $loginResult = loginUser($email, $password);
    if ($loginResult['success']) {
        setFlashMessage('success', $loginResult['message']);
        redirect($redirectUrl);
    } else {
        $errorMessage = $loginResult['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- ===== HEADER ===== -->
    <header>
        <div class="top-bar">
            <div class="container">
                <p>🎉 Welcome to <?php echo APP_NAME; ?> | <span>Free Shipping on Orders Over <?php echo formatPrice(FREE_SHIPPING_THRESHOLD); ?>!</span></p>
            </div>
        </div>
        
        <nav>
            <a href="index.php" class="logo">
                <div class="logo-icon">🏎</div>
                <?php echo APP_NAME; ?>
            </a>

            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php">Shop Catalog</a></li>
                <li><a href="shop.php?category=1">Sports Cars</a></li>
                <li><a href="shop.php?category=4">Limited Edition</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php $currentUser = getCurrentUser(); ?>
                    <li><span class="user-greeting">Welcome, <?php echo sanitize($currentUser['full_name'] ?? 'Collector'); ?></span></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="logout.php" class="nav-logout-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="active">Login</a></li>
                    <li><a href="registration.php<?php echo !empty($redirectParam) ? '?redirect=' . urlencode($redirectParam) : ''; ?>">Register</a></li>
                <?php endif; ?>
            </ul>

            <div class="nav-right">
                <form action="shop.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search model cars..." required>
                    <button type="submit" title="Search">🔍</button>
                </form>
                <a href="cart.php" class="cart-icon-wrapper" title="View Shopping Cart">
                    🛒
                    <span class="cart-count"><?php echo $cartCount; ?></span>
                </a>
            </div>
        </nav>
    </header>

    <!-- ===== BREADCRUMB ===== -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <span>/</span>
            <span class="active">Customer Login</span>
        </div>
    </div>

    <!-- ===== MAIN AUTH SECTION ===== -->
    <div class="main-content auth-section">
        <div class="container auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-icon">🏎️</div>
                    <h1>Sign In to ModelCars Pro</h1>
                    <p>Access your collector account, track orders & save your favorites</p>
                </div>

                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'); ?>">
                        <span><?php echo sanitize($flash['message']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger">
                        <span>⚠️ <?php echo sanitize($errorMessage); ?></span>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="auth-form">
                    <?php if (!empty($redirectParam)): ?>
                        <input type="hidden" name="redirect" value="<?php echo sanitize($redirectParam); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?php echo $emailValue; ?>" required autofocus placeholder="collector@example.com">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block auth-btn">
                        Sign In →
                    </button>
                </form>

                <div class="auth-footer">
                    Don't have an account yet? <a href="registration.php<?php echo !empty($redirectParam) ? '?redirect=' . urlencode($redirectParam) : ''; ?>">Create Account</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== FOOTER ===== -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p><?php echo APP_NAME; ?> is Sri Lanka's leading premier online store for authentic die-cast model cars and Hot Wheels collectibles.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="shop.php">All Products</a></li>
                        <li><a href="cart.php">Shopping Cart</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="registration.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="index.php#about">Shipping Information</a></li>
                        <li><a href="index.php#about">Returns Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <ul>
                        <li>📍 Colombo, Sri Lanka</li>
                        <li>📞 +94 77 123 4567</li>
                        <li>✉️ support@modelcars.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All Rights Reserved. | Designed for ICT2142 E-Business Systems Project</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
