<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include('../konekdb.php');
session_start();

// --- Handle Approval and Rejection ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_order_id'])) {
        // Handle order_id approval
        $order_id = (int)$_POST['order_id'];
        $approved_by = mysqli_real_escape_string($mysqli, $_SESSION['username']);
        $approval_date = date('Y-m-d H:i:s');
        
        $update_query = "UPDATE pemesanan1 SET 
                        status = 2, 
                        approved_by = '$approved_by', 
                        tanggal_approval = '$approval_date',
                        rejection_reason = NULL
                        WHERE order_id = '$order_id'";
        
        if (mysqli_query($mysqli, $update_query)) {
            // Recorder_id in history
            $history_query = "INSERT INTO history_pemesanan1 (order_id, action, performed_by, action_date) 
                             VALUES ('$order_id', 'approved', '$approved_by', '$approval_date')";
            mysqli_query($mysqli, $history_query);
            
            $_SESSION['message'] = "order_id #$order_id has been approved.";
            header("Location: pemesanan.php?display=approved");
            exit();
        } else {
            $_SESSION['error'] = "Failed to approve order_id: " . mysqli_error($mysqli);
            header("Location: pemesanan.php");
            exit();
        }
    }
    
    if (isset($_POST['reject_order_id'])) {
        // Handle order_id rejection
        $order_id = (int)$_POST['order_id'];
        $rejected_by = mysqli_real_escape_string($mysqli, $_SESSION['username']);
        $rejection_date = date('Y-m-d H:i:s');
        $rejection_reason = mysqli_real_escape_string($mysqli, $_POST['rejection_reason']);
        
        $update_query = "UPDATE pemesanan1 SET 
                        status = 3, 
                        approved_by = '$rejected_by', 
                        tanggal_approval = '$rejection_date',
                        rejection_reason = '$rejection_reason'
                        WHERE order_id = '$order_id'";
        
        if (mysqli_query($mysqli, $update_query)) {
            // Recorder_id in history
            $history_query = "INSERT INTO history_pemesanan1 (order_id, action, performed_by, action_date, notes) 
                             VALUES ('$order_id', 'rejected', '$rejected_by', '$rejection_date', '$rejection_reason')";
            mysqli_query($mysqli, $history_query);
            
            $_SESSION['message'] = "order_id #$order_id has been rejected.";
            header("Location: pemesanan.php?display=rejected");
            exit();
        } else {
            $_SESSION['error'] = "Failed to reject order_id: " . mysqli_error($mysqli);
            header("Location: pemesanan.php");
            exit();
        }
    }

    // --- Handle Form Submission for Adding New Purchase Request (PRQ) ---
    if (isset($_POST['add_pemesanan1'])) {
        // Sanitize input data
        $item_name = mysqli_real_escape_string($mysqli, $_POST['item_name']); 
        $category = mysqli_real_escape_string($mysqli, $_POST['category']); 
        $quantity = (int)$_POST['quantity'];
        $price = (float)$_POST['price'];
        $unit = mysqli_real_escape_string($mysqli, $_POST['unit']); 
        $id_supplier = (int)$_POST['id_supplier'];
        
        // Validate required fields
        if (empty($item_name) || empty($category) || empty($quantity) || empty($price) || empty($unit) || empty($id_supplier)) {
            $_SESSION['error'] = "All fields are required!";
            header("location:pemesanan.php");
            exit();
        }

        // Calculate total price
        $total_price = $quantity * $price;

        // Get supplier name
        $get_supplier_name_query = mysqli_query($mysqli, "SELECT nama_perusahaan FROM supplier WHERE id_supplier = '$id_supplier'");
        $supplier_data = mysqli_fetch_array($get_supplier_name_query);
        $supplier_name = $supplier_data['nama_perusahaan'] ?? 'Unknown Supplier';

        // Prepare and execute the INSERT query
        $insert_query = "INSERT INTO pemesanan1 (item_name, category, quantity, price, total_price, unit, supplier_id, supplier_name, order_date, status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1)";

        $stmt = mysqli_prepare($mysqli, $insert_query);
        mysqli_stmt_bind_param($stmt, 'ssiidsss', $item_name, $category, $quantity, $price, $total_price, $unit, $id_supplier, $supplier_name);

        if (mysqli_stmt_execute($stmt)) {
            $order_id = mysqli_insert_id($mysqli);
            $_SESSION['message'] = "New purchase request for " . htmlspecialchars($item_name) . " has been submitted (ID: PRQ-$order_id)";
        } else {
            $_SESSION['error'] = "Failed to submit purchase request: " . mysqli_error($mysqli);
        }
        mysqli_stmt_close($stmt);
        header("location:pemesanan.php");
        exit();
    }
}

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header("location:../index.php");
    exit();
}

