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

if (!isset($_GET['id'])) {
    die("Purchase Order ID is missing.");
}

$po_id = $_GET['id'];
$po = mysqli_fetch_assoc(mysqli_query($mysqli, "
    SELECT po.*, q.quotation_no, o.opp_name 
    FROM purchase_order po
    LEFT JOIN quotation q ON po.quotation_id = q.quotation_id
    LEFT JOIN opportunity o ON q.opp_id = o.opp_id
    WHERE po.po_id = '$po_id'
"));

if (!$po) {
    die("Purchase Order not found.");
}

// Tambah item
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_code = $_POST['product_code'];
    $quantity = (int) $_POST['quantity'];
    $unit_price = (float) $_POST['price'];
    $total_price = $unit_price * $quantity;

    $product = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT * FROM warehouse WHERE Code = '$product_code'"));
    if (!$product) {
        $error = "Product not found.";
    } elseif ($unit_price <= 0) {
        $error = "Harga tidak valid.";
    } elseif ($product['Stok'] < $quantity) {
        $error = "Stok tidak mencukupi. Sisa stok hanya: " . $product['Stok'];
    } else {
        $cek = mysqli_fetch_assoc(mysqli_query($mysqli, "
            SELECT * FROM purchase_order_item 
            WHERE po_id = '$po_id' AND product_code = '$product_code'
        "));

        if ($cek) {
            $new_qty = $cek['quantity'] + $quantity;
            $new_total = $unit_price * $new_qty;
            mysqli_query($mysqli, "
                UPDATE purchase_order_item 
                SET quantity='$new_qty', unit_price='$unit_price', total_price='$new_total'
                WHERE item_id='{$cek['item_id']}'
            ");
        } else {
            mysqli_query($mysqli, "
                INSERT INTO purchase_order_item (po_id, product_code, quantity, unit_price, total_price)
                VALUES ('$po_id', '$product_code', '$quantity', '$unit_price', '$total_price')
            ");
        }

        mysqli_query($mysqli, "
            UPDATE warehouse SET Stok = Stok - $quantity WHERE Code = '$product_code'
        ");

        header("Location: purchase_order_detail.php?id=$po_id");
        exit();
    }
}

$items = mysqli_query($mysqli, "
    SELECT i.*, w.Nama AS product_name
    FROM purchase_order_item i
    LEFT JOIN warehouse w ON i.product_code = w.Code
    WHERE i.po_id = '$po_id'
");

$products = mysqli_query($mysqli, "SELECT Code, Nama FROM warehouse");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Purchase Order Detail</title>
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
                <li><a href="product_request.php"><i class="fa fa-plus-square"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-lightbulb-o"></i> <span>Leads</span></a></li>
                <li><a href="opportunity.php"><i class="fa fa-rocket"></i> <span>Opportunity</span></a></li>
                <li><a href="quotation.php"><i class="fa fa-truck"></i> <span>Quotation</span></a></li>
                <li class="active"><a href="purchase_order.php"><i class="fa fa-clipboard"></i> <span>Purchase Order</span></a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Purchase Order Detail <small>for PO: <?php echo htmlspecialchars($po['po_no']); ?></small></h1>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-body row">
                    <div class="col-md-4"><strong>PO No:</strong> <?php echo htmlspecialchars($po['po_no']); ?></div>
                    <div class="col-md-4"><strong>Opportunity:</strong> <?php echo htmlspecialchars($po['opp_name']); ?></div>
                    <div class="col-md-4"><strong>Quotation:</strong> <?php echo htmlspecialchars($po['quotation_no']); ?></div>
                    <div class="col-md-4"><strong>Order Date:</strong> <?php echo htmlspecialchars($po['order_date']); ?></div>
                </div>
            </div>

            <div class="box box-success">
                <form method="post" class="form-inline" style="margin-bottom:20px;">
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
                            <label for="price">Price</label>
                            <input type="number" name="price" id="price" class="form-control" required min="0" step="0.01" placeholder="Harga per unit">
                        </div>
                        <div class="form-group col-md-2" style="margin-top:25px;">
                            <button type="submit" class="btn btn-success">Add Item</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">PO Items</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Product</th>
                                <th>Qty</th>
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
                                    <td><?php echo number_format($row['unit_price'], 2); ?></td>
                                    <td><?php echo number_format($row['total_price'], 2); ?></td>
                                </tr>
                                <?php $grand_total += $row['total_price']; ?>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Grand Total</th>
                                <th><?php echo number_format($grand_total, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="box-footer">
                    <a href="purchase_order.php" class="btn btn-default">Back</a>
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
