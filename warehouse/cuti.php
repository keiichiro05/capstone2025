<!DOCTYPE html>
<?php 
session_start();

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];
if (!isset($username)) {
    header("location:../index.php?status=please login first");
    exit();
}
if (!isset($idpegawai)) {
    header("location:../index.php?status=please login first");
    exit();
}
include('../konekdb.php');

$cekuser=mysqli_query($mysqli,"SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
$user=mysqli_fetch_array($cekuser);
$getpegawai=mysqli_query($mysqli, "SELECT * FROM pegawai where id_pegawai='$idpegawai'");
$pegawai=mysqli_fetch_array($getpegawai);
if($user['jmluser']=="0")
{
header("location:../index.php");
};?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Warehouse Manager </title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- font Awesome -->
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- Morris chart -->
        <link href="../css/morris/morris.css" rel="stylesheet" type="text/css" />
        <!-- jvectormap -->
        <link href="../css/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
        <!-- fullCalendar -->
        <link href="../css/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css" />
        <!-- Daterange picker -->
        <link href="../css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
        <!-- bootstrap wysihtml5 - text editor -->
        <link href="../css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" /> 

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="skin-blue">
        <!-- header logo: style can be found in header.less -->
        <header class="header">
            <a href="index.html" class="logo">
                <!-- Add the class icon to your logo image or logo icon to add the margining -->
                PSN
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
                        <!-- Messages: style can be found in dropdown.less-->
                        
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo $username;?> <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header bg-light-blue">
                                    <img src="img/<?php echo $pegawai['foto'];?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php 
										echo $pegawai['Nama']." - ".$pegawai['Jabatan']." ".$pegawai['Departemen'];?>
                                        <small>Member since <?php echo "$pegawai[Tanggal_Masuk]"; ?></small>
                                    </p>
                                </li>
                                <!-- Menu Body -->
                                <li class="user-body">
                                    <div class="col-xs-4 text-center">
                                        <a href="#">Admin</a>
                                    </div>
                                    <div class="col-xs-4 text-center">
                                        <a href="#">Warehouse</a>
                                    </div>
                                    <div class="col-xs-4 text-center">
                                        <a href="#"></a>
                                    </div>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
								 <div class="pull-left">
                                        <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
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
                          <img src="img/<?php echo $pegawai['foto'];?>" class="img-circle" alt="User Image" />
                               </div>
                        <div class="pull-left info">
                            <p>Hello, <?php echo $username;?></p>

                            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                        </div>
                    </div>
                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                     <ul class="sidebar-menu">
                        <li>
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="product.php">
                                <i class="fa fa-list-alt"></i> <span>Categories</span>
                            </a>
                        </li>
                        <li >
                            <a href="new_request.php">
                                <i class="fa fa-th"></i> <span>order_ids</span>
                            </a>
                        </li>
                        <li>
                            <a href="history_request.php">
                                <i class="fa fa-history" aria-hidden="true"></i> <span>order_id History</span>
                            </a>
                        </li>
						<li class="active">
                            <a href="cuti.php">
                                <i class="fa fa-suitcase"></i> <span>Leave</span>
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
                        Warehouse
                        <small>Admin</small>
                    </h1>
                    <ol class="breadcrumb">
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                <form method="post">
                    <h1>Form Cuti</h1><br>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Tanggal Mulai</strong>
                            <input type="date" name="mulai" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <strong>Tanggal Selesai</strong>
                            <input type="date" name="selesai" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <strong>Detail Cuti</strong>
                            <select name="detail" class="form-control">
                            <option value="">---Pilih Detail---</option>
                            <option value="1">Sakit</option>
                            <option value="2">Hamil</option>
                            <option value="3">Lainnya</option>
                        </select>
                        </div>
                        <div class="col-md-1">
                            <br>
                            <input type="submit" class="btn btn-primary" value="Submit">
                        </div>
                    </div><br>
                </form>    
                <?php
                include "konekdb.php";
                error_reporting(0);
                $mulai=$_POST['mulai'];
                $selesai=$_POST['selesai'];
                $detail=$_POST['detail'];
                $aksi=0;

                if($mulai&&$selesai&&$detail){
                mysqli_query("INSERT INTO cuti (id_pegawai, Nama, Departemen,Tanggal_Mulai, Tanggal_Selesai, Detail_cuti, Aksi) VALUES ('$idpegawai','$pegawai[Nama]', '$pegawai[Departemen]','$mulai','$selesai','$detail','$aksi')");}else{}
                ?>
                        <h1>Pengajuan Cuti</h1>
                        <table class="table table-border_ided table-striped">
                        <tr><td>Tanggal Mulai</td><td>Tanggal Selesai</td><td>Detail Cuti</td><td>Status</td></tr>
                        <?php
                        $sql = "SELECT * FROM cuti where id_pegawai='$idpegawai'";
                        $hasil = mysqli_query($mysqli, $sql);
                        while ($baris=mysqli_fetch_array($hasil)){
                        $mulai=$baris[7];
                        $selesai=$baris[8];
                        $detail=$baris[4];
                        $status=$baris[5];
        
                        echo "<tr><td>$mulai</td><td>$selesai</td>";
                        
                        if($detail==1){
                        echo "<td>Sakit</td>";}
                        if($detail==2){
                        echo "<td>Hamil</td>";}
                        if($detail==3){
                        echo "<td>Lainnya</td>";}


                        if($status==0){
                        echo "<td><button class=\"btn btn-warning\">Pending</button></td></tr>";}
                        if($status==1){
                        echo "<td><button class=\"btn btn-success\">Accepted</button></td></tr>";}
						if($status==2){
                        echo "<td><button class=\"btn btn-success\">Denied</button></td></tr>";}
                        }
                    ?>
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

        <!-- add new calendar event modal -->


        <!-- jQuery 2.0.2 -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <!-- jQuery UI 1.10.3 -->
        <script src="../js/jquery-ui-1.10.3.min.js" type="text/javascript"></script>
        <!-- Bootstrap -->
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <!-- Morris.js charts -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
        <script src="../js/plugins/morris/morris.min.js" type="text/javascript"></script>
        <!-- Sparkline -->
        <script src="../js/plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>
        <!-- jvectormap -->
        <script src="../js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js" type="text/javascript"></script>
        <script src="../js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js" type="text/javascript"></script>
        <!-- fullCalendar -->
        <script src="../js/plugins/fullcalendar/fullcalendar.min.js" type="text/javascript"></script>
        <!-- jQuery Knob Chart -->
        <script src="../js/plugins/jqueryKnob/jquery.knob.js" type="text/javascript"></script>
        <!-- daterangepicker -->
        <script src="../js/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
        <!-- Bootstrap WYSIHTML5 -->
        <script src="../js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
        <!-- iCheck -->
        <script src="../js/plugins/iCheck/icheck.min.js" type="text/javascript"></script>

        <!-- AdminLTE App -->
        <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
        
        <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
        <script src="../js/AdminLTE/dashboard.js" type="text/javascript"></script>        

    </body>
</html>