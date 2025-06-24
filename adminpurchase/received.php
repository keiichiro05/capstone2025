<?php
// received.php
// Halaman ini menampilkan pesanan pembelian yang telah disetujui (status 'accepted').

// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sertakan file koneksi database
include('../konekdb.php');

// Periksa koneksi database di awal
if ($mysqli->connect_errno) {
    die("KONEKSI DATABASE GAGAL: " . $mysqli->connect_error);
}

// Mulai sesi untuk menggunakan variabel sesi
session_start();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header("location:../index.php");
    exit();
}

// Ambil username dan id_pegawai dari sesi
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Periksa otorisasi pengguna
$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Purchase'");

if (!$cekuser) {
    die("QUERY OTORISASI GAGAL: " . mysqli_error($mysqli));
}
$user = mysqli_fetch_array($cekuser);

if ($user['jmluser'] == "0") {
    $_SESSION['error'] = "Anda tidak memiliki izin untuk mengakses halaman ini.";
    header("location:../index.php");
    exit();
}

// Ambil data pegawai untuk tampilan profil
$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
if (!$getpegawai) {
    die("QUERY PEGAWAI GAGAL: " . mysqli_error($mysqli));
}
$pegawai = mysqli_fetch_array($getpegawai);

// Function to get notification count (optimized)
function getNotificationCount($mysqli, $query) {
    $result = mysqli_query($mysqli, $query);
    if (!$result) {
        die("QUERY NOTIF GAGAL: " . mysqli_error($mysqli));
    }
    $data = mysqli_fetch_array($result);
    return $data;
}

// --- Ambil Jumlah Notifikasi ---
$tot1 = getNotificationCount($mysqli, "SELECT count(order_id) as count_pemesanan1 FROM pemesanan1 WHERE status='draft'");
$tot2 = getNotificationCount($mysqli, "SELECT count(order_id) as jml FROM pemesanan1 WHERE status='pending'");
$tot3 = getNotificationCount($mysqli, "SELECT count(distinct order_id) as jml FROM pemesanan1 WHERE status='accepted'");
$tot4 = getNotificationCount($mysqli, "SELECT count(id_pegawai) as jml FROM cuti WHERE aksi='1' AND id_pegawai='$idpegawai'");
$tot5 = getNotificationCount($mysqli, "SELECT count(id_pesan) as jml FROM pesan WHERE ke='$idpegawai' AND status='0'");

