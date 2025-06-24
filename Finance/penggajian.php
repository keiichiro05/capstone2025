<?php 
session_start();
if(!isset($_SESSION['username'])){
	header("location:../index.php");
	exit();
	}

if(isset($_SESSION['username'])){
	$username = $_SESSION['username'];
}
	include "../config.php";
	$profil=mysqli_fetch_array(mysqli_query("select p.*,DATE_FORMAT( p.Tanggal_Masuk, '%b, %Y') as tglmasuk from pegawai p,authorization a where a.username='$username' and a.id_pegawai = p.id_pegawai"));
    //$bulan = mysqli_fetch_array(mysqli_query("SELECT DATE_FORMAT(NOW(),'%m') from DUAL"));
    //$qsaldo = mysqli_fetch_array(mysqli_query("select * from saldo WHERE DATE_FORMAT( tanggal, '%m' ) = '$bulan[0]'"));
    $query = mysqli_query("SELECT *,DATE_FORMAT(date,'%d %b %Y') as tanggal FROM gajibulan WHERE status=0");
    

    $count = mysqli_fetch_array(mysqli_query("SELECT COUNT(*) FROM
                                            (SELECT pg.nama, isi, DATE_FORMAT(waktu,'%d %b %Y %h:%i %p'), p.status, a.username
                                            FROM pesan p, pegawai pg, authorization a
                                            WHERE p.dari = pg.id_pegawai AND a.id_pegawai = p.ke AND a.username = '$username' AND p.status=0) PESAN"));
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Keuangan dan Akuntansi E-pharm</title>
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
            <a href="index.php" class="logo">
                <!-- Add the class icon to your logo image or logo icon to add the margining -->
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
                        <!-- Messages: style can be found in dropdown.less-->
                        <ul class="dropdown-menu">
                                <li class="footer">
                                    <a href="#">View all tasks</a>
                                </li>
                            </ul>
                        </li>
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo $profil['Nama'];?> <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header bg-light-blue">
                                    <img src="../<?php echo $profil['foto'];?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php echo $profil['Nama'];?> - Financial Accouting
                                        <small>Member since <?php echo $profil['tglmasuk'];?></small>
                                    </p>
                                </li>
                                <!-- Menu Body -->
                                <li class="user-body">
                                    <div class="col-xs-4 text-center">
                                        <a href="#">Followers</a>
                                    </div>
                                    <div class="col-xs-4 text-center">
                                        <a href="#">Sales</a>
                                    </div>
                                    <div class="col-xs-4 text-center">
                                        <a href="#">Friends</a>
                                    </div>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="#" class="btn btn-default btn-flat">Profile</a>
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
                            <img src="../<?php echo $profil['foto'];?>" class="img-circle" alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p>Hello, <?php echo $username;?></p>

                            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                        </div>
                    </div>
                    <!-- search form -->
                    <form action="#" method="get" class="sidebar-form">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Search..."/>
                            <span class="input-group-btn">
                                <button type='submit' name='seach' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    </form>
                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <li>
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="general.php">
                                <i class="fa fa-th"></i> <span>Akuntansi Umum</span>
                            </a>
                        </li>
                        <li class="treeview active">
                            <a href="#">
                                <i class="fa fa-bar-chart-o"></i>
                                <span>CO-Controlling</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="pemesanan.php"><i class="fa fa-angle-double-right"></i> Warehouse</a></li>
                                <li><a href="pages/charts/flot.html"><i class="fa fa-angle-double-right"></i> Sales</a></li>
                                <li><a href="penggajian.php"><i class="fa fa-angle-double-right"></i> Human Resource</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-laptop"></i>
                                <span>Kas</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="income.php"><i class="fa fa-angle-double-right"></i> Pemasukan</a></li>
                                <li><a href="exp.php"><i class="fa fa-angle-double-right"></i> Pengeluaran</a></li>
                            </ul>
                        </li>
                        
                        <li>
                            <a href="cuti.php">
                                <i class="fa fa-suitcase"></i> <span>Cuti</span>
                            </a>
                        </li>

                        <li>
                            <a href="mailbox.php">
                                <i class="fa fa-envelope"></i> <span>Mailbox</span>
                                <?php
                                    if($count[0]!=0){
                                ?>
                                <small class="badge pull-right bg-yellow"><?php echo $count[0];?></small>
                                <?php
                                    }
                                ?>
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
                        Pemesanan
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Pemesanan</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="row">
                        <div class="col-xs-12">
                            
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">Pengajuan Pemesanan</h3>                                    
                                </div><!-- /.box-header -->
                                     
                                <div class="box-body table-responsive">
                                    
                                    <table id="kas" class="table table-border_ided table-striped">
                                        <thead>
                                            <tr style=" background-color: #428bca; border_id-color: #428bca; color: #ffffff;">
                                                <th style="text-align : center;">Tanggal</th>
                                                <th style="text-align : center;">Keterangan</th>
                                                <th style="text-align : center;">Total</th>
                                                <th style="text-align : center;">Setujui</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        <?php
                                        
                                            while($gaji=mysqli_fetch_array($query)){
                                    
                                            ?>
                                            <tr>
                                                <td align="center"><?php echo $gaji['tanggal']; ?></td>
                                                <td>Penggajian Pegawai</td>
                                            
                                                <td align="right"><?php echo $gaji['total']; ?></td>
                                                <td align="center"><a href="proses_penggajian.php?id=<?php echo $gaji[0]."&pegawai=".$profil[0]; ?>"><button type="submit" class="btn btn-success" style=" width: 70px;" onclick="return confirm('Apakah Anda Yakin?')">Acc</button></a></td>
                                                
                                            </tr>
                                            <?php
                                                }
                                            ?>
                                         
                                        </tbody>

                                        
                                    </table>
                                    <br>
                                    
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
                    </div>

                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

        <!-- add new calendar event modal -->


                <!-- jQuery 2.0.2 -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <!-- DATA TABES SCRIPT -->
        <script src="../js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
        <script src="../js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="../js/AdminLTE.min.js" type="text/javascript"></script>

        <!-- page script -->
        <script type="text/javascript">
            $(function() {
                $("#example1").dataTable();
                $('#example2').dataTable({
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bSort": true,
                    "bInfo": true,
                    "bAutoWidth": false
                });
            });
        </script>

        <script type="text/javascript"> 
            var htmlobjek; 
            $(document).ready(function(){ 
               
              $("#bulan").change(function(){ 
                var bulan = $("#bulan").val();
                
                $.ajax({ 
                    url: "ambilkas.php", 
                    data: {bulan: bulan},
                    cache: false, 
                    success: function(msg){ 
                        //jika data sukses diambil dari server kita tampilkan 
                        $("#kas").html(msg); 
                    } 
                }); 
              });
              
            }); 
             
        </script>

    </body>
</html>