<?php 
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

// Set active submenu for sidebar highlighting
$active_submenu = isset($_GET['submenu']) ? $_GET['submenu'] : 'movement';

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

// Handle stock updates for inbound
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = mysqli_real_escape_string($mysqli, $_POST['product_id']);
    $quantity = (int)$_POST['quantity'];
    $notes = mysqli_real_escape_string($mysqli, $_POST['notes'] ?? '');
    
    // Validate quantity
    if ($quantity <= 0) {
        $_SESSION['error'] = "Quantity must be greater than 0";
        header("Location: movement_inbound.php?product_id=$product_id");
        exit();
    }
    
    // Get current stock
    $current_query = mysqli_query($mysqli, "SELECT Stok, cabang FROM warehouse WHERE Code = '$product_id'");
    if (!$current_query || mysqli_num_rows($current_query) == 0) {
        $_SESSION['error'] = "Product not found";
        header("Location: movement_inbound.php");
        exit();
    }
    
    $current_data = mysqli_fetch_assoc($current_query);
    $current_stock = (int)$current_data['Stok'];
    $warehouse = $current_data['cabang'];
    
    $new_stock = $current_stock + $quantity;
    
    // Update warehouse stock
    if (!mysqli_query($mysqli, "UPDATE warehouse SET Stok = '$new_stock' WHERE Code = '$product_id'")) {
        $_SESSION['error'] = "Failed to update stock: " . mysqli_error($mysqli);
        header("Location: movement_inbound.php?product_id=$product_id");
        exit();
    }
    
    // Log the movement
    $movement_date = date('Y-m-d H:i:s');
    $insert_query = mysqli_query($mysqli, "INSERT INTO inventory_movement (product_code, movement_type, previous_stock, new_stock, quantity, movement_date, pic, warehouse, notes) 
                                VALUES ('$product_id', 'inbound', '$quantity', '$current_stock', '$new_stock', '$movement_date', '$username', '$warehouse', '$notes')");
    
    if (!$insert_query) {
        $_SESSION['error'] = "Failed to log movement: " . mysqli_error($mysqli);
        header("Location: movement_inbound.php?product_id=$product_id");
        exit();
    }
    
    $_SESSION['success'] = "Inbound movement recorded successfully!";
    header("Location: movement_inbound.php");
    exit();
}

$product_id = isset($_GET['product_id']) ? mysqli_real_escape_string($mysqli, $_GET['product_id']) : null;
$cabang_filter = isset($_GET['cabang']) ? mysqli_real_escape_string($mysqli, $_GET['cabang']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <?php include('styles.php'); ?>
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
    
    
</head>
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
                            <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
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
                        <li>
                            <a href="stock.php">
                                <i class="fa fa-folder"></i> <span>Stock</span>
                            </a>
                        </li>
                        <li class="treeview active">
                            <a href="#">
                                <i class="fa fa-exchange"></i> <span>Movement</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu" style="display: block;">
                                <li>
                                    <a href="movement.php?submenu=movement"><i class="fa fa-th"></i> All Movement</a>
                                </li>
                                <li>
                                    <a href="movement_history.php?submenu=movement-history"><i class="fa fa-undo"></i> Movement History</a>
                                </li>
                                <li class="active">
                                    <a href="movement_inbound.php?submenu=inbound"><i class="fa fa-sign-in"></i> Inbound</a>
                                </li>
                                <li>
                                    <a href="movement_outbound.php?submenu=outbound"><i class="fa fa-sign-out"></i> Outbound</a>
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
                            <a href="mailbox.php">
                                <i class="fa fa-comments"></i> <span>Mailbox</span>
                            </a>
                        </li>
                    </ul>
                </section>  
            </aside>
        
            <aside class="right-side">
                <section class="content-header">
                    <h1>
                        Inbound Movements
                        <small>Add stock to inventory</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Inbound</li>
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
                            
                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Inbound Movements</h3>
                                    <div class="box-tools pull-right">
                                        <a href="movement_history.php?type=inbound" class="btn btn-default btn-sm">
                                            <i class="fa fa-history"></i> View Inbound History
                                        </a>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <?php if ($product_id): ?>
                                        <?php
                                        $product_query = mysqli_query($mysqli, "SELECT * FROM warehouse WHERE Code = '$product_id'");
                                        if (!$product_query || mysqli_num_rows($product_query) == 0) {
                                            echo "<div class='alert alert-danger'>Product not found</div>";
                                        } else {
                                            $product = mysqli_fetch_assoc($product_query);
                                        ?>
                                        
                                        <div class="row">
                                            <div class="col-md-6 col-md-offset-3">
                                                <div class="panel panel-primary">
                                                    <div class="panel-heading">
                                                        <h3 class="panel-title">Add Inbound Movement</h3>
                                                    </div>
                                                    <div class="panel-body">
                                                        <form method="post">
                                                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">
                                                            
                                                            <div class="form-group">
                                                                <label>Product Code</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($product['Code']); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Product Name</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($product['Nama']); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Current Stock</label>
                                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($product['Stok']); ?>" readonly>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Quantity to Add *</label>
                                                                <input type="number" name="quantity" class="form-control" min="1" required>
                                                            </div>
                                                            
                                                            <div class="form-group">
                                                                <label>Notes</label>
                                                                <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this movement"></textarea>
                                                            </div>
                                                            
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fa fa-plus"></i> Add Stock
                                                            </button>
                                                            <a href="movement_inbound.php" class="btn btn-default">Cancel</a>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    <?php else: ?>
                                        <!-- Filter Form -->
                                        <div class="row">
                                            <div class="col-md-12">
                                                <form method="get" action="movement_inbound.php" class="form-inline">
                                                    <input type="hidden" name="submenu" value="inbound">
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
                                                    <a href="movement_inbound.php?submenu=inbound" class="btn btn-default btn-sm" style="margin-left:10px;">
                                                        <i class="fa fa-times"></i> Clear
                                                    </a>
                                                </form>
                                            </div>
                                        </div>
                                        
                                        <div class="table-responsive" style="margin-top:20px;">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Product ID</th>
                                                        <th>Item Name</th>
                                                        <th>Current Stock</th>
                                                        <th>Unit</th>
                                                        <th>Warehouse</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT * FROM warehouse WHERE 1=1";
                                                    if ($cabang_filter != '') {
                                                        $sql .= " AND cabang = '$cabang_filter'";
                                                    }
                                                    $sql .= " ORDER BY Nama ASC";
                                                    
                                                    $hasil = $mysqli->query($sql);
                                                    if ($hasil && $hasil->num_rows > 0) {
                                                        while ($baris = $hasil->fetch_assoc()) {
                                                            echo "<tr>
                                                                <td>" . htmlspecialchars($baris['Code']) . "</td>
                                                                <td>" . htmlspecialchars($baris['Nama']) . "</td>
                                                                <td>" . htmlspecialchars($baris['Stok']) . "</td>
                                                                <td>" . htmlspecialchars($baris['Satuan']) . "</td>
                                                                <td>" . htmlspecialchars($baris['cabang']) . "</td>
                                                                <td>
                                                                    <a href='movement_inbound.php?submenu=inbound&product_id=" . htmlspecialchars($baris['Code']) . "' class='btn btn-success btn-xs'>
                                                                        <i class='fa fa-plus'></i> Add Stock
                                                                    </a>
                                                                </td>
                                                            </tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan='6' class='text-center'>No products found</td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
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