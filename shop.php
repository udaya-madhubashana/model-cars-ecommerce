<?php
/**
 * ModelCars Pro - Product Catalog Page
 * File: shop.php
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/products.php';
require_once __DIR__ . '/php/cart.php';
require_once __DIR__ . '/php/auth.php';

$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$brand = isset($_GET['brand']) ? sanitize($_GET['brand']) : null;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'featured';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;

$products = getAllProducts($categoryId, $brand, $sort, $search);
$categories = getCategories();
$brands = getBrands();
$cartCount = getCartCount();

// Determine active category name for title
$activeCategoryName = 'All Model Cars';
if ($categoryId) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categoryId) {
            $activeCategoryName = $cat['name'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Catalog - <?php echo APP_NAME; ?></title>
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
                    <input type="text" name="search" placeholder="Search model cars..." value="<?php echo sanitize($search ?? ''); ?>" required>
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
            <?php if ($categoryId || $brand || $search): ?>
                <span>/</span>
                <span class="active"><?php echo sanitize($activeCategoryName); ?><?php echo $brand ? " - " . sanitize($brand) : ""; ?><?php echo $search ? " (Search: \"" . sanitize($search) . "\")" : ""; ?></span>
            <?php else: ?>
                <span>/</span>
                <span class="active">All Models</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== MAIN SHOP LAYOUT ===== -->
    <div class="main-content">
        <div class="container">
            <div class="shop-layout">
                <!-- Sidebar Filters -->
                <aside class="filter-sidebar">
                    <div class="filter-group">
                        <h4 class="filter-title">Categories</h4>
                        <ul class="filter-list">
                            <li>
                                <a href="shop.php<?php echo $brand ? '?brand=' . urlencode($brand) : ''; ?>" class="<?php echo empty($categoryId) ? 'active' : ''; ?>">
                                    <span>All Categories</span>
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a href="shop.php?category=<?php echo $cat['id']; ?><?php echo $brand ? '&brand=' . urlencode($brand) : ''; ?>" class="<?php echo $categoryId == $cat['id'] ? 'active' : ''; ?>">
                                        <span><?php echo $cat['icon']; ?> <?php echo sanitize($cat['name']); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="filter-group">
                        <h4 class="filter-title">Brands</h4>
                        <ul class="filter-list">
                            <li>
                                <a href="shop.php<?php echo $categoryId ? '?category=' . $categoryId : ''; ?>" class="<?php echo empty($brand) ? 'active' : ''; ?>">
                                    <span>All Brands</span>
                                </a>
                            </li>
                            <?php foreach ($brands as $b): ?>
                                <li>
                                    <a href="shop.php?brand=<?php echo urlencode($b); ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?>" class="<?php echo strcasecmp($brand ?? '', $b) === 0 ? 'active' : ''; ?>">
                                        <span><?php echo sanitize($b); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <?php if ($categoryId || $brand || $search): ?>
                        <div style="margin-top: 20px;">
                            <a href="shop.php" class="btn btn-outline-dark btn-sm btn-block">Reset All Filters</a>
                        </div>
                    <?php endif; ?>
                </aside>

                <!-- Products Catalog -->
                <main class="shop-main">
                    <div class="shop-top-bar">
                        <div>
                            <strong>Showing <?php echo count($products); ?></strong> Model Cars
                            <?php if ($search): ?>
                                for <em>"<?php echo sanitize($search); ?>"</em>
                            <?php endif; ?>
                        </div>

                        <form method="GET" action="shop.php" style="display: flex; align-items: center; gap: 8px;">
                            <?php if ($categoryId): ?><input type="hidden" name="category" value="<?php echo $categoryId; ?>"><?php endif; ?>
                            <?php if ($brand): ?><input type="hidden" name="brand" value="<?php echo sanitize($brand); ?>"><?php endif; ?>
                            <?php if ($search): ?><input type="hidden" name="search" value="<?php echo sanitize($search); ?>"><?php endif; ?>
                            
                            <label for="sort-select" style="font-size: 13px; font-weight: 600;">Sort By:</label>
                            <select id="sort-select" name="sort" class="sort-select" onchange="this.form.submit()">
                                <option value="featured" <?php echo $sort === 'featured' ? 'selected' : ''; ?>>Featured</option>
                                <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Customer Rating</option>
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
                            </select>
                        </form>
                    </div>

                    <?php if (empty($products)): ?>
                        <div style="background: white; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 48px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 12px;">🏎️💨</div>
                            <h3>No Model Cars Found</h3>
                            <p style="color: var(--text-muted); margin: 10px 0 20px;">We couldn't find any scale models matching your selected filter criteria.</p>
                            <a href="shop.php" class="btn btn-primary">Browse All Models</a>
                        </div>
                    <?php else: ?>
                        <div class="products-grid">
                            <?php foreach ($products as $product): ?>
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
                    <?php endif; ?>
                </main>
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
