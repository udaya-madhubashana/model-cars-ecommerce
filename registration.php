<?php
/**
 * ModelCars Pro - Customer Registration Page
 * File: registration.php
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/cart.php';

// If user is already logged in, redirect to homepage
if (isLoggedIn()) {
    redirect('index.php');
}

$errorMessage = '';
$fullNameValue = '';
$emailValue = '';
$phoneValue = '';
$addressValue = '';
$cityValue = 'Colombo';
$postalCodeValue = '00100';

$cartCount = getCartCount();
$flash = getFlashMessage();

// Process Registration Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $postalCode = $_POST['postal_code'] ?? '';

    // Preserve entered form values
    $fullNameValue = sanitize($fullName);
    $emailValue = sanitize($email);
    $phoneValue = sanitize($phone);
    $addressValue = sanitize($address);
    $cityValue = sanitize($city);
    $postalCodeValue = sanitize($postalCode);

    // Validation
    if (empty(trim($fullName)) || empty(trim($email)) || empty($password)) {
        $errorMessage = 'Please fill in all required fields (Full Name, Email, and Password).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match. Please re-enter your password.';
    } else {
        // Proceed with registration
        $regResult = registerUser($fullName, $email, $password, $phone, $address, $city, $postalCode);
        if ($regResult['success']) {
            setFlashMessage('success', $regResult['message']);
            redirect('index.php');
        } else {
            $errorMessage = $regResult['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration - <?php echo APP_NAME; ?></title>
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
                    <li><a href="logout.php" class="nav-logout-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="registration.php" class="active">Register</a></li>
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
            <span class="active">Customer Registration</span>
        </div>
    </div>

    <!-- ===== MAIN AUTH SECTION ===== -->
    <div class="main-content auth-section">
        <div class="container auth-container register-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-icon">🏁</div>
                    <h1>Create Collector Account</h1>
                    <p>Join ModelCars Pro to manage orders, fast-track checkout & get exclusive collector deals</p>
                </div>

                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'info'; ?>">
                        <span><?php echo sanitize($flash['message']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="alert alert-danger">
                        <span>⚠️ <?php echo sanitize($errorMessage); ?></span>
                    </div>
                <?php endif; ?>

                <form action="registration.php" method="POST" class="auth-form">
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo $fullNameValue; ?>" required autofocus placeholder="e.g. Kasun Perera">
                        </div>

                        <div class="form-group full-width">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?php echo $emailValue; ?>" required placeholder="kasun@example.com">
                        </div>

                        <div class="form-group">
                            <label for="password">Password * (min 6 characters)</label>
                            <input type="password" id="password" name="password" required placeholder="Enter password" minlength="6">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter password" minlength="6">
                        </div>

                        <div class="form-group full-width">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo $phoneValue; ?>" placeholder="+94 77 123 4567">
                        </div>

                        <div class="form-group full-width">
                            <label for="address">Delivery Address</label>
                            <textarea id="address" name="address" rows="2" placeholder="Street Address, Apartment / House No."><?php echo $addressValue; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="city">City / District</label>
                            <input type="text" id="city" name="city" value="<?php echo $cityValue; ?>" placeholder="Colombo">
                        </div>

                        <div class="form-group">
                            <label for="postal_code">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code" value="<?php echo $postalCodeValue; ?>" placeholder="00100">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg btn-block auth-btn">
                        Create My Account →
                    </button>
                </form>

                <div class="auth-footer">
                    Already registered? <a href="login.php">Sign In to Your Account</a>
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