// Ambil pesanan pembelian yang berstatus 'accepted'
$show_accepted_order_ids = mysqli_query($mysqli, "SELECT p.*, s.nama_perusahaan, a.Nama as approved_by_name
                                                FROM pemesanan1 p
                                                LEFT JOIN supplier s ON p.supplier_id = s.id_supplier
                                                LEFT JOIN pegawai a ON p.approved_by = a.id_pegawai
                                                WHERE p.status='accepted' ORDER BY p.approved_date DESC");

if (!$show_accepted_order_ids) {
    die("QUERY DATA RECEIVED GAGAL: " . mysqli_error($mysqli));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>E-pharm | Received Items</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/ionicons.min.css" rel="stylesheet">
    <link href="../css/datatables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
    <style>
        /* Enhanced Table Styles with Dark Blue Theme */
        .content-wrapper {
            background-color: #f4f4f4;
        }
        
        .table-container {
            border_id: 1px solid #d2d6de;
            border_id-radius: 5px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);
            background: white;
        }
        
        .table {
            margin-bottom: 0;
            border_id-collapse: separate;
            border_id-spacing: 0;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #ffffff;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            padding: 15px 12px;
            border_id: none;
            border_id-bottom: 2px solid #1a365d;
            position: sticky;
            top: 0;
            z-index: 100;
            text-align: center;
            vertical-align: middle;
            letter-spacing: 0.5px;
        }
        
        .table thead th:first-child {
            border_id-top-left-radius: 5px;
        }
        
        .table thead th:last-child {
            border_id-top-right-radius: 5px;
        }
        
        /* DataTables Sorting Icons */
        .table thead th.sorting,
        .table thead th.sorting_asc,
        .table thead th.sorting_desc {
            cursor: pointer;
            position: relative;
        }
        
        .table thead th.sorting:after,
        .table thead th.sorting_asc:after,
        .table thead th.sorting_desc:after {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.7);
            font-size: 11px;
        }
        
        .table thead th.sorting:hover,
        .table thead th.sorting_asc:hover,
        .table thead th.sorting_desc:hover {
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        }
        
        .table tbody td {
            padding: 12px;
            font-size: 13px;
            border_id-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
            border_id-left: none;
            border_id-right: none;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9ff;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }
        
        .table tbody tr:nth-child(even):hover {
            background-color: #f8f9ff;
        }
        
        /* Filter Section Styling */
        .filter-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            margin-bottom: 20px;
            border_id-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            color: white;
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .filter-row:last-child {
            margin-bottom: 0;
        }
        
        .custom-search-container {
            flex: 1;
            min-width: 300px;
            position: relative;
        }
        
        .custom-search-box {
            position: relative;
            width: 100%;
        }
        
        .custom-search-box input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border_id: 2px solid rgba(255,255,255,0.3);
            border_id-radius: 25px;
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .custom-search-box input::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .custom-search-box input:focus {
            outline: none;
            border_id-color: rgba(255,255,255,0.8);
            background: rgba(255,255,255,0.2);
            box-shadow: 0 0 0 3px rgba(255,255,255,0.1);
        }
        
        .custom-search-box .fa {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.7);
            font-size: 16px;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 250px;
        }
        
        .filter-label {
            font-weight: 600;
            font-size: 14px;
            min-width: 80px;
            color: rgba(255,255,255,0.9);
        }
        
        .filter-control {
            flex: 1;
            padding: 10px 15px;
            border_id: 2px solid rgba(255,255,255,0.3);
            border_id-radius: 5px;
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 14px;
        }
        
        .filter-control:focus {
            outline: none;
            border_id-color: rgba(255,255,255,0.8);
            background: rgba(255,255,255,0.2);
        }
        
        .filter-control option {
            background: #1e3c72;
            color: white;
        }
        
        .apply-filters-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border_id: none;
            padding: 12px 25px;
            border_id-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .apply-filters-btn:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        /* Status Badges */
        .badge-status {
            padding: 6px 12px;
            border_id-radius: 20px;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-accepted {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
        }
        
        /* Price Styling */
        .total-price {
            font-weight: 700;
            color: #28a745;
            font-size: 14px;
        }
        
        .price-cell {
            font-weight: 600;
            color: #495057;
        }
        
        /* Box Styling */
        .box {
            border_id-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .box-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 20px;
            border_id: none;
        }
        
        .box-title {
            font-weight: 600;
            font-size: 18px;
            margin: 0;
        }
        
        .box-body {
            padding: 25px;
            background: white;
        }
        
        .box-footer {
            background: #f8f9fa;
            padding: 15px 25px;
            border_id-top: 1px solid #dee2e6;
        }
        
        /* DataTables Custom Styling */
        .dataTables_wrapper {
            padding: 0;
        }
        
        .dataTables_filter {
            display: none;
        }
        
        .dataTables_length {
            margin-bottom: 20px;
        }
        
        .dataTables_length select {
            padding: 5px 10px;
            border_id: 1px solid #d2d6de;
            border_id-radius: 4px;
            margin: 0 5px;
        }
        
        .dataTables_info {
            color: #666;
            font-size: 13px;
        }
        
        .dataTables_paginate {
            margin-top: 15px;
        }
        
        .dataTables_paginate .paginate_button {
            padding: 8px 12px;
            margin: 0 2px;
            border_id: 1px solid #d2d6de;
            border_id-radius: 4px;
            background: white;
            color: #333;
            text-decoration: none;
        }
        
        .dataTables_paginate .paginate_button:hover {
            background: #1e3c72;
            color: white;
            border_id-color: #1e3c72;
        }
        
        .dataTables_paginate .paginate_button.current {
            background: #1e3c72;
            color: white;
            border_id-color: #1e3c72;
        }
        
        /* No Data Message */
        .no-data-message {
            padding: 40px;
            text-align: center;
            color: #6c757d;
            font-size: 16px;
            background: #f8f9fa;
        }
        
        .no-data-message i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                min-width: 100%;
            }
            
            .custom-search-container {
                min-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }
            
            .table thead th {
                white-space: nowrap;
                font-size: 12px;
                padding: 10px 8px;
            }
            
            .table tbody td {
                padding: 8px;
                font-size: 12px;
            }
            
            .filter-section {
                padding: 15px;
            }
        }
        
        /* Supplier styling */
        .supplier-unknown {
            color: #6c757d;
            font-style: italic;
        }
        
        /* Date column styling */
        .approved-date {
            white-space: nowrap;
            font-size: 13px;
        }
        
        /* Animation for row hover */
        .table tbody tr {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Export buttons styling */
        .dt-buttons {
            margin-bottom: 15px;
        }
        
        .dt-button {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border_id: none;
            padding: 8px 15px;
            border_id-radius: 4px;
            margin-right: 10px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .dt-button:hover {
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="skin-blue">
    <header class="header">
        <a href="../index.php" class="logo">E-pharm</a>
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
                    <li>
                        <a href="pemesanan.php">
                            <i class="fa fa-shopping-cart"></i> <span>order_ids</span>
                        </a>
                    </li>
                    <li>
                        <a href="transaksi.php">
                            <i class="fa fa-check-square"></i> <span>Transaction Approval</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="received.php">
                            <i class="fa fa-cubes"></i> <span>Received Items</span>
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
                <h1>
                    <i class="fa fa-cubes"></i> Received Purchase order_ids
                    <small>Approved order_ids ready for delivery</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="../index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Received Items</li>
                </ol>
            </section>

            <section class="content">
                <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fa fa-check"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fa fa-ban"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title">
                                    <i class="fa fa-check-circle"></i> Approved order_ids Management
                                </h3>
                                <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse" style="color: white;">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <!-- Enhanced Filter Section -->
                                <div class="filter-section">
                                    <div class="filter-row">
                                        <div class="custom-search-container">
                                            <div class="custom-search-box">
                                                <i class="fa fa-search"></i>
                                                <input type="text" id="globalSearch" placeholder="Search order_ids, items, suppliers...">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <span class="filter-label">Supplier:</span>
                                            <select id="supplierFilter" class="form-control filter-control">
                                                <option value="">All Suppliers</option>
                                                <?php
                                                $suppliers = mysqli_query($mysqli, "SELECT DISTINCT s.id_supplier, s.nama_perusahaan 
                                                                                   FROM supplier s 
                                                                                   INNER JOIN pemesanan1 p ON s.id_supplier = p.supplier_id 
                                                                                   WHERE p.status = 'accepted' 
                                                                                   ORDER BY s.nama_perusahaan");
                                                while($supp = mysqli_fetch_array($suppliers)) {
                                                    echo '<option value="'.htmlspecialchars($supp['nama_perusahaan']).'">'.htmlspecialchars($supp['nama_perusahaan']).'</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <span class="filter-label">Date Range:</span>
                                            <input type="text" id="dateRangeFilter" class="form-control filter-control" placeholder="Select approval date range">
                                        </div>
                                        <button id="applyFilters" class="apply-filters-btn">
                                            <i class="fa fa-filter"></i> Apply Filters
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Table Container -->
                                <div class="table-container">
                                    <table id="acceptedorder_idsTable" class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="40">#</th>
                                                <th width="100">order_id ID</th>
                                                <th width="200">Item Name</th>
                                                <th width="120">Category</th>
                                                <th width="60">Qty</th>
                                                <th width="60">Unit</th>
                                                <th width="150">Supplier</th>
                                                <th width="100">order_id Date</th>
                                                <th width="120">Unit Price</th>
                                                <th width="120">Total Price</th>
                                                <th width="120">Approved By</th>
                                                <th width="130">Approved Date</th>
                                                <th width="80">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $i = 0;
                                            if ($show_accepted_order_ids && mysqli_num_rows($show_accepted_order_ids) > 0):
                                                while($data = mysqli_fetch_array($show_accepted_order_ids)):
                                                    $i++;
                                                    $supplierName = $data['nama_perusahaan'] ?? null;
                                                    $approvedByName = $data['approved_by_name'] ?? null;
                                                    $approvedDate = $data['approved_date'] ?? null;
                                                    ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $i; ?></td>
                                                        <td><strong><?php echo htmlspecialchars($data['order_id']); ?></strong></td>
                                                        <td><?php echo htmlspecialchars($data['item_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($data['category']); ?></td>
                                                        <td class="text-center"><strong><?php echo number_format($data['quantity'], 0); ?></strong></td>
                                                        <td class="text-center"><?php echo htmlspecialchars($data['unit']); ?></td>
                                                        <td class="<?php echo !$supplierName ? 'supplier-unknown' : ''; ?>">
                                                            <?php echo $supplierName ? htmlspecialchars($supplierName) : 'Not Available'; ?>
                                                        </td>
                                                        <td><?php echo date('d M Y', strtotime($data['order_date'])); ?></td>
                                                        <td class="text-right price-cell">Rp <?php echo number_format($data['price'], 0, ',', '.'); ?></td>
                                                        <td class="text-right total-price">Rp <?php echo number_format($data['total_price'], 0, ',', '.'); ?></td>
                                                        <td><?php echo $approvedByName ? htmlspecialchars($approvedByName) : 'System'; ?></td>
                                                        <td class="approved-date">
                                                            <?php 
                                                            if ($approvedDate) {
                                                                echo date('d M Y H:i', strtotime($approvedDate));
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge-status badge-accepted">Approved</span>
                                                        </td>
                                                    </tr>
                                                <?php endwhile;
                                            else: ?>
                                                <tr>
                                                    <td colspan="13" class="no-data-message">
                                                        <i class="fa fa-inbox"></i>
                                                        <div>No approved order_ids found</div>
                                                        <small>order_ids will appear here once they are approved by management</small>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="box-footer clearfix">
                                <div class="pull-left">
                                    <i class="fa fa-info-circle text-info"></i>
                                    <small class="text-muted">Total approved order_ids: <strong                                    <small class="text-muted">Total approved order_ids: <strong><?php echo $i; ?></strong></small>
                                </div>
                                <div class="pull-right">
                                    <small class="text-muted">Last updated: <?php echo date('d M Y H:i:s'); ?></small>
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
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.print.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize DataTable with enhanced features
        var table = $('#acceptedorder_idsTable').DataTable({
            "dom": '<"top"Bf>rt<"bottom"lip><"clear">',
            "buttons": [
                {
                    extend: 'copy',
                    text: '<i class="fa fa-copy"></i> Copy',
                    className: 'btn btn-default'
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    className: 'btn btn-default'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    className: 'btn btn-default'
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> Print',
                    className: 'btn btn-default'
                }
            ],
            "pagingType": "full_numbers",
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            "order_id": [[11, "desc"]], // Default sort by approved date
            "columnDefs": [
                { "order_idable": false, "targets": [0, 12] }, // Disable sorting for # and Status columns
                { "type": "date", "targets": [7, 11] }, // Specify date type for date columns
                { "type": "num-fmt", "targets": [8, 9] } // Specify numeric type for price columns
            ],
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search...",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "No entries found",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "zeroRecorder_ids": "No matching recorder_ids found",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
        
        // Initialize date range picker
        $('#dateRangeFilter').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'DD MMM YYYY'
            },
            opens: 'right',
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
        
        // Update the date range display when dates are selected
        $('#dateRangeFilter').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD MMM YYYY') + ' - ' + picker.endDate.format('DD MMM YYYY'));
        });
        
        // Clear the date range field
        $('#dateRangeFilter').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
        
        // Apply filters button functionality
        $('#applyFilters').on('click', function() {
            var supplier = $('#supplierFilter').val();
            var dateRange = $('#dateRangeFilter').val();
            var searchTerm = $('#globalSearch').val();
            
            // Reset all filters first
            table
                .search('')
                .columns().search('')
                .draw();
            
            // Apply supplier filter if selected
            if (supplier) {
                table.column(6).search(supplier).draw();
            }
            
            // Apply date range filter if selected
            if (dateRange) {
                var dates = dateRange.split(' - ');
                var startDate = moment(dates[0], 'DD MMM YYYY').format('YYYY-MM-DD');
                var endDate = moment(dates[1], 'DD MMM YYYY').format('YYYY-MM-DD');
                
                // Custom filtering function for date range
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        var order_idDate = moment(data[11], 'DD MMM YYYY HH:mm').format('YYYY-MM-DD');
                        return (order_idDate >= startDate && order_idDate <= endDate);
                    }
                );
                table.draw();
                // Remove the custom filter function after applying
                $.fn.dataTable.ext.search.pop();
            }
            
            // Apply global search if term exists
            if (searchTerm) {
                table.search(searchTerm).draw();
            }
        });
        
        // Reset all filters
        $('#resetFilters').on('click', function() {
            $('#supplierFilter').val('');
            $('#dateRangeFilter').val('');
            $('#globalSearch').val('');
            table
                .search('')
                .columns().search('')
                .draw();
        });
        
        // Highlight row on hover with animation
        $('#acceptedorder_idsTable tbody').on('mouseenter', 'tr', function() {
            $(this).css('transform', 'translateY(-1px)');
            $(this).css('box-shadow', '0 2px 4px rgba(0,0,0,0.1)');
        }).on('mouseleave', 'tr', function() {
            $(this).css('transform', '');
            $(this).css('box-shadow', '');
        });
        
        // Responsive table adjustments
        $(window).resize(function() {
            table.columns.adjust();
        });
    });
    </script>
</body>
</html>
<?php
// Close database connection
$mysqli->close();
?>