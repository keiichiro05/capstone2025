<?php
session_start();
include('../konekdb.php');

// Check login
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'] ?? null;

// Check module authorization
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Warehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($jmluser);
$stmt->fetch();
$stmt->close();

if ($jmluser == 0) {
    header("Location: ../index.php");
    exit();
}

// Get employee data
$stmt = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmt->bind_param("s", $idpegawai);
$stmt->execute();
$result = $stmt->get_result();
$pegawai = $result->fetch_assoc();
$stmt->close();

// Count new arrivals (status = 2)
$new_arrivals_count = 0;
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM pemesanan WHERE status = 2");
$stmt->execute();
$stmt->bind_result($new_arrivals_count);
$stmt->fetch();
$stmt->close();

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $order_id = $_POST['order_id'];
    $quantity = (int)$_POST['quantity'];
    $warehouse = $_POST['warehouse'];
    
    // Get warehouse PIC
    $stmt = $mysqli->prepare("SELECT pic FROM list_warehouse WHERE nama = ?");
    $stmt->bind_param("s", $warehouse);
    $stmt->execute();
    $stmt->bind_result($warehouse_pic);
    $stmt->fetch();
    $stmt->close();
    
    // Get order details
    $stmt = $mysqli->prepare("SELECT item_name, category, unit, supplier_name FROM pemesanan WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if ($order) {
        // Check if item exists in warehouse
        $stmt = $mysqli->prepare("SELECT Code, Stok FROM warehouse WHERE Nama = ? AND warehouse = ?");
        $stmt->bind_param("ss", $order['item_name'], $warehouse);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_item = $result->fetch_assoc();
        $stmt->close();
        
        if ($existing_item) {
            // Update existing item
            $new_stock = $existing_item['Stok'] + $quantity;
            $stmt = $mysqli->prepare("UPDATE warehouse SET Stok = ?, Kategori = ?, Satuan = ?, Supplier = ? WHERE Code = ?");
            $stmt->bind_param("isssi", $new_stock, $order['category'], $order['unit'], $order['supplier_name'], $existing_item['Code']);
            $update_success = $stmt->execute();
            $stmt->close();
        } else {
            // Insert new item
            $code = time(); // Simple way to generate unique code
            $stmt = $mysqli->prepare("INSERT INTO warehouse (Code, Nama, Stok, Kategori, Satuan, Supplier, warehouse, Tanggal) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issssss", $code, $order['item_name'], $quantity, $order['category'], $order['unit'], $order['supplier_name'], $warehouse);
            $update_success = $stmt->execute();
            $stmt->close();
        }
        
        if ($update_success) {
            // Record movement
            $movement_type = 'inbound';
            $notes = "Stock arrival from purchase order #$order_id";
            $stmt = $mysqli->prepare("INSERT INTO inventory_movement (product_code, movement_type, previous_stock, new_stock, warehouse, pic, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $previous_stock = $existing_item['Stok'] ?? 0;
            $new_stock = $previous_stock + $quantity;
            $stmt->bind_param("ssiisss", $order['item_name'], $movement_type, $previous_stock, $new_stock, $warehouse, $warehouse_pic, $notes);
            $stmt->execute();
            $stmt->close();
            
            // Update order status to 3 (processed)
            $stmt = $mysqli->prepare("UPDATE pemesanan SET status = 3, approved_by = ?, approved_date = NOW() WHERE order_id = ?");
            $stmt->bind_param("si", $username, $order_id);
            $stmt->execute();
            $stmt->close();
            
            $_SESSION['message'] = "<div class='alert alert-success'>Stock updated successfully in $warehouse warehouse!</div>";
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger'>Failed to update stock.</div>";
        }
    }
    
    header("Location: frompurchase.php");
    exit();
}

// Get all pending arrivals (status = 2)
$pending_arrivals = [];
$stmt = $mysqli->prepare("SELECT * FROM pemesanan WHERE status = 2 ORDER BY order_date DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pending_arrivals[] = $row;
}
$stmt->close();

