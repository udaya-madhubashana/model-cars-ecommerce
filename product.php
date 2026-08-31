<?php
/**
 * ModelCars Pro - Single Product Detail Page
 * File: product.php
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/products.php';
require_once __DIR__ . '/php/cart.php';
require_once __DIR__ . '/php/auth.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$product = getProductById($productId);

if (!$product) {
    // 404 fallback
    header("HTTP/1.0 404 Not Found");
}

$cartCount = getCartCount();
$relatedProducts = $product ? getRelatedProducts($product['id'], $product['category_id'], 3) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? sanitize($product['name']) . " - " . APP_NAME : "Product Not Found - " . APP_NAME; ?></title>
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
                <li><a href="shop.php" class="active">Shop Catalog</a></li>
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

    <?php if (!$product): ?>
        <div class="main-content container" style="padding: 80px 24px; text-align: center;">
            <div style="font-size: 56px; margin-bottom: 16px;">🔍</div>
            <h2>Model Car Not Found</h2>
            <p style="color: var(--text-muted); margin: 12px 0 24px;">The model car you are looking for might have been retired or sold out.</p>
            <a href="shop.php" class="btn btn-primary btn-lg">Back to Catalog</a>
        </div>
    <?php else: ?>
        <!-- ===== BREADCRUMB ===== -->
        <div class="breadcrumb">
            <div class="container">
                <a href="index.php">Home</a>
                <span>/</span>
                <a href="shop.php">Shop</a>
                <span>/</span>
                <a href="shop.php?category=<?php echo $product['category_id']; ?>"><?php echo sanitize($product['category_name'] ?? 'Category'); ?></a>
                <span>/</span>
                <span class="active"><?php echo sanitize($product['name']); ?></span>
            </div>
        </div>

        <div class="main-content">
            <div class="container product-detail-section">
                <div class="product-detail-grid">
                    <!-- Product Gallery -->
                    <div class="product-gallery">
                        <img src="<?php echo getProductImagePath($product['image']); ?>" alt="<?php echo sanitize($product['name']); ?>">
                        <?php if (!empty($product['badge'])): ?>
                            <span class="badge" style="top: 20px; right: 20px; font-size: 13px; padding: 6px 16px;"><?php echo sanitize($product['badge']); ?></span>
                        <?php endif; ?>
                        <span class="badge-scale" style="bottom: 20px; left: 20px; font-size: 13px; padding: 6px 14px;">Scale: <?php echo sanitize($product['scale']); ?></span>
                    </div>

                    <!-- Product Summary -->
                    <div class="product-summary">
                        <div class="product-summary-brand"><?php echo sanitize($product['brand']); ?></div>
                        <h1 class="product-summary-title"><?php echo sanitize($product['name']); ?></h1>
                        
                        <div class="product-summary-meta">
                            <span style="color: #FFB800; font-size: 15px;">★★★★★</span>
                            <span style="color: var(--text-muted); font-size: 14px;">(<?php echo $product['reviews_count']; ?> Customer Reviews)</span>
                            <span style="color: #cbd5e0;">|</span>
                            <span style="color: var(--success-color); font-weight: 700; font-size: 14px;">✓ In Stock (<?php echo $product['stock_quantity']; ?> units available)</span>
                        </div>

                        <div class="product-summary-price">
                            <?php if (!empty($product['old_price'])): ?>
                                <span class="old-price"><?php echo formatPrice($product['old_price']); ?></span>
                            <?php endif; ?>
                            <span><?php echo formatPrice($product['price']); ?></span>
                        </div>

                        <p class="product-summary-desc">
                            <?php echo nl2br(sanitize($product['description'])); ?>
                        </p>

                        <!-- Specifications Table -->
                        <table class="spec-table">
                            <tbody>
                                <tr>
                                    <th>Scale Ratio</th>
                                    <td><strong><?php echo sanitize($product['scale']); ?></strong> Precision Die-Cast</td>
                                </tr>
                                <tr>
                                    <th>Brand / Manufacturer</th>
                                    <td><?php echo sanitize($product['brand']); ?></td>
                                </tr>
                                <tr>
                                    <th>Primary Material</th>
                                    <td>Die-Cast Zinc Alloy Body, ABS Chassis & Rubber Wheels</td>
                                </tr>
                                <tr>
                                    <th>Authenticity</th>
                                    <td>100% Officially Licensed Model Vehicle</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Add to Cart / Quantity Form -->
                        <form action="cart.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="quantity-add-group">
                                <div class="qty-control">
                                    <button type="button" class="qty-btn qty-minus">−</button>
                                    <input type="number" name="quantity" class="qty-input" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" data-product-id="<?php echo $product['id']; ?>">
                                    <button type="button" class="qty-btn qty-plus">+</button>
                                </div>

                                <button type="button" class="btn btn-primary btn-lg ajax-add-to-cart" data-product-id="<?php echo $product['id']; ?>" style="flex: 1;">
                                    🛒 Add to Shopping Cart
                                </button>
                                
                                <button type="submit" class="btn btn-danger btn-lg">
                                    Buy Now
                                </button>
                            </div>
                        </form>

                        <!-- Trust Features Box -->
                        <div style="background: #f8f9fa; border: 1px solid #edf2f7; border-radius: var(--radius-sm); padding: 16px; margin-top: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                            <div>🚚 <strong>Free Island-wide Delivery</strong> over <?php echo formatPrice(FREE_SHIPPING_THRESHOLD); ?></div>
                            <div>🛡️ <strong>Safe Delivery Guarantee</strong> in protective casing</div>
                            <div>⭐ <strong>Collector Verified</strong> authentic die-cast</div>
                            <div>💬 <strong>24/7 Support</strong> for questions & inquiries</div>
                        </div>
                    </div>
                </div>

                <!-- Related Products Section -->
                <?php if (!empty($relatedProducts)): ?>
                    <div style="margin-top: 40px;">
                        <h2 class="section-title">Related Model Cars</h2>
                        <div class="products-grid">
                            <?php foreach ($relatedProducts as $rel): ?>
                                <div class="product-card">
                                    <div class="product-image">
                                        <a href="product.php?id=<?php echo $rel['id']; ?>">
                                            <img src="<?php echo getProductImagePath($rel['image']); ?>" alt="<?php echo sanitize($rel['name']); ?>" loading="lazy">
                                        </a>
                                        <span class="badge-scale"><?php echo sanitize($rel['scale']); ?></span>
                                    </div>
                                    <div class="product-info">
                                        <div class="product-brand"><?php echo sanitize($rel['brand']); ?></div>
                                        <h3 class="product-name">
                                            <a href="product.php?id=<?php echo $rel['id']; ?>"><?php echo sanitize($rel['name']); ?></a>
                                        </h3>
                                        <div class="product-footer">
                                            <div class="product-price"><?php echo formatPrice($rel['price']); ?></div>
                                            <button type="button" class="add-to-cart-btn ajax-add-to-cart" data-product-id="<?php echo $rel['id']; ?>">
                                                🛒 Add
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

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
