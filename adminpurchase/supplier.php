<?php
session_start();
require_once('../konekdb.php');

// Redirect if user not logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    header("location:../index.php");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Get employee details for sidebar
$pegawai = [];
$stmt_pegawai = $mysqli->prepare("SELECT Nama, Jabatan, Departemen, Tanggal_Masuk, foto FROM pegawai WHERE id_pegawai = ?");
if ($stmt_pegawai) {
    $stmt_pegawai->bind_param("i", $idpegawai);
    $stmt_pegawai->execute();
    $result_pegawai = $stmt_pegawai->get_result();
    if ($result_pegawai->num_rows > 0) {
        $pegawai = $result_pegawai->fetch_assoc();
    }
    $stmt_pegawai->close();
}

// Initialize message variables
$message = '';
if (isset($_SESSION['success'])) {
    $message = "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $message = "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
    unset($_SESSION['error']);
}

// Initialize form variables
$new_id_supplier = '';
$nama_supplier = '';
$alamat_supplier = '';
$telepon_supplier = '';
$nama_perusahaan = '';
$produk_supplier = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_supplier_submit'])) {
    $new_id_supplier = $_POST['id_supplier_form'] ?? '';
    $nama_supplier = $_POST['nama_supplier'] ?? '';
    $alamat_supplier = $_POST['alamat_supplier'] ?? '';
    $telepon_supplier = $_POST['telepon_supplier'] ?? '';
    $nama_perusahaan = $_POST['nama_perusahaan'] ?? '';
    $produk_supplier = $_POST['produk_supplier'] ?? '';

    // Validate input
    if (empty($new_id_supplier) || empty($nama_supplier) || empty($alamat_supplier) || 
        empty($telepon_supplier) || empty($nama_perusahaan) || empty($produk_supplier)) {
        $_SESSION['error'] = "All fields are required!";
    } else {
        // Check if supplier ID already exists
        $stmt_check = $mysqli->prepare("SELECT id_supplier FROM supplier WHERE id_supplier = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("s", $new_id_supplier);
            $stmt_check->execute();
            $stmt_check->store_result();
            
            if ($stmt_check->num_rows > 0) {
                $_SESSION['error'] = "Supplier ID already exists!";
            } else {
                // Insert new supplier
                $stmt_insert = $mysqli->prepare("INSERT INTO supplier (id_supplier, Nama, Alamat, Telepon, Nama_perusahaan, Produk) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt_insert) {
                    $stmt_insert->bind_param("ssssss", $new_id_supplier, $nama_supplier, $alamat_supplier, $telepon_supplier, $nama_perusahaan, $produk_supplier);
                    if ($stmt_insert->execute()) {
                        $_SESSION['success'] = "Supplier added successfully!";
                        // Clear form fields
                        $new_id_supplier = $nama_supplier = $alamat_supplier = $telepon_supplier = $nama_perusahaan = $produk_supplier = '';
                    } else {
                        $_SESSION['error'] = "Error adding supplier: " . $stmt_insert->error;
                    }
                    $stmt_insert->close();
                } else {
                    $_SESSION['error'] = "Error preparing insert statement: " . $mysqli->error;
                }
            }
            $stmt_check->close();
        } else {
            $_SESSION['error'] = "Error preparing check statement: " . $mysqli->error;
        }
    }
    header("Location: supplier.php");
    exit();
}

// Get supplier count for sidebar
$totSupplier = ['jml' => 0];
$stmt_count = $mysqli->prepare("SELECT COUNT(*) AS jml FROM supplier");
if ($stmt_count) {
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $totSupplier = $result_count->fetch_assoc();
    $stmt_count->close();
}
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
                        <img src="../img/<?= htmlspecialchars($pegawai['foto'] ?? 'default.jpg'); ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?= htmlspecialchars($username); ?></p>
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
                        <li class="active">
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
                <h1>Supplier Management</h1>
            </section>
            <section class="content">
                <?= $message; ?>

                <div class="box box-primary">
                    <div class="box-header with-border_id">
                        <h3 class="box-title">Add New Supplier</h3>
                    </div>
                    <form method="post" class="form-horizontal">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="id_supplier_form" class="col-sm-2 control-label">Supplier ID</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="id_supplier_form" name="id_supplier_form" value="<?= htmlspecialchars($new_id_supplier); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nama_supplier" class="col-sm-2 control-label">Supplier Name</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" value="<?= htmlspecialchars($nama_supplier); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="alamat_supplier" class="col-sm-2 control-label">Address</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="alamat_supplier" name="alamat_supplier" value="<?= htmlspecialchars($alamat_supplier); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="telepon_supplier" class="col-sm-2 control-label">Phone</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="telepon_supplier" name="telepon_supplier" value="<?= htmlspecialchars($telepon_supplier); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nama_perusahaan" class="col-sm-2 control-label">Company</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" value="<?= htmlspecialchars($nama_perusahaan); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="produk_supplier" class="col-sm-2 control-label">Product</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="produk_supplier" name="produk_supplier" value="<?= htmlspecialchars($produk_supplier); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" name="add_supplier_submit" class="btn btn-primary">Add Supplier</button>
                        </div>
                    </form>
                </div>

                <div class="box">
                    <div class="box-header with-border_id">
                        <h3 class="box-title">Supplier List</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-border_ided table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Phone</th>
                                    <th>Company</th>
                                    <th>Product</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt_suppliers = $mysqli->prepare("SELECT id_supplier, Nama, Alamat, Telepon, Nama_perusahaan, Produk FROM supplier ORDER BY Nama ASC");
                                if ($stmt_suppliers) {
                                    $stmt_suppliers->execute();
                                    $result_suppliers = $stmt_suppliers->get_result();
                                    
                                    if ($result_suppliers->num_rows > 0) {
                                        while ($row = $result_suppliers->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($row['id_supplier']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['Nama']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['Alamat']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['Telepon']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['Nama_perusahaan']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['Produk']) . "</td>";
                                            echo "<td>";
                                            echo "<a href='edit_supplier.php?id=" . urlencode($row['id_supplier']) . "' class='btn btn-sm btn-warning'>Edit</a> ";
                                            echo "<a href='delete_supplier.php?id=" . urlencode($row['id_supplier']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>No suppliers found</td></tr>";
                                    }
                                    $stmt_suppliers->close();
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </aside>
    </div>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/plugins/datatables/jquery.dataTables.js"></script>
    <script src="../js/plugins/datatables/dataTables.bootstrap.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    <script>
        $(function() {
            $('.table').DataTable();
        });
    </script>
</body>
</html>