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

$account_id = intval($_GET['id']);

// Ambil data account untuk konfirmasi delete
$stmt = $mysqli->prepare("SELECT * FROM account WHERE account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();
$stmt->close();

if (!$account) {
    die("Account not found.");
}

// Proses delete setelah konfirmasi form POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $del = $mysqli->prepare("DELETE FROM account WHERE account_id = ?");
        $del->bind_param("i", $account_id);
        if ($del->execute()) {
            $del->close();
            header("Location: account.php?msg=deleted");
            exit();
        } else {
            echo "<div style='color:red;'>Failed to delete account: " . $mysqli->error . "</div>";
        }
    } else {
        // Jika batal delete, redirect ke account.php
        header("Location: account.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delete Account | E-pharm</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
</head>
<body class="skin-blue">
<header class="header">
    <a href="index.php" class="logo">E-pharm</a>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
        </a>
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
                <li><a href="leads.php"><i class="fa fa-shopping-cart"></i> <span>Leads</span></a></li>
                <li><a href="inventory_check.php"><i class="fa fa-cubes"></i> <span>Opportunity</span></a></li>
                <li><a href="salesorder.php"><i class="fa fa-truck"></i> <span>Sales Order</span></a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Delete Account <small>Confirm Delete</small></h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="account.php">Account</a></li>
                <li class="active">Delete</li>
            </ol>
        </section>

        <section class="content">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Are you sure you want to delete this account?</h3>
                </div>
                <div class="box-body">
                    <p><strong>Account Name:</strong> <?php echo htmlspecialchars($account['account_name']); ?></p>
                    <p><strong>Account Group:</strong> <?php echo htmlspecialchars($account['account_group']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($account['address']); ?></p>
                </div>
                <form method="POST">
                    <div class="box-footer">
                        <button type="submit" name="confirm" value="yes" class="btn btn-danger">Yes, Delete</button>
                        <button type="submit" name="confirm" value="no" class="btn btn-default">Cancel</button>
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
