
<!DOCTYPE html>
<?php 
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

if(!isset($_SESSION['username'])){
    header("location:../index.php?status=please login first");
    exit();
}
if (isset($_SESSION['idpegawai'])) {
    $idpegawai = $_SESSION['idpegawai'];
} else {
    header("location:../index.php?status=please login first");
    exit();
}
$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
$user = mysqli_fetch_assoc($cekuser);

$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
$pegawai = mysqli_fetch_array($getpegawai);

if ($user['jmluser'] == "0") {
    header("location:../index.php");
    exit;
}

// Get filter values
$cabang_filter = isset($_GET['cabang']) ? mysqli_real_escape_string($mysqli, $_GET['cabang']) : '';
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($mysqli, $_GET['status']) : '';
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Admin Warehouse</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- CSS Links -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    
        <style>
            /* Custom CSS for order_id History */
            .order_id-history-container {
                background: #fff;
                border_id-radius: 10px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                overflow: hidden;
                margin-bottom: 30px;
            }

            .order_id-history-header {
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                padding: 15px 20px;
                border_id-bottom: 1px solid rgba(255,255,255,0.2);
            }

            .order_id-history-header h3 {
                margin: 0;
                font-weight: 600;
                font-size: 18px;
                display: inline-block;
            }

            .filter-container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 15px 20px;
                background-color: #f8f9fa;
                border_id-bottom: 1px solid #eee;
            }

            .filter-form {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .filter-form .form-control {
                min-width: 180px;
                border_id-radius: 4px;
                border_id: 1px solid #ddd;
                box-shadow: none;
            }

            .filter-form .btn {
                border_id-radius: 4px;
            }

            .order_id-history-table {
                width: 100%;
                border_id-collapse: collapse;
            }

            .order_id-history-table th {
                background-color: #2c3e50;
                color: white;
                font-weight: 600;
                padding: 12px 15px;
                text-align: left;
                position: sticky;
                top: 0;
            }

            .order_id-history-table td {
                padding: 12px 15px;
                border_id-bottom: 1px solid #eee;
                vertical-align: middle;
            }

            .order_id-history-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .order_id-history-table tr:hover {
                background-color: #f1f1f1;
            }

            /* Status Styles */
            .status-badge {
                display: inline-block;
                padding: 5px 10px;
                border_id-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .status-pending {
                background-color: #f39c12;
                color: white;
            }

            .status-accepted {
                background-color: #2ecc71;
                color: white;
            }

            .status-declined {
                background-color: #e74c3c;
                color: white;
            }

            /* Date Styles */
            .order_id-date {
                font-family: monospace;
                color: #555;
            }

            /* Responsive Adjustments */
            @media (max-width: 768px) {
                .filter-container {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                }
                
                .filter-form {
                    width: 100%;
                    flex-direction: column;
                }
                
                .filter-form .form-control,
                .filter-form .btn {
                    width: 100%;
                }
                
                .order_id-history-table {
                    display: block;
                    overflow-x: auto;
                }
            }

            /* Animation for table rows */
            @keyframes fadeInRow {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .order_id-history-table tbody tr {
                animation: fadeInRow 0.3s ease-out forwards;
                animation-delay: calc(var(--row-index) * 0.05s);
            }

            /* Pagination Styles */
            .pagination-container {
                display: flex;
                justify-content: center;
                padding: 15px;
                background-color: #f8f9fa;
                border_id-top: 1px solid #eee;
            }

            .pagination {
                margin: 0;
            }

            .pagination > li > a,
            .pagination > li > span {
                color: #2c3e50;
                border_id: 1px solid #ddd;
                margin: 0 2px;
                border_id-radius: 4px !important;
            }

            .pagination > li.active > a,
            .pagination > li.active > span {
                background: linear-gradient(135deg, #3498db, #2980b9);
                border_id-color: #2980b9;
                color: white;
            }

            /* Empty State */
            .empty-state {
                padding: 40px 20px;
                text-align: center;
                color: #7f8c8d;
            }

            .empty-state i {
                font-size: 50px;
                margin-bottom: 20px;
                color: #bdc3c7;
            }

            .empty-state h4 {
                margin-bottom: 10px;
                color: #2c3e50;
            }
            
            /* Sidebar Fix */
            .sidebar {
                display: block !important;
            }
            
            .left-side {
                width: 220px;
            }
            
            .right-side {
                margin-left: 220px;
            }
            
            @media (max-width: 767px) {
                .left-side {
                    width: 0;
                }
                
                .right-side {
                    margin-left: 0;
                }
            }
        </style>
    </head>
      <?php include('styles.php'); ?>
</head>
<body class="skin-blue">
    <?php include('navbar.php'); ?>
    
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
                            <a href="index.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="stock.php">
                                <i class="fa fa-folder"></i> <span>Stock</span>
                            </a>
                        </li>
                        <li>
                            <a href="movement.php">
                                <i class="fa fa-exchange"></i> <span>Movement</span>
                            </a>
                        <li>
                            <a href="product.php">
                                <i class="fa fa-list-alt"></i> <span>Products</span>
                            </a>
                        </li>
                         <li>
                            <a href="new_request.php">
                                <i class="fa fa-plus-square"></i> <span>New Request</span>
                            </a>
                        </li>
                        <li class="active">
                            <a href="history_request.php">
                                <i class="fa fa-archive"></i> <span>Request History</span>
                            </a>
                        </li>
                         <li>
                            <a href="sales_request.php">
                                <i class="fa fa-retweet"></i> <span>Sales Request</span>
                            </a>
                        </li>
                          <li>
                        <a href="purchase_order.php">
                            <i class="fa fa-shopping-cart"></i> <span>Purchase Orders</span>
                        </a>
                    </li>
                        <li>
                        <a href="frompurchase.php">
                            <i class="fa fa-share-square-o"></i> <span>Goods Delivered</span>
                        </a>
                    </li>
                   
                    </ul>
                </section>
            </aside>
            <aside class="right-side">
                <section class="content-header">
                    <h1>
                        Request History
                        <small>View and Manage Request History</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">History Request</li>
                    </ol>
                </section>

                <section class="content">
                    <div class="order_id-history-container">
                        <div class="filter-container">
                            <div class="filter-form">
                                    <button id="exportExcel" class="btn btn-success" title="Download as Excel"><i class="fa fa-file-excel-o"></i> Excel</button>
                                    <button id="exportCSV" class="btn btn-info" title="Download as CSV"><i class="fa fa-file-text-o"></i> CSV</button>
                                    <button id="exportPDF" class="btn btn-danger" title="Download as PDF"><i class="fa fa-file-pdf-o"></i> PDF</button>
                            
                                <form method="get" action="history_request.php" class="form-inline">
                                    <select name="cabang" class="form-control">
                                        <option value="">All Warehouse</option>
                                        <option value="Ambon" <?php echo ($cabang_filter == 'Ambon' ? 'selected' : ''); ?>>Ambon</option>
                                        <option value="Cikarang" <?php echo ($cabang_filter == 'Cikarang' ? 'selected' : ''); ?>>Cikarang</option>
                                        <option value="Medan" <?php echo ($cabang_filter == 'Medan' ? 'selected' : ''); ?>>Medan</option>
                                        <option value="Blitar" <?php echo ($cabang_filter == 'Blitar' ? 'selected' : ''); ?>>Blitar</option>
                                        <option value="Surabaya" <?php echo ($cabang_filter == 'Surabaya' ? 'selected' : ''); ?>>Surabaya</option>
                                    </select>
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="pending" <?php echo ($status_filter == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                        <option value="1" <?php echo ($status_filter == '1' ? 'selected' : ''); ?>>Accepted</option>
                                        <option value="2" <?php echo ($status_filter == '2' ? 'selected' : ''); ?>>Declined</option>
                                    </select>
                            
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                    <?php if(isset($_GET['status']) || isset($_GET['cabang'])): ?>
                                        <a href="history_request.php" class="btn btn-default">
                                            <i class="fa fa-times"></i> Clear
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <div class="total-recorder_ids">
                                <?php
                                $count_query = "SELECT COUNT(*) as total FROM list_request WHERE 1=1";
                                
                                if ($cabang_filter != '') {
                                    $count_query .= " AND cabang = '$cabang_filter'";
                                }
                                
                                if ($status_filter != '') {
                                    $count_query .= " AND status = '$status_filter'";
                                } else {
                                    $count_query .= " AND status IN ('1', '2', 'pending')";
                                }
                                
                                $count_result = mysqli_query($mysqli, $count_query);
                                $count_row = mysqli_fetch_assoc($count_result);
                                echo "<span class='badge bg-blue'>{$count_row['total']} recorder_ids found</span>";
                                ?>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="order_id-history-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Request Date</th>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Supplier</th>
                                        <th>Warehouse</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT p.*, s.Nama as supplier_name 
                                            FROM list_request p
                                            LEFT JOIN supplier s ON p.id_supplier = s.id_supplier
                                            WHERE 1=1";
                                    
                                    if ($cabang_filter != '') {
                                        $query .= " AND p.cabang = '$cabang_filter'";
                                    }
                                    
                                    if ($status_filter != '') {
                                        $query .= " AND p.status = '$status_filter'";
                                    } else {
                                        $query .= " AND p.status IN ('1', '2', 'pending')";
                                    }
                                    
                                    $query .= " ORDER BY p.tanggal DESC";
                                    
                                    $per_page = 15;
                                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    $start = ($page - 1) * $per_page;
                                    $query .= " LIMIT $start, $per_page";
                                    
                                    $result = mysqli_query($mysqli, $query);
                                    $no = $start + 1;
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr style='--row-index: {$no};'>
                                                <td>{$no}</td>
                                             
                                                <td class='order_id-date'>".date('d M Y H:i', strtotime($row['tanggal']))."</td>
                                                <td>{$row['namabarang']}</td>
                                                <td>{$row['kategori']}</td>
                                                <td>{$row['jumlah']}</td>
                                                <td>".($row['satuan'] ? $row['satuan'] : 'Serial')."</td>
                                                <td>".($row['supplier_name'] ? $row['supplier_name'] : 'N/A')."</td>
                                                <td>".($row['cabang'] ? $row['cabang'] : 'Cikarang')."</td>
                                                <td>";
                                            
                                            if ($row['status'] == '1') {
                                                echo "<span class='status-badge status-accepted'>Accepted</span>";
                                            } elseif ($row['status'] == '2') {
                                                echo "<span class='status-badge status-declined'>Declined</span>";
                                            } elseif ($row['status'] == 'pending') {
                                                echo "<span class='status-badge status-pending'>Pending</span>";
                                            }
                                            
                                            echo "</td>
                                            </tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr>
                                            <td colspan='10'>
                                                <div class='empty-state'>
                                                    <i class='fa fa-inbox'></i>
                                                    <h4>No order_id History Found</h4>
                                                    <p>There are no order_ids matching your criteria</p>
                                                </div>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="pagination-container">
                            <ul class="pagination">
                                <?php
                                $total_pages = ceil($count_row['total'] / $per_page);
                                
                                if ($page > 1) {
                                    echo "<li><a href='history_request.php?page=".($page-1).
                                        ($cabang_filter ? "&cabang=$cabang_filter" : "").
                                        ($status_filter ? "&status=$status_filter" : "").
                                        "'>&laquo;</a></li>";
                                }
                                
                                for ($i = 1; $i <= $total_pages; $i++) {
                                    $active = ($i == $page) ? "active" : "";
                                    echo "<li class='$active'><a href='history_request.php?page=$i".
                                        ($cabang_filter ? "&cabang=$cabang_filter" : "").
                                        ($status_filter ? "&status=$status_filter" : "").
                                        "'>$i</a></li>";
                                }
                                
                                if ($page < $total_pages) {
                                    echo "<li><a href='history_request.php?page=".($page+1).
                                        ($cabang_filter ? "&cabang=$cabang_filter" : "").
                                        ($status_filter ? "&status=$status_filter" : "").
                                        "'>&raquo;</a></li>";
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
        <!-- SheetJS & jsPDF CDN -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

        <script>
        function getTableData() {
            var table = document.querySelector('.order_id-history-table');
            var data = [];
            var rows = table.querySelectorAll('tr');
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll('th,td');
                for (var j = 0; j < cols.length; j++) {
                    row.push(cols[j].innerText.trim());
                }
                data.push(row);
            }
            return data;
        }

        // Excel Export
        document.getElementById('exportExcel').onclick = function() {
            var data = getTableData();
            var ws = XLSX.utils.aoa_to_sheet(data);
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "order_ids");
            XLSX.writeFile(wb, "history-request.xlsx");
        };

        // CSV Export
        document.getElementById('exportCSV').onclick = function() {
            var data = getTableData();
            var ws = XLSX.utils.aoa_to_sheet(data);
            var csv = XLSX.utils.sheet_to_csv(ws);
            var blob = new Blob([csv], {type: "text/csv"});
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = "history-request.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

        // PDF Export
        document.getElementById('exportPDF').onclick = function() {
            var data = getTableData();
            var doc = new jspdf.jsPDF('l', 'pt', 'a4');
            doc.text("Request History List", 40, 30);
            doc.autoTable({
                head: [data[0]],
                body: data.slice(1),
                startY: 50,
                styles: {fontSize: 8}
            });
            doc.save("history-request.pdf");
        };
        </script>
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
    </body>
</html>