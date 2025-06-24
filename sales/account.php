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

// Ambil data account dari tabel 'account'
$accountsql = mysqli_query($mysqli, "SELECT * FROM account ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Account</title>
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

            <form action="#" method="get" class="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Search..."/>
                    <span class="input-group-btn">
                        <button type='submit' name='search' id='search-btn' class="btn btn-flat">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
            </form>

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
            <h1>Account <small>Manage Accounts</small></h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Account</li>
            </ol>
        </section>

        <section class="content">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">List of Accounts</h3>
                    <div class="box-footer">
                        <a href="add_account.php" class="btn btn-primary">Add Account</a>
                  </div>
</div>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Account ID</th>
                                <th>Account Name</th>
                                <th>Account Group</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Country</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($accountsql)) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['account_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['account_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['account_group']); ?></td>
                                <td><?php echo htmlspecialchars($row['address']); ?></td>
                                <td><?php echo htmlspecialchars($row['city']); ?></td>
                                <td><?php echo htmlspecialchars($row['state']); ?></td>
                                <td><?php echo htmlspecialchars($row['country']); ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
                                <td>
                                    <a href="edit_account.php?id=<?php echo $row['account_id']; ?>" class="btn btn-xs btn-warning">Edit</a>
                                    <a href="delete_account.php?id=<?php echo $row['account_id']; ?>" onclick="return confirm('Are you sure want to delete this account?');" class="btn btn-xs btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
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
