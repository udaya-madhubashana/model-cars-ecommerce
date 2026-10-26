<?php
require_once __DIR__ . '/bootstrap.php';
$id=(int)($_GET['id'] ?? $_POST['id'] ?? 0);
$isEdit=$id>0;
$categories=$pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();
$product=['category_id'=>1,'brand'=>'','name'=>'','slug'=>'','scale'=>'1:64','description'=>'','price'=>'','old_price'=>'','stock_quantity'=>10,'rating'=>5,'reviews_count'=>0,'badge'=>'','image'=>'','is_featured'=>1,'is_active'=>1];
if($isEdit){$s=$pdo->prepare("SELECT * FROM products WHERE id=?");$s->execute([$id]);$product=$s->fetch();if(!$product){setFlashMessage('error','Product not found.');redirect('products.php');}}
if($_SERVER['REQUEST_METHOD']==='POST'){
 verifyAdminCsrf();
 $name=trim($_POST['name']??'');$brand=trim($_POST['brand']??'');$slug=trim($_POST['slug']??'');$desc=trim($_POST['description']??'');$price=(float)($_POST['price']??0);$old=$_POST['old_price']!==''?(float)$_POST['old_price']:null;$stock=max(0,(int)($_POST['stock_quantity']??0));$cat=(int)($_POST['category_id']??0);$scale=trim($_POST['scale']??'1:64');$badge=trim($_POST['badge']??'');$rating=(float)($_POST['rating']??5);$reviews=max(0,(int)($_POST['reviews_count']??0));$image=trim($_POST['image']??'');$featured=isset($_POST['is_featured'])?1:0;$active=isset($_POST['is_active'])?1:0;
 if($name===''||$brand===''||$slug===''||$desc===''||$price<=0||$cat<=0||$image===''){setFlashMessage('error','Please fill all required product fields.');redirect($isEdit?'product-edit.php?id='.$id:'product-edit.php');}
 try{
  if($isEdit){$sql="UPDATE products SET category_id=?,brand=?,name=?,slug=?,scale=?,description=?,price=?,old_price=?,stock_quantity=?,rating=?,reviews_count=?,badge=?,image=?,is_featured=?,is_active=? WHERE id=?";$pdo->prepare($sql)->execute([$cat,$brand,$name,$slug,$scale,$desc,$price,$old,$stock,$rating,$reviews,$badge?:null,$image,$featured,$active,$id]);setFlashMessage('success','Product updated successfully.');}
  else{$sql="INSERT INTO products (category_id,brand,name,slug,scale,description,price,old_price,stock_quantity,rating,reviews_count,badge,image,is_featured,is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";$pdo->prepare($sql)->execute([$cat,$brand,$name,$slug,$scale,$desc,$price,$old,$stock,$rating,$reviews,$badge?:null,$image,$featured,$active]);setFlashMessage('success','Product added successfully.');}
  redirect('products.php');
 }catch(Throwable $e){setFlashMessage('error','Could not save product. Check that the slug is unique.');redirect($isEdit?'product-edit.php?id='.$id:'product-edit.php');}
}
$pageTitle=$isEdit?'Edit Product':'Add Product';$activePage='products';require_once __DIR__.'/_header.php';
?>
<div class="admin-title-row"><div><h1><?php echo $isEdit?'Edit Product':'Add Product'; ?></h1><p>Save product data directly to the MySQL database.</p></div><a class="admin-btn light" href="products.php">← Back</a></div>
<div class="admin-card"><form class="admin-form" method="post"><input type="hidden" name="csrf_token" value="<?php echo adminCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo $id; ?>">
<div><label>Product Name *</label><input required name="name" value="<?php echo sanitize($product['name']); ?>"></div>
<div><label>Brand *</label><input required name="brand" value="<?php echo sanitize($product['brand']); ?>"></div>
<div><label>Slug *</label><input required name="slug" value="<?php echo sanitize($product['slug']); ?>"></div>
<div><label>Category *</label><select name="category_id" required><?php foreach($categories as $c): ?><option value="<?php echo $c['id']; ?>" <?php echo $product['category_id']==$c['id']?'selected':''; ?>><?php echo sanitize($c['name']); ?></option><?php endforeach; ?></select></div>
<div><label>Price (LKR) *</label><input type="number" step="0.01" min="0" required name="price" value="<?php echo sanitize($product['price']); ?>"></div>
<div><label>Old Price</label><input type="number" step="0.01" min="0" name="old_price" value="<?php echo sanitize($product['old_price']); ?>"></div>
<div><label>Stock Quantity *</label><input type="number" min="0" required name="stock_quantity" value="<?php echo (int)$product['stock_quantity']; ?>"></div>
<div><label>Scale</label><input name="scale" value="<?php echo sanitize($product['scale']); ?>"></div>
<div><label>Rating</label><input type="number" step="0.1" min="0" max="5" name="rating" value="<?php echo sanitize($product['rating']); ?>"></div>
<div><label>Reviews Count</label><input type="number" min="0" name="reviews_count" value="<?php echo (int)$product['reviews_count']; ?>"></div>
<div><label>Badge</label><input name="badge" value="<?php echo sanitize($product['badge']); ?>" placeholder="New / Popular / Limited"></div>
<div><label>Image Filename *</label><input required name="image" value="<?php echo sanitize($product['image']); ?>" placeholder="Porsche 911 GT3 RS.jpeg"></div>
<div class="full"><label>Description *</label><textarea required name="description"><?php echo sanitize($product['description']); ?></textarea></div>
<div class="full" style="display:flex;gap:20px;flex-wrap:wrap"><label><input type="checkbox" name="is_featured" <?php echo !empty($product['is_featured'])?'checked':''; ?>> Featured</label><label><input type="checkbox" name="is_active" <?php echo !isset($product['is_active'])||$product['is_active']?'checked':''; ?>> Active in customer catalog</label></div>
<div class="full"><button class="admin-btn" type="submit">Save Product</button></div>
</form></div>
<?php require_once __DIR__.'/_footer.php'; ?>
