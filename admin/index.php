<?php
$pageTitle = 'Admin Dashboard'; $activePage = 'dashboard';
require_once __DIR__ . '/_header.php';

$stats = ['products'=>0,'active_products'=>0,'orders'=>0,'users'=>0,'low_stock'=>0,'revenue'=>0];
$recentOrders=[];
try {
    $stats['products'] = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['active_products'] = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
    $stats['orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['users'] = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $stats['low_stock'] = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1 AND stock_quantity <= 5")->fetchColumn();
    $stats['revenue'] = (float)$pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE order_status <> 'Cancelled'")->fetchColumn();
    $recentOrders = $pdo->query("SELECT id, order_number, customer_name, total_amount, order_status, created_at FROM orders ORDER BY id DESC LIMIT 6")->fetchAll();
} catch (Throwable $e) {
    setFlashMessage('error', 'Could not load dashboard data: ' . $e->getMessage());
}
?>
<div class="admin-title-row"><div><h1>Store Admin Dashboard</h1><p>Database-backed overview of products, customers, orders and stock.</p></div><a class="admin-btn" href="product-edit.php">+ Add Product</a></div>
<div class="admin-stats">
 <div class="admin-stat"><div class="label">PRODUCTS</div><div class="value"><?php echo $stats['products']; ?></div><div class="hint"><?php echo $stats['active_products']; ?> active</div></div>
 <div class="admin-stat"><div class="label">ORDERS</div><div class="value"><?php echo $stats['orders']; ?></div><div class="hint">All customer orders</div></div>
 <div class="admin-stat"><div class="label">LOW STOCK</div><div class="value"><?php echo $stats['low_stock']; ?></div><div class="hint">5 units or less</div></div>
 <div class="admin-stat"><div class="label">CUSTOMERS</div><div class="value"><?php echo $stats['users']; ?></div><div class="hint">Registered customers</div></div>
</div>
<div class="dashboard-grid">
 <div class="admin-card"><h2>Recent Orders</h2><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead><tbody>
 <?php foreach ($recentOrders as $o): ?><tr><td><strong>#<?php echo sanitize($o['order_number']); ?></strong></td><td><?php echo sanitize($o['customer_name']); ?></td><td><?php echo formatPrice($o['total_amount']); ?></td><td><span class="status <?php echo sanitize($o['order_status']); ?>"><?php echo sanitize($o['order_status']); ?></span></td></tr><?php endforeach; ?>
 <?php if (!$recentOrders): ?><tr><td colspan="4" class="admin-empty">No orders yet.</td></tr><?php endif; ?>
 </tbody></table></div></div>
 <div class="admin-card"><h2>Quick Management</h2><div class="quick-links">
 <a class="quick-link" href="products.php"><strong>🚗 Products</strong><span>Add, edit, search or deactivate products.</span></a>
 <a class="quick-link" href="orders.php"><strong>📦 Orders</strong><span>View customers and update order status.</span></a>
 <a class="quick-link" href="inventory.php"><strong>📋 Inventory</strong><span>Monitor and update stock quantities.</span></a>
 <a class="quick-link" href="../shop.php"><strong>🛒 Storefront</strong><span>Verify admin changes on the customer catalog.</span></a>
 </div><hr style="border:0;border-top:1px solid #e2e8f0;margin:20px 0"><strong>Total non-cancelled order value:</strong><div style="font-size:25px;font-weight:900;margin-top:5px"><?php echo formatPrice($stats['revenue']); ?></div></div>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
