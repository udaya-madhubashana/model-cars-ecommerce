<?php
$pageTitle = 'Product Management'; $activePage = 'products';
require_once __DIR__ . '/_header.php';
$q = trim($_GET['q'] ?? '');
$params=[];
$sql="SELECT p.*, c.name category_name FROM products p JOIN categories c ON c.id=p.category_id WHERE 1=1";
if($q!==''){ $sql.=" AND (p.name LIKE :q OR p.brand LIKE :q OR p.slug LIKE :q)"; $params[':q']='%'.$q.'%'; }
$sql.=" ORDER BY p.id DESC";
$stmt=$pdo->prepare($sql); $stmt->execute($params); $products=$stmt->fetchAll();
?>
<div class="admin-title-row"><div><h1>Product Management</h1><p>Database CRUD for catalog products.</p></div><a class="admin-btn" href="product-edit.php">+ Add Product</a></div>
<div class="admin-card"><form class="admin-search" method="get"><input name="q" value="<?php echo sanitize($q); ?>" placeholder="Search by product name, brand or slug"><button class="admin-btn" type="submit">Search</button><?php if($q!==''): ?><a class="admin-btn light" href="products.php">Clear</a><?php endif; ?></form>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Image</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($products as $p): ?><tr>
<td><img class="admin-thumb" src="../<?php echo sanitize(getProductImagePath($p['image'])); ?>" alt=""></td>
<td><strong><?php echo sanitize($p['name']); ?></strong><br><small><?php echo sanitize($p['brand']); ?></small></td>
<td><?php echo sanitize($p['category_name']); ?></td><td><?php echo formatPrice($p['price']); ?></td>
<td class="<?php echo ((int)$p['stock_quantity']<=5?'stock-low':'stock-ok'); ?>"><?php echo (int)$p['stock_quantity']; ?></td>
<td><span class="status <?php echo $p['is_active'] ? 'Delivered' : 'Cancelled'; ?>"><?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
<td><div class="admin-actions"><a class="admin-btn light" href="product-edit.php?id=<?php echo (int)$p['id']; ?>">Edit</a><form method="post" action="product-action.php" style="display:inline"><input type="hidden" name="csrf_token" value="<?php echo adminCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>"><input type="hidden" name="action" value="toggle"><button class="admin-btn <?php echo $p['is_active']?'danger':'success'; ?>" type="submit"><?php echo $p['is_active']?'Deactivate':'Activate'; ?></button></form><form method="post" action="product-action.php" style="display:inline" onsubmit="return confirm('Delete this product? Products with order history will be deactivated to preserve records.');"><input type="hidden" name="csrf_token" value="<?php echo adminCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>"><input type="hidden" name="action" value="delete"><button class="admin-btn danger" type="submit">Delete</button></form></div></td>
</tr><?php endforeach; ?>
<?php if(!$products): ?><tr><td colspan="7" class="admin-empty">No products found.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once __DIR__ . '/_footer.php'; ?>
