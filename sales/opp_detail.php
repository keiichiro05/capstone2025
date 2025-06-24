<?php
include "konekdb.php";
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Ambil data pengguna yang sedang login
$iduser = $_SESSION['idpegawai'];
$usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$iduser'");
$hasiluser = mysqli_fetch_array($usersql);

// Proses penambahan data penjualan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_penjualan = mysqli_real_escape_string($mysqli, $_POST['kode_penjualan']);
    $tanggal_jual = mysqli_real_escape_string($mysqli, $_POST['tanggal_jual']);
    $cabang = mysqli_real_escape_string($mysqli, $_POST['cabang']);
    $total_harga = mysqli_real_escape_string($mysqli, $_POST['total_harga']);

    // Validasi input
    if (!empty($kode_penjualan) && !empty($tanggal_jual) && !empty($cabang) && is_numeric($total_harga)) {
        // Insert data ke tabel sales_orders
        $stmt = $mysqli->prepare("INSERT INTO sales_orders (kode_penjualan, tanggal_jual, cabang, total_harga) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $kode_penjualan, $tanggal_jual, $cabang, $total_harga);
        if ($stmt->execute()) {
            $msg = "Data penjualan berhasil ditambahkan.";
        } else {
            $msg = "Gagal menambahkan data penjualan: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $msg = "Semua field harus diisi dengan benar.";
    }
}

// Ambil data sales_orders untuk ditampilkan
$sales_orders = [];
$result = mysqli_query($mysqli, "SELECT * FROM sales_orders ORDER BY tanggal_jual DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $sales_orders[] = $row;
    }
} else {
    $msg = "Gagal mengambil data sales orders: " . mysqli_error($mysqli);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>E-pharm | Account</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Bootstrap 3.0.2 -->
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome -->
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />

    
</head>
<body class="skin-blue">
<header class="header">
    <a href="index.php" class="logo">E-pharm</a>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
        </a>
        <div class="navbar-right">
            <ul class="nav navbar-nav">
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span><?php echo htmlspecialchars($hasiluser['Nama']); ?> <i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="<?php echo htmlspecialchars($hasiluser['foto']); ?>" class="img-circle" alt="User Image" />
                            <p>
                                <?php echo htmlspecialchars($hasiluser['Nama']) . " - " . htmlspecialchars($hasiluser['Jabatan']); ?>
                                <small>Member since <?php echo htmlspecialchars($hasiluser['Tanggal_Masuk']); ?></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="prosesLogout.php" class="btn btn-default btn-flat">Sign out</a>
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
                    <img src="<?php echo htmlspecialchars($hasiluser['foto']); ?>" class="img-circle" alt="User Image" />
                </div>
                <div class="pull-left info">
                    <p>Hello, <?php echo htmlspecialchars($hasiluser['Nama']); ?></p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>

            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li class="active"><a href="account.php"><i class="fa fa-users"></i> <span>Account</span></a></li>
                <li><a href="contact.php"><i class="fa fa-file-text"></i> <span>Contact</span></a></li>
                <li><a href="products.php"><i class="fa fa-file-text"></i> <span>Product</span></a></li>
                <li><a href="product_request.php"><i class="fa fa-file-text"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-shopping-cart"></i> <span>Leads</span></a></li>
                <li><a href="opportunity.php"><i class="fa fa-cubes"></i> <span>Opportunity</span></a></li>
                <li><a href="sales_order.php"><i class="fa fa-truck"></i> <span>Sales Order</span></a></li>
                <li><a href="sales_order_item.php"><i class="fa fa-truck"></i> <span>Sales Order Item</span></a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Sales Order <small>Manage Sales Orders</small></h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Sales Order</li>
            </ol>
        </section>

        <section class="content">
            <?php if (isset($msg)): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Add New Sales Order</h3>
                </div>
                <div class="box-body">
                    <form method="post" action="sales_order.php">
                        <div class="form-group">
                            <label for="kode_penjualan">Kode Penjualan</label>
                            <select name="kode_penjualan" class="form-control" required>
                                <option value="">-- Pilih Kode Penjualan --</option>
                                <?php
                                // Ambil kode_penjualan dari tabel sales_orders (atau tabel lain jika perlu)
                                $kodeResult = mysqli_query($mysqli, "SELECT DISTINCT kode_penjualan FROM sales_orders ORDER BY kode_penjualan ASC");
                                if ($kodeResult) {
                                    while ($row = mysqli_fetch_assoc($kodeResult)) {
                                        echo '<option value="' . htmlspecialchars($row['kode_penjualan']) . '">' . htmlspecialchars($row['kode_penjualan']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal_jual">Tanggal Jual</label>
                            <input type="date" name="tanggal_jual" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="cabang">Cabang</label>
                            <input type="text" name="cabang" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="total_harga">Total Harga</label>
                            <input type="number" step="0.01" name="total_harga" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="sales_order_item.php" class="btn btn-success" style="margin-left:10px;">Sales Order Item</a>
                    </form>
                </div>
            </div>

            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Sales Orders</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID Penjualan</th>
                                <th>Kode Penjualan</th>
                                <th>Tanggal Jual</th>
                                <th>Cabang</th>
                                <th>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sales_orders)): ?>
                                <?php foreach ($sales_orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['id_penjualan']); ?></td>
                                        <td><?php echo htmlspecialchars($order['kode_penjualan']); ?></td>
                                        <td><?php echo htmlspecialchars($order['tanggal_jual']); ?></td>
                                        <td><?php echo htmlspecialchars($order['cabang']); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($order['total_harga'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada data penjualan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<script src="../js/AdminLTE/app.js" type="text/javascript"></script>
</body>
</html>
