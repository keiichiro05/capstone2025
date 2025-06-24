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

// Fungsi untuk generate ID otomatis
function generateAccountID($mysqli) {
    $query = "SELECT account_id FROM account ORDER BY account_id DESC LIMIT 1";
    $result = $mysqli->query($query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastIdNum = (int)substr($row['account_id'], 3);
        $newIdNum = $lastIdNum + 1;
        return 'ACC' . str_pad($newIdNum, 3, '0', STR_PAD_LEFT);
    } else {
        return 'ACC001';
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = generateAccountID($mysqli);
    $account_name = trim($_POST['account_name']);
    $account_group = $_POST['account_group'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $country = trim($_POST['country']) ?: 'Indonesia';


    // Validate required fields
    if (empty($account_name) || empty($account_group) || empty($address) || empty($city) || empty($state)) {
        $error = "Please fill in all required fields (*).";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO account (account_id, account_name, account_group, address, city, state, country) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $account_id, $account_name, $account_group, $address, $city, $state, $country);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Account successfully added!";
            header("Location: account.php");
            exit();
        } else {
            $error = "Failed to add account: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>E-pharm | Add Account</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
    <style>
        .required:after {
            content: " *";
            color: red;
        }
        .error-message {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="skin-blue">
<header class="header">
    <a href="index.php" class="logo">E-pharm</a>
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
    <li><a href="quotation.php"><i class="fa fa-truck"></i> <span>Quotation</span></a></li>
      <li><a href="purchase_order.php"><i class="fa fa-clipboard"></i> Purchase Order</a></li>

            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Add Account <small>Create new Account</small></h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="account.php">Account</a></li>
                <li class="active">Add Account</li>
            </ol>
        </section>

        <section class="content">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">Account Information</h3>
                </div>
                <form method="POST" action="">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="required" for="account_name">Account Name</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" required maxlength="100" value="<?= isset($_POST['account_name']) ? htmlspecialchars($_POST['account_name']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label class="required" for="account_group">Account Group</label>
                            <select class="form-control" id="account_group" name="account_group" required>
                                <option value="">-- Select Account Group --</option>
                                <option value="Sold To" <?= (isset($_POST['account_group']) && $_POST['account_group'] === 'Sold To') ? 'selected' : '' ?>>Sold To</option>
                                <option value="Ship To" <?= (isset($_POST['account_group']) && $_POST['account_group'] === 'Ship To') ? 'selected' : '' ?>>Ship To</option>
                                <option value="Payer" <?= (isset($_POST['account_group']) && $_POST['account_group'] === 'Payer') ? 'selected' : '' ?>>Payer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="required" for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required" for="city">City</label>
                                    <input type="text" class="form-control" id="city" name="city" required maxlength="50" value="<?= isset($_POST['city']) ? htmlspecialchars($_POST['city']) : '' ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required" for="state">State/Province</label>
                                    <input type="text" class="form-control" id="state" name="state" required maxlength="50" value="<?= isset($_POST['state']) ? htmlspecialchars($_POST['state']) : '' ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="country">Country</label>
                            <input type="text" class="form-control" id="country" name="country" maxlength="50" value="<?= isset($_POST['country']) ? htmlspecialchars($_POST['country']) : 'Indonesia' ?>">
                            <small class="text-muted">Default is Indonesia</small>
                        </div>
                        <div class="row">
                    
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Account</button>
                        <a href="account.php" class="btn btn-default"><i class="fa fa-times"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
</body>
</html>