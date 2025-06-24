<?php
require_once('../konekdb.php');
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];
?>

<aside class="left-side sidebar-offcanvas">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <img src="../img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
            </div>
            <div class="pull-left info">
                <p>Hello, <?php echo htmlspecialchars($username); ?></p>
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li>
                <a href="streamlit.php">
                    <i class="fa fa-signal"></i> <span>Analytics</span>
                </a>
            </li>
            <li class="active">
                <a href="dashboard.php">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="list_request.php">
                    <i class="fa fa-list"></i> <span>List Request</span>
                </a>
            </li>
            <li>
                <a href="daftarACC.php">
                    <i class="fa fa-undo"></i> <span>Request History</span>
                </a>
            </li>
            <li>
                <a href="frompurchase.php">
                    <i class="fa fa-tasks"></i> <span>Purchase Order</span>
                </a>
            </li>
            <li>
                <a href="stock.php">
                   <i class="fa fa-archive"></i> <span>Inventory</span>
                </a>
            </li>
 
        </ul>
    </section>
</aside>