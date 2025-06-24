<?php
include "konekdb.php";
session_start();

// Autentikasi user
if (!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    header("location:../index.php");
    exit();
}

$idpegawai = $_SESSION['idpegawai'];
$username = $_SESSION['username'];

// Data user untuk header/sidebar
$usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
$hasiluser = mysqli_fetch_array($usersql);

// Ambil produk dari warehouse
$produk_query = mysqli_query($mysqli, "SELECT Code, Nama FROM warehouse WHERE Code IS NOT NULL AND Code != ''");
$produk = [];
while ($row = mysqli_fetch_assoc($produk_query)) {
    $produk[$row['Code']] = $row['Nama'];
}

// Proses form submit
$pesan = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_code = $_POST['item_code'];
    $nama = $_POST['nama'];
    $quantity = $_POST['quantity'];
    $reason = $_POST['reason'];

    $stmt = $mysqli->prepare("INSERT INTO sales_requests 
        (Code, no, sales_person, customer_name, nama, customer_id, id_pegawai, item_code, quantity, reason, requested_by, status, urgency, doc_number)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'medium', '')");

    $dummyCode = $item_code;
    $dummyNo = $item_code;
    $sales_person = $username;
    $customer_name = "Default Customer";
    $customer_id = "CUST001";
    $requested_by = $username;

    $stmt->bind_param("ssssssissss", $dummyCode, $dummyNo, $sales_person, $customer_name, $nama, $customer_id, $idpegawai, $item_code, $quantity, $reason, $requested_by);

    if ($stmt->execute()) {
        $pesan = "✅ Request berhasil dikirim!";
    } else {
        $pesan = "❌ Gagal mengirim request: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Product Request</title>
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
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li><a href="account.php"><i class="fa fa-users"></i> <span>Account</span></a></li>
                <li><a href="contact.php"><i class="fa fa-envelope"></i> <span>Contact</span></a></li>
                <li><a href="products.php"><i class="fa fa-archive"></i> <span>Product</span></a></li>
                <li class="active"><a href="product_request.php"><i class="fa fa-plus-square"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-lightbulb-o"></i> <span>Leads</span></a></li>
                <li><a href="opportunity.php"><i class="fa fa-rocket"></i> <span>Opportunity</span></a></li>
                <li><a href="quotation.php"><i class="fa fa-truck"></i> <span>Quotation</span></a></li>
                <li><a href="purchase_order.php"><i class="fa fa-clipboard"></i> Purchase Order</a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Product Request <small>Request product ke warehouse</small></h1>
            <ol class="breadcrumb">
                <li><a href="product_request.php"><i class="fa fa-plus-square"></i> Product Request</a></li>
                <li class="active">Request Product</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <form action="" method="post" role="form">
                    <?php if ($pesan): ?>
                        <div class="alert <?php echo (strpos($pesan, 'berhasil') !== false) ? 'alert-success' : 'alert-danger'; ?>">
                            <?php echo $pesan; ?>
                        </div>
                    <?php endif; ?>

                    <div class="box-body">
                        <div class="form-group">
                            <label for="item_code">Item Code</label>
                            <select name="item_code" id="item_code" class="form-control" onchange="isiNama()" required>
                                <option value="">-- Pilih Produk --</option>
                                <?php foreach ($produk as $code => $name): ?>
                                    <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($code) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nama">Item Name</label>
                            <input type="text" name="nama" id="nama" class="form-control" readonly required>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" name="quantity" min="1" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="reason">Reason</label>
                            <textarea name="reason" rows="4" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                        <a href="products.php" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/app.js"></script>
<script>
    const productMap = <?= json_encode($produk) ?>;
    function isiNama() {
        const kode = document.getElementById('item_code').value;
        document.getElementById('nama').value = productMap[kode] || '';
    }
</script>
</body>
</html>
