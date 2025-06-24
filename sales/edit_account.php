<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

$iduser = $_SESSION['idpegawai'];
$usersql = $mysqli->query("SELECT * FROM pegawai WHERE id_pegawai='$iduser'");
if (!$usersql) {
    die("Error fetching user data: " . $mysqli->error);
}
$hasiluser = $usersql->fetch_assoc();

if (!isset($_GET['id'])) {
    die("No account ID specified.");
}

$account_id = intval($_GET['id']); // Pastikan ID adalah integer

// Ambil data account dari database
$accountsql = $mysqli->prepare("SELECT * FROM account WHERE account_id = ?");
$accountsql->bind_param("i", $account_id);
$accountsql->execute();
$result = $accountsql->get_result();
$account = $result->fetch_assoc();

if (!$account) {
    die("Account not found.");
}

// Proses update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan trim data POST
    $account_name = trim($_POST['account_name'] ?? '');
    $account_group = trim($_POST['account_group'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $updated_at = date("Y-m-d H:i:s");

    if ($account_name == '') {
        echo "<div style='color:red;'>Account Name is required.</div>";
    } else {
        // Update dengan prepared statement
        $update = $mysqli->prepare("UPDATE account SET 
            account_name = ?,
            account_group = ?,
            address = ?,
            city = ?,
            state = ?,
            country = ?,
            updated_at = ?
            WHERE account_id = ?");
        $update->bind_param("sssssssi", $account_name, $account_group, $address, $city, $state, $country, $updated_at, $account_id);

        if ($update->execute()) {
            header("Location: account.php");
            exit();
        } else {
            echo "<div style='color:red;'>Update failed: " . $update->error . "</div>";
        }
        $update->close();
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
                <li class="active"><a href="account.php"><i class="fa fa-users"></i> <span>Account</span></a></li>
                <li><a href="contact.php"><i class="fa fa-envelope"></i> <span>Contact</span></a></li>
                <li><a href="products.php"><i class="fa fa-archive"></i> <span>Product</span></a></li>
                <li><a href="product_request.php"><i class="fa fa-plus-square"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-lightbulb-o"></i> <span>Leads</span></a></li>
                <li><a href="opportunity.php"><i class="fa fa-rocket"></i> <span>Opportunity</span></a></li>
                <li><a href="sales_order.php"><i class="fa fa-truck"></i> <span>Sales Order</span></a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Edit Account <small>Update Account Details</small></h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="account.php">Account</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Account Form</h3>
                </div>
                <form action="" method="POST">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Account Name</label>
                            <input type="text" name="account_name" class="form-control" required value="<?php echo htmlspecialchars($account['account_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Account Group</label>
                            <input type="text" name="account_group" class="form-control" value="<?php echo htmlspecialchars($account['account_group']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($account['address']); ?>">
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($account['city']); ?>">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($account['state']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" name="country" class="form-control" value="<?php echo htmlspecialchars($account['country']); ?>">
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-success">Update</button>
                        <a href="account.php" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </aside>
</div>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
</body>
</html>
