<!DOCTYPE html>
<?php
include "../konekdb.php";
session_start();
$idpegawai = $_SESSION['idpegawai'] ?? null;
if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Fetch user data
$username = $_SESSION['username'];
$iduser = $_SESSION['idpegawai'];
$usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$iduser'"); 
$hasiluser = mysqli_fetch_array($usersql);

// Handle case when user data not found
if (!$hasiluser) {
    die("User data not found");
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>E-pharm | Profil Pegawai</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- font Awesome -->
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="skin-blue">
        <!-- header logo: style can be found in header.less -->
        <header class="header">
            <a href="index.php" class="logo">
                E-pharm
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo htmlspecialchars($hasiluser['Nama']); ?><i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header bg-light-blue">
                                    <img src="../img/<?php echo htmlspecialchars($hasiluser['foto']); ?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php echo htmlspecialchars($hasiluser['Nama']." - ".$hasiluser['Jabatan']); ?>
                                        <small>Member since <?php echo htmlspecialchars($hasiluser['Tanggal_Masuk']); ?></small>
                                    </p>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="profil.php" class="btn btn-default btn-flat" id="profileBtn">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="../logout.php" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="left-side sidebar-offcanvas">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="../img/<?php echo htmlspecialchars($hasiluser['foto']); ?>" class="img-circle" alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p>Hello, <?php echo htmlspecialchars($hasiluser['Nama']); ?></p>
                            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                        </div>
                    </div>
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <li class="active">
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Statistical</span>
                            </a>
                        </li>
                        <li>
                            <a href="new_request.php">
                                <i class="fa fa-th"></i> <span>order_id</span>
                            </a>
                        </li>
                        <li>
                            <a href="cuti.php">
                                <i class="fa fa-suitcase"></i> <span>Cuti</span>
                            </a>
                        </li>
                        <li>
                            <a href="mailbox.php">
                                <i class="fa fa-comments"></i> <span>Mailbox</span>
                            </a>
                        </li>
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">                
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        <?php echo htmlspecialchars($hasiluser['Nama']); ?>
                        <small><?php echo htmlspecialchars($hasiluser['Jabatan']); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li><a href="#"><?php echo htmlspecialchars($hasiluser['Jabatan']); ?></a></li>
                        <li class="active"><?php echo htmlspecialchars($hasiluser['Nama']); ?></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="box box-primary">
                                <div class="box-body box-profile">
                                    <img class="profile-user-img img-responsive img-circle" src="../img/<?php echo htmlspecialchars($hasiluser['foto']); ?>" alt="User profile picture">
                                    <h3 class="profile-username text-center"><?php echo htmlspecialchars($hasiluser['Nama']); ?></h3>
                                    <p class="text-muted text-center"><?php echo htmlspecialchars($hasiluser['Jabatan']); ?></p>
                                    <ul class="list-group list-group-unbordered">
                                        <li class="list-group-item">
                                            <b>ID Pegawai</b> <a class="pull-right"><?php echo htmlspecialchars($hasiluser['id_pegawai']); ?></a>
                                        </li>
                                        <li class="list-group-item">
                                            <b>Status</b> <a class="pull-right"><?php echo htmlspecialchars($hasiluser['status_pegawai']); ?></a>
                                        </li>
                                        <li class="list-group-item">
                                            <b>Tanggal Masuk</b> <a class="pull-right"><?php echo htmlspecialchars($hasiluser['Tanggal_Masuk']); ?></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Biodata Pegawai</h3>
                                </div>
                                <div class="box-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th width="30%">Nama Lengkap</th>
                                            <td><?php echo htmlspecialchars($hasiluser['Nama']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td><?php echo htmlspecialchars($hasiluser['Alamat']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Telepon</th>
                                            <td><?php echo htmlspecialchars($hasiluser['Telepon']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Jabatan</th>
                                            <td><?php echo htmlspecialchars($hasiluser['Jabatan']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Gaji</th>
                                            <td><?php echo 'Rp. ' . number_format($hasiluser['Gaji'], 0, ',', '.'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Keluar</th>
                                            <td><?php echo $hasiluser['Tanggal_Keluar'] ? htmlspecialchars($hasiluser['Tanggal_Keluar']) : '-'; ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="box-footer">
                                    <a href="edit_profil.php" class="btn btn-primary">Edit Profil</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section><!-- /.content -->                
            </aside>
            <!-- /.right-side -->
        </div><!-- ./wrapper -->

        <!-- jQuery 2.0.2 -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
        
        <script>
        // Prevent dropdown from closing when clicking on profile button
        $(document).ready(function() {
            $('#profileBtn').click(function(e) {
                e.stopPropagation();
            });
        });
        </script>
    </body>
</html>