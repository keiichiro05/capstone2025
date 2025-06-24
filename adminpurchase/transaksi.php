<?php
// transaksi.php
// Purchase Approval Page - Final Version

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include('../konekdb.php');

// Check database connection
if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Get user info from session
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check user authorization for Purchase module
$cekuser = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Purchase'");
$cekuser->bind_param("s", $username);
$cekuser->execute();
$user = $cekuser->get_result()->fetch_assoc();
$cekuser->close();

if ($user['jmluser'] == "0") {
    $_SESSION['error'] = "You don't have permission to access this page.";
    header("location:../index.php");
    exit();
}

// Get employee data for profile display
$getpegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$getpegawai->bind_param("s", $idpegawai);
$getpegawai->execute();
$pegawai = $getpegawai->get_result()->fetch_assoc();
$getpegawai->close();

// Set display variables for header
$displayUsername = htmlspecialchars($pegawai['Nama'] ?? '');
$displayPegawaiFoto = htmlspecialchars($pegawai['foto'] ?? 'default.png');
$displayPegawaiNama = htmlspecialchars($pegawai['Nama'] ?? '');
$displayPegawaiJabatan = htmlspecialchars($pegawai['Jabatan'] ?? '');
$displayPegawaiDepartemen = htmlspecialchars($pegawai['Departemen'] ?? '');
$displayPegawaiTanggalMasuk = isset($pegawai['tanggal_masuk']) ? date('M Y', strtotime($pegawai['tanggal_masuk'])) : '';

// Get pending order_ids count for notification
$not2 = $mysqli->query("SELECT COUNT(order_id) as jml FROM pemesanan1 WHERE status='pending'");
$tot2 = $not2->fetch_assoc();
$not2->close();

