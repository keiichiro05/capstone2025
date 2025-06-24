<?php
// Assume these variables are set in the main script before including the header
// $username = $_SESSION['username'] ?? null;
// $idpegawai = $_SESSION['idpegawai'] ?? null;
// $pegawai = mysqli_fetch_array($getpegawai); // from getpegawai query
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warehouse Management System</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet" />
    
    <link rel="stylesheet" href="/capp/css/styles.css">
    <link rel="stylesheet" href="/capp/css/sidebar.css">
    <link rel="stylesheet" href="/capp/css/dashboard.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Add this right after the existing user dropdown in the header -->
<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-bell-o"></i>
        <?php if ($lowStockCount > 0): ?>
            <span class="label label-danger"><?php echo htmlspecialchars($lowStockCount); ?></span>
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu">
        <li class="header">You have <?php echo htmlspecialchars($lowStockCount); ?> stock alerts</li>
        <li>
            <ul class="menu">
                <?php if (!empty($lowStockItems)): ?>
                    <?php foreach ($lowStockItems as $item): ?>
                        <li>
                            <a href="stock.php">
                                <i class="fa fa-exclamation-circle text-danger"></i> 
                                <?php echo htmlspecialchars($item['nama']); ?> - 
                                Stock: <?php echo htmlspecialchars($item['stok']); ?> (Reorder_id: <?php echo htmlspecialchars($item['reorder_id_level']); ?>)
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><a href="#"><i class="fa fa-check-circle text-success"></i> No stock alerts</a></li>
                <?php endif; ?>
            </ul>
        </li>
        <li class="footer"><a href="stock.php">View all</a></li>
    </ul>
</li>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <button class="btn btn-sm d-lg-none" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($username ?? 'Guest'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <script>
            // Add this to your existing JavaScript
$(document).ready(function() {
    // Toggle notifications dropdown
    $('.notifications-menu .dropdown-toggle').click(function(e) {
        e.preventDefault();
        $(this).parent().toggleClass('open');
    });

    // Close notifications when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.notifications-menu').length) {
            $('.notifications-menu').removeClass('open');
        }
    });
});
        </script>