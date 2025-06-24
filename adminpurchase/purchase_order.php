<!DOCTYPE html>
<?php 
$konekdb_path = dirname(__DIR__) . '/konekdb.php';
if (!file_exists($konekdb_path)) {
    die("Database connection file not found.");
}
include($konekdb_path);
session_start();
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$idpegawai = isset($_SESSION['idpegawai']) ? $_SESSION['idpegawai'] : null;

if (!$username || !$idpegawai) {
    header("location:../index.php");
    exit;
}

$mysqli = new mysqli('localhost', 'your_username', 'your_Password', 'your_database');
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '" . mysqli_real_escape_string($mysqli, $username) . "' AND modul = 'Purchase'");
if (!$cekuser) {
    die("Database query failed: " . mysqli_error($mysqli));
}
$user = mysqli_fetch_array($cekuser);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Input new order_id data
    $namabarang = mysqli_real_escape_string($mysqli, $_POST['namabarang']);
    $satuan = mysqli_real_escape_string($mysqli, $_POST['satuan']);
    $jumlah = intval($_POST['jumlah']);
    $id_supplier = intval($_POST['id_supplier']);
    $tanggal = date('Y-m-d');
    $status = 0;

    $insert = mysqli_query($mysqli, "INSERT INTO pemesanan1 (namabarang, satuan, jumlah, id_supplier, tanggal, status) VALUES ('$namabarang', '$satuan', $jumlah, $id_supplier, '$tanggal', $status)");
    if (!$insert) {
        echo "<div class='alert alert-danger'>Failed to add data: " . mysqli_error($mysqli) . "</div>";
    } else {
        echo "<div class='alert alert-success'>order_id data added successfully.</div>";
    }
}
$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='" . mysqli_real_escape_string($mysqli, $idpegawai) . "'");
if (!$getpegawai) {
    die("Database query failed: " . mysqli_error($mysqli));
}
$pegawai = mysqli_fetch_array($getpegawai);

if ($user['jmluser'] == "0") {
    header("location:../index.php");
    exit;
}

