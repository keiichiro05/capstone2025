<!DOCTYPE html>
<?php
include "../konekdb.php";
session_start();
$idpegawai=$_SESSION['idpegawai'];
if(!isset($_SESSION['username'])){
	header ("location:../index.php");
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
                         <?php 
						$username=$_SESSION['username'];
						$iduser=$_SESSION['idpegawai'];
						$usersql = mysqli_query("SELECT * FROM pegawai where id_pegawai='$iduser'"); 
						$hasiluser=mysqli_fetch_array($usersql);
						?>
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo "$hasiluser[Nama]"; ?><i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header bg-light-blue">
                                    <img src="../img/<?php echo "$hasiluser[foto]";?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php echo $hasiluser['Nama']." - ".$hasiluser['Jabatan']; ?>
                                        <small>Member since <?php echo "$hasiluser[Tanggal_Masuk]"; ?></small>
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
                            <img src="../img/<?php echo "$hasiluser[foto]";?>" class="img-circle" alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p>Hello, <?php echo "$hasiluser[Nama]"; ?></p>

                            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                        </div>
                    </div>
                    <!-- search form -->
                    
                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->
<ul class="sidebar-menu">
                        <li class="active">
                            <a href="index.php">
                                <i class="fa fa-user"></i> <span>Penerimaan Pegawai</span>
                            </a>
                        </li>
                        <li >
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
                        <?php echo "$hasiluser[Nama]"; ?>
                        <small><?php echo $hasiluser['Jabatan']; ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li><a href="#"><?php echo $hasiluser['Jabatan']; ?></a></li>
                        <li class="active"><?php echo "$hasiluser[Nama]"; ?></li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title"><img src="../img/<?php echo $hasiluser['foto'];?>"/></h3>
                                </div><!-- /.box-header -->
                            </div><!-- /.box -->

                            <!-- /.box -->
                        </div><!-- /.col -->
                        <div class="col-md-6">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">Biodata Pegawai</h3>
                                </div><!-- /.box-header -->
                                <div class="box-body no-padding">
                                    <table class="table table-condensed">
                                    	<tr>
                                            
                                            <th>ID Pegawai</th>
                                            <th style="width: 10px">:</th>
                                            <td><?php echo $hasiluser['id_pegawai']; ?></td>
                                            
                                        </tr>
                                        <tr>
                                            
                                            <th>Nama</th>
                                            <th style="width: 10px">:</th>
                                            <td><?php echo $hasiluser['Nama']; ?></td>
                                            
                                        </tr>
                                        <tr>
                                            
                                            <th>Alamat</th>
                                            <th style="width: 10px">:</th>
                                            <td><?php echo $hasiluser['Alamat']; ?></td>
                                            
                                        </tr>
                                        <tr>
                                            
                                            <th>Telepon</th>
                                            <th style="width: 10px">:</th>
                                            <td><?php echo $hasiluser['Telepon']; ?></td>
                                            
                                        </tr>
                                        <tr>
                                            
                                            <th>Jabatan</th>
                                            <th style="width: 10px">:</th>
                                            <td><?php echo $hasiluser['Jabatan']; ?></td>
                                            
                                        </tr>
                                        <tr>
                                            
                                            <th>Gaji</th>
                                            <th style="width: 10px">:</th>
                                            <td><?php echo $hasiluser['Gaji']; ?></td>
                                            
                                        </tr>
                                        <tr>
                                            
                                            <th>Tanggal Masuk</th>
                                            <th style="width: 10px">:</th>
                                            <td><?php echo $hasiluser['Tanggal_Masuk']; ?></td>
                                            
                                        </tr>
                                        <tr>
                                            
                                            <th>Tanggal Keluar</th>
                                            <th style="width: 10px">:</th>
                                            <?php if($hasiluser['Tanggal_Keluar']!=null){
                                            echo "<td> ".$hasiluser['Tanggal_Keluar']."</td>";
                                            }else{ 
											echo "<td> - </td>";
											}?>
                                        </tr>
                                        <tr>
                                            
                                            <th>Status Pegawai</th>
                                            <th style="width: 10px">:</th>
                                            <td><?php echo $hasiluser['status_pegawai']; ?></td>
                                            
                                        </tr>
                                        
                                        
                                    </table>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->

                        </div><!-- /.col -->
                    </div><!-- /.row -->
                    
                </section><!-- /.content -->                
            </aside>
            
            <!-- /.right-side -->
        </div><!-- ./wrapper -->
        <!-- jQuery 2.0.2 -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
    </body>
</html>