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

if (!isset($_GET['opp_id'])) {
    die("Opportunity ID is missing.");
}

$opp_id = $_GET['opp_id'];
$opp = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT * FROM opportunity WHERE opp_id = '$opp_id'"));

// Cek / buat quotation
$quotation = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT * FROM quotation WHERE opp_id = '$opp_id'"));
if (!$quotation) {
    // Generate quotation number
    $result = mysqli_query($mysqli, "
        SELECT MAX(CAST(SUBSTRING_INDEX(quotation_no, '-', -1) AS UNSIGNED)) AS max_no 
        FROM quotation
    ");
    $row = mysqli_fetch_assoc($result);
    $next_number = $row['max_no'] ? (int)$row['max_no'] + 1 : 1;
    $quotation_no = 'Q-' . str_pad($next_number, 4, '0', STR_PAD_LEFT);

    $quotation_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+14 days')); // default 2 weeks from today
    $description = '';
    $status = 'Draft';

    mysqli_query($mysqli, "
        INSERT INTO quotation (quotation_no, quotation_date, due_date, status, opp_id, description)
        VALUES ('$quotation_no', '$quotation_date', '$due_date', '$status', '$opp_id', '$description')
    ");
    $quotation_id = mysqli_insert_id($mysqli);
} else {
    $quotation_id = $quotation['quotation_id'];
}


// Tambah item jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_code = $_POST['product_code'];
    $quantity = (int) $_POST['quantity'];
    $discount = (float) $_POST['discount'];

    $product = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT * FROM warehouse WHERE Code = '$product_code'"));
    if (!$product) {
        $error = "Produk dengan kode $product_code tidak ditemukan.";
    } else {
        $price = $product['Harga'];
        $total = ($price * $quantity) * ((100 - $discount) / 100);

        // Cek duplikasi (opsional)
        $cek = mysqli_fetch_assoc(mysqli_query($mysqli, "
            SELECT * FROM quotation_item 
            WHERE quotation_id = '$quotation_id' AND product_code = '$product_code'
        "));
        if ($cek) {
            $new_qty = $cek['quantity'] + $quantity;
            $new_total = ($price * $new_qty) * ((100 - $discount) / 100);
            mysqli_query($mysqli, "
                UPDATE quotation_item 
                SET quantity='$new_qty', discount='$discount', price='$price', total='$new_total'
                WHERE item_id='{$cek['item_id']}'
            ");
        } else {
            mysqli_query($mysqli, "
                INSERT INTO quotation_item (quotation_id, product_code, quantity, discount, price, total)
                VALUES ('$quotation_id', '$product_code', '$quantity', '$discount', '$price', '$total')
            ");
        }
        header("Location: add_quotation_detail.php?opp_id=$opp_id");
        exit();
    }
}

$items = mysqli_query($mysqli, "
    SELECT qi.*, w.Nama AS product_name
    FROM quotation_item qi
    JOIN warehouse w ON qi.product_code = w.Code
    WHERE qi.quotation_id = '$quotation_id'
");

$products = mysqli_query($mysqli, "SELECT Code, Nama FROM warehouse");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Add Quotation Detail</title>
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
                <li><a href="products.php"><i class="fa fa-file-text"></i> <span>Product</span></a></li>
                <li><a href="product_request.php"><i class="fa fa-file-text"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-lightbulb-o"></i> <span>Leads</span></a></li>
                <li><a href="opportunity.php"><i class="fa fa-rocket"></i> <span>Opportunity</span></a></li>
                <li class="active"><a href="quotation.php"><i class="fa fa-truck"></i> <span>Quotation</span></a></li>
                <li><a href="input_purchase_order.php"><i class="fa fa-plus-square"></i> <span>Input Purchase Order</span></a></li>
                <li><a href="purchase_order.php"><i class="fa fa-truck"></i> <span>Purchase Order</span></a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Add Quotation Detail <small>for Opportunity: <?php echo htmlspecialchars($opp['opp_name']); ?></small></h1>
            <ol class="breadcrumb">
                <li><a href="opportunity.php"><i class="fa fa-rocket"></i> Opportunity</a></li>
                <li class="active">Add Quotation Detail</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <form method="post" role="form" class="form-inline" style="margin-bottom:20px;">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <div class="box-body row">
                        <div class="form-group col-md-4">
                            <label for="product_code">Product</label>
                            <select name="product_code" id="product_code" class="form-control" required>
                                <option value="">- Select Product -</option>
                                <?php while ($p = mysqli_fetch_assoc($products)): ?>
                                    <option value="<?php echo $p['Code']; ?>"><?php echo htmlspecialchars($p['Nama']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="quantity">Qty</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" required min="1" placeholder="Qty">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="discount">Disc (%)</label>
                            <input type="number" name="discount" id="discount" class="form-control" min="0" max="100" value="0" placeholder="Disc %">
                        </div>
                        <div class="form-group col-md-2" style="margin-top:25px;">
                            <button type="submit" class="btn btn-success">Add Item</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Quotation Items</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Disc (%)</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $no = 1; $grand_total = 0; ?>
                        <?php while ($row = mysqli_fetch_assoc($items)): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo $row['discount']; ?></td>
                                <td><?php echo number_format($row['price'],2); ?></td>
                                <td><?php echo number_format($row['total'],2); ?></td>
                            </tr>
                            <?php $grand_total += $row['total']; ?>
                        <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Grand Total</th>
                                <th><?php echo number_format($grand_total,2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="box-footer">
                    <a href="template_quotation.php?opp_id=<?php echo $opp_id; ?>" target="_blank" class="btn btn-info">
                        View Quotation
                    </a>
                    <a href="opportunity.php" class="btn btn-default">Back</a>
                </div>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/app.js"></script>
</body>
</html>
