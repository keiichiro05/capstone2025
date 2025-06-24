<?php
require_once('../konekdb.php');
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];
?>
?>
<nav class="navbar">
    <a href="#" class="navbar-brand">Admin Warehouse</a>
    <button class="navbar-toggle">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>
    <div class="navbar-right">
        <div class="user-menu">
            <img src="../img/<?php echo htmlspecialchars($pegawai['foto']); ?>" alt="User Profile">
            <span class="user-name"><?php echo htmlspecialchars($pegawai['Nama']); ?></span>
            <a href="logout.php" class="logout-btn">
                <i class="fa fa-sign-out"></i> Logout
            </a>
        </div>
    </div>
</nav>