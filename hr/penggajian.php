<!DOCTYPE html>
<?php include('../konekdb.php');
session_start();
$username=$_SESSION['username'];
$idpegawai=$_SESSION['idpegawai'];
$cekuser=mysqli_query("SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'HR'");
$user=mysqli_fetch_array($cekuser);
$getpegawai=mysqli_query("SELECT * FROM pegawai where id_pegawai='$idpegawai'");
$pegawai=mysqli_fetch_array($getpegawai);
if($user['jmluser']=="0")
{
header("location:../index.php");
};?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>
		Admin | HR</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- font Awesome -->
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- DATA TABLES -->
        <link href="../css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
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
            <a href="index.html" class="logo">
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
                                <span><?php echo "$username"?> <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                 <li class="user-header bg-light-blue">
                                    <img src="../img/<?php echo $pegawai['foto']?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php 
										echo $pegawai['Nama']." - ".$pegawai['Jabatan']." ".$pegawai['Departemen'];?>
                                        <small>Member since <?php echo "$pegawai[Tanggal_Masuk]"; ?></small>
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
                           <img src="../img/<?php echo $pegawai['foto']?>" class="img-circle" alt="User Image" />
                          </div>
                        <div class="pull-left info">
                            <p>Hello, <?php echo "$username"?></p>

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
                        <li >
                            <a href="index.php">
                                <i class="fa fa-user"></i> <span>Penerimaan Pegawai</span>
                            </a>
                        </li>
                        <li class="active">
                            <a href="penggajian.php">
                                <i class="fa fa-dollar"></i> <span>Penggajian</span>
                            </a>
                        </li>
                        <li >
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
                        Human Resource
                        <small>Admin</small>                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Human Resource</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content"><br>
				<h1>Update Gaji</h1>
				<form role="form" method="post" action="insertgaji.php">
				             <div class="box-body">
                                        <div class="form-group">
                                            <label for="id_pegawai">ID Pegawai</label>
                                            <input type="text" class="form-control" name="id_pegawai" placeholder="id_pegawai">
                                        </div>
										
										<div class="form-group">
                                            <label for="hari">Hari Aktif</label>
                                            <input type="text" class="form-control" name="aktif" placeholder="Hari Aktif">
                                        </div>
										
                                        <div class="form-group">
                                            <label for="hari">Cuti</label>
                                            <input type="text" class="form-control" name="cuti" placeholder="cuti">
                                        </div>
										
										<div class="form-group">
                                            <label for="hari">Lembur</label>
                                            <input type="text" class="form-control" name="lembur" placeholder="lembur">
                                        </div>
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary" input type="submit" >Submit</button>
                                    </div>
                                </form>
                            </div><!-- /.box -->

                <!-- Main content -->
                <section class="content">
                <h1>Penggajian Pegawai</h1>
                <table class="table table-striped">
				<thead>
                <tr><td>No</td>
				<td>ID Pegawai</td>
				<td>Hari Aktif</td>
				<td>Cuti</td>
				<td>Lembur</td>
				<td>Bulan</td>
				<td>Total</td>
				</tr></thead><tbody>
                
				<?php
				

$tampil=mysqli_query("select * from penggajian");


$total_gaji = mysqli_fetch_array(mysqli_query("SELECT round(SUM(total)) AS JUMLAH
FROM penggajian"));


$i=0;
while($row = mysqli_fetch_array($tampil))
{
$i++;
echo "<tr>";
echo "<td>" . $i."</td>";
echo "<td>" . $row['id_pegawai'] . "</td>";
echo "<td>" . $row['hari_aktif'] . "</td>";
echo "<td>" . $row['cuti'] . "</td>";
echo "<td>" . $row['lembur'] . "</td>";
echo "<td>" . $row['date'] . "</td>";
echo "<td>" . $row['total'] . "</td>";
echo "<td>";
} ?>  
<div class="form-group">

											
											
                                        </div>
								<?php echo "</td>";
echo "</tr>";
echo "<tr><td colspan=\"6\" align=\"right\"><b>Jumlah   </b></td><td><b>$total_gaji[0]</b></td></tr>";
echo "</tbody>";
echo "</table>";

?>
<!--<a href="tambahpegawai.php?aksi=Ditolak&&id=<?php echo $row['id_pendaftaran']?>&&Nama=<?php echo $row['nama'];?>&&Departemen=<?php echo $row['departemen'];?>">-->
											<?php echo "<a href=\"kirimTotalGaji.php?total=$total_gaji[0]\"><button class=\"btn btn-success\" type=\"submit\" td colspan=\"6\" align=\"right\">Kirim</button></a>";  ?>
																		
                                       
										
					
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


    </body>
</html>