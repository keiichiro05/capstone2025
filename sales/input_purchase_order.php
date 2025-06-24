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

// Ambil data quotation dan opportunity untuk dropdown
$quotations = mysqli_query($mysqli, "SELECT quotation_id, quotation_no, opp_id FROM quotation");
$opportunities = mysqli_query($mysqli, "SELECT opp_id, opp_name FROM opportunity");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $po_no = mysqli_real_escape_string($mysqli, $_POST['po_no']);
    $quotation_id = intval($_POST['quotation_id']);
    $delivery_date = !empty($_POST['delivery_date']) ? mysqli_real_escape_string($mysqli, $_POST['delivery_date']) : null;
    $delivery_status = mysqli_real_escape_string($mysqli, $_POST['delivery_status']);
    $payment_status = mysqli_real_escape_string($mysqli, $_POST['payment_status']);
    $order_date = date('Y-m-d'); // tanggal saat ini

    // Ambil opp_id dari quotation_id
    $getOppId = mysqli_query($mysqli, "SELECT opp_id FROM quotation WHERE quotation_id = $quotation_id");
    $oppRow = mysqli_fetch_assoc($getOppId);
    if (!$oppRow) {
        $error = "Invalid Quotation selected. Opportunity not found.";
    } else {
        $opp_id = intval($oppRow['opp_id']);

        // Sesuaikan nama kolom di database, saya asumsikan 'opp_id'
        $sql = "INSERT INTO purchase_order 
            (po_no, quotation_id, opp_id, delivery_status, payment_status, order_date, delivery_date, created_by)
            VALUES (
                '$po_no', $quotation_id, $opp_id, 
                '$delivery_status', '$payment_status', 
                '$order_date', " . ($delivery_date ? "'$delivery_date'" : "NULL") . ", $iduser
            )";

        if (mysqli_query($mysqli, $sql)) {
            header("Location: purchase_order.php?msg=PO successfully added.");
            exit();
        } else {
            $error = "Failed to insert data: " . mysqli_error($mysqli);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Add Purchase Order</title>
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
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="account.php"><i class="fa fa-users"></i> Account</a></li>
                <li><a href="contact.php"><i class="fa fa-envelope"></i> Contact</a></li>
                <li><a href="products.php"><i class="fa fa-archive"></i> Product</a></li>
                <li><a href="product_request.php"><i class="fa fa-plus-square"></i> Product Request</a></li>
                <li><a href="leads.php"><i class="fa fa-shopping-cart"></i> Leads</a></li>
                <li><a href="opportunity.php"><i class="fa fa-lightbulb-o"></i> Opportunity</a></li>
                <li><a href="quotation.php"><i class="fa fa-truck"></i> Quotation</a></li>
                <li class="active"><a href="purchase_order.php"><i class="fa fa-clipboard"></i> <span>Purchase Order</span></a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Add Purchase Order <small>Create new Purchase Order</small></h1>
            <ol class="breadcrumb">
                <li><a href="purchase_order.php"><i class="fa fa-truck"></i> Purchase Order</a></li>
                <li class="active">Add Purchase Order</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <form action="" method="post" role="form">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="box-body">
                        <div class="form-group">
                            <label for="po_no">PO No</label>
                            <input type="text" name="po_no" class="form-control" id="po_no" maxlength="50" required value="<?php echo isset($_POST['po_no']) ? htmlspecialchars($_POST['po_no']) : '' ?>">
                        </div>

                        <div class="form-group">
                            <label for="quotation_id">Quotation</label>
                            <select name="quotation_id" id="quotation_id" class="form-control" required>
                                <option value="">-- Select Quotation --</option>
                                <?php mysqli_data_seek($quotations, 0); while ($q = mysqli_fetch_assoc($quotations)): ?>
                                    <option value="<?php echo $q['quotation_id']; ?>" <?php if (isset($_POST['quotation_id']) && $_POST['quotation_id'] == $q['quotation_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($q['quotation_no']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="opportunity_id">Opportunity</label>
                            <select name="opportunity_id" id="opportunity_id" class="form-control" required>
                                <option value="">-- Select Opportunity --</option>
                                <?php mysqli_data_seek($opportunities, 0); while ($o = mysqli_fetch_assoc($opportunities)): ?>
                                    <option value="<?php echo htmlspecialchars($o['opp_id']); ?>" <?php if (isset($_POST['opportunity_id']) && $_POST['opportunity_id'] == $o['opp_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($o['opp_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="delivery_date">Delivery Date</label>
                            <input type="date" name="delivery_date" id="delivery_date" class="form-control" value="<?php echo isset($_POST['delivery_date']) ? htmlspecialchars($_POST['delivery_date']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="delivery_status">Delivery Status</label>
                            <select name="delivery_status" id="delivery_status" class="form-control" required>
                                <option value="Pending" <?php if (isset($_POST['delivery_status']) && $_POST['delivery_status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="Delivered" <?php if (isset($_POST['delivery_status']) && $_POST['delivery_status'] === 'Delivered') echo 'selected'; ?>>Delivered</option>
                                <option value="Cancelled" <?php if (isset($_POST['delivery_status']) && $_POST['delivery_status'] === 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="payment_status">Payment Status</label>
                            <select name="payment_status" id="payment_status" class="form-control" required>
                                <option value="Unpaid" <?php if (isset($_POST['payment_status']) && $_POST['payment_status'] === 'Unpaid') echo 'selected'; ?>>Unpaid</option>
                                <option value="Partial" <?php if (isset($_POST['payment_status']) && $_POST['payment_status'] === 'Partial') echo 'selected'; ?>>Partial</option>
                                <option value="Paid" <?php if (isset($_POST['payment_status']) && $_POST['payment_status'] === 'Paid') echo 'selected'; ?>>Paid</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="order_date">Order Date</label>
                            <input type="date" name="order_date" id="order_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="purchase_order.php" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>

<script>
  // Buat mapping quotation_id ke opp_id
  const quotationToOpp = {
    <?php
    mysqli_data_seek($quotations, 0);
    $pairs = [];
    while ($q = mysqli_fetch_assoc($quotations)) {
      $pairs[] = '"' . $q['quotation_id'] . '": "' . $q['opp_id'] . '"';
    }
    echo implode(", ", $pairs);
    ?>
  };

  // Saat quotation dropdown berubah, otomatis set opportunity dropdown
  document.getElementById('quotation_id').addEventListener('change', function() {
    const selectedQuotation = this.value;
    const oppId = quotationToOpp[selectedQuotation] || '';

    document.getElementById('opportunity_id').value = oppId;
  });

  // Trigger change saat halaman load untuk set nilai default jika ada
  window.onload = function() {
    document.getElementById('quotation_id').dispatchEvent(new Event('change'));
  };
</script>

</body>
</html>
