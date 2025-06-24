<?php
session_start();

// Header keamanan
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");

// Generate CSRF Token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Log in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
</head>
<body>
<div class="split-screen">
    <div class="left-section">
        <div class="text-white text-center p-4">
            <h2 class="display-5 fw-bold">Welcome to U-PSN</h2>
            <p class="lead">Your trusted pharmaceutical network</p>
        </div>
    </div>
    <div class="right-section">
        <div class="login-container">
            <img src="img/logo.png" alt="E-pharm Logo" class="login-logo">
            <h1 class="login-header">Sign In</h1>

            <?php if (!empty($_GET['status'])): ?>
                <div class="alert alert-warning"><?= htmlspecialchars($_GET['status']) ?></div>
            <?php endif; ?>

            <form action="proses_login.php" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="userid" class="form-control" placeholder="Username" required>
                    </div>
                </div>
                <div class="form-group mb-3 password-toggle">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="password" required>
                    </div>
                </div>
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-login">Sign In</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