// Determine active submenu
$active_submenu = $_GET['submenu'] ?? 'pending';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Warehouse</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- CSS Links -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
<style>
    /* Layout and Structure */
    .submenu-content { 
        padding: 20px 0; 
    }
    
    .table-container {
        width: 100%;
        max-width: 98%;
        margin: 0 auto;
        overflow: hidden;
    }
    
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 auto;
        display: block;
    }

    /* Table Styling */
    .table-3d {
        width: 100%;
        margin: 0 auto;
        border-collapse: collapse;
        table-layout: auto;
    }
    
    .table-3d th {
        background-color: #3c8dbc;
        color: white;
        text-align: left;
        padding: 12px;
        white-space: nowrap;
    }
    
    .table-3d td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
        white-space: nowrap;
    }
    
    .table-3d tr:hover {
        background-color: #f5f5f5;
    }

    /* Treeview Menu */
    .sidebar-menu > li > a {
        position: relative;
        display: block;
        padding: 12px 5px 12px 15px;
    }
    
    .sidebar-menu li > a > .fa-angle-left {
        width: auto;
        height: auto;
        padding: 0;
        margin-right: 10px;
        margin-top: 3px;
        transition: transform 0.3s ease;
    }
    
    .sidebar-menu li.active > a > .fa-angle-left {
        transform: rotate(-90deg);
    }
    
    .treeview-menu {
        display: none;
        list-style: none;
        padding: 0;
        margin: 0;
        padding-left: 15px;
    }
    
    .treeview-menu > li {
        margin: 0;
    }
    
    .treeview-menu > li > a {
        padding: 8px 5px 8px 25px;
        display: block;
        font-size: 14px;
    }
    
    .treeview-menu > li > a > .fa {
        width: 20px;
    }
    
    .treeview-menu > li > a > .fa-circle-o {
        font-size: 10px;
    }
    
    .treeview-menu > li.active > a {
        font-weight: bold;
        color: #fff;
        background: rgba(255,255,255,0.1);
    }

    /* Buttons and Forms */
    .nav-pills { 
        margin-bottom: 20px; 
    }
    
    .btn-action { 
        padding: 5px 10px; 
        font-size: 12px; 
    }
    
    .btn-edit { 
        background-color: #f0ad4e; 
        border-color: #eea236; 
        color: white; 
    }
    
    .btn-delete { 
        background-color: #d9534f; 
        border-color: #d43f3a; 
        color: white; 
    }
    
    .input-group { 
        width: auto; 
        display: inline-flex; 
    }
    
    .is-invalid { 
        border-color: #dc3545 !important; 
    }

    /* Action Buttons */
    .action-buttons {
        white-space: nowrap;
    }
    
    /* Warehouse Dropdown */
    .warehouse-select {
        min-width: 200px;
        margin-right: 5px;
    }

    /* Notification Badge */
    .notification-badge {
        position: absolute;
        top: 5px;
        right: 5px;
        font-size: 10px;
        background-color: #ff5722;
        color: white;
        border-radius: 50%;
        padding: 3px 6px;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .table-3d {
            width: 100%;
            display: block;
        }
        
        .table-3d th, 
        .table-3d td {
            padding: 8px;
            font-size: 14px;
        }
        
        .warehouse-select {
            min-width: 150px;
        }
    }
</style>
    <?php include('styles.php'); ?>
