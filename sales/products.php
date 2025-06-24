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

$msg = '';
$search = '';
if (isset($_GET['q'])) {
    $search = $_GET['q'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = mysqli_real_escape_string($mysqli, $_POST['code']);
    $namabarang = mysqli_real_escape_string($mysqli, $_POST['namabarang']);
    $kategori = mysqli_real_escape_string($mysqli, $_POST['kategori']);
    $satuan = mysqli_real_escape_string($mysqli, $_POST['satuan']);
    $stok_minimum = intval($_POST['stok_minimum']);
    $deviasi_demand = floatval($_POST['deviasi_demand']);
    $deviasi_lead_time = floatval($_POST['deviasi_lead_time']);

    if ($code && $namabarang && $kategori && $satuan) {
        $stmt = $mysqli->prepare("INSERT INTO products (code, namabarang, kategori, satuan, stok_minimum, deviasi_demand, deviasi_lead_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdd", $code, $namabarang, $kategori, $satuan, $stok_minimum, $deviasi_demand, $deviasi_lead_time);
        if ($stmt->execute()) {
            $msg = "Produk berhasil ditambahkan.";
        } else {
            $msg = "Gagal menambahkan produk: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $msg = "Semua field wajib diisi.";
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Products</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
</head>
<body class="skin-blue">
<header class="header">
    <a href="index.php" class="logo">U-PSN</a>
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

            <form action="products.php" method="get" class="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Search..." />
                    <span class="input-group-btn">
                        <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </form>

            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li><a href="account.php"><i class="fa fa-users"></i> <span>Account</span></a></li>
                <li><a href="contact.php"><i class="fa fa-envelope"></i> <span>Contact</span></a></li>
                <li class="active"><a href="products.php"><i class="fa fa-archive"></i> <span>Product</span></a></li>
                <li><a href="product_request.php"><i class="fa fa-plus-square"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-lightbulb-o"></i> <span>Leads</span></a></li>
                <li><a href="opportunity.php"><i class="fa fa-rocket"></i> <span>Opportunity</span></a></li>
                <li><a href="quotation.php"><i class="fa fa-truck"></i> <span>Quotation</span></a></li>
                <li><a href="purchase_order.php"><i class="fa fa-clipboard"></i> Purchase Order</a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Stock <small>List Product</small></h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Stock</li>
            </ol>
        </section>

        <section class="content">

            <?php if ($msg): ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <!DOCTYPE html>
<html>
<head>
    <title>Inventory Stock</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
</head>
<body>
    <div class="box">
        <div class="box-header">
            <h3 class="box-title">List Available Stock from Warehouse</h3>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product Id</th>
                                            <th>Item Name</th>
                                            <th>Stock</th>
                                            <th>Category</th>
                                            <th>Unit</th>
                                            <th>Stock Min</th>
                                            <th>Warehouse</th>
                                             <th>Harga</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM warehouse ORDER BY Code DESC";
                                        $hasil = $mysqli->query($sql);
                                        if ($hasil) {
                                            while ($baris = $hasil->fetch_assoc()) {
                                                echo "<tr>
                                                    <td>" . htmlspecialchars($baris['Code']) . "</td>
                                                    <td>" . htmlspecialchars($baris['Nama']) . "</td>
                                                    <td>" . htmlspecialchars($baris['Stok']) . "</td>
                                                    <td>" . htmlspecialchars($baris['Kategori']) . "</td>
                                                    <td>" . htmlspecialchars($baris['Satuan']) . "</td>
                                                    <td>" . htmlspecialchars($baris['reorder_level']) . "</td>
                                                    <td>" . htmlspecialchars($baris['cabang']) . "</td>
                                                    <td>" . htmlspecialchars($baris['Harga']) . "</td>
                                                   
                                                </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='7'>No products found.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/AdminLTE/app.js"></script>
</body>
</html>
