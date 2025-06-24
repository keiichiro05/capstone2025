<?php 
session_start();

$idpegawai=$_SESSION['idpegawai'];
if(!isset($_SESSION['username'])){
	header("location:../index.php");
	exit();
	}

if(isset($_SESSION['username'])){
	$username = $_SESSION['username'];
}
	include "../config.php";
	$profil=mysqli_fetch_array(mysqli_query("select p.*,DATE_FORMAT( p.Tanggal_Masuk, '%b, %Y') as tglmasuk from pegawai p,authorization a where a.username='$username' and a.id_pegawai = p.id_pegawai"));
    $pesan = mysqli_query("SELECT id_pesan, pg.nama, isi, DATE_FORMAT(waktu,'%d %b %Y %h:%i %p') as waktu, p.status, a.username
                        FROM pesan p, pegawai pg, authorization a
                        WHERE p.ke = pg.id_pegawai AND a.id_pegawai = p.dari AND a.username = '$username' AND p.draft=0
                        order_id BY waktu DESC");
    $count = mysqli_fetch_array(mysqli_query("SELECT COUNT(*) FROM
                                            (SELECT pg.nama, isi, DATE_FORMAT(waktu,'%d %b %Y %h:%i %p'), p.status, a.username
                                            FROM pesan p, pegawai pg, authorization a
                                            WHERE p.dari = pg.id_pegawai
                                            AND a.id_pegawai = p.ke
                                            AND a.username = '$username' AND p.status=0) PESAN"));
    $count_draft = mysqli_fetch_array(mysqli_query("SELECT COUNT(*) FROM
                                            (SELECT id_pesan, pg.nama, isi, DATE_FORMAT(waktu,'%d %b %Y %h:%i %p') as waktu, p.status, a.username
                                            FROM pesan p, pegawai pg, authorization a
                                            WHERE p.ke = pg.id_pegawai AND a.id_pegawai = p.dari AND a.username = '$username' AND p.draft=1
                                            order_id BY waktu DESC) PESAN"));

    $name = mysqli_query("SELECT nama FROM pegawai p, authorization a WHERE a.id_pegawai = p.id_pegawai AND a.username NOT LIKE '$username'");
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Purchase | E-pharm</title>
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
                                    <img src="../img/<?php echo $profil['foto'];?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php echo $profil['Nama'];?> - Admin Purchase
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
                            <img src="../img/<?php echo $profil['foto'];?>" class="img-circle" alt="User Image" />
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
					<li >
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li >
                            <a href="pemesanan.php">
                                <i class="fa fa-list-alt"></i> <span>pemesanan1</span>
								<?php 
								$not1=mysqli_query("SELECT count(order_id) from pemesanan1 where status='0'");
								$tot1=mysqli_fetch_array($not1);
								$not2=mysqli_query("SELECT count(distinct id_transaksi) as jml from transaksi where status='1' group by id_transaksi");
								$tot2=mysqli_fetch_array($not2);
								$not3=mysqli_query("SELECT count(distinct id_transaksi) as jml from transaksi where status='4' group by id_transaksi");
								$tot3=mysqli_fetch_array($not3);
								$not4=mysqli_query("SELECT count(id_pegawai) as jml from cuti where aksi='1' and id_pegawai='$idpegawai'");
								$tot4=mysqli_fetch_array($not4);
								$not5=mysqli_query("SELECT count(id_pesan) as jml from pesan where ke='$idpegawai' and status='0'");
								$tot5=mysqli_fetch_array($not5);
								if($tot1['count(order_id)']!=0){
								?>
								 <small class="badge pull-right bg-yellow"><?php echo $tot1['count(order_id)']?></small>
								 <?php }?>
                            </a>
                        </li>
                        <li >
                            <a href="transaksi.php">
                                <i class="fa fa-envelope"></i> <span>Perijinan Transaksi</span>
								<?php if($tot2['jml']!=0){?>
								<small class="badge pull-right bg-green"><?php echo $tot2['jml']?></small>
								<?php }?>
                            </a>
                        </li>
                        <li>
                            <a href="laporan.php">
                               <i class="fa fa-check-square"></i> <span>Laporan</span>
								<?php if($tot3['jml']!=0){?>
                                <small class="badge pull-right bg-red"><?php echo $tot3['jml']?></small>
								<?php }?>
                            </a>
                        </li>
                       <li>
                            <a href="cuti.php">
                                <i class="fa fa-suitcase"></i> <span>Cuti</span>
								<?php if($tot5['jml']!=0){?>
								<small class="badge pull-right bg-aqua"><?php echo $tot5['jml']?></small>
								<?php }?>
                            </a>
                        </li>
						 <li class="active">
                            <a href="mailbox.php">
                                <i class="fa fa-comments"></i> <span>Mailbox</span>
								<?php if($tot5['jml']!=0){?>
								<small class="badge pull-right bg-aqua"><?php echo $tot5['jml']?></small>
								<?php }?>
                            </a>
                        </li>
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>

            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header no-margin">
                    <h1 class="text-center">
                        Mailbox
                    </h1>
                </section>

                <!-- Main content -->
                <section class="content">
                    <!-- MAILBOX BEGIN -->
                    <div class="mailbox row">
                        <div class="col-xs-12">
                            <div class="box box-solid">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-4">
                                            <!-- BOXES are complex enough to move the .box-header around.
                                                 This is an example of having the box header within the box body -->
                                            <div class="box-header">
                                                <i class="fa fa-mail-forward"></i>
                                                <h3 class="box-title">SENT</h3>
                                            </div>
                                            <!-- compose message btn -->
                                            <a class="btn btn-block btn-primary" data-toggle="modal" data-target="#compose-modal"><i class="fa fa-pencil"></i> Compose Message</a>
                                            <!-- Navigation - folders-->
                                            <div style="margin-top: 15px;">
                                                <ul class="nav nav-pills nav-stacked">
                                                    <li class="header">Folders</li>
                                                    <?php
                                                        $c = "";
                                                        if($count[0]!=0)
                                                            $c = "(".$count[0].")";
                                                        else
                                                            $c = "";

                                                        $cd = "";
                                                        if($count_draft[0]!=0)
                                                            $cd = "(".$count_draft[0].")";
                                                        else
                                                            $cd = "";
                                                    ?>
                                                    <li><a href="mailbox.php"><i class="fa fa-inbox"></i> Inbox <?php echo $c;?></a></li>
                                                    <li><a href="draft.php"><i class="fa fa-pencil-square-o"></i> Drafts <?php echo $cd;?></a></li>
                                                    <li class="active"><a href="sent.php"><i class="fa fa-mail-forward"></i> Sent</a></li>
                                                    <li><a href="#"><i class="fa fa-star"></i> Starred</a></li>
                                                </ul>
                                            </div>
                                        </div><!-- /.col (LEFT) -->
                                        <div class="col-md-9 col-sm-8">
                                            <div class="row pad">
                                                <div class="col-sm-6">
                                                    <label style="margin-right: 10px;">
                                                        <input type="checkbox" id="check-all"/>
                                                    </label>
                                                    <!-- Action button -->
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-default btn-sm btn-flat dropdown-toggle" data-toggle="dropdown">
                                                            Action <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            <li><a href="#">Mark as read</a></li>
                                                            <li><a href="#">Mark as unread</a></li>
                                                            <li class="divider"></li>
                                                            <li><a href="#">Move to junk</a></li>
                                                            <li class="divider"></li>
                                                            <li><a href="#">Delete</a></li>
                                                        </ul>
                                                    </div>

                                                </div>
                                                <div class="col-sm-6 search-form">
                                                    <form action="#" class="text-right">
                                                        <div class="input-group">                                                            
                                                            <input type="text" class="form-control input-sm" placeholder="Search">
                                                            <div class="input-group-btn">
                                                                <button type="submit" name="q" class="btn btn-sm btn-primary"><i class="fa fa-search"></i></button>
                                                            </div>
                                                        </div>                                                     
                                                    </form>
                                                </div>
                                            </div><!-- /.row -->

                                            <div class="table-responsive">
                                                <!-- THE MESSAGES -->
                                                <table class="table table-mailbox">
                                                <?php
                                                while($p=mysqli_fetch_array($pesan)){
                                                
                                                ?>
                                                    
                                                        <td class="small-col"><input type="checkbox" /></td>
                                                        <td class="small-col"><i class="fa fa-star"></i></td>
                                                        <td class="name"><a href="isi.php?id=<?php echo $p['0'];?>&s=0"><?php echo $p['nama'];?></a></td>
                                                        <td class="subject"><a href="isi.php?id=<?php echo $p['0'];?>&s=0"><?php echo $p['isi'];?></a></td>
                                                        <td class="time"><?php echo $p['waktu'];?></td>
                                                    </tr>
                                                <?php
                                                    }
                                                ?>
                                                </table>
                                            </div><!-- /.table-responsive -->
                                        </div><!-- /.col (RIGHT) -->
                                    </div><!-- /.row -->
                                </div><!-- /.box-body -->
                                
                            </div><!-- /.box -->
                        </div><!-- /.col (MAIN) -->
                    </div>
                    <!-- MAILBOX END -->

                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

        <!-- COMPOSE MESSAGE MODAL -->
        <div class="modal fade" id="compose-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-envelope-o"></i> Compose New Message</h4>
                    </div>
                    <form action="insert_pesan.php" method="post" id="form_pesan">
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="input-group">
                                    <input name="username" type="hidden" value="<?php echo $username; ?>" />
                                    <span class="input-group-addon">TO:</span>
                                                              
                                    <select name="nama" list="pegawai" class="form-control" placeholder="Name" required>                          
                                    
                                    <?php
                                        while($n=mysqli_fetch_array($name)){
                                    ?>
                                      <option value="<?php echo $n[0];?>"><?php echo $n[0];?></option>
                                    <?php
                                        }
                                    ?>
                                    </select>

                                </div>
                            </div>
                            
                            <div class="form-group">
                                <textarea name="message" id="email_message" class="form-control" placeholder="Message" style="height: 120px;" required></textarea>
                            </div>
                            <div class="form-group">                                
                                <div class="btn btn-success btn-file">
                                    <i class="fa fa-paperclip"></i> Attachment
                                    <input type="file" name="attachment"/>
                                </div>
                                <p class="help-block">Max. 32MB</p>
                            </div>

                        </div>
                        <div class="modal-footer clearfix">

                            <button type="submit" id="draft" name="draft" value="Send to Draft" class="btn btn-danger" data-dismiss="modal" onclick="draftsubmit();"><i class="fa fa-times"></i> Send to Draft</button>

                            <button type="submit" name="send" value="Send Message" class="btn btn-primary pull-left"><i class="fa fa-envelope"></i> Send Message</button>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

        <script type="text/javascript">
            function draftsubmit() {
                var b = document.getElementById
              if (confirm("Pesan akan disimpan ke dalam draft?")) {
                document.getElementById("form_pesan").submit();
              }
              return false;
            }

        </script>


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