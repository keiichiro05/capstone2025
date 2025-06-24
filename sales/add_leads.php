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

// Ambil data accounts untuk dropdown
$accountsql = mysqli_query($mysqli, "SELECT account_id, account_name FROM account ORDER BY created_at DESC");

// Ambil data contacts untuk dropdown
$contactsql = mysqli_query($mysqli, "SELECT id, first_name FROM contact ORDER BY first_name ASC");


// Enum values untuk business_line dan source (harus disamakan dengan enum di DB)
$business_lines = ['CHO', 'QHO', 'SHO'];
$sources = ['Cold Call','IoT','Email','Online External Media','Sales Visit','Telemarketing','Web E-Commerce','Web Inquiry'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lead_name = $_POST['lead_name'];
    $account_id = $_POST['account_id'];
    $contact_id = $_POST['contact_id'];
    $business_line = $_POST['business_line'];
    $source = $_POST['source'];
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime($start_date . ' +1 month'));

    $status = $_POST['status'];

    // Insert ke database leads
    $insert_sql = "INSERT INTO leads (lead_name, account_id, contact_id, business_line, source, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($mysqli, $insert_sql);
    mysqli_stmt_bind_param($stmt, 'siisssss', $lead_name, $account_id, $contact_id, $business_line, $source, $start_date, $end_date, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: leads.php?message=success");
        exit();
    } else {
        $error = "Error: " . mysqli_error($mysqli);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Add Leads</title>
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
                <li class="active"><a href="leads.php"><i class="fa fa-lightbulb-o"></i> <span>Leads</span></a></li>
                <li><a href="opportunity.php"><i class="fa fa-rocket"></i> <span>Opportunity</span></a></li>
                <li><a href="quotation.php"><i class="fa fa-truck"></i> <span>Quotation</span></a></li>
                <li><a href="purchase_order.php"><i class="fa fa-clipboard"></i> Purchase Order</a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Add Leads <small>Create new lead</small></h1>
            <ol class="breadcrumb">
                <li><a href="leads.php"><i class="fa fa-user-plus"></i> Leads</a></li>
                <li class="active">Add Leads</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <form action="" method="post" role="form">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <div class="box-body">
                        <div class="form-group">
                            <label for="lead_name">Lead Name</label>
                            <input type="text" name="lead_name" class="form-control" id="lead_name" required>
                        </div>

                        <div class="form-group">
                            <label for="account_id">Account</label>
                            <select name="account_id" id="account_id" class="form-control" required>
                                <option value="">- Select Account -</option>
                                <?php while ($row = mysqli_fetch_assoc($accountsql)) : ?>
                                    <option value="<?php echo $row['account_id']; ?>">
                                        <?php echo htmlspecialchars($row['account_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="contact_id">Contact</label>
                            <select name="contact_id" id="contact_id" class="form-control" required>
                                <option value="">- Select Contact -</option>
                                     <?php while ($row = mysqli_fetch_assoc($contactsql)) : ?>
                                <option value="<?php echo $row['id']; ?>">
                                     <?php echo htmlspecialchars($row['first_name']); ?>
                                </option>
                                     <?php endwhile; ?>
                                </select>
                        </div>
                        <div class="form-group">
                            <label for="business_line">Business Line</label>
                            <select name="business_line" id="business_line" class="form-control" required>
                                <option value="">- Select Business Line -</option>
                                <?php foreach ($business_lines as $bl) : ?>
                                    <option value="<?php echo $bl; ?>"><?php echo $bl; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="source">Source</label>
                            <select name="source" id="source" class="form-control" required>
                                <option value="">- Select Source -</option>
                                <?php foreach ($sources as $src) : ?>
                                    <option value="<?php echo $src; ?>"><?php echo $src; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                             <label for="start_date">Start Date</label>
                             <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                        </div>

                        <div class="form-group">
                              <label for="end_date">End Date</label>
                             <input type="  date" name="end_date" id="end_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <input type="text" name="status" id="status" class="form-control" value="Open" readonly>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="lead_detail.php" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/app.js"></script>
</body>
</html>
