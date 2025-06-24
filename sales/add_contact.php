<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Ambil data user yang sedang login
$iduser = $_SESSION['idpegawai'];
$usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$iduser'");
$hasiluser = mysqli_fetch_array($usersql);

// Ambil data account dari tabel 'account' untuk dropdown
$accountsql = mysqli_query($mysqli, "SELECT * FROM account ORDER BY created_at DESC");

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contact_name = $_POST['contact_name'];
    $account = $_POST['account'];  // Ini sudah dari dropdown
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $job_title = $_POST['job_title'];
    $department = $_POST['department'];

    // Validasi dan simpan ke database
    $insert_sql = "INSERT INTO contact (first_name, account, phone, job_title, department, email) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($mysqli, $insert_sql);
    mysqli_stmt_bind_param($stmt, 'ssssss', $contact_name, $account, $phone, $job_title, $department, $email);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: contact.php?message=success");
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
                        <span><?php echo $hasiluser['Nama']; ?> <i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="<?php echo $hasiluser['foto']; ?>" class="img-circle" alt="User Image" />
                            <p>
                                <?php echo $hasiluser['Nama'] . " - " . $hasiluser['Jabatan']; ?>
                                <small>Member since <?php echo $hasiluser['Tanggal_Masuk']; ?></small>
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
                    <img src="<?php echo $hasiluser['foto']; ?>" class="img-circle" alt="User Image" />
                </div>
                <div class="pull-left info">
                    <p>Hello, <?php echo $hasiluser['Nama']; ?></p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>
            <!-- Sidebar menu -->
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li><a href="account.php"><i class="fa fa-users"></i> <span>Account</span></a></li>
                <li class="active"><a href="contact.php"><i class="fa fa-envelope"></i> <span>Contact</span></a></li>
                <li><a href="products.php"><i class="fa fa-archive"></i> <span>Product</span></a></li>
                <li><a href="product_request.php"><i class="fa fa-plus-square"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-lightbulb-o"></i> <span>Leads</span></a></li>
                <li><a href="opportunity.php"><i class="fa fa-rocket"></i> <span>Opportunity</span></a></li>
                <li><a href="sales_order.php"><i class="fa fa-truck"></i> <span>Sales Order</span></a></li>
                <!-- Other menu -->
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Add Contact <small>Create new contact</small></h1>
            <ol class="breadcrumb">
                <li><a href="contact.php"><i class="fa fa-file-text"></i> Contacts</a></li>
                <li class="active">Add Contact</li>
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
                            <label for="contact_name">Contact Name</label>
                            <input type="text" name="contact_name" class="form-control" id="contact_name" required>
                        </div>

                        <div class="form-group">
                            <label for="account">Account</label>
                            <select name="account" id="account" class="form-control" required>
                                <option value="">- Select Account -</option>
                                <?php while ($row = mysqli_fetch_assoc($accountsql)) : ?>
                                    <option value="<?php echo htmlspecialchars($row['account_name']); ?>">
                                        <?php echo htmlspecialchars($row['account_name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" name="phone" class="form-control" id="phone" required>
                        </div>

                        <div class="form-group">
                            <label for="job_title">Job Title</label>
                            <input type="text" name="job_title" class="form-control" id="job_title" required>
                        </div>

                        <div class="form-group">
                            <label for="job_title"> Department</label>
                            <input type="text" name="department" class="form-control" id="department" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email address</label>
                            <input type="email" name="email" class="form-control" id="email" required>
                        </div>
                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Save Contact</button>
                        <a href="contact.php" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </aside>
</div>

<!-- jQuery -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
</body>
</html>
