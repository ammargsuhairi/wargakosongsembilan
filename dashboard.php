<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = htmlspecialchars($_SESSION['user']);
// CSRF token for admin actions
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

// Ticker file path (store JSON with {level,text} or legacy plain text)
$tickerFile = __DIR__ . '/data/ticker.txt';
$tickerText = '';
$tickerLevel = 'info';
if (file_exists($tickerFile)) {
    $raw = trim(file_get_contents($tickerFile));
    // try parse JSON
    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($json['text'])) {
        $tickerText = $json['text'];
        $tickerLevel = $json['level'] ?? 'info';
    } else {
        $tickerText = $raw; // legacy
        // leave level as info by default
    }
}

// simple heuristic detector for level (very small "AI")
function detect_level($text) {
    $t = mb_strtolower($text);
    $warnKeywords = ['peringatan','warning','maintenance','error','gagal','waspada','awas'];
    foreach ($warnKeywords as $k) {
        if (mb_stripos($t, $k) !== false) return 'warn';
    }
    $infoKeywords = ['info','informasi','pengumuman','pemberitahuan'];
    foreach ($infoKeywords as $k) {
        if (mb_stripos($t, $k) !== false) return 'info';
    }
    return 'info';
}

// Handle ticker update by admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_ticker') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $msg = 'CSRF token invalid.';
    } else {
        $new = trim($_POST['ticker_text'] ?? '');
        $level = ($_POST['ticker_level'] ?? 'auto');
        if ($level === 'auto') {
            $level = detect_level($new);
        }
        if ($new === '') {
            // delete ticker file to hide ticker
            if (file_exists($tickerFile)) unlink($tickerFile);
            $tickerText = '';
            $tickerLevel = 'info';
        } else {
            $save = json_encode(['level'=>$level,'text'=>$new], JSON_UNESCAPED_UNICODE);
            file_put_contents($tickerFile, $save);
            $tickerText = $new;
            $tickerLevel = $level;
        }
        $msg = 'Ticker berhasil diperbarui.';
    }
}
?>
<!doctype html>
<html lang="id">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>KURMA - DASHBOARD</title>
        <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="topbar">
        <div class="brand">KARANG TARUNA 09</div>
        <div>
            <span style="margin-right:12px">Halo, <strong><?php echo $user; ?></strong></span>
            <a href="logout.php" style="color:var(--muted);text-decoration:none">Logout</a>
        </div>
    </div>

    <div class="dashboard-wrap">
        <?php if (trim($tickerText) !== ''): ?>
        <div class="ticker-wrapper <?php echo ($tickerLevel === 'warn') ? 'ticker-warn' : 'ticker-info'; ?>">
            <div class="ticker">
                <div class="ticker-inner"><?php echo htmlspecialchars($tickerText); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($user === 'admin'): ?>
            <div style="margin:12px 0" class="ticker-editor">
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="update_ticker">
                    <label style="display:block;margin-bottom:6px;color:var(--muted)"><strong>Ubah Pemberitahuan Ticker (public):</strong></label>
                    <textarea name="ticker_text" rows="2"><?php echo htmlspecialchars($tickerText); ?></textarea>
                    <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">
                        <label style="color:var(--muted);font-size:13px">Level:
                          <select name="ticker_level" style="margin-left:6px;padding:6px;border-radius:6px;background:rgba(255, 255, 255, 1);border:1px solid rgba(255, 255, 255, 1)">
                            <option value="auto">Auto (deteksi)</option>
                            <option value="info" <?php echo ($tickerLevel==='info')?'selected':''; ?>>Info</option>
                            <option value="warn" <?php echo ($tickerLevel==='warn')?'selected':''; ?>>Peringatan</option>
                          </select>
                        </label>
                        <div style="flex:1"></div>
                        <div><button class="button" type="submit">Simpan Pemberitahuan</button></div>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        <div class="card-d">
            <h2>Selamat datang, <?php echo $user; ?>!</h2>
            <?php if ($user === 'admin'): ?>
                <div style="margin-top:8px"><span class="admin-badge">Administrator (Superuser)</span></div>
                <p style="margin-top:12px;color:var(--muted)">Anda memiliki akses penuh: Menambahkan Username untuk Warga, Verifikasi Keuangan IURAN, dan Membuat Laporan Lengkap.</p>
            <?php else: ?>
                <div style="margin-top:8px"><span class="user-badge">Hallo Bapak/Ibu</span></div>
                <p style="margin-top:12px;color:var(--muted)">Hari ini semoga selalu ceria. Bapak/Ibu dapat melihat informasi dan bertransaksi untuk kas warga yang tersedia untuk aktifitas oleh RT/RW.</p>
            <?php endif; ?>
        </div>

        <?php if ($user === 'admin'): ?>
            <div class="grid">
                <div class="card-d">
                    <h3>Manajemen User</h3>
                    <p class="muted">Tambahkan, edit, atau hapus akun pengguna.</p>
                    <p><a href="users.php">Buka Manajemen User</a></p>
                </div>
                <div class="card-d">
                    <h3>Statistik Sistem</h3>
                    <p class="muted">Ringkasan penggunaan, log, dan kesehatan sistem.</p>
                </div>
                <div class="card-d">
                    <h3>Pengaturan</h3>
                    <p class="muted">Pengaturan aplikasi dan konfigurasi penting.</p>
                    <p><a href="pengaturan.php" target=_blank>Buka Pengaturan</a></p>
                </div>
                <div class="card-d">
                    <h3>VERIFIKASI IURAN WARGA</h3>
                    <p class="muted">Iuran warga real-time untuk di verifikasi!</p>
                    <p><a href="verifikasiiuran.php">Buka Verifikasi Iuran</a></p>
                </div>
            </div>
        <?php else: ?>
            <div class="grid">
                <div class="card-d">
                    <h3>Profil Anda</h3>
                    <p class="muted">Lihat dan perbarui informasi akun dasar Anda.</p>
                </div>
                <div class="card-d">
                    <h3>Aktivitas Terbaru</h3>
                    <p class="muted">Ringkasan aktivitas terakhir yang relevan untuk Anda.</p>
                </div>
                <div class="card-d">
                    <h3>Bantuan</h3>
                    <p class="muted">Panduan singkat untuk menggunakan dashboard.</p>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
