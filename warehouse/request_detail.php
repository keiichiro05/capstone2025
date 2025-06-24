<?php
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

if(!isset($_SESSION['username'])) {
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

// Get request ID
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch request details
$query = "SELECT * FROM dariwarehouse WHERE no = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if(!$request) {
    die("Request not found");
}

// Helper functions
function getStatusText($status) {
    switch($status) {
        case '0': return 'Pending';
        case '1': return 'Accepted';
        case '2': return 'Rejected';
        default: return 'Unknown';
    }
}

function getStatusClass($status) {
    switch($status) {
        case '0': return 'status-pending';
        case '1': return 'status-accepted';
        case '2': return 'status-rejected';
        default: return '';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Request Detail</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" /> 
    <style>
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border_id-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending {
            background-color: #f39c12;
            color: white;
        }
        
        .status-accepted {
            background-color: #2ecc71;
            color: white;
        }
        
        .status-rejected {
            background-color: #e74c3c;
            color: white;
        }
        
        /* Request Detail Styles */
        .request-detail {
            background: #fff;
            border_id-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .request-detail p {
            margin-bottom: 15px;
            font-size: 15px;
        }
        
        .request-detail strong {
            color: #2c3e50;
            min-width: 120px;
            display: inline-block;
        }
        
        .action-buttons {
            margin-top: 30px;
        }
        
        .action-buttons .btn {
            margin-right: 10px;
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
                    <li class="active">
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
            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Request Detail #<?= $request['no'] ?></h3>
                            </div>
                            <div class="box-body">
                                <div class="request-detail">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Product Name:</strong> <?= htmlspecialchars($request['nama']) ?></p>
                                            <p><strong>Category:</strong> <?= htmlspecialchars($request['kategori']) ?></p>
                                            <p><strong>Quantity:</strong> <?= $request['jumlah'] ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Unit:</strong> <?= htmlspecialchars($request['satuan']) ?></p>
                                            <p><strong>Supplier:</strong> <?= htmlspecialchars($request['supplier']) ?></p>
                                            <p><strong>Warehouse:</strong> <?= htmlspecialchars($request['cabang']) ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p><strong>Status:</strong> 
                                                <span class="status-badge <?= getStatusClass($request['status']) ?>">
                                                    <?= getStatusText($request['status']) ?>
                                                </span>
                                            </p>
                                            <p><strong>Date Created:</strong> <?= date('d M Y H:i', strtotime($request['date_created'])) ?></p>
                                           
                                    </div>
                                    
                                    <div class="action-buttons text-right">
                                        <a href="new_request.php" class="btn btn-default">Back to List</a>
                                        <a href="request_edit.php?id=<?= $request['no'] ?>" class="btn btn-primary">Edit Request</a>
                                    </div>
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
</body>
</html>