// Handle form submission for approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['order_id'])) {
        $_SESSION['error'] = "Invalid request: order_id ID missing.";
        header("location:transaksi.php");
        exit();
    }

    $order_id = $_POST['order_id'];

    if (isset($_POST['approve'])) {
        if (!isset($_POST['manual_price']) || !isset($_POST['quantity'])) {
            $_SESSION['error'] = "Required fields are missing.";
            header("location:transaksi.php");
            exit();
        }

        $manual_price = str_replace(['.', ','], '', $_POST['manual_price']);
        $manual_price = floatval($manual_price);
        $quantity = intval($_POST['quantity']);

        if (!is_numeric($manual_price) || $manual_price <= 0) {
            $_SESSION['error'] = "Price must be a positive number.";
            header("location:transaksi.php");
            exit();
        }
        
        $total_price = $manual_price * $quantity;
        $approved_by = $idpegawai;
        $approved_date = date('Y-m-d H:i:s');

        $stmt = $mysqli->prepare("UPDATE pemesanan1 SET status='accepted', price=?, total_price=?, approved_by=?, approved_date=? WHERE order_id=?");
        if ($stmt) {
            $stmt->bind_param("ddsss", $manual_price, $total_price, $approved_by, $approved_date, $order_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Purchase order_id #" . htmlspecialchars($order_id) . " approved successfully.";
            } else {
                $_SESSION['error'] = "Failed to approve order_id: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Database error: " . $mysqli->error;
        }

    } elseif (isset($_POST['reject'])) {
        $rejected_by = $idpegawai;
        $rejected_date = date('Y-m-d H:i:s');

        $stmt = $mysqli->prepare("UPDATE pemesanan1 SET status='denied', rejected_by=?, rejected_date=? WHERE order_id=?");
        if ($stmt) {
            $stmt->bind_param("sss", $rejected_by, $rejected_date, $order_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Purchase order_id #" . htmlspecialchars($order_id) . " rejected.";
            } else {
                $_SESSION['error'] = "Failed to reject order_id: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Database error: " . $mysqli->error;
        }
    }
    header("location:transaksi.php");
    exit();
}

// Get filter parameters from GET request
$search = isset($_GET['search']) ? $_GET['search'] : '';
$supplier = isset($_GET['supplier']) ? $_GET['supplier'] : '';
$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : '';
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : '';

// Build the query with filters
$query = "SELECT p.*, s.nama_perusahaan FROM pemesanan1 p 
          LEFT JOIN supplier s ON p.supplier_id = s.id_supplier
          WHERE p.status='pending'";

$params = array();
$types = '';

// Add search filter
if (!empty($search)) {
    $query .= " AND (p.order_id LIKE ? OR p.item_name LIKE ? OR s.nama_perusahaan LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

// Add supplier filter
if (!empty($supplier)) {
    $query .= " AND s.nama_perusahaan = ?";
    $params[] = $supplier;
    $types .= 's';
}

// Add date range filter
if (!empty($dateFrom)) {
    $query .= " AND p.order_date >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}
if (!empty($dateTo)) {
    $query .= " AND p.order_date <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

$query .= " ORDER BY p.order_date DESC";

// Prepare and execute the query with filters
$stmt = $mysqli->prepare($query);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $show_pending_order_ids = $stmt->get_result();
} else {
    die("Error preparing statement: " . $mysqli->error);
}

// Get total pending value with filters
$total_query = "SELECT SUM(total_price) as total FROM pemesanan1 p 
                LEFT JOIN supplier s ON p.supplier_id = s.id_supplier
                WHERE p.status='pending'";

if (!empty($search)) {
    $total_query .= " AND (p.order_id LIKE ? OR p.item_name LIKE ? OR s.nama_perusahaan LIKE ?)";
}
if (!empty($supplier)) {
    $total_query .= " AND s.nama_perusahaan = ?";
}
if (!empty($dateFrom)) {
    $total_query .= " AND p.order_date >= ?";
}
if (!empty($dateTo)) {
    $total_query .= " AND p.order_date <= ?";
}

$total_stmt = $mysqli->prepare($total_query);
if ($total_stmt) {
    if (!empty($params)) {
        $total_stmt->bind_param($types, ...$params);
    }
    $total_stmt->execute();
    $total_pending_value = $total_stmt->get_result();
    $pending_value = $total_pending_value->fetch_assoc();
    $total_stmt->close();
} else {
    die("Error preparing total statement: " . $mysqli->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>U-PSN | Purchase Approval</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/ionicons.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
        }
        .dashboard-section {
            margin-bottom: 20px;
            border_id: 1px solid #e0e0e0;
            border_id-radius: 4px;
            padding: 15px;
            background: white;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }
        .dashboard-section h3 {
            margin-top: 0;
            border_id-bottom: 1px solid #eee;
            padding-bottom: 10px;
            color: #3c8dbc;
            font-size: 20px;
        }
        .info-box {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 15px;
            border_id-radius: 8px;
            color: white;
            min-height: 90px;
        }
        .info-box .info-box-icon {
            font-size: 45px;
            width: 70px;
            text-align: center;
            line-height: 70px;
            background: rgba(0,0,0,0.2);
            border_id-radius: 4px;
            padding: 10px;
        }
        .info-box .info-box-content {
            flex-grow: 1;
            padding-left: 15px;
        }
        .info-box .info-box-text {
            font-size: 16px;
            margin-bottom: 5px;
            opacity: 0.9;
        }
        .info-box .info-box-number {
            font-size: 24px;
            font-weight: bold;
        }
        .bg-aqua { background-color: #00c0ef !important; }
        .bg-green { background-color: #00a65a !important; }
        .bg-yellow { background-color: #f39c12 !important; }
        .bg-blue { background-color: #3c8dbc !important; }
        
        .table-responsive {
            border_id-radius: 4px;
            overflow: hidden;
        }
        .table thead th {
            background-color: #3c8dbc;
            color: white;
            vertical-align: middle;
        }
        .price-input {
            text-align: right;
            width: 120px;
        }
        .btn-action {
            padding: 5px 10px;
            font-size: 12px;
            border_id-radius: 3px;
            margin: 2px;
            min-width: 70px;
        }
        .filter-controls {
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border_id-radius: 4px;
        }
        .filter-group {
            margin-right: 15px;
            display: inline-block;
            vertical-align: top;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: normal;
        }
        .form-control {
            height: 30px;
            padding: 3px 6px;
        }
        .table > tbody > tr > td {
            vertical-align: middle;
        }
        .input-group-addon {
            padding: 3px 6px;
            font-size: 12px;
        }
        .btn-filter {
            margin-top: 23px;
        }
        @media (max-width: 768px) {
            .filter-group {
                display: block;
                margin-bottom: 10px;
                margin-right: 0;
            }
            #searchInput {
                width: 100% !important;
            }
            .btn-filter {
                margin-top: 0;
            }
        }
    </style>
</head>
<body class="skin-blue">
    <div class="wrapper">
        <!-- Header -->
        <header class="header">
            <a href="index.php" class="logo">U-PSN</a>
            <nav class="navbar navbar-static-top" role="navigation">
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?= $displayUsername; ?> <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header bg-light-blue">
                                    <img src="../img/<?= $displayPegawaiFoto; ?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?= $displayPegawaiNama . " - " . $displayPegawaiJabatan . " " . $displayPegawaiDepartemen; ?>
                                        <small>Member since <?= $displayPegawaiTanggalMasuk; ?></small>
                                    </p>
                                </li>
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="../logout.php" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- Sidebar -->
        <aside class="left-side sidebar-offcanvas">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="../img/<?= $displayPegawaiFoto; ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?= $displayPegawaiNama; ?></p>
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
                        <a href="supplier.php">
                            <i class="fa fa-truck"></i> <span>Supplier</span>
                        </a>
                    </li>
                    <li>
                        <a href="pemesanan.php">
                            <i class="fa fa-shopping-cart"></i> <span>order_ids</span>
                        </a>
                    </li>
                    <li>
                        <li class="active">
                        <a href="transaksi.php">
                            <i class="fa fa-check-square"></i> <span>Transaction Approval</span>
                        </a>
                    </li>
                    <li>
                        <a href="received.php">
                            <i class="fa fa-shopping-cart"></i> <span>Received Item</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan.php">
                           <i class="fa fa-file-text"></i> <span>Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="cuti.php">
                            <i class="fa fa-calendar-times-o"></i> <span>Leave Requests</span>
                        </a>
                    </li>
                    <li>
                        <a href="mailbox.php">
                            <i class="fa fa-envelope"></i> <span>Mailbox</span>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>

        <!-- Content -->
        <div class="right-side">
            <section class="content-header">
                <h1>
                    Purchase Approval
                    <small>Review and approve purchase order_ids</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="../index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Purchase Approval</li>
                </ol>
            </section>

            <section class="content">
                <!-- Notifications -->
                <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissable">
                    <i class="fa fa-check"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissable">
                    <i class="fa fa-ban"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>
                
                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-lg-6 col-xs-12">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-hourglass-half"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pending Approvals</span>
                                <span class="info-box-number"><?= $show_pending_order_ids->num_rows; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6 col-xs-12">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-money"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pending Value</span>
                                <span class="info-box-number">Rp <?= number_format($pending_value['total'] ?? 0, 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Table -->
                <div class="row">
                    <div class="col-xs-12">
                        <div class="dashboard-section">
                            <h3><i class="fa fa-hourglass-half"></i> Pending Purchase order_ids</h3>
                            
                            <!-- Filter Controls -->
                            <form method="GET" action="transaksi.php" class="filter-controls">
                                <div class="filter-group">
                                    <label>Search:</label>
                                    <input type="text" name="search" id="searchInput" class="form-control" placeholder="Search all columns..." 
                                           value="<?= htmlspecialchars($search); ?>" style="width: 200px;">
                                </div>
                                <div class="filter-group">
                                    <label>Supplier:</label>
                                    <select name="supplier" id="supplierFilter" class="form-control">
                                        <option value="">All Suppliers</option>
                                        <?php
                                        $suppliers = $mysqli->query("SELECT DISTINCT nama_perusahaan FROM supplier ORDER BY nama_perusahaan");
                                        while($supplier = $suppliers->fetch_assoc()) {
                                            $selected = ($supplier['nama_perusahaan'] == $supplier) ? 'selected' : '';
                                            echo '<option value="'.htmlspecialchars($supplier['nama_perusahaan']).'" '.$selected.'>'.htmlspecialchars($supplier['nama_perusahaan']).'</option>';
                                        }
                                        $suppliers->close();
                                        ?>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label>Date Range:</label>
                                    <div style="display: flex;">
                                        <input type="date" name="dateFrom" id="dateFrom" class="form-control" 
                                               value="<?= htmlspecialchars($dateFrom); ?>" placeholder="From" style="margin-right: 5px;">
                                        <input type="date" name="dateTo" id="dateTo" class="form-control" 
                                               value="<?= htmlspecialchars($dateTo); ?>" placeholder="To">
                                    </div>
                                </div>
                                <div class="filter-group">
                                    <button type="submit" class="btn btn-primary btn-filter">
                                        <i class="fa fa-filter"></i> Apply Filters
                                    </button>
                                    <?php if (!empty($search) || !empty($supplier) || !empty($dateFrom) || !empty($dateTo)): ?>
                                    <a href="transaksi.php" class="btn btn-default btn-filter">
                                        <i class="fa fa-times"></i> Clear Filters
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                            
                            <div class="table-responsive">
                                <table class="table table-border_ided table-hover" id="order_idsTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>order_id ID</th>
                                            <th>Item</th>
                                            <th>Supplier</th>
                                            <th>Qty</th>
                                            <th>Unit</th>
                                            <th>order_id Date</th>
                                            <th>Price (Rp)</th>
                                            <th>Total (Rp)</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($show_pending_order_ids->num_rows > 0): ?>
                                            <?php 
                                            $i = 1; 
                                            $show_pending_order_ids->data_seek(0); // Reset pointer
                                            while($data = $show_pending_order_ids->fetch_assoc()): 
                                            ?>
                                            <tr>
                                                <td><?= $i++; ?></td>
                                                <td><?= htmlspecialchars($data['order_id']); ?></td>
                                                <td><?= htmlspecialchars($data['item_name']); ?></td>
                                                <td><?= htmlspecialchars($data['nama_perusahaan'] ?? 'N/A'); ?></td>
                                                <td class="text-center"><?= htmlspecialchars($data['quantity']); ?></td>
                                                <td class="text-center"><?= htmlspecialchars($data['unit']); ?></td>
                                                <td><?= date('d M Y', strtotime($data['order_date'])); ?></td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-addon">Rp</span>
                                                        <input type="text"
                                                               form="form_<?= htmlspecialchars($data['order_id']); ?>"
                                                               name="manual_price"
                                                               class="form-control price-input"
                                                               value="<?= number_format($data['price'], 0, ',', '.'); ?>"
                                                               required
                                                               data-original-value="<?= $data['price']; ?>">
                                                        <input type="hidden"
                                                               form="form_<?= htmlspecialchars($data['order_id']); ?>"
                                                               name="quantity"
                                                               value="<?= htmlspecialchars($data['quantity']); ?>">
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    Rp <?= number_format($data['total_price'], 0, ',', '.'); ?>
                                                </td>
                                                <td>
                                                    <form id="form_<?= htmlspecialchars($data['order_id']); ?>"
                                                          action="transaksi.php" method="POST" onsubmit="return validateForm(this)">
                                                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($data['order_id']); ?>">
                                                        <button type="submit" name="approve" class="btn btn-success btn-xs btn-action">
                                                            <i class="fa fa-check"></i> Approve
                                                        </button>
                                                        <button type="submit" name="reject" class="btn btn-danger btn-xs btn-action">
                                                            <i class="fa fa-times"></i> Reject
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="text-center text-muted">
                                                    <i class="fa fa-info-circle"></i> No pending order_ids found
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <footer class="main-footer">
            <div class="pull-right hidden-xs">
                <b>Version</b> 1.0.0
            </div>
            <strong>Copyright &copy; <?= date('Y'); ?> <a href="#">U-PSN</a>.</strong> All rights reserved.
        </footer>
    </div>

    <!-- JavaScript -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    <script>
        $(document).ready(function() {
            // Price formatting
            $('.price-input').on('keyup', function(e) {
                // Allow navigation keys
                if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                    (e.keyCode == 65 && e.ctrlKey === true) || 
                    (e.keyCode == 67 && e.ctrlKey === true) ||
                    (e.keyCode == 88 && e.ctrlKey === true) ||
                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                    return;
                }
                
                // Format the number
                var value = $(this).val().replace(/[^\d]/g, '');
                $(this).val(formatNumber(value));
                
                // Update the hidden original value
                $(this).data('original-value', value ? parseInt(value) : 0);
            });
            
            // Helper function to format numbers
            function formatNumber(num) {
                if (!num) return '';
                return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.')
            }
        });

        // Form validation
        function validateForm(form) {
            if ($(form).find('button[name="approve"]').is(':focus')) {
                var priceInput = $(form).find('.price-input');
                var priceValue = priceInput.data('original-value');
                
                if (!priceInput.val() || isNaN(priceValue) || priceValue <= 0) {
                    alert('Please enter a valid price before approving.');
                    priceInput.focus();
                    return false;
                }
            }
            return true;
        }
    </script>
</body>
</html>
<?php
// Close database connection
$mysqli->close();
?>