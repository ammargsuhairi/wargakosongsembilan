<?php
session_start();
require_once __DIR__ . '/inc/db.php';

$errors = '';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors = 'Isi username dan password.';
    } else {
        // Cari user di database
        $stmt = $pdo->prepare('SELECT id, username, password, fullname FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors = 'Username atau password salah.';
        } else {
            // sukses
            $_SESSION['user'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            header('Location: dashboard.php');
            exit;
        }
    }
}

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="page-center">
<div class="container">
    <div class="hero">
        <h1>Selamat datang di OKEDEH</h1>
        <p>Silahkan masuk untuk melihat transparasi antar transaksi khusus lingkungan.</p>
    </div>

    <div class="card">
        <div class="form-title">
            <h2>Login</h2>
        </div>

        <?php if ($errors): ?>
            <div class="error"><?php echo htmlspecialchars($errors); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['user'])): ?>
            <p>Anda sudah login sebagai <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong>.</p>
            <p><a href="dashboard.php">Ke Dashboard</a> | <a href="logout.php">Logout</a></p>
        <?php else: ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password">
                </div>

                <button class="button" type="submit">Login</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</div>
</body>
</html>