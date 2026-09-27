<?php
/**
 * ModelCars Pro - Customer Account & Profile Management Dashboard
 * File: profile.php
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/cart.php';
require_once __DIR__ . '/php/orders.php';

// Route Guard: user must be authenticated
requireAuth('profile.php');

$currentUser = getCurrentUser();
$userId = (int)$currentUser['id'];
$cartCount = getCartCount();
$flash = getFlashMessage();

$profileSuccess = null;
$profileError = null;
$passwordSuccess = null;
$passwordError = null;
$activeTab = isset($_GET['tab']) ? sanitize($_GET['tab']) : 'overview';

// Handle Profile Update Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_profile') {
        $activeTab = 'edit';
        $fullName = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $city = $_POST['city'] ?? '';
        $postalCode = $_POST['postal_code'] ?? '';

        $updateResult = updateUserProfile($userId, $fullName, $phone, $address, $city, $postalCode);
        if ($updateResult['success']) {
            $profileSuccess = $updateResult['message'];
            // Refresh user details
            $currentUser = getCurrentUser();
        } else {
            $profileError = $updateResult['message'];
        }
    } elseif ($action === 'change_password') {
        $activeTab = 'password';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $pwResult = changeUserPassword($userId, $currentPassword, $newPassword, $confirmPassword);
        if ($pwResult['success']) {
            $passwordSuccess = $pwResult['message'];
        } else {
            $passwordError = $pwResult['message'];
        }
    }
}

// Retrieve user's orders
$userOrders = getUserOrders($userId);
$joinedDate = !empty($currentUser['created_at']) ? date('F j, Y', strtotime($currentUser['created_at'])) : 'Recent Collector';
$roleName = ($currentUser['role'] ?? 'customer') === 'admin' ? 'Administrator' : 'Verified Collector';
$roleClass = ($currentUser['role'] ?? 'customer') === 'admin' ? 'admin' : 'customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-tab-content {
            display: none;
        }
        .profile-tab-content.active {
            display: block;
        }
    </style>
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
                <li><span class="user-greeting">Welcome, <?php echo sanitize($currentUser['full_name'] ?? 'Collector'); ?></span></li>
                <li><a href="profile.php" class="active">My Profile</a></li>
                <li><a href="logout.php" class="nav-logout-btn">Logout</a></li>
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
            <span class="active">My Account & Profile</span>
        </div>
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content profile-section">
        <div class="container">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'info'); ?>">
                    <span><?php echo sanitize($flash['message']); ?></span>
                </div>
            <?php endif; ?>

            <div class="profile-layout">
                <!-- Sidebar -->
                <aside class="profile-sidebar">
                    <div class="profile-sidebar-header">
                        <div class="profile-avatar">🏎️</div>
                        <h2 class="profile-name"><?php echo sanitize($currentUser['full_name']); ?></h2>
                        <div class="profile-email"><?php echo sanitize($currentUser['email']); ?></div>
                        <span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleName; ?></span>
                    </div>

                    <ul class="profile-nav-menu">
                        <li>
                            <a href="#overview" class="profile-nav-item tab-link <?php echo $activeTab === 'overview' ? 'active' : ''; ?>" data-tab="overview">
                                <span>📊</span> Overview & Details
                            </a>
                        </li>
                        <li>
                            <a href="#edit" class="profile-nav-item tab-link <?php echo $activeTab === 'edit' ? 'active' : ''; ?>" data-tab="edit">
                                <span>✏️</span> Edit Profile Information
                            </a>
                        </li>
                        <li>
                            <a href="#password" class="profile-nav-item tab-link <?php echo $activeTab === 'password' ? 'active' : ''; ?>" data-tab="password">
                                <span>🔒</span> Change Password
                            </a>
                        </li>
                        <li>
                            <a href="#orders" class="profile-nav-item tab-link <?php echo $activeTab === 'orders' ? 'active' : ''; ?>" data-tab="orders">
                                <span>📦</span> Order History (<?php echo count($userOrders); ?>)
                            </a>
                        </li>
                        <li style="border-top: 1px solid #edf2f7; margin-top: 8px; padding-top: 8px;">
                            <a href="cart.php" class="profile-nav-item">
                                <span>🛒</span> View Shopping Cart
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="profile-nav-item" style="color: #e53e3e;">
                                <span>🚪</span> Logout
                            </a>
                        </li>
                    </ul>
                </aside>

                <!-- Main Content Area -->
                <main class="profile-content">
                    <!-- Tab 1: Overview -->
                    <div id="tab-overview" class="profile-tab-content <?php echo $activeTab === 'overview' ? 'active' : ''; ?>">
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <h3 class="profile-card-title">👤 Account Overview</h3>
                                <div class="status-indicator">
                                    <span class="status-dot"></span> Active Member
                                </div>
                            </div>

                            <div class="profile-info-grid">
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Full Name</div>
                                    <div class="profile-info-val"><?php echo sanitize($currentUser['full_name']); ?></div>
                                </div>

                                <div class="profile-info-item">
                                    <div class="profile-info-label">Email Address</div>
                                    <div class="profile-info-val"><?php echo sanitize($currentUser['email']); ?></div>
                                </div>

                                <div class="profile-info-item">
                                    <div class="profile-info-label">Contact Phone</div>
                                    <div class="profile-info-val"><?php echo !empty($currentUser['phone']) ? sanitize($currentUser['phone']) : '<em style="color:#a0aec0;">Not set</em>'; ?></div>
                                </div>

                                <div class="profile-info-item">
                                    <div class="profile-info-label">Delivery Address</div>
                                    <div class="profile-info-val"><?php echo !empty($currentUser['address']) ? sanitize($currentUser['address']) : '<em style="color:#a0aec0;">Not set</em>'; ?></div>
                                </div>

                                <div class="profile-info-item">
                                    <div class="profile-info-label">City / District</div>
                                    <div class="profile-info-val"><?php echo !empty($currentUser['city']) ? sanitize($currentUser['city']) : '<em style="color:#a0aec0;">Not set</em>'; ?></div>
                                </div>

                                <div class="profile-info-item">
                                    <div class="profile-info-label">Postal Code</div>
                                    <div class="profile-info-val"><?php echo !empty($currentUser['postal_code']) ? sanitize($currentUser['postal_code']) : '<em style="color:#a0aec0;">Not set</em>'; ?></div>
                                </div>

                                <div class="profile-info-item">
                                    <div class="profile-info-label">Account Role</div>
                                    <div class="profile-info-val">
                                        <span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleName; ?></span>
                                    </div>
                                </div>

                                <div class="profile-info-item">
                                    <div class="profile-info-label">Member Since</div>
                                    <div class="profile-info-val"><?php echo $joinedDate; ?></div>
                                </div>
                            </div>

                            <div style="margin-top: 28px; display: flex; gap: 14px; flex-wrap: wrap;">
                                <button type="button" class="btn btn-primary tab-switch-btn" data-target="edit">✏️ Edit Profile</button>
                                <button type="button" class="btn btn-outline-dark tab-switch-btn" data-target="password">🔒 Change Password</button>
                                <a href="shop.php" class="btn btn-secondary">Browse Cars</a>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Edit Profile -->
                    <div id="tab-edit" class="profile-tab-content <?php echo $activeTab === 'edit' ? 'active' : ''; ?>">
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <h3 class="profile-card-title">✏️ Edit Profile Information</h3>
                                <span style="font-size: 13px; color: var(--text-muted);">Update your personal and shipping details</span>
                            </div>

                            <?php if ($profileSuccess): ?>
                                <div class="alert alert-success">
                                    <span>✓ <?php echo sanitize($profileSuccess); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($profileError): ?>
                                <div class="alert alert-danger">
                                    <span>⚠️ <?php echo sanitize($profileError); ?></span>
                                </div>
                            <?php endif; ?>

                            <form action="profile.php" method="POST" class="auth-form" style="max-width: 100%;">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="full_name">Full Name *</label>
                                        <input type="text" id="full_name" name="full_name" value="<?php echo sanitize($currentUser['full_name']); ?>" required>
                                    </div>

                                    <div class="form-group full-width">
                                        <label for="email">Email Address</label>
                                        <input type="email" id="email" value="<?php echo sanitize($currentUser['email']); ?>" disabled style="background: #edf2f7; cursor: not-allowed; opacity: 0.85;">
                                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">Email is linked to your account identity and cannot be altered directly.</small>
                                    </div>

                                    <div class="form-group full-width">
                                        <label for="phone">Contact Phone Number</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo sanitize($currentUser['phone'] ?? ''); ?>" placeholder="+94 77 123 4567">
                                    </div>

                                    <div class="form-group full-width">
                                        <label for="address">Delivery Address</label>
                                        <textarea id="address" name="address" rows="2" placeholder="Street Address, House/Apartment"><?php echo sanitize($currentUser['address'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="city">City / District</label>
                                        <input type="text" id="city" name="city" value="<?php echo sanitize($currentUser['city'] ?? ''); ?>" placeholder="Colombo">
                                    </div>

                                    <div class="form-group">
                                        <label for="postal_code">Postal Code</label>
                                        <input type="text" id="postal_code" name="postal_code" value="<?php echo sanitize($currentUser['postal_code'] ?? ''); ?>" placeholder="00100">
                                    </div>
                                </div>

                                <div style="margin-top: 24px; display: flex; gap: 12px;">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        💾 Save Profile Changes
                                    </button>
                                    <button type="button" class="btn btn-outline-dark tab-switch-btn" data-target="overview">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tab 3: Change Password -->
                    <div id="tab-password" class="profile-tab-content <?php echo $activeTab === 'password' ? 'active' : ''; ?>">
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <h3 class="profile-card-title">🔒 Change Account Password</h3>
                                <span style="font-size: 13px; color: var(--text-muted);">Ensure your account remains safe & protected</span>
                            </div>

                            <?php if ($passwordSuccess): ?>
                                <div class="alert alert-success">
                                    <span>✓ <?php echo sanitize($passwordSuccess); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($passwordError): ?>
                                <div class="alert alert-danger">
                                    <span>⚠️ <?php echo sanitize($passwordError); ?></span>
                                </div>
                            <?php endif; ?>

                            <form action="profile.php" method="POST" class="auth-form" style="max-width: 520px;">
                                <input type="hidden" name="action" value="change_password">

                                <div class="form-group">
                                    <label for="current_password">Current Password *</label>
                                    <input type="password" id="current_password" name="current_password" required placeholder="Enter current password">
                                </div>

                                <div class="form-group">
                                    <label for="new_password">New Password * (min 6 characters)</label>
                                    <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="Enter new password">
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password *</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Re-enter new password">
                                </div>

                                <div style="margin-top: 24px; display: flex; gap: 12px;">
                                    <button type="submit" class="btn btn-danger btn-lg">
                                        🔑 Update Password
                                    </button>
                                    <button type="button" class="btn btn-outline-dark tab-switch-btn" data-target="overview">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tab 4: Orders History -->
                    <div id="tab-orders" class="profile-tab-content <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">
                        <div class="profile-card">
                            <div class="profile-card-header">
                                <h3 class="profile-card-title">📦 Order History</h3>
                                <span style="font-size: 13px; color: var(--text-muted);"><?php echo count($userOrders); ?> order(s) placed</span>
                            </div>

                            <?php if (empty($userOrders)): ?>
                                <div style="text-align: center; padding: 40px 20px;">
                                    <div style="font-size: 48px; margin-bottom: 12px;">🏎️📦</div>
                                    <h4>No Orders Found Yet</h4>
                                    <p style="color: var(--text-muted); margin: 8px 0 20px;">You haven't placed any orders yet. Start your collection today!</p>
                                    <a href="shop.php" class="btn btn-primary">Browse Model Cars</a>
                                </div>
                            <?php else: ?>
                                <div style="overflow-x: auto;">
                                    <table class="profile-orders-table">
                                        <thead>
                                            <tr>
                                                <th>Order Number</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Payment</th>
                                                <th>Total Amount</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($userOrders as $ord): ?>
                                                <tr>
                                                    <td><strong><?php echo sanitize($ord['order_number']); ?></strong></td>
                                                    <td><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                                                    <td>
                                                        <span class="badge" style="position: static; font-size: 11px; background: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8;">
                                                            <?php echo sanitize($ord['order_status'] ?? 'Completed'); ?>
                                                        </span>
                                                    </td>
                                                    <td style="text-transform: uppercase; font-size: 12px; font-weight: 600;">
                                                        <?php echo sanitize($ord['payment_method'] ?? 'COD'); ?>
                                                    </td>
                                                    <td style="font-weight: 700; color: var(--primary-color);">
                                                        <?php echo formatPrice($ord['total_amount']); ?>
                                                    </td>
                                                    <td>
                                                        <a href="checkout.php?order_id=<?php echo urlencode($ord['order_number']); ?>" class="btn btn-outline-dark btn-sm">
                                                            View Receipt
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
                        <li><a href="profile.php">My Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
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

    <!-- Interactive Client-side Tab Switcher Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.profile-tab-content');
            const switchBtns = document.querySelectorAll('.tab-switch-btn');

            function switchTab(tabId) {
                tabLinks.forEach(link => {
                    link.classList.toggle('active', link.getAttribute('data-tab') === tabId);
                });
                tabContents.forEach(content => {
                    content.classList.toggle('active', content.id === 'tab-' + tabId);
                });
                // Update URL hash without jumping
                if (history.pushState) {
                    history.pushState(null, null, '#' + tabId);
                }
            }

            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = this.getAttribute('data-tab');
                    switchTab(target);
                });
            });

            switchBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = this.getAttribute('data-target');
                    switchTab(target);
                });
            });

            // Check URL hash on page load
            const hash = window.location.hash.replace('#', '');
            if (hash && document.getElementById('tab-' + hash)) {
                switchTab(hash);
            }
        });
    </script>
    <script src="js/script.js"></script>
</body>
</html>
