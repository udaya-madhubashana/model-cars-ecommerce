<?php
/**
 * ModelCars Pro - Checkout & Order Placement Page
 * File: checkout.php
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/products.php';
require_once __DIR__ . '/php/cart.php';
require_once __DIR__ . '/php/orders.php';
require_once __DIR__ . '/php/auth.php';

$cartItems = getCartItems();
$subtotal = getCartSubtotal();
$shippingFee = getCartShippingFee($subtotal);
$grandTotal = $subtotal + $shippingFee;
$cartCount = getCartCount();
$currentUser = getCurrentUser();

$orderPlaced = false;
$orderResult = null;
$errorMessage = null;

// Handle Order Placement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $orderResult = createOrder($_POST);
    if ($orderResult['success']) {
        $orderPlaced = true;
    } else {
        $errorMessage = $orderResult['message'];
    }
}

// Redirect to cart if empty and not viewing placed order
if (empty($cartItems) && !$orderPlaced) {
    // Check if viewing order confirmation via query
    if (isset($_GET['order_id'])) {
        $orderPlaced = true;
        $orderResult = getOrderDetails($_GET['order_id']);
    } else {
        redirect('cart.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $orderPlaced ? "Order Confirmation - " . APP_NAME : "Checkout - " . APP_NAME; ?></title>
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
                <li><a href="cart.php">Shopping Cart</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php $currentUser = getCurrentUser(); ?>
                    <li><span class="user-greeting">Welcome, <?php echo sanitize($currentUser['full_name'] ?? 'Collector'); ?></span></li>
                    <li><a href="logout.php" class="nav-logout-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-auth-link">Login</a></li>
                    <li><a href="registration.php" class="nav-auth-link">Register</a></li>
                <?php endif; ?>
            </ul>

            <div class="nav-right">
                <a href="cart.php" class="cart-icon-wrapper" title="View Shopping Cart">
                    🛒
                    <span class="cart-count"><?php echo getCartCount(); ?></span>
                </a>
            </div>
        </nav>
    </header>

    <!-- ===== BREADCRUMB ===== -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="cart.php">Cart</a>
            <span>/</span>
            <span class="active"><?php echo $orderPlaced ? "Order Placed" : "Checkout"; ?></span>
        </div>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <div class="container checkout-section">
            <?php if ($orderPlaced && $orderResult): ?>
                <!-- Order Confirmation Receipt -->
                <div class="order-success-card">
                    <div class="success-icon">✓</div>
                    <h1 style="font-size: 28px; margin-bottom: 8px;">Thank You for Your Order!</h1>
                    <p style="color: var(--text-muted); margin-bottom: 24px;">
                        Your order <strong>#<?php echo sanitize($orderResult['order_number']); ?></strong> has been received and is being prepared for dispatch.
                    </p>

                    <div style="background: #f8f9fa; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; text-align: left; margin-bottom: 28px;">
                        <h4 style="margin-bottom: 16px; border-bottom: 1px solid #edf2f7; padding-bottom: 8px;">Order Details</h4>
                        
                        <div class="summary-row">
                            <span style="color: var(--text-muted);">Customer Name:</span>
                            <strong><?php echo sanitize($orderResult['customer_name']); ?></strong>
                        </div>
                        <div class="summary-row">
                            <span style="color: var(--text-muted);">Email Confirmation:</span>
                            <span><?php echo sanitize($orderResult['customer_email']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span style="color: var(--text-muted);">Payment Method:</span>
                            <span style="text-transform: uppercase; font-weight: 600;">
                                <?php 
                                    $pm = $orderResult['payment_method'];
                                    echo $pm === 'cod' ? 'Cash on Delivery' : ($pm === 'bank_transfer' ? 'Direct Bank Transfer' : 'Credit / Debit Card');
                                ?>
                            </span>
                        </div>
                        <div class="summary-row total">
                            <span>Amount Billed:</span>
                            <span><?php echo formatPrice($orderResult['total_amount']); ?></span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <button onclick="window.print()" class="btn btn-outline-dark">🖨️ Print Receipt</button>
                        <a href="shop.php" class="btn btn-primary btn-lg">Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Checkout Form & Order Summary -->
                <h1 class="section-title" style="text-align: left; margin-bottom: 30px;">Checkout & Delivery</h1>

                <?php if ($errorMessage): ?>
                    <div style="background: #fff5f5; border: 1px solid #feb2b2; color: #c53030; padding: 14px 20px; border-radius: var(--radius-sm); margin-bottom: 24px; font-weight: 600;">
                        ⚠️ <?php echo sanitize($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <form action="checkout.php" method="POST">
                    <input type="hidden" name="place_order" value="1">
                    
                    <div class="checkout-layout">
                        <!-- Left Column: Shipping & Payment Information -->
                        <div class="checkout-form-card">
                            <h3 class="form-section-title">📍 1. Delivery Information</h3>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="customer_name">Full Name *</label>
                                    <input type="text" id="customer_name" name="customer_name" value="<?php echo sanitize($currentUser['full_name'] ?? ''); ?>" required placeholder="e.g. Kasun Perera">
                                </div>

                                <div class="form-group">
                                    <label for="customer_email">Email Address *</label>
                                    <input type="email" id="customer_email" name="customer_email" value="<?php echo sanitize($currentUser['email'] ?? ''); ?>" required placeholder="kasun@example.com">
                                </div>

                                <div class="form-group full-width">
                                    <label for="customer_phone">Contact Phone Number *</label>
                                    <input type="tel" id="customer_phone" name="customer_phone" value="<?php echo sanitize($currentUser['phone'] ?? ''); ?>" required placeholder="+94 77 123 4567">
                                </div>

                                <div class="form-group full-width">
                                    <label for="shipping_address">Delivery Street Address *</label>
                                    <textarea id="shipping_address" name="shipping_address" rows="3" required placeholder="House/Apartment number, Street name, Area"><?php echo sanitize($currentUser['address'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="city">City / District *</label>
                                    <input type="text" id="city" name="city" value="<?php echo sanitize($currentUser['city'] ?? 'Colombo'); ?>" required placeholder="Colombo">
                                </div>

                                <div class="form-group">
                                    <label for="postal_code">Postal Code *</label>
                                    <input type="text" id="postal_code" name="postal_code" value="<?php echo sanitize($currentUser['postal_code'] ?? '00100'); ?>" required placeholder="00100">
                                </div>
                            </div>

                            <h3 class="form-section-title" style="margin-top: 32px;">💳 2. Payment Method</h3>
                            
                            <div class="payment-options">
                                <label class="payment-label selected">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <div>
                                        <strong>💵 Cash on Delivery (COD)</strong>
                                        <div style="font-size: 12px; color: var(--text-muted);">Pay with cash directly to the courier upon parcel arrival.</div>
                                    </div>
                                </label>

                                <label class="payment-label">
                                    <input type="radio" name="payment_method" value="bank_transfer">
                                    <div>
                                        <strong>🏦 Direct Bank Deposit / Online Banking</strong>
                                        <div style="font-size: 12px; color: var(--text-muted);">Commercial Bank / Sampath Bank account details provided after placement.</div>
                                    </div>
                                </label>

                                <label class="payment-label">
                                    <input type="radio" name="payment_method" value="card">
                                    <div>
                                        <strong>💳 Credit / Debit Card (Visa, Mastercard)</strong>
                                        <div style="font-size: 12px; color: var(--text-muted);">Fast, encrypted online checkout.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Right Column: Order Review Summary -->
                        <div>
                            <div class="order-summary-card">
                                <h3 class="order-summary-title">Order Review (<?php echo $cartCount; ?> Items)</h3>

                                <div style="max-height: 240px; overflow-y: auto; margin-bottom: 18px; padding-right: 6px;">
                                    <?php foreach ($cartItems as $item): ?>
                                        <div style="display: flex; gap: 12px; margin-bottom: 12px; align-items: center;">
                                            <img src="<?php echo getProductImagePath($item['product']['image']); ?>" alt="<?php echo sanitize($item['product']['name']); ?>" style="width: 50px; height: 42px; object-fit: cover; border-radius: 4px; background: #111;">
                                            <div style="flex: 1; font-size: 13px;">
                                                <div style="font-weight: 700;"><?php echo sanitize($item['product']['name']); ?></div>
                                                <div style="color: var(--text-muted); font-size: 12px;"><?php echo $item['quantity']; ?> × <?php echo formatPrice($item['unit_price']); ?></div>
                                            </div>
                                            <div style="font-weight: 700; font-size: 13px;">
                                                <?php echo formatPrice($item['line_total']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="summary-row">
                                    <span style="color: var(--text-muted);">Subtotal</span>
                                    <strong><?php echo formatPrice($subtotal); ?></strong>
                                </div>

                                <div class="summary-row">
                                    <span style="color: var(--text-muted);">Delivery Charge</span>
                                    <span>
                                        <?php if ($shippingFee == 0): ?>
                                            <strong style="color: var(--success-color);">FREE</strong>
                                        <?php else: ?>
                                            <strong><?php echo formatPrice($shippingFee); ?></strong>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <div class="summary-row total">
                                    <span>Grand Total</span>
                                    <span><?php echo formatPrice($grandTotal); ?></span>
                                </div>

                                <div style="margin-top: 24px;">
                                    <button type="submit" class="btn btn-danger btn-lg btn-block" style="font-size: 16px;">
                                        Place Order Now (<?php echo formatPrice($grandTotal); ?>)
                                    </button>
                                </div>

                                <div style="margin-top: 18px; font-size: 12px; color: var(--text-muted); text-align: center;">
                                    By placing your order, you agree to ModelCars Pro's Terms and Conditions.
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
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
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
