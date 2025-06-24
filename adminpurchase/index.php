<?php
session_start();
require_once('../konekdb.php'); // Assuming this connects to your database

// Redirect if not logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    header("location:../index.php");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check user authorization for 'Purchase' module
$stmt_cekuser = $mysqli->prepare("SELECT COUNT(username) AS jmluser FROM authorization WHERE username = ? AND modul = 'Purchase'");
if ($stmt_cekuser) {
    $stmt_cekuser->bind_param("s", $username);
    $stmt_cekuser->execute();
    $result_cekuser = $stmt_cekuser->get_result();
    $user = $result_cekuser->fetch_array();
    $stmt_cekuser->close();
} else {
    // Log error instead of dying directly in production
    error_log("Error preparing statement for user authorization: " . $mysqli->error);
    $_SESSION['error'] = "An internal error occurred. Please try again later.";
    header("location:../index.php");
    exit();
}

// Redirect if user doesn't have permission
if ((int)$user['jmluser'] === 0) {
    $_SESSION['error'] = "You do not have permission to access the Purchase dashboard.";
    header("location:../index.php");
    exit();
}

// Get employee data
$pegawai = []; // Initialize to prevent undefined variable notice
$stmt_getpegawai = $mysqli->prepare("SELECT Nama, Jabatan, Departemen, Tanggal_Masuk, foto FROM pegawai WHERE id_pegawai=?");
if ($stmt_getpegawai) {
    $stmt_getpegawai->bind_param("i", $idpegawai);
    $stmt_getpegawai->execute();
    $result_getpegawai = $stmt_getpegawai->get_result();
    if ($result_getpegawai->num_rows > 0) {
        $pegawai = $result_getpegawai->fetch_array(MYSQLI_ASSOC);
    }
    $stmt_getpegawai->close();
} else {
    error_log("Error preparing statement for employee data: " . $mysqli->error);
}

// Function to safely get counts from the database
function getCount($mysqli, $query, $params = [], $types = '') {
    $result = ['jml' => 0];
    if ($stmt = $mysqli->prepare($query)) {
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $result = $res->fetch_array(MYSQLI_ASSOC);
        }
        $stmt->close();
    } else {
        error_log("Error preparing statement: " . $mysqli->error . " for query: " . $query);
    }
    return $result;
}

// Get counts for quick actions using the helper function
// Note: These will be updated via AJAX as well for consistency
$tot1 = getCount($mysqli, "SELECT COUNT(order_id) AS count_pemesanan1 FROM pemesanan1 WHERE status='0'"); // New order_ids
$tot2 = getCount($mysqli, "SELECT COUNT(DISTINCT id_transaksi) AS jml FROM transaksi WHERE status='1'"); // Pending Approval
$tot3 = getCount($mysqli, "SELECT COUNT(DISTINCT id_transaksi) AS jml FROM transaksi WHERE status='4'"); // Reports (Assuming status '4' relates to reportable transactions)
$totSupplier = getCount($mysqli, "SELECT COUNT(id_supplier) AS jml FROM supplier"); // Supplier count
$tot4 = getCount($mysqli, "SELECT COUNT(id_pegawai) AS jml FROM cuti WHERE aksi='1' AND id_pegawai=?", [$idpegawai], 'i'); // Employee leave approvals
$tot5 = getCount($mysqli, "SELECT COUNT(id_pesan) AS jml FROM pesan WHERE ke=? AND status='0'", [$idpegawai], 'i'); // Unread messages


