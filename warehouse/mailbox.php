<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Please Login First");
    exit();
}

require_once('../konekdb.php');

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Cek apakah user memiliki hak akses ke modul Adminwarehouse (menggunakan prepared statement)
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Warehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['jmluser'] == "0") {
    header("Location: ../index.php?status=Access Declined");
    exit();
}
	include "../config.php";
    $profil=mysqli_fetch_array(mysqli_query($conn, "select p.*,DATE_FORMAT( p.Tanggal_Masuk, '%b, %Y') as tglmasuk from pegawai p,authorization a where a.username='$username' and a.id_pegawai = p.id_pegawai"));
    $pesan = mysqli_query($conn, "SELECT id_pesan, pg.nama, isi, DATE_FORMAT(waktu,'%d %b %Y %h:%i %p') as waktu, p.status, a.username
                        FROM pesan p, pegawai pg, authorization a
                        WHERE p.dari = pg.id_pegawai AND a.id_pegawai = p.ke AND a.username = '$username' AND p.draft=0
                        ORDER BY waktu DESC");
    $count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM
                                            (SELECT pg.nama, isi, DATE_FORMAT(waktu,'%d %b %Y %h:%i %p'), p.status, a.username
                                            FROM pesan p, pegawai pg, authorization a
                                            WHERE p.dari = pg.id_pegawai AND a.id_pegawai = p.ke AND a.username = '$username' AND p.status=0) PESAN"));
    $count_draft = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM
                                            (SELECT id_pesan, pg.nama, isi, DATE_FORMAT(waktu,'%d %b %Y %h:%i %p') as waktu, p.status, a.username
                                            FROM pesan p, pegawai pg, authorization a
                                            WHERE p.ke = pg.id_pegawai AND a.id_pegawai = p.dari AND a.username = '$username' AND p.draft=1
                                            ORDER BY waktu DESC) PESAN"));

    $name = mysqli_query($conn, "SELECT nama FROM pegawai p, authorization a WHERE a.id_pegawai = p.id_pegawai AND a.username NOT LIKE '$username'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Manager Dashboard</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --dark: #5a5c69;
            --light: #f8f9fc;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fc;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary) 0%, #224abe 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border_id-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .dashboard-header h1 {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .dashboard-header h1 small {
            color: rgba(255,255,255,0.7);
            font-size: 16px;
            display: block;
            margin-top: 5px;
        }
        
        .header-date-time {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .header-date-time i {
            margin-right: 5px;
        }
        
        .small-box {
            border_id-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border_id: none;
            overflow: hidden;
        }
        
        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .small-box .inner {
            padding: 15px;
        }
        
        .small-box h3 {
            font-size: 28px;
            font-weight: 600;
            margin: 0 0 5px 0;
        }
        
        .small-box p {
            font-size: 15px;
            margin-bottom: 0;
        }
        
        .small-box .icon {
            font-size: 70px;
            position: absolute;
            right: 15px;
            top: 15px;
            transition: all 0.3s;
            opacity: 0.2;
        }
        
        .small-box:hover .icon {
            opacity: 0.3;
            transform: scale(1.1);
        }
        
        .small-box-footer {
            background: rgba(0,0,0,0.05);
            color: rgba(255,255,255,0.8);
            display: block;
            padding: 8px 0;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .small-box-footer:hover {
            background: rgba(0,0,0,0.1);
            color: white;
        }
        
        .box {
            border_id-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border_id: none;
            margin-bottom: 20px;
        }
        
        .box-header {
            border_id-top-left-radius: 10px;
            border_id-top-right-radius: 10px;
            padding: 15px 20px;
            background-color: white;
            border_id-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .box-header h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: inline-block;
        }
        
        .box-header .box-tools {
            position: absolute;
            right: 20px;
            top: 15px;
        }
        
        .box-body {
            padding: 20px;
            background-color: white;
            border_id-bottom-left-radius: 10px;
            border_id-bottom-right-radius: 10px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .alert-item {
            border_id-left: 4px solid var(--danger);
            margin-bottom: 10px;
            border_id-radius: 6px;
            transition: all 0.3s;
            padding: 10px 15px;
        }
        
        .alert-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stock-critical {
            background-color: #f8d7da;
            border_id-left-color: var(--danger);
        }
        
        .stock-warning {
            background-color: #fff3cd;
            border_id-left-color: var(--warning);
        }
        
        .products-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .products-list .item {
            padding: 10px 0;
            border_id-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .products-list .item:last-child {
            border_id-bottom: none;
        }
        
        .product-title {
            font-weight: 500;
            display: block;
            margin-bottom: 5px;
        }
        
        .product-description {
            font-size: 13px;
            color: #6c757d;
        }
        
        .sidebar-menu > li > a {
            border_id-radius: 5px;
            margin: 5px 10px;
        }
        
        .sidebar-menu > li.active > a {
            background-color: var(--primary);
            color: white;
        }
        
        .sidebar-menu > li > a:hover {
            background-color: rgba(78, 115, 223, 0.1);
        }
        
        .user-panel {
            padding: 15px;
        }
        
        .skin-blue .sidebar-menu > li:hover > a, 
        .skin-blue .sidebar-menu > li.active > a {
            color: white;
            background: var(--primary);
            border_id-left-color: var(--primary);
        }
        
        .info-box {
            border_id-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }
        
        .info-box-icon {
            border_id-radius: 8px 0 0 8px;
            display: block;
            float: left;
            height: 90px;
            width: 90px;
            text-align: center;
            font-size: 45px;
            line-height: 90px;
            background: rgba(0,0,0,0.2);
        }
        
        .info-box-content {
            padding: 15px;
            margin-left: 90px;
        }
        
        .info-box-text {
            display: block;
            font-size: 16px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .info-box-number {
            display: block;
            font-size: 22px;
            font-weight: 600;
        }
        
        .progress-description {
            display: block;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .bg-primary { background-color: var(--primary) !important; }
        .bg-success { background-color: var(--success) !important; }
        .bg-info { background-color: var(--info) !important; }
        .bg-warning { background-color: var(--warning) !important; }
        .bg-danger { background-color: var(--danger) !important; }
        .bg-purple { background-color: #6f42c1 !important; }
        
        .text-primary { color: var(--primary) !important; }
        .text-success { color: var(--success) !important; }
        .text-info { color: var(--info) !important; }
        .text-warning { color: var(--warning) !important; }
        .text-danger { color: var(--danger) !important; }
        
        .label-primary { background-color: var(--primary) !important; }
        .label-success { background-color: var(--success) !important; }
        .label-info { background-color: var(--info) !important; }
        .label-warning { background-color: var(--warning) !important; }
        .label-danger { background-color: var(--danger) !important; }
    </style>
    <?php include('styles.php'); ?>
</head>
<body class="skin-blue">
    <?php include('navbar.php'); ?>
    
    <div class="wrapper row-offcanvas row-offcanvas-left">
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <aside class="left-side sidebar-offcanvas">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="../img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?php echo htmlspecialchars($username); ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                <ul class="sidebar-menu">
                    <li>
                        <a href="streamlit.php">
                            <i class="fa fa-signal"></i> <span>Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php">
                            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="list_request.php">
                            <i class="fa fa-list"></i> <span>List Request</span>
                        </a>
                    </li>
                    <li>
                        <a href="daftarACC.php">
                            <i class="fa fa-undo"></i> <span>Request History</span>
                        </a>
                    </li>
                    <li>
                        <a href="stock.php">
                            <i class="fa fa-cube"></i> <span>Inventory</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="mailbox.php">
                            <i class="fa fa-envelope"></i> <span>Mailbox</span>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>
            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        Inbox  
                        <small>Warehouse Manager</small>
                    </h1>
                    <ol class="breadcrumb">
                    </ol>
                </section>
                </section>
                <!-- /.sidebar -->
            </aside>

            <aside class="right-side">
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
                                                <i class="fa fa-inbox"></i>
                                                <h3 class="box-title">INBOX</h3>
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
                                                    <li class="active"><a href="mailbox.php"><i class="fa fa-inbox"></i> Inbox <?php echo $c;?></a></li>
                                                    <li><a href="draft.php"><i class="fa fa-pencil-square-o"></i> Drafts <?php echo $cd;?></a></li>
                                                    <li><a href="sent.php"><i class="fa fa-mail-forward"></i> Sent</a></li>
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
                                                    if($p['status']==0){
                                                        echo "<tr class=\"unread\">";
                                                    }else{
                                                        echo "<tr>";
                                                    }
                                                ?>
                                                        <td class="small-col"><input type="checkbox" /></td>
                                                        <td class="small-col"><i class="fa fa-star"></i></td>
                                                        <td class="name"><a href="isi.php?id=<?php echo $p['0'];?>&s=1"><?php echo $p['nama'];?></a></td>
                                                        <td class="subject"><a href="isi.php?id=<?php echo $p['0'];?>&s=1"><?php echo $p['isi'];?></a></td>
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
                                <div class="box-footer clearfix">
                                    
                                </div><!-- box-footer -->
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