<!DOCTYPE html>
<?php 
include('../konekdb.php');
session_start();

if (empty($_SESSION['username']) || empty($_SESSION['idpegawai'])) {
    header("location:../index.php?status=Silakan login ulang");
    exit();
}

// Authorization check
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Purchase'");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if ($user['jmluser'] == "0") {
    header("location:../index.php?status=Akses ditolak");
    exit();
}

// Pegawai data
$stmt_pegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmt_pegawai->bind_param("s", $_SESSION['idpegawai']);
$stmt_pegawai->execute();
$pegawai = $stmt_pegawai->get_result()->fetch_assoc();
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Purchase Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/ionicons.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-section {
            margin-bottom: 20px;
            border_id: 1px solid #e0e0e0;
            border_id-radius: 4px;
            padding: 15px;
            background: white;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }
        .dashboard-section h3 {
            margin-top: 0;
            border_id-bottom: 1px solid #eee;
            padding-bottom: 10px;
            color: #3c8dbc;
            font-size: 20px;
        }
        .dashboard-section h4 {
            color: #555;
            font-size: 16px;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .info-box {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 15px;
            border_id-radius: 8px;
            color: white;
            min-height: 90px; /* Ensure consistent height */
        }
        .info-box .info-box-icon {
            font-size: 45px;
            width: 70px;
            text-align: center;
            line-height: 70px;
            background: rgba(0,0,0,0.2);
            border_id-radius: 4px;
            padding: 10px; /* Adjust padding to make icon look better */
        }
        .info-box .info-box-content {
            flex-grow: 1;
            padding-left: 15px;
        }
        .info-box .info-box-text {
            font-size: 16px;
            margin-bottom: 5px;
            opacity: 0.9;
        }
        .info-box .info-box-number {
            font-size: 24px;
            font-weight: bold;
        }
        .small-box-footer {
            display: block;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            margin-top: 10px;
            padding-top: 5px;
            border_id-top: 1px solid rgba(255,255,255,0.3);
        }
        .small-box-footer:hover {
            color: white;
        }
        .bg-aqua { background-color: #00c0ef !important; }
        .bg-green { background-color: #00a65a !important; }
        .bg-yellow { background-color: #f39c12 !important; }
        .bg-blue { background-color: #3c8dbc !important; }
        .chart-responsive {
            margin-bottom: 20px;
        }
        .status-box {
            padding: 15px;
            border_id-radius: 4px;
            background: #f9f9f9;
            border_id: 1px solid #eee;
            margin-top: 15px;
        }
    </style>
</head>
<body class="skin-blue">
    <header class="header">
        <a href="index.php" class="logo">U-PSN</a>
        <nav class="navbar navbar-static-top" role="navigation">
            <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <div class="navbar-right">
                <ul class="nav navbar-nav">
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="glyphicon glyphicon-user"></i>
                            <span><?php echo $displayUsername; ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header bg-light-blue">
                                <img src="../img/<?php echo $displayPegawaiFoto; ?>" class="img-circle" alt="User Image" />
                                <p>
                                    <?php echo $displayPegawaiNama . " - " . $displayPegawaiJabatan . " " . $displayPegawaiDepartemen; ?>
                                    <small>Member since <?php echo $displayPegawaiTanggalMasuk; ?></small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
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
        <aside class="left-side sidebar-offcanvas">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="../img/<?= htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image">
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?= htmlspecialchars($pegawai['Nama']); ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
               <ul class="sidebar-menu">
                    <li>
                        <a href="index.php">
                            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="pemesanan.php">
                            <i class="fa fa-shopping-cart"></i> <span>order_ids</span>
                        </a>
                    </li>
                    <li>
                        <a href="transaksi.php">
                            <i class="fa fa-check-square"></i> <span>Transaction Approval</span>
                        </a>
                    </li>
                    <li>
                        <a href="supplier.php">
                            <i class="fa fa-truck"></i> <span>Supplier</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="laporan.php">
                           <i class="fa fa-file-text"></i> <span>Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="cuti.php">
                            <i class="fa fa-calendar-times-o"></i> <span>Leave Requests</span>
                        </a>
                    </li>
                    <li>
                        <a href="mailbox.php">
                            <i class="fa fa-envelope"></i> <span>Mailbox</span>
                        </a>
                    </li>
                </ul>
                        </li>
            </section>
        </aside>

        <aside class="right-side">
            <section class="content-header">
                <h1>Laporan <small>Transaksi</small></h1>
                <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Purchase</li>
                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="box">
                            <div class="box-header with-border_id">
                                <h3 class="box-title">Daftar Laporan</h3>
                                <div class="box-tools pull-right">
                                    <form class="form-inline" method="get" action="">
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="search" class="form-control" placeholder="Cari ID/Supplier..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                                            <span class="input-group-btn">
                                                <button type="submit" class="btn btn-info btn-flat"><i class="fa fa-search"></i></button>
                                            </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="box-body table-responsive">
                                <table id="example1" class="table table-border_ided table-striped">
                                    <thead>
                                        <tr>
                                            <th class="bg-blue">NO.</th>
                                            <th class="bg-blue">ID TRANSAKSI</th>
                                            <th class="bg-blue">TANGGAL</th>
                                            <th class="bg-blue">SUPPLIER</th>
                                            <th class="bg-blue">RINCIAN</th>
                                            <th class="bg-blue">TOTAL HARGA</th>
                                            <th class="bg-blue">STATUS</th>
                                            <th class="bg-blue">AKSI</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                    // Search filter
                                    $where = "status='4'";
                                    $params = [];
                                    $types = "";
                                    if (!empty($_GET['search'])) {
                                        $search = "%".$_GET['search']."%";
                                        $where .= " AND (id_transaksi LIKE ? OR id_supplier IN (SELECT id_supplier FROM supplier WHERE nama_perusahaan LIKE ?))";
                                        $params[] = $search;
                                        $params[] = $search;
                                        $types .= "ss";
                                    }
                                    $sql = "SELECT * FROM transaksi WHERE $where ORDER BY tanggal DESC";
                                    $stmt = $mysqli->prepare($sql);
                                    if ($types) {
                                        $stmt->bind_param($types, ...$params);
                                    }
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $i = 0;
                                    while($data = $result->fetch_assoc()):
                                        $i++;
                                        $id = $data['id_transaksi'];
                                        $ids = $data['id_supplier'];

                                        $show2 = $mysqli->prepare("SELECT nama_perusahaan FROM supplier WHERE id_supplier = ?");
                                        $show2->bind_param("s", $ids);
                                        $show2->execute();
                                        $data2 = $show2->get_result()->fetch_assoc();

                                        $totharga0 = $mysqli->prepare("SELECT SUM(HARGA) as total FROM pemesanan1 WHERE order_id IN (SELECT order_id FROM transaksi WHERE id_transaksi = ?)");
                                        $totharga0->bind_param("s", $id);
                                        $totharga0->execute();
                                        $totharga1 = $totharga0->get_result()->fetch_assoc();
                                        $totharga = $totharga1['total'];

                                        $status = '<span class="label label-success">Completed</span>';
                                    ?>
                                        <tr>
                                            <td><?= $i ?></td>
                                            <td><?= htmlspecialchars($id) ?></td>
                                            <td><?= htmlspecialchars($data['tanggal']) ?></td>
                                            <td><?= htmlspecialchars($data2['nama_perusahaan'] ?? '-') ?></td>
                                            <td>
                                                <a href="detail.php?idt=<?= urlencode($id) ?>" class="btn bg-maroon btn-sm" title="Lihat detail transaksi">
                                                    <i class="fa fa-eye"></i> view
                                                </a>
                                            </td>
                                            <td><?= number_format($totharga, 0, ',', '.') ?></td>
                                            <td><?= $status ?></td>
                                            <td>
                                                <a href="ijinpesan.php?p=<?= urlencode($id) ?>&a=fin" class="btn btn-info btn-flat" title="Selesaikan transaksi">
                                                    <i class="fa fa-check"></i> Finish
                                                </a>
                                                <a href="cetak_laporan.php?idt=<?= urlencode($id) ?>" class="btn btn-success btn-flat" title="Cetak laporan" target="_blank">
                                                    <i class="fa fa-print"></i> Print
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <?php if ($i == 0): ?>
                                    <div class="alert alert-warning text-center" style="margin-top:20px;">
                                        <i class="fa fa-info-circle"></i> Data tidak ditemukan.
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="box-footer">
                                <small>
                                    <i class="fa fa-info-circle text-blue"></i> 
                                    Klik <b>view</b> untuk melihat detail, <b>Finish</b> untuk menyelesaikan, dan <b>Print</b> untuk mencetak laporan transaksi.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    <script src="../js/plugins/datatables/jquery.dataTables.js"></script>
    <script src="../js/plugins/datatables/dataTables.bootstrap.js"></script>
    <script>
    $(function() {
        $('#example1').dataTable();
    });
    </script>
</body>
</html>
