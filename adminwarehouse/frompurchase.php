<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../list_request.php?status=Please Login First");
    exit();
}

require_once('../konekdb.php');

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check if user has access to Adminwarehouse module
$stmt = $mysqli->prepare("SELECT COUNT(username) as usercount FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['usercount'] == "0") {
    header("Location: ../list_request.php?status=Access Declined");
    exit();
}

// Get employee data
$stmtPegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmtPegawai->bind_param("i", $idpegawai);
$stmtPegawai->execute();
$resultPegawai = $stmtPegawai->get_result();
$pegawai = $resultPegawai->fetch_assoc();

// Get filter parameters
$status_filter = isset($_GET['status']) ? intval($_GET['status']) : null;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;
$supplier_filter = isset($_GET['supplier']) ? $_GET['supplier'] : null;

// Get stats for approved orders
$approved_stats = mysqli_query($mysqli, 
    "SELECT COUNT(*) as count, SUM(total_price) as total 
     FROM pemesanan 
     WHERE status = 2"
)->fetch_assoc();

// Get stats for rejected orders
$rejected_stats = mysqli_query($mysqli, 
    "SELECT COUNT(*) as count FROM pemesanan WHERE status = 3"
)->fetch_assoc();
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
     .stat-card {
            border-radius: 8px;
            color: #fff;
            padding: 24px 16px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card h3 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .stat-card p {
            font-size: 16px;
            margin-bottom: 0;
        }
        .bg-success { background: #28a745 !important; }
        .bg-info { background: #17a2b8 !important; }
        .bg-danger { background: #dc3545 !important; }
        .filter-section {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            border: 1px solid #eee;
        }
        .badge {
            padding: 6px 10px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 4px;
        }
        .badge-success { background: #28a745; }
        .badge-danger { background: #dc3545; }
        .select2-container { 
            min-width: 200px !important; 
            width: 100% !important;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .btn-xs {
            padding: 3px 8px;
            font-size: 12px;
            border-radius: 4px;
        }
        @media (max-width: 767px) {
            .stat-card { 
                padding: 16px 8px;
                margin-bottom: 15px;
            }
            .stat-card h3 {
                font-size: 22px;
            }
            .filter-section { 
                padding: 16px;
            }
            .form-group {
                margin-bottom: 15px;
            }
        }
    </style>
    <?php include('styles.php'); ?>
</head>

<body class="skin-blue">
    <?php include('navbar.php'); ?>
    
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <!-- Sidebar -->
        <aside class="left-side sidebar-offcanvas">
            <section class="sidebar">
                <div class="user-panel mb-3">
                    <div class="pull-left image">
                        <img src="../img/<?php echo isset($pegawai['foto']) && $pegawai['foto'] ? htmlspecialchars($pegawai['foto']) : 'default.png'; ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?php echo htmlspecialchars($username); ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                <ul class="sidebar-menu">
                    <li><a href="streamlit.php"><i class="fa fa-signal"></i> <span>Analytics</span></a></li>
                    <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                    <li><a href="list_request.php"><i class="fa fa-list"></i> <span>List Request</span></a></li>
                    <li><a href="daftarACC.php"><i class="fa fa-undo"></i> <span>Request History</span></a></li>
                    <li class="active"><a href="frompurchase.php"><i class="fa fa-tasks"></i> <span>Purchase Order</span></a></li>
                    <li><a href="stock.php"><i class="fa fa-archive"></i> <span>Inventory</span></a></li>
                   
                </ul>
            </section>
        </aside>
        
        <aside class="right-side">
            <section class="content-header">
                 <section class="content-header">
                <h1>
                    Purchase Order History
                    <small>Warehouse Manager</small>
                </h1>
            </section>
            </section>
            
            <section class="content">
                <div class="container-fluid">
                    <?php 
                    if (isset($_SESSION['message'])) {
                        echo '<div class="alert alert-info alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                '.$_SESSION['message'].'
                              </div>';
                        unset($_SESSION['message']);
                    }
                    ?>
                    
                    <!-- Filter Section -->
                    <div class="filter-section">
                        <h4 class="mb-3"><i class="fa fa-filter"></i> Filter Orders</h4>
                        <form method="get">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-2">
                                    <label for="status">Status:</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="2" <?php echo $status_filter == 2 ? 'selected' : ''; ?>>Approved</option>
                                        <option value="3" <?php echo $status_filter == 3 ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="date_from">From:</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="date_to">To:</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="supplier">Supplier:</label>
                                    <select name="supplier" id="supplier" class="form-control select2">
                                        <option value="">All Suppliers</option>
                                        <?php
                                        $suppliers = mysqli_query($mysqli, "SELECT DISTINCT supplier_name FROM pemesanan ORDER BY supplier_name");
                                        while ($supplier = mysqli_fetch_assoc($suppliers)) {
                                            $selected = $supplier_filter == $supplier['supplier_name'] ? 'selected' : '';
                                            echo "<option value='".htmlspecialchars($supplier['supplier_name'])."' $selected>".htmlspecialchars($supplier['supplier_name'])."</option>";
                                        }
                                        ?>
                                    </select>
                                    </br>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-filter"></i> Apply
                                    </button>
                              
                                    <a href="frompurchase.php" class="btn btn-outline-secondary btn-block">
                                        <i class="fa fa-times"></i> Reset
                                    </a>
                                </div>
                                
                            </div>
                        </form>
                    </div>
                  
                    <!-- Stats Cards -->
                    <div class="row text-center justify-content-center">
                        <div class="col-md-3 d-flex align-items-stretch">
                            <div class="stat-card bg-success w-100">
                                <h3><?php echo number_format($approved_stats['count']); ?></h3>
                                <p>Approved Orders</p>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-stretch">
                            <div class="stat-card bg-info w-100">
                                <h3>Rp <?php echo number_format($approved_stats['total'] ?? 0, 0, ',', '.'); ?></h3>
                                <p>Total Approved Value</p>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-stretch">
                            <div class="stat-card bg-danger w-100">
                                <h3><?php echo number_format($rejected_stats['count']); ?></h3>
                                <p>Rejected Orders</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Orders Table -->
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Purchase Order Details</h3>
                            <div class="box-tools">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-minus"></i>
                                </button>
                            </div>
                        </div>
                                                           <div class="box-body">
                            <div class="table-responsive">
                                <table id="ordersTable" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Item</th>
                                            <th>Category</th>
                                            <th>Qty</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>Supplier</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                           
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Build the base query
                                        $query = "SELECT * FROM pemesanan WHERE 1=1";
                                        $params = [];
                                        $types = '';
                                                                            
                                        // Apply filters
                                        if (!is_null($status_filter) && $status_filter !== '') {
                                            $query .= " AND status = ?";
                                            $params[] = $status_filter;
                                            $types .= 'i';
                                        }
                                                                            
                                        if (!empty($date_from)) {
                                            $query .= " AND order_date >= ?";
                                            $params[] = $date_from;
                                            $types .= 's';
                                        }
                                                                            
                                        if (!empty($date_to)) {
                                            $query .= " AND order_date <= ?";
                                            $params[] = $date_to;
                                            $types .= 's';
                                        }
                                                                            
                                        if (!empty($supplier_filter)) {
                                            $query .= " AND supplier_name = ?";
                                            $params[] = $supplier_filter;
                                            $types .= 's';
                                        }
                                                                            
                                        $query .= " ORDER BY order_date DESC";
                                                                            
                                        // Prepare and execute the query
                                        $stmt = $mysqli->prepare($query);
                                        if ($params) {
                                            $stmt->bind_param($types, ...$params);
                                        }
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        
                                        if ($result->num_rows > 0) {
                                            while ($order = $result->fetch_assoc()) {
                                                $status_badge = $order['status'] == 2 ? 
                                                    '<span class="badge badge-success">Approved</span>' : 
                                                    '<span class="badge badge-danger">Rejected</span>';
                                                
                                                echo "<tr>
                                                    <td>{$order['order_id']}</td>
                                                    <td>".htmlspecialchars($order['item_name'])."</td>
                                                    <td>".htmlspecialchars($order['category'])."</td>
                                                    <td>{$order['quantity']}</td>
                                                    <td>Rp ".number_format($order['price'], 0, ',', '.')."</td>
                                                    <td>Rp ".number_format($order['total_price'], 0, ',', '.')."</td>
                                                    <td>".htmlspecialchars($order['supplier_name'])."</td>
                                                    <td>{$status_badge}</td>
                                                    <td>".date('d M Y', strtotime($order['order_date']))."</td>
                                                  
                                                </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='10' class='text-center'>No orders found with current filters</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#ordersTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search orders...",
                    paginate: {
                        previous: "&laquo;",
                        next: "&raquo;"
                    }
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                // No action column, so do not disable any column sorting
            });

            // Initialize Select2 for supplier dropdown
            $('.select2').select2({
                placeholder: "Select supplier",
                allowClear: true
            });
            
            // Set default dates if not set
            if (!$('#date_from').val()) {
                var date = new Date();
                date.setMonth(date.getMonth() - 1);
                $('#date_from').val(date.toISOString().split('T')[0]);
            }
            
            if (!$('#date_to').val()) {
                $('#date_to').val(new Date().toISOString().split('T')[0]);
            }
            
            // Close alert message
            $('.alert').alert();
        });
        
    </script>
</body>
</html>