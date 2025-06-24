<?php
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

if(!isset($_SESSION['username'])){
    header("location:../index.php?status=please login first");
    exit();
}

$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
$user = mysqli_fetch_assoc($cekuser);

$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
$pegawai = mysqli_fetch_array($getpegawai);

// Fetch all outbound history
$outbound_history = [];
// *** IMPORTANT FIX: The SQL query was incorrect for outbound_log
// It was joining based on 'penerima' which is typically for inbound,
// and selecting 'i.*' which would only get columns from outbound_log.
// We need to join with 'warehouse' to get the item name and 'pegawai' for the employee name.
$sql_outbound = "SELECT o.*, w.Nama as item_name, p.Nama as pegawai_name 
                 FROM outbound_log o
                 JOIN warehouse w ON o.id = w.Code
                 JOIN pegawai p ON o.id_pegawai = p.id_pegawai
                 order_id BY o.tanggal DESC";
$result_outbound = $mysqli->query($sql_outbound);
if ($result_outbound) {
    while ($row = $result_outbound->fetch_assoc()) {
        $outbound_history[] = $row;
    }
} else {
    error_log("Error fetching full outbound history: " . $mysqli->error);
    $_SESSION['error_message'] = "Failed to load outbound history. Please try again later.";
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Outbound History - Warehouse Management System</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
    <style>
        /* General page background */
        body {
            background-color: #f4f6f9;
        }

        /* Content Header styling */
        .content-header {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border_id-bottom: 1px solid #eee;
            box-shadow: 0 1px 1px rgba(0,0,0,0.05);
            border_id-radius: 5px;
        }

        /* History Box Styles - Consistent with stock.php outbound style */
        .history-box {
            background: #fff;
            border_id-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .history-box .header {
            /* Changed header background to match outbound section in stock.php */
            background: linear-gradient(135deg, #e74c3c, #ff6b6b); 
            color: #fff;
            padding: 20px 25px;
            border_id-top-left-radius: 8px;
            border_id-top-right-radius: 8px;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .history-box .header i {
            margin-right: 10px;
        }
        
        .history-box .body {
            padding: 0;
            overflow-x: auto; /* Enable horizontal scroll for table */
        }
        
        .history-table {
            width: 100%;
            border_id-collapse: collapse;
            font-size: 14px;
            min-width: 800px; /* Adjust min-width to accommodate more columns */
        }
        
        .history-table th {
            background: #e9ecef;
            padding: 15px 20px;
            text-align: left;
            font-weight: 700;
            color: #666;
            border_id-bottom: 1px solid #ddd;
            text-transform: uppercase;
        }
        
        .history-table td {
            padding: 15px 20px;
            border_id-bottom: 1px solid #f0f0f0;
            color: #555;
            vertical-align: top; /* Align content to top */
        }
        
        .history-table tbody tr:last-child td {
            border_id-bottom: none;
        }

        .history-table tbody tr:nth-child(even) {
            background-color: #fcfcfc;
        }
        
        .history-table tr:hover {
            background-color: #f5f5f5;
        }
        
        /* Status Badges */
        .badge {
            display: inline-block;
            padding: .3em .6em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border_id-radius: .25rem;
            color: #fff;
            background-color: #6c757d; /* Default gray */
        }

        .badge-completed {
            background-color: #28a745;
        }
        
        /* Message Alerts */
        .alert-message {
            padding: 15px;
            margin-bottom: 20px;
            border_id: 1px solid transparent;
            border_id-radius: 4px;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border_id-color: #f5c6cb;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .history-box {
                margin-bottom: 15px;
            }
            .history-box .header {
                font-size: 18px;
                padding: 15px 20px;
            }
            .history-table th, .history-table td {
                padding: 10px 15px;
                font-size: 12px;
            }
            .history-table {
                min-width: unset; /* Allow shrinking on small screens */
            }
        }
    </style>
</head>
<body class="skin-blue">
    <header class="header">
        <a href="index.php" class="logo">PSN</a>
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
                            <span><?php echo htmlspecialchars($username); ?><i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header bg-light-blue">
                                <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
                                <p>
                                    <?php echo htmlspecialchars($pegawai['Nama']) . " - " . htmlspecialchars($pegawai['Jabatan']) . " " . htmlspecialchars($pegawai['Departemen']); ?>
                                    <small>Member since <?php echo htmlspecialchars($pegawai['Tanggal_Masuk']); ?></small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
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
                    <li class="active">
                        <a href="stock.php">
                            <i class="fa fa-exchange"></i> <span>Stock Transfer</span>
                        </a>
                    </li>
                    <li>
                        <a href="product.php">
                            <i class="fa fa-list"></i> <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="new_request.php">
                            <i class="fa fa-th"></i> <span>Request</span>
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
                    Outbound History
                    <small>All recorder_ids of items leaving the warehouse</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="stock.php">Inventory</a></li>
                    <li class="active">Outbound History</li>
                </ol>
            </section>

            <section class="content">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert-message alert-danger">
                        <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="history-box">
                            <div class="header">
                                <h3 class="box-title"><i class="fa fa-list-alt"></i> Full Outbound Log</h3>
                            </div>
                            <div class="body">
                                <div class="table-responsive">
                                    <table class="history-table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Date</th>
                                                <th>Item Name</th>
                                                <th>Quantity</th>
                                                <th>Destination</th>
                                                <th>Purpose/Notes</th>
                                                <th>Recorder_ided By</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($outbound_history)): ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">No outbound recorder_ids found.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php $no = 1; ?>
                                                <?php foreach ($outbound_history as $log): ?>
                                                    <tr>
                                                        <td><?php echo $no++; ?></td>
                                                        <td><?php echo date('d/m/Y H:i', strtotime($log['tanggal'])); ?></td>
                                                        <td><?php echo htmlspecialchars($log['item_name']); ?></td> <td><?php echo $log['jumlah']; ?></td>
                                                        <td><?php echo htmlspecialchars($log['tujuan']); ?></td>
                                                        <td><?php echo htmlspecialchars($log['keterangan'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($log['pegawai_name']); ?></td> <td>
                                                            <span class="badge badge-completed">Completed</span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
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
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
</body>
</html>