// Sanitize outputs for HTML
$displayUsername = htmlspecialchars($username);
$displayPegawaiNama = htmlspecialchars($pegawai['Nama'] ?? 'N/A');
$displayPegawaiJabatan = htmlspecialchars($pegawai['Jabatan'] ?? 'N/A');
$displayPegawaiDepartemen = htmlspecialchars($pegawai['Departemen'] ?? 'N/A');
$displayPegawaiTanggalMasuk = htmlspecialchars($pegawai['Tanggal_Masuk'] ?? 'N/A');
$displayPegawaiFoto = htmlspecialchars($pegawai['foto'] ?? 'default.jpg');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Purchase Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/ionicons.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
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
        .dashboard-section h4 {
            color: #555;
            font-size: 16px;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .info-box {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 15px;
            border_id-radius: 8px;
            color: white;
            min-height: 90px; /* Ensure consistent height */
        }
        .info-box .info-box-icon {
            font-size: 45px;
            width: 70px;
            text-align: center;
            line-height: 70px;
            background: rgba(0,0,0,0.2);
            border_id-radius: 4px;
            padding: 10px; /* Adjust padding to make icon look better */
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
        .small-box-footer {
            display: block;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            margin-top: 10px;
            padding-top: 5px;
            border_id-top: 1px solid rgba(255,255,255,0.3);
        }
        .small-box-footer:hover {
            color: white;
        }
        .bg-aqua { background-color: #00c0ef !important; }
        .bg-green { background-color: #00a65a !important; }
        .bg-yellow { background-color: #f39c12 !important; }
        .bg-blue { background-color: #3c8dbc !important; }
        .chart-responsive {
            margin-bottom: 20px;
        }
        .status-box {
            padding: 15px;
            border_id-radius: 4px;
            background: #f9f9f9;
            border_id: 1px solid #eee;
            margin-top: 15px;
        }
    </style>
</head>
<body class="skin-blue">
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
                            <span><?php echo $displayUsername; ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header bg-light-blue">
                                <img src="../img/<?php echo $displayPegawaiFoto; ?>" class="img-circle" alt="User Image" />
                                <p>
                                    <?php echo $displayPegawaiNama . " - " . $displayPegawaiJabatan . " " . $displayPegawaiDepartemen; ?>
                                    <small>Member since <?php echo $displayPegawaiTanggalMasuk; ?></small>
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
                        <img src="../img/<?php echo $displayPegawaiFoto; ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?php echo $displayUsername; ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                
               <ul class="sidebar-menu">
                    <li class="active">
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
                <h1>
                    Purchase Dashboard
                    <small>Overview & Analytics</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Dashboard</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="dashboard-section">
                            <h3>Quick Actions</h3>
                            <div class="row">
                                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <div class="info-box bg-aqua">
                                        <span class="info-box-icon"><i class="fa fa-cart-plus"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">New order_ids</span>
                                            <span class="info-box-number" id="new-order_ids-count"><?php echo isset($tot1['count_pemesanan1']) ? htmlspecialchars($tot1['count_pemesanan1']) : "0"; ?></span>
                                            <a href="pemesanan.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <div class="info-box bg-green">
                                        <span class="info-box-icon"><i class="fa fa-thumbs-up"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Pending Approval</span>
                                            <span class="info-box-number" id="pending-approval-count"><?php echo isset($tot2['jml']) ? htmlspecialchars($tot2['jml']) : "0"; ?></span>
                                            <a href="transaksi.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <div class="info-box bg-yellow">
                                        <span class="info-box-icon"><i class="fa fa-file-text-o"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Reports</span>
                                            <span class="info-box-number" id="reports-count"><?php echo isset($tot3['jml']) ? htmlspecialchars($tot3['jml']) : "0"; ?></span>
                                            <a href="laporan.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                    <div class="info-box bg-blue">
                                        <span class="info-box-icon"><i class="fa fa-truck"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Suppliers</span>
                                            <span class="info-box-number" id="supplier-count"><?php echo isset($totSupplier['jml']) ? htmlspecialchars($totSupplier['jml']) : "0"; ?></span>
                                            <a href="supplier.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="dashboard-section">
                                    <h3>Purchase order_id Status</h3>
                                    <div class="chart-responsive">
                                        <canvas id="purchaseorder_idStatusChart" style="height: 250px;"></canvas>
                                    </div>
                                    <h4>Key Metrics</h4>
                                    <p>Here you can find a summary of key metrics related to purchase order_ids, including total value of pending order_ids and average approval time.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="dashboard-section">
                                    <h3>Supplier Performance</h3>
                                    <div class="chart-responsive">
                                        <canvas id="supplierPerformanceChart" style="height: 250px;"></canvas>
                                    </div>
                                    <h4>Recent Supplier Activity</h4>
                                    <p>This section lists recent interactions or highlights top and bottom performing suppliers based on delivery and quality metrics.</p>
                                    <a href="supplier.php" class="btn btn-default btn-sm">Manage Suppliers</a>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
    
    <script>
    // Function to update quick action counts via AJAX
    function updateQuickActionCounts() {
        $.ajax({
            url: 'get_dashboard_counts.php', // A new PHP file to handle AJAX requests for counts
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Update info boxes
                $('#new-order_ids-count').text(data.neworder_ids || "0");
                $('#pending-approval-count').text(data.pendingApproval || "0");
                $('#reports-count').text(data.reports || "0");
                $('#supplier-count').text(data.supplier || "0");
                
                // Mailbox and Leave are already handled by PHP on initial load, but can be updated via AJAX too
                // Ensure the data keys match what your get_dashboard_counts.php returns
                // If you want to show badges in sidebar based on AJAX, you'd add similar logic here.
                // For now, we are removing badges from sidebar as per request.
            },
            error: function(xhr, status, error) {
                console.error("Error fetching dashboard counts:", status, error);
                console.log(xhr.responseText); // Log the response for debugging
            }
        });
    }

    // Chart.js - Placeholder for actual data fetching and rendering
    function renderCharts() {
        // Purchase order_id Status Chart (Example: Bar Chart)
        var ctxPoStatus = document.getElementById('purchaseorder_idStatusChart').getContext('2d');
        new Chart(ctxPoStatus, {
            type: 'bar',
            data: {
                labels: ['New order_ids', 'Pending Approval', 'Reports', 'Completed'], // Updated labels
                datasets: [{
                    label: 'Number of order_ids',
                    data: [
                        <?php echo $tot1['count_pemesanan1']; ?>, 
                        <?php echo $tot2['jml']; ?>, 
                        <?php echo $tot3['jml']; ?>, 
                        20 // Placeholder for completed order_ids, replace with dynamic data
                    ], 
                    backgroundColor: ['#00c0ef', '#00a65a', '#f39c12', '#3c8dbc'],
                    border_idColor: ['#00a0d2', '#008d4c', '#d78b10', '#3071a9'],
                    border_idWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0 // Ensure integer ticks for count
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // Hide legend if only one dataset
                    },
                    title: {
                        display: true,
                        text: 'Current Purchase order_id Status'
                    }
                }
            }
        });

        // Supplier Performance Chart (Example: Doughnut Chart)
        var ctxSupplierPerf = document.getElementById('supplierPerformanceChart').getContext('2d');
        new Chart(ctxSupplierPerf, {
            type: 'doughnut',
            data: {
                labels: ['On-time Delivery', 'Delayed Delivery', 'Issues Reported'],
                datasets: [{
                    label: 'Supplier Performance',
                    data: [80, 15, 5], // Replace with dynamic data from DB (e.g., % of on-time deliveries)
                    backgroundColor: ['#5cb85c', '#f0ad4e', '#d9534f'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Supplier Performance Snapshot'
                    }
                }
            }
        });

        // Transaction Flow Chart (Example: Line Chart - to show trend over time)
        var ctxTransFlow = document.getElementById('transactionFlowChart').getContext('2d');
        new Chart(ctxTransFlow, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'], // Replace with dynamic time-series data
                datasets: [{
                    label: 'Completed Transactions',
                    data: [10, 25, 18, 30, 22, 35], // Example data
                    border_idColor: '#3c8dbc',
                    backgroundColor: 'rgba(60, 141, 188, 0.2)',
                    fill: true,
                    tension: 0.1
                },
                {
                    label: 'Transactions Approved',
                    data: [8, 20, 15, 25, 18, 30], // Example data
                    border_idColor: '#00a65a',
                    backgroundColor: 'rgba(0, 166, 90, 0.2)',
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0 // Ensure integer ticks for count
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Transaction Flow'
                    }
                }
            }
        });
    }

    $(document).ready(function() {
        updateQuickActionCounts(); // Initial load of counts
        renderCharts(); // Render all charts
        // You can uncomment the line below to update counts every minute
        // setInterval(updateQuickActionCounts, 60000); 
    });
    </script>
</body>
</html>