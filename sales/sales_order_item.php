<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

$iduser = $_SESSION['idpegawai'];
$usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$iduser'");
$hasiluser = mysqli_fetch_array($usersql);

// Proses tambah item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_penjualan = (int) $_POST['id_penjualan'];
    $code = mysqli_real_escape_string($mysqli, $_POST['code']);
    $jumlah = (int) $_POST['jumlah'];
    $harga = (float) $_POST['harga'];

    if ($id_penjualan && $code && $jumlah > 0 && $harga >= 0) {
        $stmt = $mysqli->prepare("INSERT INTO sales_order_items (id_penjualan, code, jumlah, harga) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isid", $id_penjualan, $code, $jumlah, $harga);
        if ($stmt->execute()) {
            $msg = "Item berhasil ditambahkan.";
        } else {
            $msg = "Gagal menambahkan item: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $msg = "Semua data harus diisi dengan benar.";
    }
}

// Ambil data produk
$produk = [];
$res_produk = mysqli_query($mysqli, "SELECT code, namabarang FROM products");
while ($row = mysqli_fetch_assoc($res_produk)) {
    $produk[] = $row;
}

// Ambil data sales order
$sales_orders = [];
$res_orders = mysqli_query($mysqli, "SELECT id_penjualan FROM sales_orders ORDER BY id_penjualan DESC");
while ($row = mysqli_fetch_assoc($res_orders)) {
    $sales_orders[] = $row;
}

// Ambil item
$itemlist = [];
$res_items = mysqli_query($mysqli, "SELECT s.*, p.namabarang FROM sales_order_items s JOIN products p ON s.code = p.code ORDER BY s.id_item DESC");
while ($row = mysqli_fetch_assoc($res_items)) {
    $itemlist[] = $row;
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
            <h1>Sales Order Items <small>Detail Penjualan</small></h1>
        </section>

        <section class="content">
            <?php if (isset($msg)): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Tambah Item ke Sales Order</h3>
                </div>
                <div class="box-body">
                    <form method="post" action="sales_order_item.php">
                        <div class="form-group">
                            <label>ID Penjualan</label>
                            <select name="id_penjualan" class="form-control" required>
                                <option value="">Pilih ID</option>
                                <?php foreach ($sales_orders as $so): ?>
                                    <option value="<?php echo $so['id_penjualan']; ?>"><?php echo $so['id_penjualan']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Produk</label>
                            <select name="code" class="form-control" required>
                                <option value="">Pilih Produk</option>
                                <?php foreach ($produk as $p): ?>
                                    <option value="<?php echo $p['code']; ?>"><?php echo $p['code'] . ' - ' . $p['namabarang']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Harga</label>
                            <input type="number" step="0.01" name="harga" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success">Tambah Item</button>
                    </form>
                </div>
            </div>

            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Daftar Item Penjualan</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID Item</th>
                                <th>ID Penjualan</th>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itemlist as $item): ?>
                            <tr>
                                <td><?php echo $item['id_item']; ?></td>
                                <td><?php echo $item['id_penjualan']; ?></td>
                                <td><?php echo $item['namabarang']; ?></td>
                                <td><?php echo $item['jumlah']; ?></td>
                                <td>Rp <?php echo number_format($item['harga'], 2, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($item['jumlah'] * $item['harga'], 2, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($itemlist)): ?>
                            <tr><td colspan="6" class="text-center">Belum ada data item.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/AdminLTE/app.js"></script>
</body>
</html>
