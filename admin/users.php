<?php
$pageTitle='User Management'; $activePage='users'; require_once __DIR__.'/_header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyAdminCsrf(); $action=$_POST['action']??''; $id=(int)($_POST['id']??0);
    try {
        if ($id <= 0) throw new Exception('Invalid user.');
        if ($action === 'role') {
            $role=$_POST['role']??'customer'; if(!in_array($role,['customer','admin'],true)) throw new Exception('Invalid role.');
            if ($id === (int)$user['id'] && $role !== 'admin') throw new Exception('You cannot remove your own administrator role.');
            if ($role === 'customer') { $c=$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn(); $r=$pdo->prepare("SELECT role FROM users WHERE id=?");$r->execute([$id]); if($r->fetchColumn()==='admin' && (int)$c<=1) throw new Exception('The last administrator cannot be demoted.'); }
            $st=$pdo->prepare('UPDATE users SET role=? WHERE id=?');$st->execute([$role,$id]);setFlashMessage('success','User role updated.');
        } elseif ($action === 'delete') {
            if ($id === (int)$user['id']) throw new Exception('You cannot delete your own account while signed in.');
            $r=$pdo->prepare('SELECT role FROM users WHERE id=?');$r->execute([$id]);$target=$r->fetch();if(!$target) throw new Exception('User not found.');
            if($target['role']==='admin' && (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn()<=1) throw new Exception('The last administrator cannot be deleted.');
            $d=$pdo->prepare('DELETE FROM users WHERE id=?');$d->execute([$id]);setFlashMessage('success','User deleted. Existing orders remain in the order table.');
        } else throw new Exception('Unknown action.');
    } catch(Throwable $e) { setFlashMessage('error',$e->getMessage()); }
    redirect('users.php');
}
$users=$pdo->query('SELECT id,full_name,email,phone,role,created_at FROM users ORDER BY id DESC')->fetchAll();
?>
<div class="admin-title-row"><div><h1>User Management</h1><p>View registered customers, change roles and remove accounts safely.</p></div></div>
<div class="admin-card"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>User</th><th>Contact</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
<?php foreach($users as $u): ?><tr><td><strong><?php echo sanitize($u['full_name']); ?></strong><br><small><?php echo sanitize($u['email']); ?></small></td><td><?php echo sanitize($u['phone']??''); ?></td><td><?php echo sanitize(ucfirst($u['role'])); ?></td><td><?php echo sanitize($u['created_at']); ?></td><td><form method="post" class="admin-actions"><input type="hidden" name="csrf_token" value="<?php echo adminCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>"><input type="hidden" name="action" value="role"><select name="role" aria-label="Role for <?php echo sanitize($u['email']); ?>"><option value="customer" <?php echo $u['role']==='customer'?'selected':''; ?>>Customer</option><option value="admin" <?php echo $u['role']==='admin'?'selected':''; ?>>Admin</option></select><button class="admin-btn" type="submit">Save role</button></form><?php if((int)$u['id'] !== (int)$user['id']): ?><form method="post" class="admin-actions" onsubmit="return confirm('Delete this user account? Existing orders will be preserved.');"><input type="hidden" name="csrf_token" value="<?php echo adminCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>"><input type="hidden" name="action" value="delete"><button class="admin-btn danger" type="submit">Delete user</button></form><?php endif; ?></td></tr><?php endforeach; ?>
<?php if(!$users): ?><tr><td colspan="5" class="admin-empty">No registered users.</td></tr><?php endif; ?></tbody></table></div></div>
<?php require_once __DIR__.'/_footer.php'; ?>
