<!DOCTYPE html>
<?php include('../konekdb.php');
session_start();
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];
if(!isset($_SESSION['username'])){
	header("location:../index.php?status=please login first");
	exit();
	}
    $username = $_SESSION['username'];
    $idpegawai = $_SESSION['idpegawai'];

    $cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
    if (!$cekuser) {
        die("Error checking user authorization: " . mysqli_error($mysqli));
    }
    $user = mysqli_fetch_array($cekuser);

    $getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
    if (!$getpegawai) {
        die("Error fetching employee data: " . mysqli_error($mysqli));
    }
    $pegawai = mysqli_fetch_array($getpegawai);

    if ($user['jmluser'] == "0") {
        header("location:../index.php");
        exit();
    }
    ?>
    <html>
        <head>
            <meta charset="UTF-8">
            <title>Warehouse Cabang Blitar</title>
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
                            
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="glyphicon glyphicon-user"></i>
                                    <span><?php echo $username;?><i class="caret"></i></span>
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
                                            <a href="#">Warehouse</a>
                                        </div>
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Cabang</a>
                                        </div>
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Blitar</a>
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
                                    <i class="fa fa-dashboard"></i> <span>Statistical</span>
                                </a>
                            </li>
                            <li>
                                <a href="product.php">
                                    <i class="fa fa-list-alt"></i> <span>Category</span>
                                </a>
                            </li>
                            <li class="active">
                                <a href="new_request.php">
                                    <i class="fa fa-th"></i> <span>order_id</span>
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
                            Warehouse
                            <small>Blitar</small>
                        </h1>
                        <ol class="breadcrumb">
                        </ol>
                    </section>

                    <!-- Main content -->
                    <section class="content">
                        <h1>order_id Barang</h1>
                        <form method="post" onsubmit="alert('Success Create!'); this.style.border_id = '2px solid green';">
                        <div class="row">
                        <div class="col-md-2">
                            <strong>Product Id</strong>
                            <input type="number" class="form-control" placeholder="Input Prod ID..." name="code">
                            </div>
                            <div class="col-md-2">
                            <strong>Nama Barang</strong>
                            <input type="text" class="form-control" placeholder="Input Nama Barang..." name="nama">
                            </div>
                            <div class="col-md-2">
                            <strong>Category</strong>
                            <select name="kategori" class="form-control">
                                <option value="">---Pilih Category---</option>
                                <?php
                                $query = mysqli_query($mysqli, "SELECT nama_kategori FROM kategori");
                                while ($row = mysqli_fetch_assoc($query)) {
                                    echo "<option value=\"{$row['nama_kategori']}\">{$row['nama_kategori']}</option>";
                                }
                                ?>
                                    
                            </select>
                            </div>
                            <div class="col-md-2">  
                            <strong>Jumlah</strong>
                            <input type="number" class="form-control" placeholder="Input Minimum Barang..." name="jumlah" min="5" oninvalid="this.setCustomValidity('Jumlah minimum adalah 5')" oninput="this.setCustomValidity('')">
                            </div>
                            <div class="col-md-2">
                            <strong>Satuan</strong>
                            <input type="text" class="form-control" placeholder="Input Satuan Barang..." name="satuan">
                            </div>
                            <div class="col-md-2">
                            <strong>Reorder_id-Level</strong>
                            <input type="number" class="form-control" placeholder="Input Minimum Barang..." name="reorder_id-level" min="5" oninvalid="this.setCustomValidity('Jumlah minimum adalah 5')" oninput="this.setCustomValidity('')">
                            </div>
                            <div class="col-md-2">
                            <strong>Supplier</strong>
                            <select name="supplier" class="form-control">
                                <option value="">---Pilih Supplier---</option>
                                <?php
                                $query = mysqli_query($mysqli, "SELECT Nama FROM supplier");
                                while ($row = mysqli_fetch_assoc($query)) {
                                    echo "<option value=\"{$row['Nama']}\">{$row['Nama']}</option>";
                                }
                                ?>
                            </select>
                            </div>
                        </div>
                            <br>
                        <div class="row">
                            <div class="col-md-1">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            </div>
                        </div>
                        </form>

                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $code         = mysqli_real_escape_string($mysqli, $_POST['code']);
                            $nama         = mysqli_real_escape_string($mysqli, $_POST['nama']);
                            $kategori     = mysqli_real_escape_string($mysqli, $_POST['kategori']);
                            $jumlah       = mysqli_real_escape_string($mysqli, $_POST['jumlah']);
                            $satuan       = mysqli_real_escape_string($mysqli, $_POST['satuan']);
                            $reorder_idLevel = mysqli_real_escape_string($mysqli, $_POST['reorder_id-level']);
                            $supplier     = mysqli_real_escape_string($mysqli, $_POST['supplier']);
                        
                            if (
                                !empty($code) && !empty($nama) && !empty($kategori) &&
                                !empty($jumlah) && !empty($satuan) && !empty($reorder_idLevel) &&
                                !empty($supplier)
                            ) {
                                $insert = mysqli_query($mysqli, "INSERT INTO dariwarehouse 
                                    (code, nama, kategori, jumlah, satuan, reorder_id_level, supplier) 
                                    VALUES 
                                    ('$code', '$nama', '$kategori', '$jumlah', '$satuan', '$reorder_idLevel', '$supplier', NOW())");
                        
                                if ($insert) {
                                    echo "<div class='alert alert-success' style='margin-top:10px;'>order_id berhasil disimpan!</div>";
                                } else {
                                    echo "<div class='alert alert-danger' style='margin-top:10px;'>Gagal menyimpan order_id: " . mysqli_error($mysqli) . "</div>";
                                }
                            } else {
                                echo "<div class='alert alert-warning' style='margin-top:10px;'>Semua kolom wajib diisi!</div>";
                            }
                        }
                        ?>
                        
                        <br>
                        <h1>Daftar Pesanan</h1>                
                        <table class="table table-striped table-border_ided">
                            <tr><td>ID</td><td>Nama</td><td>Kategori</td><td>Jumlah</td><td>Satuan</td><td>Supplier</td><td>Barcode</td><td>Status</td><td>Hapus</td></tr>
                        <?php
                            $sql = "SELECT * FROM dariwarehouse";
                            $hasil = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                    
                            while ($baris = mysqli_fetch_array($hasil)) {
                                $no = $baris[0];
                                $code = $baris[1];
                                $nama = $baris[2];
                                $jumlah = $baris[3];
                                $satuan = $baris[4];
                                $supplier = $baris[5];
                                $status = $baris[6];
                                $cabang = $baris[7];
                                $kategori = $baris[8];
                                $barcode = "https://barcode.tec-it.com/barcode.ashx?data=$code&code=Code128&dpi=96"; // Generate 1D barcode URL
            
                                echo "<tr><td>$code</td><td>$nama</td><td>$kategori</td><td>$jumlah</td><td>$satuan</td><td>$supplier</td>";
                                echo "<td><img src='$barcode' alt='Barcode'></td>";
                            
                                if ($status == 0) {
                                    echo "<td><button class=\"btn btn-warning\">Pending</button></td><td><a href=\"hapus.php?no=$no\" onclick=\"alert('Success Delete!')\"><button class=\"btn btn-danger\">Hapus</button></a></td></tr>";
                                }
                                if ($status == 1) {
                                    echo "<td><button class=\"btn btn-success\">Accepted</button></td><td><a href=\"hapus.php?no=$no\" onclick=\"alert('Success Delete!')\"><button class=\"btn btn-danger\">Hapus</button></a></td></tr>";
                                }
                            }
                        ?>
                        </table>
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