// Get user data from session
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check user authorization for 'Purchase' module
$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Purchase'");
$user = mysqli_fetch_array($cekuser);

if ($user['jmluser'] == "0") {
    $_SESSION['error'] = "You don't have permission to access this page.";
    header("location:../index.php");
    exit();
}

// Get employee data for profile display
$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
$pegawai = mysqli_fetch_array($getpegawai);

// --- Fetch Notification Counts (for sidebar) ---
$not1 = mysqli_query($mysqli, "SELECT count(order_id) as count_pemesanan1 FROM pemesanan1 WHERE status='1'"); 
$pending_order_ids_count = mysqli_fetch_array($not1);

$not2 = mysqli_query($mysqli, "SELECT count(distinct id_transaksi) as jml FROM transaksi WHERE status='1'");
$tot2 = mysqli_fetch_array($not2);

$not3 = mysqli_query($mysqli, "SELECT count(distinct id_transaksi) as jml FROM transaksi WHERE status='4'");
$tot3 = mysqli_fetch_array($not3);

$not4 = mysqli_query($mysqli, "SELECT count(id_pegawai) as jml FROM cuti WHERE aksi='1' AND id_pegawai='$idpegawai'");
$tot4 = mysqli_fetch_array($not4);

$not5 = mysqli_query($mysqli, "SELECT count(id_pesan) as jml FROM pesan WHERE ke='$idpegawai' AND status='0'");
$tot5 = mysqli_fetch_array($not5);

// --- Filter Display of order_ids ---
$display_filter = isset($_GET['display']) ? $_GET['display'] : 'all';

$query_condition = "";
$page_title_suffix = "";

switch ($display_filter) {
    case 'pending':
        $query_condition = " WHERE status = 1";
        $page_title_suffix = " (Pending Approval)";
        break;
    case 'approved':
        $query_condition = " WHERE status = 2";
        $page_title_suffix = " (Approved)";
        break;
    case 'rejected':
        $query_condition = " WHERE status = 3";
        $page_title_suffix = " (Rejected)";
        break;
    case 'all':
    default:
        $query_condition = "";
        $page_title_suffix = " (All order_ids)";
        break;
}

// Get all supplier data for the dropdown
$get_suppliers = mysqli_query($mysqli, "SELECT id_supplier, nama_perusahaan FROM supplier ORDER BY nama_perusahaan ASC");

$show_order_ids_query = "SELECT p.order_id, p.item_name, p.category, p.quantity, p.price, p.total_price, p.unit, 
                      p.supplier_id, p.supplier_name, p.order_date, p.delivery_date, p.status, p.rejection_reason, 
                      p.approved_by, p.approved_date, 
                      s.nama_perusahaan 
                      FROM pemesanan1 p 
                      LEFT JOIN supplier s ON p.supplier_id = s.id_supplier " . $query_condition . " 
                      ORDER BY p.order_date DESC";

$show_order_ids = mysqli_query($mysqli, $show_order_ids_query);


// Get counts for summary cards
$all_count = mysqli_num_rows($show_order_ids);
$pending_count = $pending_order_ids_count['count_pemesanan1'];
$approved_count_result = mysqli_query($mysqli, "SELECT count(order_id) as count FROM pemesanan1 WHERE status=2");
$approved_count = mysqli_fetch_array($approved_count_result)['count'];
$rejected_count_result = mysqli_query($mysqli, "SELECT count(order_id) as count FROM pemesanan1 WHERE status=3");
$rejected_count = mysqli_fetch_array($rejected_count_result)['count'];

// Get average and total for approved order_ids
$avg_query = mysqli_query($mysqli, "SELECT AVG(total_price) as avg_price FROM pemesanan1 WHERE status=2");
$avg_data = mysqli_fetch_array($avg_query);
$avg_price = $avg_data['avg_price'] ? number_format($avg_data['avg_price'], 0, ',', '.') : 0;

