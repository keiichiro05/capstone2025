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

$lead_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$lead_sql = mysqli_query($mysqli, "SELECT * FROM leads WHERE lead_id = '$lead_id'");
$lead = mysqli_fetch_array($lead_sql);

if (!$lead) {
    echo "Lead not found.";
    exit();
}

if (isset($_GET['converted']) && $_GET['converted'] == 'success') {
    $msg = 'Lead has been successfully converted to opportunity.';
} else {
    $msg = '';
}

// Ambil nama account
$accsql = mysqli_query($mysqli, "SELECT account_name FROM account WHERE account_id = '".$lead['account_id']."'");
$acc = mysqli_fetch_array($accsql);

// Ambil nama contact (jika ada kolom contact_id)
$contact_name = '';
if (!empty($lead['contact_id'])) {
    $contactsql = mysqli_query($mysqli, "SELECT first_name FROM contact WHERE id = '".$lead['contact_id']."'");
    $contact = mysqli_fetch_array($contactsql);
    $contact_name = $contact ? $contact['first_name'] : '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Add Contact</title>
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
            <h1>Lead Detail <small>Detail Information</small></h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="leads.php">Leads</a></li>
                <li class="active">Lead Detail</li>
            </ol>
        </section>

        <section class="content">
            <?php if ($msg): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Lead Information</h3>
                    <a href="leads.php" class="btn btn-default btn-sm pull-right" style="margin-left:10px;"><i class="fa fa-arrow-left"></i> Back to Leads</a>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <tr><th>Lead Name</th><td><?php echo htmlspecialchars($lead['lead_name']); ?></td></tr>
                        <tr><th>Account</th><td><?php echo htmlspecialchars($acc['account_name']); ?></td></tr>
                        <tr><th>Contact</th><td><?php echo htmlspecialchars($contact_name); ?></td></tr>
                        <tr><th>Business Line</th><td><?php echo htmlspecialchars($lead['business_line']); ?></td></tr>
                        <tr><th>Source</th><td><?php echo htmlspecialchars($lead['source']); ?></td></tr>
                        <tr><th>Start Date</th><td><?php echo htmlspecialchars($lead['start_date']); ?></td></tr>
                        <tr><th>End Date</th><td><?php echo htmlspecialchars($lead['end_date']); ?></td></tr>
                        <tr><th>Status</th><td><?php echo htmlspecialchars($lead['status']); ?></td></tr>
                        <tr><th>Reason</th><td><?php echo nl2br(htmlspecialchars($lead['reason'])); ?></td></tr>
                    </table>
                </div>
                <div class="box-footer">
                    <?php if ($lead['status'] === 'Open'): ?>
                        <a href="convert_lead.php?id=<?php echo $lead_id; ?>" class="btn btn-success">
                            <i class="fa fa-exchange"></i> Convert to Opportunity
                        </a>
                    <?php else: ?>
                        <a href="add_opportunity.php?lead_id=<?php echo $lead_id; ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Create Opportunity
                        </a>
                    <?php endif; ?>
                    <a href="leads.php" class="btn btn-default">Back to Leads</a>
                </div>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
</body>
</html>
