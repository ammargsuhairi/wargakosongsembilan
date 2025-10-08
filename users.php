<?php
session_start();
require_once __DIR__ . '/inc/db.php';

// Simple auth guard
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Simple CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$errors = '';
$success = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errors = 'CSRF token invalid.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'add') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $fullname = trim($_POST['fullname'] ?? '');
            if ($username === '' || $password === '') {
                $errors = 'Username dan password wajib diisi.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, password, fullname) VALUES (:u, :p, :f)');
                try {
                    $stmt->execute(['u'=>$username,'p'=>$hash,'f'=>$fullname]);
                    $success = 'User ditambahkan.';
                } catch (PDOException $e) {
                    $errors = 'Gagal menambahkan user: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id === 0) $errors = 'ID invalid.';
            else {
                $stmt = $pdo->prepare('DELETE FROM users WHERE id=:id');
                $stmt->execute(['id'=>$id]);
                $success = 'User dihapus.';
            }
        } elseif ($action === 'reset') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id === 0) $errors = 'ID invalid.';
            else {
                $newpass = 'changeme123';
                $hash = password_hash($newpass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET password=:p WHERE id=:id');
                $stmt->execute(['p'=>$hash,'id'=>$id]);
                $success = 'Password direset ke changeme123.';
            }
        }
    }
}

// Fetch users
$stmt = $pdo->query('SELECT id, username, fullname, created_at FROM users ORDER BY id ASC');
$users = $stmt->fetchAll();

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manajemen User</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style> .table{width:100%;border-collapse:collapse} .table th,.table td{padding:8px;border-bottom:1px solid rgba(255,255,255,0.04);text-align:left} form.inline{display:inline} .muted{color:var(--muted)} </style>
</head>
<body>
<div style="max-width:1000px;margin:40px auto;padding:20px">
  <h1>Manajemen User</h1>
  <p>Akun sedang login: <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong></p>
  <p><a href="dashboard.php">Kembali ke Dashboard</a></p>

  <?php if ($errors): ?><div class="error"><?php echo htmlspecialchars($errors); ?></div><?php endif; ?>
  <?php if ($success): ?><div style="background:rgba(110,231,183,0.08);padding:10px;border-radius:8px;color:var(--accent)"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

  <h2>Daftar User</h2>
  <div class="table-responsive">
  <table class="table">
    <thead><tr><th>ID</th><th>Username</th><th>Fullname</th><th>Dibuat</th><th>Aksi</th></tr></thead>
    <tbody>
      <?php foreach($users as $u): ?>
        <tr>
          <td><?php echo $u['id']; ?></td>
          <td><?php echo htmlspecialchars($u['username']); ?></td>
          <td><?php echo htmlspecialchars($u['fullname']); ?></td>
          <td class="muted"><?php echo $u['created_at']; ?></td>
          <td>
            <form class="inline" method="post" action="">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="action" value="reset">
              <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
              <button class="button" type="submit">Reset Password</button>
            </form>
            <form class="inline" method="post" action="" onsubmit="return confirm('Hapus user ini?');">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
              <button style="background:#ff6b6b;color:#140404;border-radius:8px;padding:8px 10px;border:none;margin-left:8px" type="submit">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>

  <h2 style="margin-top:24px">Tambah User</h2>
  <form method="post" action="">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="action" value="add">
    <label>Username<br><input type="text" name="username"></label><br>
    <label>Password<br><input type="password" name="password"></label><br>
    <label>Fullname<br><input type="text" name="fullname"></label><br>
    <button class="button" type="submit">Tambah User</button>
  </form>

</div>
</body>
</html>