$total_query = mysqli_query($mysqli, "SELECT SUM(total_price) as total FROM pemesanan1 WHERE status=2");
$total_data = mysqli_fetch_array($total_query);
$total_price = $total_data['total'] ? number_format($total_data['total'], 0, ',', '.') : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Purchase order_id Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/ionicons.min.css" rel="stylesheet">
    <link href="../css/datatables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <style>
        /* Modern Table Styles */
        .table-container {
            max-height: 600px;
            overflow-y: auto;
            border_id-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        #order_idsTable {
            width: 100% !important;
            font-size: 0.9em;
            margin: 0;
            border_id-collapse: separate;
            border_id-spacing: 0;
        }
        
        #order_idsTable th {
            background-color: #3c8dbc;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            padding: 12px 15px;
            border_id: none;
        }
        
        #order_idsTable td {
            padding: 10px 15px;
            vertical-align: middle;
            border_id-bottom: 1px solid #ddd;
        }
        
        #order_idsTable tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        #order_idsTable tbody tr:hover {
            background-color: #f1f7fd;
        }
        
        /* Status Badges */
        .status-badge {
            padding: 5px 10px;
            border_id-radius: 3px;
            font-size: 0.8em;
            font-weight: 600;
            display: inline-block;
            text-align: center;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; } /* Pending */
        .status-approved { background-color: #d4edda; color: #155724; } /* Approved */
        .status-rejected { background-color: #f8d7da; color: #721c24; } /* Rejected */
        
        /* Price Formatting */
        .price {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }
        
        /* Tab Styling */
        .nav-tabs {
            border_id-bottom: 2px solid #dee2e6;
            margin-bottom: 15px;
        }
        
        .nav-tabs > li > a {
            color: #495057;
            font-weight: 500;
            border_id: none;
            padding: 10px 20px;
            margin-right: 5px;
            border_id-radius: 4px 4px 0 0;
        }
        
        .nav-tabs > li.active > a,
        .nav-tabs > li.active > a:hover,
        .nav-tabs > li.active > a:focus {
            color: #3c8dbc;
            background-color: white;
            border_id: none;
            border_id-bottom: 3px solid #3c8dbc;
        }
        
        /* Card Styling */
        .box {
            border_id-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border_id: none;
        }
        
        .box-header {
            border_id-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 15px 20px;
        }
        
        .box-title {
            font-size: 18px;
            font-weight: 500;
        }
        
        /* order_id ID Link */
        .order_id-id-link {
            color: #3c8dbc;
            font-weight: 600;
            cursor: pointer;
        }
        
        .order_id-id-link:hover {
            color: #2a6496;
            text-decoration: underline;
        }
        
        /* Summary Cards */
        .summary-card {
            border_id-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .summary-card .title {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .summary-card .value {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        
        .summary-card .sub-value {
            font-size: 12px;
            color: #999;
        }
        
        .summary-card.pending { border_id-left: 4px solid #ffc107; }
        .summary-card.approved { border_id-left: 4px solid #28a745; }
        .summary-card.rejected { border_id-left: 4px solid #dc3545; }
        .summary-card.average { border_id-left: 4px solid #17a2b8; }
        .summary-card.total { border_id-left: 4px solid #007bff; }
        
        /* Modal Styling */
        .modal-body .detail-row {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border_id-bottom: 1px solid #eee;
        }
        
        .modal-body .detail-label {
            font-weight: 600;
            color: #555;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            #order_idsTable td:nth-child(4),
            #order_idsTable th:nth-child(4),
            #order_idsTable td:nth-child(6),
            #order_idsTable th:nth-child(6) {
                display: none;
            }
            
            .box-body {
                padding: 10px;
            }
            
            .summary-card {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body class="skin-blue">
    <header class="header">
        <a href="../index.html" class="logo">U-PSN</a>
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
                            <span><?= htmlspecialchars($_SESSION['username']); ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header bg-light-blue">
                                <img src="../img/<?= htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image">
                                <p>
                                    <?= htmlspecialchars($pegawai['Nama'] . " - " . $pegawai['Jabatan'] . " " . $pegawai['Departemen']); ?>
                                    <small>Member since <?= htmlspecialchars($pegawai['Tanggal_Masuk']); ?></small>
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
                        <a href="supplier.php">
                            <i class="fa fa-truck"></i> <span>Supplier</span>
                        </a>
                    </li>
                
                        <li class="active">
                        <a href="pemesanan.php">
                            <i class="fa fa-shopping-cart"></i> <span>order_ids</span>
                        </a>
                    </li>
                    <li>
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

        <aside class="right-side">              
            <section class="content-header">
                <h1>Purchase order_id Management<?php echo $page_title_suffix; ?></h1>
                <ol class="breadcrumb">
                    <li><a href="../index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Purchase order_ids</li>
                </ol>
            </section>

            <section class="content">
                <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fa fa-check"></i> <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fa fa-ban"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="summary-card pending">
                            <div class="title">Pending order_ids</div>
                            <div class="value"><?php echo $pending_count; ?></div>
                            <div class="sub-value">Waiting for approval</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="summary-card approved">
                            <div class="title">Approved order_ids</div>
                            <div class="value"><?php echo $approved_count; ?></div>
                            <div class="sub-value">Completed approvals</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="summary-card rejected">
                            <div class="title">Rejected order_ids</div>
                            <div class="value"><?php echo $rejected_count; ?></div>
                            <div class="sub-value">Not approved</div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="summary-card total">
                            <div class="title">Total Approved Value</div>
                            <div class="value">Rp<?php echo $total_price; ?></div>
                            <div class="sub-value">All approved order_ids</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="box box-info">
                            <div class="box-header">
                                <h3 class="box-title"><i class="fa fa-list"></i> Purchase order_id List<?php echo $page_title_suffix; ?></h3>
                                <div class="box-tools">
                                    <div class="input-group" style="width: 250px;">
                                        <input type="text" id="searchBox" class="form-control input-sm pull-right" placeholder="Search order_ids...">
                                        <div class="input-group-btn">
                                            <button class="btn btn-sm btn-default"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="box-body">
                                <ul class="nav nav-tabs">
                                    <li class="<?php echo $display_filter == 'all' ? 'active' : ''; ?>">
                                        <a href="pemesanan.php?display=all"><i class="fa fa-list"></i> All order_ids</a>
                                    </li>
                                    <li class="<?php echo $display_filter == 'pending' ? 'active' : ''; ?>">
                                        <a href="pemesanan.php?display=pending"><i class="fa fa-clock-o"></i> Pending</a>
                                    </li>
                                    <li class="<?php echo $display_filter == 'approved' ? 'active' : ''; ?>">
                                        <a href="pemesanan.php?display=approved"><i class="fa fa-check"></i> Approved</a>
                                    </li>
                                    <li class="<?php echo $display_filter == 'rejected' ? 'active' : ''; ?>">
                                        <a href="pemesanan.php?display=rejected"><i class="fa fa-times"></i> Rejected</a>
                                    </li>
                                </ul>
                                
                                <div class="table-container">
                                    <table id="order_idsTable" class="table table-border_ided table-striped table-hover">
                                         <thead>
                                            <tr>
                                                <th width="3%">NO</th>
                                                <th width="8%">order_id ID</th>
                                                <th width="15%">ITEM NAME</th>
                                                <th width="10%">CATEGORY</th>
                                                <th width="5%">QTY</th>
                                                <th width="5%">UNIT</th>
                                                <th width="10%">UNIT PRICE</th>
                                                <th width="10%">TOTAL PRICE</th>
                                                <th width="12%">SUPPLIER</th>
                                                <th width="10%">order_id DATE</th>
                                                <th width="12%">STATUS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $i = 0;
                                            if (mysqli_num_rows($show_order_ids) > 0) {
                                                while($data = mysqli_fetch_array($show_order_ids)):
                                                    $i++;
                                                    $status_class = '';
                                                    $status_text = '';
                                                    switch($data['status']) {
                                                        case 1: 
                                                            $status_class = 'status-pending';
                                                            $status_text = 'Pending Approval'; 
                                                            break;
                                                        case 2: 
                                                            $status_class = 'status-approved';
                                                            $status_text = 'Approved'; 
                                                            break;
                                                        case 3: 
                                                            $status_class = 'status-rejected';
                                                            $status_text = 'Rejected'; 
                                                            break;
                                                        default: 
                                                            $status_text = 'Unknown'; 
                                                            break;
                                                    }
                                            ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td>
                                                    <a href="order_id_details.php?id=<?php echo $data['order_id']; ?>" class="order_id-id-link">
                                                        PRQ-<?php echo htmlspecialchars($data['order_id']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($data['item_name']); ?></td>
                                                <td><?php echo htmlspecialchars($data['category']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($data['quantity']); ?></td>
                                                <td class="text-center"><?php echo htmlspecialchars($data['unit']); ?></td>
                                                <td class="price">Rp<?php echo number_format($data['price'], 0, ',', '.'); ?></td>
                                                <td class="price">Rp<?php echo number_format($data['total_price'], 0, ',', '.'); ?></td>
                                                <td><?php echo htmlspecialchars($data['supplier_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($data['order_date'])); ?></td>
                                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                            </tr>

                                            
                                            <!-- order_id Details Modal -->
                                            <div class="modal fade" id="order_idDetailsModal<?php echo $data['order_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="order_idDetailsModalLabel">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                            <h4 class="modal-title" id="order_idDetailsModalLabel">order_id Details: PRQ-<?php echo $data['order_id']; ?></h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Item Name</div>
                                                                        <div><?php echo htmlspecialchars($data['item_name']); ?></div>
                                                                    </div>
                                                                    
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Category</div>
                                                                        <div><?php echo htmlspecialchars($data['category']); ?></div>
                                                                    </div>
                                                                    
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Quantity</div>
                                                                        <div><?php echo htmlspecialchars($data['quantity']); ?> <?php echo htmlspecialchars($data['unit']); ?></div>
                                                                    </div>
                                                                    
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Unit Price</div>
                                                                        <div>Rp<?php echo number_format($data['price'], 0, ',', '.'); ?></div>
                                                                    </div>
                                                                    
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Total Price</div>
                                                                        <div>Rp<?php echo number_format($data['total_price'], 0, ',', '.'); ?></div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-6">
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Supplier</div>
                                                                        <div><?php echo htmlspecialchars($data['supplier_name']); ?></div>
                                                                    </div>
                                                                    
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">order_id Date</div>
                                                                        <div><?php echo date('d/m/Y H:i', strtotime($data['order_date'])); ?></div>
                                                                    </div>
                                                                    
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Status</div>
                                                                        <div><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></div>
                                                                    </div>
                                                                    
                                                                    <?php if($data['status'] == 2): ?>
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Approved By</div>
                                                                        <div><?php echo htmlspecialchars($data['approved_by']); ?></div>
                                                                    </div>
                                                                    
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Approval Date</div>
                                                                        <div><?php echo date('d/m/Y H:i', strtotime($data['tanggal_approval'])); ?></div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    
                                                                    <?php if($data['status'] == 3): ?>
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Rejected By</div>
                                                                        <div><?php echo htmlspecialchars($data['approved_by']); ?></div>
                                                                    </div>
                                                                    
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Rejection Date</div>
                                                                        <div><?php echo date('d/m/Y H:i', strtotime($data['tanggal_approval'])); ?></div>
                                                                    </div>
                                                                    
                                                                    <div class="detail-row">
                                                                        <div class="detail-label">Rejection Reason</div>
                                                                        <div><?php echo htmlspecialchars($data['rejection_reason']); ?></div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <?php if($data['status'] == 1): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="order_id" value="<?php echo $data['order_id']; ?>">
                                                                <button type="submit" name="approve_order_id" class="btn btn-success">Approve</button>
                                                            </form>
                                                            
                                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal<?php echo $data['order_id']; ?>" data-dismiss="modal">Reject</button>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Reject Modal (only for pending order_ids) -->
                                            <?php if($data['status'] == 1): ?>
                                            <div class="modal fade" id="rejectModal<?php echo $data['order_id']; ?>" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                <h4 class="modal-title">Reject order_id #PRQ-<?php echo $data['order_id']; ?></h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="order_id" value="<?php echo $data['order_id']; ?>">
                                                                <div class="form-group">
                                                                    <label>Reason for Rejection:</label>
                                                                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Please specify the reason for rejecting this order_id"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" name="reject_order_id" class="btn btn-danger">Confirm Rejection</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php 
                                                endwhile;
                                            } else {
                                            ?>
                                            <tr>
                                                <td colspan="11" class="text-center">No purchase order_ids found</td>
                                            </tr>
                                            <?php } ?>
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

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    <script src="../js/datatables/jquery.dataTables.js"></script>
    <script src="../js/datatables/dataTables.bootstrap.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable with scroll options
            $('#order_idsTable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "order_iding": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "scrollY": "400px",
                "scrollCollapse": true
            });
            
            // Focus search box when pressing Ctrl+F
            $(document).keydown(function(e) {
                if ((e.ctrlKey || e.metaKey) && e.which === 70) {
                    e.preventDefault();
                    $('#searchBox').focus();
                }
            });
        });
    </script>
</body>
</html>