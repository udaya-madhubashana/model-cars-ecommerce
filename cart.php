<?php
/**
 * ModelCars Pro - Shopping Cart Page
 * File: cart.php
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/products.php';
require_once __DIR__ . '/php/cart.php';
require_once __DIR__ . '/php/auth.php';

// Process standard non-AJAX POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);
        addToCart($productId, $qty);
        setFlashMessage('success', 'Product added to your cart.');
        redirect('cart.php');
    } elseif ($action === 'update') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);
        updateCartQuantity($productId, $qty);
        setFlashMessage('success', 'Cart updated successfully.');
        redirect('cart.php');
    } elseif ($action === 'remove') {
        $productId = (int)($_POST['product_id'] ?? 0);
        removeFromCart($productId);
        setFlashMessage('info', 'Item removed from cart.');
        redirect('cart.php');
    } elseif ($action === 'clear') {
        clearCart();
        setFlashMessage('info', 'Your cart has been cleared.');
        redirect('cart.php');
    }
}

// Handle GET remove
if (isset($_GET['remove_id'])) {
    removeFromCart((int)$_GET['remove_id']);
    setFlashMessage('info', 'Item removed from cart.');
    redirect('cart.php');
}

$cartItems = getCartItems();
$subtotal = getCartSubtotal();
$shippingFee = getCartShippingFee($subtotal);
$grandTotal = $subtotal + $shippingFee;
$cartCount = getCartCount();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?php echo APP_NAME; ?></title>
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
                <li><a href="index.php#about">About</a></li>
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
            <a href="shop.php">Shop</a>
            <span>/</span>
            <span class="active">Shopping Cart</span>
        </div>
    </div>

    <!-- ===== MAIN CART SECTION ===== -->
    <div class="main-content">
        <div class="container cart-section">
            <h1 class="section-title" style="text-align: left; margin-bottom: 30px;">Your Shopping Cart</h1>

            <?php if ($flash): ?>
                <div style="background: #ebf8ff; border: 1px solid #bee3f8; color: #2b6cb0; padding: 14px 20px; border-radius: var(--radius-sm); margin-bottom: 24px; font-weight: 600;">
                    <?php echo sanitize($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cartItems)): ?>
                <div style="background: white; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 60px 24px; text-align: center;">
                    <div style="font-size: 64px; margin-bottom: 16px;">🛒</div>
                    <h2>Your Shopping Cart is Empty</h2>
                    <p style="color: var(--text-muted); margin: 12px 0 28px; max-width: 450px; margin-left: auto; margin-right: auto;">
                        Looks like you haven't added any die-cast model cars to your collection yet.
                    </p>
                    <a href="shop.php" class="btn btn-primary btn-lg">Explore Die-Cast Models</a>
                </div>
            <?php else: ?>
                <!-- Free shipping banner indicator -->
                <?php if ($subtotal < FREE_SHIPPING_THRESHOLD): ?>
                    <div style="background: #fffaf0; border: 1px solid #feebc8; color: #7b341e; padding: 12px 20px; border-radius: var(--radius-sm); margin-bottom: 24px; font-size: 14px; font-weight: 600;">
                        🚚 Add <strong><?php echo formatPrice(FREE_SHIPPING_THRESHOLD - $subtotal); ?></strong> more to qualify for <strong>FREE island-wide delivery</strong>!
                    </div>
                <?php else: ?>
                    <div style="background: #f0fff4; border: 1px solid #c6f6d5; color: #22543d; padding: 12px 20px; border-radius: var(--radius-sm); margin-bottom: 24px; font-size: 14px; font-weight: 600;">
                        🎉 Congratulations! You have unlocked <strong>FREE Delivery</strong> on this order!
                    </div>
                <?php endif; ?>

                <div class="cart-layout">
                    <!-- Cart Line Items Table -->
                    <div>
                        <div class="cart-table-wrapper">
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th style="text-align: center;">Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="cart-product-cell">
                                                    <img src="<?php echo getProductImagePath($item['product']['image']); ?>" alt="<?php echo sanitize($item['product']['name']); ?>" class="cart-item-img">
                                                    <div>
                                                        <div class="cart-item-brand"><?php echo sanitize($item['product']['brand']); ?> • <?php echo sanitize($item['product']['scale']); ?></div>
                                                        <a href="product.php?id=<?php echo $item['product']['id']; ?>" class="cart-item-name">
                                                            <?php echo sanitize($item['product']['name']); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo formatPrice($item['unit_price']); ?></strong>
                                            </td>
                                            <td style="text-align: center;">
                                                <form action="cart.php" method="POST" style="display: inline-flex; align-items: center; gap: 6px;">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                                    
                                                    <div class="qty-control" style="transform: scale(0.9);">
                                                        <button type="submit" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>" class="qty-btn">−</button>
                                                        <input type="text" class="qty-input" value="<?php echo $item['quantity']; ?>" readonly>
                                                        <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="qty-btn">+</button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td>
                                                <strong style="color: var(--primary-color); font-size: 16px;"><?php echo formatPrice($item['line_total']); ?></strong>
                                            </td>
                                            <td style="text-align: right;">
                                                <a href="cart.php?remove_id=<?php echo $item['product']['id']; ?>" class="cart-remove-btn" title="Remove item">
                                                    🗑️
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Actions row -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 15px;">
                            <a href="shop.php" class="btn btn-outline-dark">← Continue Shopping</a>
                            
                            <form action="cart.php" method="POST" onsubmit="return confirm('Are you sure you want to clear your entire cart?');">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn btn-outline-dark" style="color: var(--danger-color); border-color: #feb2b2;">
                                    Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Order Summary Card -->
                    <div>
                        <div class="order-summary-card">
                            <h3 class="order-summary-title">Order Summary</h3>

                            <div class="summary-row">
                                <span style="color: var(--text-muted);">Items Subtotal (<?php echo $cartCount; ?>)</span>
                                <strong><?php echo formatPrice($subtotal); ?></strong>
                            </div>

                            <div class="summary-row">
                                <span style="color: var(--text-muted);">Delivery Fee</span>
                                <span>
                                    <?php if ($shippingFee == 0): ?>
                                        <strong style="color: var(--success-color);">FREE</strong>
                                    <?php else: ?>
                                        <strong><?php echo formatPrice($shippingFee); ?></strong>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="summary-row total">
                                <span>Total Amount</span>
                                <span><?php echo formatPrice($grandTotal); ?></span>
                            </div>

                            <div style="margin-top: 24px;">
                                <a href="checkout.php" class="btn btn-primary btn-lg btn-block">
                                    Proceed to Checkout →
                                </a>
                            </div>

                            <div style="margin-top: 20px; font-size: 12px; color: var(--text-muted); text-align: center;">
                                🔒 Guaranteed safe & secure checkout with cash on delivery & direct bank transfer options
                            </div>
                        </div>
                    </div>
                </div>
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
                        <li><a href="checkout.php">Checkout</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