</head>
<body class="skin-blue">
    <?php include('navbar.php'); ?>
    
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
                    <li>
                        <a href="stock.php">
                            <i class="fa fa-folder"></i> <span>Stock</span>
                        </a>
                    </li>
                    <li>
                        <a href="movement.php">
                            <i class="fa fa-exchange"></i> <span>Movement</span>
                        </a>
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
                    <li >
                        <a href="sales_request.php">
                            <i class="fa fa-retweet"></i> <span>Sales Request</span>
                        </a>
                    </li>
                      <li>
                        <a href="purchase_order.php">
                            <i class="fa fa-shopping-cart"></i> <span>Purchase Orders</span>
                        </a>
                    </li>
        
                <li class="treeview active">
                    <a href="#">
                        <i class="fa fa-truck"></i> <span>Goods Delivered</span>
                        <?php if ($new_arrivals_count > 0): ?>
                            <span class="notification-badge"><?php echo $new_arrivals_count; ?></span>
                        <?php endif; ?>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu" style="display: block;">
                        <li class="<?php echo $active_submenu == 'pending' ? 'active' : ''; ?>">
                            <a href="frompurchase.php?submenu=pending">
                                <i class="fa fa-clock-o"></i> Pending Arrivals
                                <?php if ($new_arrivals_count > 0): ?>
                                    <span class="badge pull-right bg-red"><?php echo $new_arrivals_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="<?php echo $active_submenu == 'history' ? 'active' : ''; ?>">
                            <a href="frompurchase.php?submenu=history"><i class="fa fa-history"></i> Update History</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </section>
    </aside>
    
    <aside class="right-side">
        <section class="content-header">
            <h1>
                Stock Update Management
                <small>Process incoming stock from purchasing</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="frompurchase.php"><i class="fa fa-truck"></i> Stock Update</a></li>
                <li class="active">
                    <?php 
                        $submenu_titles = [
                            'pending' => 'Pending Arrivals',
                            'history' => 'Update History'
                        ];
                        echo $submenu_titles[$active_submenu] ?? 'Stock Update';
                    ?>
                </li>
            </ol>
        </section>
        
        <section class="content">
            <?php
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']);
            }
            ?>
            
            <div class="submenu-content">
                <?php if ($active_submenu == 'pending'): ?>
                    <!-- Pending Arrivals Content -->
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Pending Stock Arrivals</h3>
                            <div class="box-tools pull-right">
                                <span class="label label-danger"><?php echo count($pending_arrivals); ?> Items</span>
                            </div>
                        </div>
                        <div class="box-body">
                            <?php if (empty($pending_arrivals)): ?>
                                <div class="alert alert-info">No pending stock arrivals at this time.</div>
                            <?php else: ?>
                               <div class="table-container">
    <div class="table-responsive">
        <table class="table table-3d">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Item Name</th>
                                                <th>Category</th>
                                                <th>Quantity</th>
                                                <th>Unit</th>
                                                <th>Supplier</th>
                                                <th>Order Date</th>
                                                <th>Delivery Date</th>
                                                <th>Process</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pending_arrivals as $arrival): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($arrival['order_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($arrival['item_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($arrival['category']); ?></td>
                                                    <td><?php echo htmlspecialchars($arrival['quantity']); ?></td>
                                                    <td><?php echo htmlspecialchars($arrival['unit']); ?></td>
                                                    <td><?php echo htmlspecialchars($arrival['supplier_name']); ?></td>
                                                    <td><?php echo date('d M Y', strtotime($arrival['order_date'])); ?></td>
                                                    <td><?php echo $arrival['delivery_date'] ? date('d M Y', strtotime($arrival['delivery_date'])) : 'N/A'; ?></td>
                                                    <td class="action-buttons">
                                                        <form method="post" style="display:inline;">
                                                            <input type="hidden" name="order_id" value="<?php echo $arrival['order_id']; ?>">
                                                            <input type="hidden" name="quantity" value="<?php echo $arrival['quantity']; ?>">
                                                            <div class="input-group">
                                                                <select name="warehouse" class="form-control input-sm warehouse-select" required>
                                                                    <option value="">Select Warehouse</option>
                                                                    <?php
                                                                    $warehouses = $mysqli->query("SELECT nama, pic FROM list_warehouse ORDER BY nama");
                                                                    while ($wh = $warehouses->fetch_assoc()): ?>
                                                                        <option value="<?php echo htmlspecialchars($wh['nama']); ?>">
                                                                            <?php echo htmlspecialchars($wh['nama']); ?> (PIC: <?php echo htmlspecialchars($wh['pic']); ?>)
                                                                        </option>
                                                                    <?php endwhile; ?>
                                                                </select>
                                                                <span class="input-group-btn">
                                                                    <button type="submit" name="update_stock" class="btn btn-success btn-sm">
                                                                        <i class="fa fa-check"></i> Confirm
                                                                    </button>
                                                                </span>
                                                            </div>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                
                <?php elseif ($active_submenu == 'history'): ?>
                    <!-- Update History Content -->
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Stock Update History</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-3d">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Item Name</th>
                                            <th>Quantity</th>
                                            <th>Warehouse</th>
                                            <th>Processed By</th>
                                            <th>Processed Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $history = [];
                                        $stmt = $mysqli->prepare("SELECT p.*, w.warehouse FROM pemesanan p 
                                            LEFT JOIN warehouse w ON p.item_name = w.Nama 
                                            WHERE p.status = 3 
                                            ORDER BY p.approved_date DESC LIMIT 50");
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        while ($row = $result->fetch_assoc()) {
                                            $history[] = $row;
                                        }
                                        $stmt->close();
                                        
                                        foreach ($history as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['order_id']); ?></td>
                                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($item['warehouse'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($item['approved_by']); ?></td>
                                                <td><?php echo date('d M Y H:i', strtotime($item['approved_date'])); ?></td>
                                                <td><span class="label label-success">Processed</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<script src="../js/AdminLTE.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
    // Initialize sidebar treeview
    $('.sidebar-menu').tree();
    
    // Auto-refresh the page every 5 minutes to check for new arrivals
    setTimeout(function() {
        location.reload();
    }, 300000); // 5 minutes
    
    // Confirm before updating stock
    $('form').on('submit', function(e) {
        if ($(this).find('button[name="update_stock"]').length) {
            var warehouse = $(this).find('select[name="warehouse"]').val();
            if (!warehouse) {
                alert('Please select a warehouse first');
                e.preventDefault();
                return false;
            }
            
            if (!confirm('Are you sure you want to confirm this stock arrival and update inventory in ' + warehouse + ' warehouse?')) {
                e.preventDefault();
            }
        }
    });
});
</script>
</body>
</html>