// Get counts for notifications
$not1 = mysqli_query($mysqli, "SELECT count(order_id) as count_pemesanan1 FROM pemesanan1 WHERE status='0'");
$tot1 = mysqli_fetch_array($not1);
$not2 = mysqli_query($mysqli, "SELECT count(distinct id_transaksi) as jml FROM transaksi WHERE status='1'");
$tot2 = mysqli_fetch_array($not2);
$not3 = mysqli_query($mysqli, "SELECT count(distinct id_transaksi) as jml FROM transaksi WHERE status='4'");
$tot3 = mysqli_fetch_array($not3);
$not4 = mysqli_query($mysqli, "SELECT count(id_pegawai) as jml FROM cuti WHERE aksi='1' AND id_pegawai='$idpegawai'");
$tot4 = mysqli_fetch_array($not4);
$not5 = mysqli_query($mysqli, "SELECT count(id_pesan) as jml FROM pesan WHERE ke='$idpegawai' AND status='0'");
$tot5 = mysqli_fetch_array($not5);
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>E-pharm | Purchase</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- Bootstrap 3.0.2 -->
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- Font Awesome -->
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- DataTables -->
        <link href="../css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
        <!-- Custom CSS -->
        <style>
            /* ===== SIDEBAR STYLING ONLY ===== */
            .sidebar, .left-side {
                background: linear-gradient(135deg, #0a1f44 0%, #1a3a7a 100%) !important;
                border_id-right: 1px solid #0a1f44;
            }

            .sidebar-menu > li > a {
                color: #e0e0e0 !important;
                font-family: 'Poppins', sans-serif;
                border_id-radius: 4px;
                margin: 4px 10px;
                transition: all 0.3s;
            }

            .sidebar-menu > li > a:hover,
            .sidebar-menu > li.active > a {
                background: rgba(255,255,255,0.1) !important;
                color: #ffffff !important;
                border_id-left: 3px solid #3a5a9a;
            }

            .sidebar-menu > li > a:hover .fa,
            .sidebar-menu > li.active > a .fa {
                color: #ffd700 !important;
            }

            .user-panel .info p, 
            .user-panel .info a {
                color: #e0e0e0 !important;
            }

            .sidebar-form .form-control {
                background-color: #1a3a7a;
                border_id-color: #2a4a8a;
                color: #ffffff;
            }

            .sidebar-form .btn {
                background-color: #2a4a8a;
                color: #ffffff;
            }

            /* Keep all other existing styles below */
            /* Improved Sidebar */
            .sidebar {
                font-family: 'Arial', sans-serif;
            }
            .sidebar-menu .badge {
                background: #3c8dbc !important;
                font-weight: 600;
            }

            /* Table Styling */
            .table thead th {
                background-color: #3c8dbc;
                color: white !important;
            }
            .table tbody tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .table tbody tr:hover {
                background-color: #e6f2fa !important;
            }

            /* Filter Section */
            .filter-section {
                background: #f5f5f5;
                padding: 15px;
                margin-bottom: 20px;
                border_id-radius: 4px;
                border_id: 1px solid #ddd;
            }
            .filter-section .form-group {
                margin-bottom: 10px;
            }

            /* Button Styling */
            .btn-export {
                margin-right: 10px;
                margin-bottom: 10px;
            }

            /* Responsive Adjustments */
            @media (max-width: 768px) {
                .filter-section .form-group {
                    margin-bottom: 15px;
                }
            }
        </style>
    </head>
    <body class="skin-blue">
        <header class="header">
            <a href="../index.html" class="logo">E-pharm</a>
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
                                <span><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?> <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header bg-light-blue">
                                    <?php
                                    $photoPath = '../img/' . htmlspecialchars($pegawai['foto'], ENT_QUOTES, 'UTF-8');
                                    $defaultPhoto = '../img/default-user.jpg';
                                    if (!file_exists($photoPath)) {
                                        $photoPath = $defaultPhoto;
                                    }
                                    ?>
                                    <!-- New order_id Button -->
                                    <div style="margin: 10px 0;">
                                        <button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#modalAddorder_id">
                                            <i class="fa fa-plus"></i> New order_id
                                        </button>
                                    </div>
                                    <?php
                                    ?>
                                    <img src="<?php echo $photoPath; ?>" class="img-circle" alt="User Image" 
                                         onerror="this.src='<?php echo $defaultPhoto; ?>'" />
                                    <div style="color: #fff; font-weight: bold; margin-top: 10px;">New order_id</div>
                                    <p>
                                        <?php 
                                        echo htmlspecialchars($pegawai['Nama'], ENT_QUOTES, 'UTF-8') . " - " . 
                                             htmlspecialchars($pegawai['Jabatan'], ENT_QUOTES, 'UTF-8') . " " . 
                                             htmlspecialchars($pegawai['Departemen'], ENT_QUOTES, 'UTF-8'); ?>
                                        <small>Member since <?php echo htmlspecialchars($pegawai['Tanggal_Masuk'], ENT_QUOTES, 'UTF-8'); ?></small>
<!-- Filter Section -->
<div class="filter-section">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" id="startDate" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>End Date</label>
                <input type="date" id="endDate" class="form-control">
            </div>
        </div>
        <!-- Status filter removed -->
        <div class="col-md-3">
            <div class="form-group">
                <label>Price Range</label>
                <div class="input-group">
                    <input type="number" id="minPrice" class="form-control" placeholder="Min" min="0">
                    <span class="input-group-addon">to</span>
                    <input type="number" id="maxPrice" class="form-control" placeholder="Max" min="0">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <button id="applyFilter" class="btn btn-primary">Apply Filter</button>
            <button id="resetFilter" class="btn btn-default">Reset</button>
            <div class="pull-right">
                <button id="exportExcel" class="btn btn-success btn-export">
                    <i class="fa fa-file-excel-o"></i> Export Excel
                </button>
                <button id="exportCSV" class="btn btn-success btn-export">
                    <i class="fa fa-file-text-o"></i> Export CSV
                </button>
            </div>
        </div>
    </div>
</div>
                                    </p>
                                    <hr>
                                    <strong>Selected order_ids:</strong>
                                    <ul style="padding-left:18px;">
                                    <?php
                                    $ids = [29, 30, 34, 35, 36, 39];
                                    $idList = implode(',', array_map('intval', $ids));
                                    $q = mysqli_query($mysqli, "SELECT order_id, namabarang FROM pemesanan1 WHERE order_id IN ($idList)");
                                    while($row = mysqli_fetch_assoc($q)) {
                                        echo '<li>#' . htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($row['namabarang'], ENT_QUOTES, 'UTF-8') . '</li>';
                                    }
                                    ?>
                                    </ul>
                                </li>
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
                        <li>
                            <a href="#" data-toggle="modal" data-target="#modalAddorder_id" title="Add order_id" style="color: #222;">
                                <i class="fa fa-plus"></i> Add order_id
                            </a>
                        </li>
                        <li>
                            <a href="pemesanan.php" title="order_id Notifications">
                                <i class="fa fa-bell"></i>
                                <?php if ($tot1['count_pemesanan1'] > 0): ?>
                                <span class="badge" style="background-color: #f39c12; color: #fff;"><?php echo htmlspecialchars($tot1['count_pemesanan1'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="refreshorder_ids" title="Refresh Data">
                                <i class="fa fa-refresh"></i>
                            </a>
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
                            <img src="<?php echo $photoPath; ?>" class="img-circle" alt="User Image" 
                                 onerror="this.src='<?php echo $defaultPhoto; ?>'" />
                        </div>
                        <div class="pull-left info">
                            <p><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></p>
                            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                        </div>
                    </div>
                    
                    <form action="#" method="get" class="sidebar-form">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Search..."/>
                            <span class="input-group-btn">
                                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    </form>
                    
                    <ul class="sidebar-menu">
                        <li>
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="active">
                            <a href="pemesanan.php">
                                <i class="fa fa-list-alt"></i> <span>order_ids</span>
                                <?php if($tot1['count_pemesanan1'] != 0): ?>
                                <small class="badge pull-right"><?php echo htmlspecialchars($tot1['count_pemesanan1'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a href="transaksi.php">
                                <i class="fa fa-check-square"></i> <span>Transaction Approval</span>
                                <?php if(isset($tot2['jml']) && $tot2['jml'] != 0): ?>
                                <small class="badge pull-right"><?php echo htmlspecialchars($tot2['jml'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a href="laporan.php">
                               <i class="fa fa-envelope"></i> <span>Reports</span>
                                <?php if(isset($tot3['jml']) && $tot3['jml'] != 0): ?>
                                <small class="badge pull-right"><?php echo htmlspecialchars($tot3['jml'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a href="cuti.php">
                                <i class="fa fa-suitcase"></i> <span>Leave</span>
                                <?php if($tot4['jml'] != 0): ?>
                                <small class="badge pull-right"><?php echo htmlspecialchars($tot4['jml'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <a href="mailbox.php">
                                <i class="fa fa-comments"></i> <span>Mailbox</span>
                                <?php if(isset($tot5['jml']) && $tot5['jml'] != 0): ?>
                                <small class="badge pull-right"><?php echo htmlspecialchars($tot5['jml'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </section>
            </aside>
            
            <aside class="right-side">                
                <section class="content-header">
                    <h1>order_ids</h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Purchase</li>
                    </ol>
                </section>

                <section class="content">
                    <!-- Filter Section -->
                    <div class="filter-section">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" id="startDate" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" id="endDate" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select id="filterStatus" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="0">Pending</option>
                                        <option value="1">Approved</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Price Range</label>
                                    <div class="input-group">
                                        <input type="number" id="minPrice" class="form-control" placeholder="Min" min="0">
                                        <span class="input-group-addon">to</span>
                                        <input type="number" id="maxPrice" class="form-control" placeholder="Max" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button id="applyFilter" class="btn btn-primary">Apply Filter</button>
                                <button id="resetFilter" class="btn btn-default">Reset</button>
                                <div class="pull-right">
                                    <button id="exportExcel" class="btn btn-success btn-export">
                                        <i class="fa fa-file-excel-o"></i> Export Excel
                                    </button>
                                    <button id="exportCSV" class="btn btn-success btn-export">
                                        <i class="fa fa-file-text-o"></i> Export CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">order_id List</h3>
                                </div>
                                <div class="box-body table-responsive">
                                    <table id="order_idsTable" class="table table-border_ided table-striped">
                                        <thead>
                                            <tr>
                                                <th>NO.</th>
                                                <th>order_id ID</th>
                                                <th>ITEM NAME</th>
                                                <th>UNIT</th>
                                                <th>QUANTITY</th>
                                                <th>PRICE (Rp.)</th>
                                                <th>SUPPLIER</th>
                                                <th>STATUS</th>
                                                <th>DATE</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $show = mysqli_query($mysqli, "SELECT * FROM pemesanan1 WHERE status='0'");
                                            $i = 0;
                                            while($data = mysqli_fetch_array($show)):
                                                $i++;
                                                $id = $data['order_id'];
                                                $ids = $data['id_supplier'];
                                                
                                                $show2 = mysqli_query($mysqli, "SELECT Nama_perusahaan FROM supplier WHERE id_supplier='$ids'");
                                                $data2 = mysqli_fetch_array($show2);
                                                $Nama_perusahaan = isset($data2['Nama_perusahaan']) ? $data2['Nama_perusahaan'] : 'Unknown Supplier';

                                                $status = ($data['status'] == '0') ? 
                                                    '<span class="label label-warning">Pending</span>' : 
                                                    '<span class="label label-success">Approved</span>';
                                            ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo htmlspecialchars($id, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($data['namabarang'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($data['satuan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($data['jumlah'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <form action="approve_order_id.php" method="get" class="form-inline">
                                                        <input type="text" class="form-control input-sm price-input" name="price" placeholder="Price" style="width: 100px;" required>
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="order_id" value="<?php echo $id; ?>">
                                                        <input type="hidden" name="supplier_id" value="<?php echo $ids; ?>">
                                                </td>
                                                <td><?php echo htmlspecialchars($Nama_perusahaan, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo $status; ?></td>
                                                <td><?php echo htmlspecialchars($data['tanggal'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                        <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                                    </form>
                                                    <a href="approve_order_id.php?order_id=<?php echo $id; ?>&action=decline&supplier_id=<?php echo $ids; ?>" class="btn btn-danger btn-sm" style="margin-top: 5px;">Decline</a>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <!-- Modal Add order_id -->
        <div class="modal fade" id="modalAddorder_id" tabindex="-1" role="dialog" aria-labelledby="modalAddorder_idLabel">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form action="" method="post">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <h4 class="modal-title" id="modalAddorder_idLabel">Add New order_id</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="namabarang">Item Name</label>
                        <input type="text" class="form-control" name="namabarang" id="namabarang" required>
                    </div>
                    <div class="form-group">
                        <label for="satuan">Unit</label>
                        <input type="text" class="form-control" name="satuan" id="satuan" required>
                    </div>
                    <div class="form-group">
                        <label for="jumlah">Quantity</label>
                        <input type="number" class="form-control" name="jumlah" id="jumlah" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="id_supplier">Supplier</label>
                        <select class="form-control" name="id_supplier" id="id_supplier" required>
                            <option value="">-- Select Supplier --</option>
                            <?php
                            $qsupp = mysqli_query($mysqli, "SELECT id_supplier, Nama_perusahaan FROM supplier");
                            while($supp = mysqli_fetch_array($qsupp)){
                                echo '<option value="'.htmlspecialchars($supp['id_supplier'], ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($supp['Nama_perusahaan'], ENT_QUOTES, 'UTF-8').'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Add</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- jQuery 2.0.2 -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <!-- DataTables -->
        <script src="../js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
        <script src="../js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
        <!-- TableExport -->
        <script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/tableExport.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/libs/js-xlsx/xlsx.core.min.js"></script>
        
        <script type="text/javascript">
            $(document).ready(function() {
                // Initialize DataTable
                var table = $('#order_idsTable').DataTable({
                    "paging": true,
                    "lengthChange": true,
                    "searching": true,
                    "order_iding": true,
                    "info": true,
                    "autoWidth": false,
                    "responsive": true
                });
                
                // Apply filters
                $('#applyFilter').click(function() {
                    var startDate = $('#startDate').val();
                    var endDate = $('#endDate').val();
                    var status = $('#filterStatus').val();
                    var minPrice = $('#minPrice').val();
                    var maxPrice = $('#maxPrice').val();
                    
                    // Filter by date range
                    if (startDate || endDate) {
                        $.fn.dataTable.ext.search.push(
                            function(settings, data, dataIndex) {
                                var date = new Date(data[8]);
                                var start = new Date(startDate);
                                var end = new Date(endDate);
                                
                                if (!startDate && endDate) {
                                    return date <= end;
                                } else if (startDate && !endDate) {
                                    return date >= start;
                                } else if (startDate && endDate) {
                                    return date >= start && date <= end;
                                }
                                return true;
                            }
                        );
                    }
                    
                    // Filter by status
                    if (status) {
                        table.column(7).search(status).draw();
                    }
                    
                    // Filter by price range
                    if (minPrice || maxPrice) {
                        $.fn.dataTable.ext.search.push(
                            function(settings, data, dataIndex) {
                                var price = parseFloat($(data[5]).find('.price-input').val()) || 0;
                                var min = parseFloat(minPrice) || 0;
                                var max = parseFloat(maxPrice) || Number.MAX_VALUE;
                                
                                return (price >= min && price <= max);
                            }
                        );
                    }
                    
                    table.draw();
                    
                    // Remove custom filters after applying
                    $.fn.dataTable.ext.search.pop();
                });
                
                // Reset filters
                $('#resetFilter').click(function() {
                    $('#startDate').val('');
                    $('#endDate').val('');
                    $('#filterStatus').val('');
                    $('#minPrice').val('');
                    $('#maxPrice').val('');
                    
                    table
                        .columns().search('')
                        .draw();
                });
                
                // Export to Excel
                $('#exportExcel').click(function() {
                    table.button('.buttons-excel').trigger();
                });
                
                // Export to CSV
                $('#exportCSV').click(function() {
                    table.button('.buttons-csv').trigger();
                });
                
                // Initialize TableExport

                // Show table with columns: order_id, code, nama barang, kategori, jumlah, satuan, id_supplier, tanggal, status, harga
                // Example: create a table dynamically (for demo purpose)
                // You can replace this with your actual data rendering logic if needed
                function renderorder_idTable(order_ids) {
                    var html = '<table class="table table-border_ided"><thead><tr>' +
                        '<th>ID pemesanan1</th>' +
                        '<th>Code</th>' +
                        '<th>Nama Barang</th>' +
                        '<th>Kategori</th>' +
                        '<th>Jumlah</th>' +
                        '<th>Satuan</th>' +
                        '<th>ID Supplier</th>' +
                        '<th>Tanggal</th>' +
                        '<th>Status</th>' +
                        '<th>Harga</th>' +
                        '</tr></thead><tbody>';
                    order_ids.forEach(function(order_id) {
                        html += '<tr>' +
                            '<td>' + order_id.order_id + '</td>' +
                            '<td>' + order_id.code + '</td>' +
                            '<td>' + order_id.namabarang + '</td>' +
                            '<td>' + order_id.kategori + '</td>' +
                            '<td>' + order_id.jumlah + '</td>' +
                            '<td>' + order_id.satuan + '</td>' +
                            '<td>' + order_id.id_supplier + '</td>' +
                            '<td>' + order_id.tanggal + '</td>' +
                            '<td>' + order_id.status + '</td>' +
                            '<td>' + order_id.harga + '</td>' +
                            '</tr>';
                    });
                    html += '</tbody></table>';
                    $('#order_idTableContainer').html(html);
                }

                // Example usage (dummy data)
                /*
                var order_ids = [
                    {order_id: 1, code: 'PO001', namabarang: 'Paracetamol', kategori: 'Obat', jumlah: 100, satuan: 'Box', id_supplier: 2, tanggal: '2024-06-01', status: 'Pending', harga: 50000},
                    {order_id: 2, code: 'PO002', namabarang: 'Amoxicillin', kategori: 'Obat', jumlah: 50, satuan: 'Box', id_supplier: 3, tanggal: '2024-06-02', status: 'Approved', harga: 75000}
                ];
                renderorder_idTable(order_ids);
                */
                $('#order_idsTable').tableExport({
                    formats: ['xlsx', 'csv'],
                    fileName: 'order_ids-data',
                    bootstrap: true
                });
                
                // Refresh data
                $('#refreshorder_ids').click(function(e) {
                    e.preventDefault();
                    table.ajax.reload();
                });
            });
        </script>
    </body>
</html>