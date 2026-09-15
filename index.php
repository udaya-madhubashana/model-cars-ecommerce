<?php
/**
 * ModelCars Pro - Homepage
 * File: index.php
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/products.php';
require_once __DIR__ . '/php/cart.php';
require_once __DIR__ . '/php/auth.php';

$featuredProducts = getFeaturedProducts(6);
$categories = getCategories();
$cartCount = getCartCount();
$currentUser = getCurrentUser();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Hot Wheels & Die-Cast Model Cars Store</title>
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
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="shop.php">Shop Catalog</a></li>
                <li><a href="shop.php?category=1">Sports Cars</a></li>
                <li><a href="shop.php?category=4">Limited Edition</a></li>
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

    <div class="main-content">
        <?php if ($flash): ?>
            <div class="container" style="padding-top: 20px;">
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'info'; ?>">
                    <span><?php echo sanitize($flash['message']); ?></span>
                </div>
            </div>
        <?php endif; ?>
        <!-- ===== HERO SECTION ===== -->
        <section class="hero" id="home">
            <div class="container hero-container">
                <div class="hero-content">
                    <span class="hero-badge">🔥 New 2024 Collection</span>
                    <h1>Discover the Perfect <span>Model Car</span></h1>
                    <p>Explore our premium collection of Hot Wheels, Matchbox, Tamiya, and exclusive collector's edition die-cast supercars.</p>
                    <div class="hero-buttons">
                        <a href="shop.php" class="btn btn-primary btn-lg">
                            Shop Now
                        </a>
                        <a href="#categories" class="btn btn-secondary btn-lg">
                            View Categories
                        </a>
                    </div>
                </div>
                <div class="hero-image-wrapper">
                    <img src="<?php echo getProductImagePath('Ferrari F40.jpeg'); ?>" alt="Featured Ferrari F40 Die-Cast Supercar Model" loading="lazy">
                </div>
            </div>
        </section>

        <!-- ===== FEATURED PRODUCTS ===== -->
        <section class="featured-section" id="shop">
            <div class="container">
                <h2 class="section-title">Featured Model Cars</h2>
                <p class="section-subtitle">Hand-picked die-cast scale collectibles from the world's most iconic automotive legends</p>
                
                <div class="products-grid">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="product.php?id=<?php echo $product['id']; ?>">
                                    <img src="<?php echo getProductImagePath($product['image']); ?>" alt="<?php echo sanitize($product['name']); ?>" loading="lazy">
                                </a>
                                <?php if (!empty($product['badge'])): ?>
                                    <span class="badge"><?php echo sanitize($product['badge']); ?></span>
                                <?php endif; ?>
                                <span class="badge-scale"><?php echo sanitize($product['scale']); ?></span>
                            </div>
                            <div class="product-info">
                                <div class="product-brand"><?php echo sanitize($product['brand']); ?></div>
                                <h3 class="product-name">
                                    <a href="product.php?id=<?php echo $product['id']; ?>"><?php echo sanitize($product['name']); ?></a>
                                </h3>
                                <div class="product-rating">
                                    ★★★★★ <span>(<?php echo $product['reviews_count']; ?> reviews)</span>
                                </div>
                                <div class="product-footer">
                                    <div class="product-price">
                                        <?php if (!empty($product['old_price'])): ?>
                                            <span class="old-price"><?php echo formatPrice($product['old_price']); ?></span>
                                        <?php endif; ?>
                                        <?php echo formatPrice($product['price']); ?>
                                    </div>
                                    <button type="button" class="add-to-cart-btn ajax-add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                        🛒 Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="text-align: center;">
                    <a href="shop.php" class="btn btn-primary btn-lg">View All Products (<?php echo count($featuredProducts); ?>+)</a>
                </div>
            </div>
        </section>

        <!-- ===== CATEGORIES ===== -->
        <section class="categories-section" id="categories">
            <div class="container">
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Find your exact scale passion across diverse model classifications</p>
                
                <div class="categories-grid">
                    <?php foreach ($categories as $cat): ?>
                        <a href="shop.php?category=<?php echo $cat['id']; ?>" class="category-card">
                            <div class="category-icon"><?php echo $cat['icon']; ?></div>
                            <div class="category-name"><?php echo sanitize($cat['name']); ?></div>
                            <div class="category-count"><?php echo isset($cat['count']) ? $cat['count'] . ' models' : 'Explore collection'; ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- ===== WHY CHOOSE US ===== -->
        <section class="features-section" id="about">
            <div class="container">
                <h2 class="section-title" style="color: white;">Why Choose <?php echo APP_NAME; ?>?</h2>
                
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">✓</div>
                        <h3>Authentic Products</h3>
                        <p>100% genuine die-cast models from trusted brands like Hot Wheels, Matchbox, and Tamiya</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🚚</div>
                        <h3>Fast Island-wide Shipping</h3>
                        <p>Free delivery on orders over <?php echo formatPrice(FREE_SHIPPING_THRESHOLD); ?>. Secure bubble-wrapped packaging</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">💰</div>
                        <h3>Best Collector Prices</h3>
                        <p>Competitive pricing with regular discounts and exclusive deals for loyal collectors</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🛡️</div>
                        <h3>Secure Checkout</h3>
                        <p>Multiple payment options including Cash on Delivery and direct bank transfers</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== NEWSLETTER ===== -->
        <section class="newsletter-section">
            <div class="container">
                <div class="newsletter-content">
                    <h2>Subscribe to Our Collector Newsletter</h2>
                    <p>Get exclusive early access to limited edition drops, new arrivals, and collector tips</p>
                    <div class="newsletter-form">
                        <input type="email" placeholder="Enter your email address...">
                        <button type="button" class="btn btn-primary">Subscribe</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- ===== FOOTER ===== -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p><?php echo APP_NAME; ?> is Sri Lanka's leading premier online store for authentic die-cast model cars and Hot Wheels collectibles.</p>
                    <div class="social-icons">
                        <a href="#" class="social-icon" title="Facebook">f</a>
                        <a href="#" class="social-icon" title="Twitter">🐦</a>
                        <a href="#" class="social-icon" title="Instagram">📷</a>
                        <a href="#" class="social-icon" title="YouTube">▶️</a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="shop.php">All Products</a></li>
                        <li><a href="shop.php?category=1">Sports Cars</a></li>
                        <li><a href="shop.php?category=4">Limited Editions</a></li>
                        <li><a href="cart.php">Shopping Cart</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="checkout.php">Order Tracking</a></li>
                        <li><a href="#about">Shipping Information</a></li>
                        <li><a href="#about">Returns & Warranty</a></li>
                        <li><a href="#about">Contact Support</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Store Information</h3>
                    <ul>
                        <li>📍 Colombo, Sri Lanka</li>
                        <li>📞 +94 72 399 4434</li>
                        <li>✉️ support@modelcars.com</li>
                        <li>⏰ Mon - Sat: 9:00 AM - 7:00 PM</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All Rights Reserved. | Designed for ICT2142 E-Business Systems Project</p>
            </div>
        </div>
    </footer>

    <!-- Toast notification wrapper & scripts -->
    <script src="js/script.js"></script>
</body>
</html>
