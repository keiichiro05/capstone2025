<?php 
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;



if(!isset($_SESSION['username'])){
    header("location:../index.php?status=please login first");
    exit();
}
if (isset($_SESSION['idpegawai'])) {
    $idpegawai = $_SESSION['idpegawai'];
} else {
    header("location:../index.php?status=please login first");
    exit();
}
$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
$user = mysqli_fetch_assoc($cekuser);

$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
$pegawai = mysqli_fetch_array($getpegawai);

if ($user['jmluser'] == "0") {
    header("location:../index.php");
    exit;
}

// Get filter values
$cabang_filter = isset($_GET['cabang']) ? mysqli_real_escape_string($mysqli, $_GET['cabang']) : '';
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 100;
$offset = ($current_page - 1) * $limit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Warehouse</title>
    
    <!-- CSS Links -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="../css/dashboard.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php include('styles.php'); ?>
</head>
<body class="skin-blue">
    <?php include('navbar.php'); ?>
    
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <div class="wrapper row-offcanvas row-offcanvas-left">
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
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="active">
                            <a href="stock.php">
                                <i class="fa fa-folder"></i> <span>Stock</span>
                            </a>
                        </li>
                 <li>
                    <a href="movement.php">
                        <i class="fa fa-exchange"></i> <span>Movement</span>
                        <i class=""></i>
                    </a>
                        <ul class="treeview-menu" style="<?php echo in_array($active_submenu, ['movement','movement-history','inbound','outbound']) ? 'display: block;' : ''; ?>">
                        <li class="<?php echo $active_submenu == 'movement' ? 'active' : ''; ?>">
                            <a href="movement.php?submenu=movement"><i class="fa fa-th"></i>All Movement</a>

                        <li class="<?php echo $active_submenu == 'movement-history' ? 'active' : ''; ?>">
                            <a href="movement_history.php?submenu=movement-history"><i class="fa fa-undo"></i>Movement History</a>
                        </li>
                        <li class="<?php echo $active_submenu == 'outbound' ? 'active' : ''; ?>">
                            <a href="movement_outbound.php?submenu=outbound"><i class="fa fa-sign-out "></i> Outbound</a>
                        </li>
                        <li class="<?php echo $active_submenu == 'outbound' ? 'active' : ''; ?>">
                            <a href="movement_outbound?submenu=unit"><i class="fa fa-sign-in "></i> Inbound</a>
                        </li>
                    </ul>
        </li>
                        <li>
                            <a href="product.php">
                                <i class="fa fa-list-alt"></i> <span>Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="new_request.php">
                                <i class="fa fa-plus-square"></i> <span>New Request</span>
                            </a>
                        </li>
                        <li>
                            <a href="history_request.php">
                                <i class="fa fa-archive"></i> <span>Request History</span>
                            </a>
                        </li>
                         <li>
                            <a href="sales_request.php">
                                <i class="fa fa-retweet"></i> <span>Sales Request</span>
                            </a>
                        </li>
                          <li>
                        <a href="purchase_order.php">
                            <i class="fa fa-shopping-cart"></i> <span>Purchase Orders</span>
                        </a>
                    </li>
                        <li>
                        <a href="frompurchase.php">
                            <i class="fa fa-share-square-o"></i> <span>Goods Delivered</span>
                        </a>
                    </li>
                   
                    </ul>
                </section>
            </aside>
            <aside class="right-side">
                <section class="content-header">
                    <h1>
                        Inventory Movements
                        <small>Track and manage inventory movements</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Inventory Movements</li>
                    </ol>
                </section>

                <section class="content">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if(isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">All Inventory Movements</h3>
                                    <div class="box-tools pull-right">
                                        <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <!-- Filter Form -->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form method="get" action="movement.php" class="form-inline">
                                                <div class="form-group">
                                                    <label for="cabang">Warehouse: </label>
                                                    <select name="cabang" class="form-control input-sm">
                                                        <option value="">All</option>
                                                        <?php
                                                        $warehouse_query = mysqli_query($mysqli, "SELECT nama FROM list_warehouse ORDER BY nama ASC");
                                                        while ($wh = mysqli_fetch_assoc($warehouse_query)): ?>
                                                            <option value="<?php echo htmlspecialchars($wh['nama']); ?>" <?php echo ($cabang_filter == $wh['nama'] ? 'selected' : ''); ?>>
                                                                <?php echo htmlspecialchars($wh['nama']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm" style="margin-left:10px;">
                                                    <i class="fa fa-filter"></i> Filter
                                                </button>
                                                <a href="movement.php" class="btn btn-default btn-sm" style="margin-left:10px;">
                                                    <i class="fa fa-times"></i> Clear
                                                </a>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive" style="margin-top:20px;">
                                        <table class="table table-bordered table-striped" id="movementsTable">
                                            <thead>
                                                <tr>
                                                    <th>Product ID</th>
                                                    <th>Item Name</th>
                                                    <th>Current Stock</th>
                                                    <th>Category</th>
                                                    <th>Unit</th>
                                                    <th>Reorder Level</th>
                                                    <th>Warehouse</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sql = "SELECT * FROM warehouse WHERE 1=1";
                                                if ($cabang_filter != '') {
                                                    $sql .= " AND cabang = '$cabang_filter'";
                                                }
                                                $sql .= " ORDER BY Code DESC";
                                                
                                                $hasil = $mysqli->query($sql);
                                                if ($hasil && $hasil->num_rows > 0) {
                                                    while ($baris = $hasil->fetch_assoc()) {
                                                        echo "<tr>
                                                            <td>" . htmlspecialchars($baris['Code']) . "</td>
                                                            <td>" . htmlspecialchars($baris['Nama']) . "</td>
                                                            <td>" . htmlspecialchars($baris['Stok']) . "</td>
                                                            <td>" . htmlspecialchars($baris['Kategori']) . "</td>
                                                            <td>" . htmlspecialchars($baris['Satuan']) . "</td>
                                                            <td>" . htmlspecialchars($baris['reorder_level']) . "</td>
                                                            <td>" . htmlspecialchars($baris['cabang']) . "</td>
                                                            <td>
                                                                <div class='btn-group'>
                                                                    <a href='movement_inbound.php?product_id=" . htmlspecialchars($baris['Code']) . "' class='btn btn-success btn-xs' title='Inbound'>
                                                                        <i class='fa fa-plus'></i>
                                                                    </a>
                                                                    <a href='movement_outbound.php?product_id=" . htmlspecialchars($baris['Code']) . "' class='btn btn-danger btn-xs' title='Outbound'>
                                                                        <i class='fa fa-minus'></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='8' class='text-center'>No products found</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <!-- JavaScript Libraries -->
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
    </body>
</html> 