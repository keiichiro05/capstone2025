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
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($mysqli, $_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($mysqli, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($mysqli, $_GET['date_to']) : '';
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Warehouse - Purchase Order History</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" /> 
        <style>
            /* Custom CSS for order history */
            .order-history-container {
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                overflow: hidden;
                margin-bottom: 30px;
            }

            .order-history-header {
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                padding: 15px 20px;
                border-bottom: 1px solid rgba(255,255,255,0.2);
            }

            .order-history-header h3 {
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
                border-bottom: 1px solid #eee;
            }

            .filter-form {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .filter-form .form-control {
                min-width: 180px;
                border-radius: 4px;
                border: 1px solid #ddd;
                box-shadow: none;
            }

            .filter-form .btn {
                border-radius: 4px;
            }

            .order-history-table {
                width: 100%;
                border-collapse: collapse;
            }

            .order-history-table th {
                background-color: #2c3e50;
                color: white;
                font-weight: 600;
                padding: 12px 15px;
                text-align: left;
                position: sticky;
                top: 0;
            }

            .order-history-table td {
                padding: 12px 15px;
                border-bottom: 1px solid #eee;
                vertical-align: middle;
            }

            .order-history-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .order-history-table tr:hover {
                background-color: #f1f1f1;
            }

            /* Status Styles */
            .status-badge {
                display: inline-block;
                padding: 5px 10px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .status-pending {
                background-color: #f39c12;
                color: white;
            }

            .status-approved {
                background-color: #2ecc71;
                color: white;
            }

            .status-rejected {
                background-color: #e74c3c;
                color: white;
            }
            
            .status-delivered {
                background-color: #9b59b6;
                color: white;
            }

            /* Date Styles */
            .order-date {
                font-family: monospace;
                color: #555;
            }
            
            /* Price Styles */
            .price {
                font-family: monospace;
                text-align: right;
            }
            
            .total-price {
                font-weight: bold;
                color: #2c3e50;
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
                
                .order-history-table {
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

            .order-history-table tbody tr {
                animation: fadeInRow 0.3s ease-out forwards;
                animation-delay: calc(var(--row-index) * 0.05s);
            }

            /* Pagination Styles */
            .pagination-container {
                display: flex;
                justify-content: center;
                padding: 15px;
                background-color: #f8f9fa;
                border-top: 1px solid #eee;
            }

            .pagination {
                margin: 0;
            }

            .pagination > li > a,
            .pagination > li > span {
                color: #2c3e50;
                border: 1px solid #ddd;
                margin: 0 2px;
                border-radius: 4px !important;
            }

            .pagination > li.active > a,
            .pagination > li.active > span {
                background: linear-gradient(135deg, #3498db, #2980b9);
                border-color: #2980b9;
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
            
            /* Action buttons */
            .action-buttons .btn {
                padding: 3px 8px;
                font-size: 12px;
                margin: 2px;
            }
            
            /* Reason tooltip */
            .reason-tooltip {
                position: relative;
                display: inline-block;
                cursor: help;
            }
            
            .reason-tooltip .tooltip-text {
                visibility: hidden;
                width: 200px;
                background-color: #333;
                color: #fff;
                text-align: center;
                border-radius: 6px;
                padding: 5px;
                position: absolute;
                z-index: 1;
                bottom: 125%;
                left: 50%;
                margin-left: -100px;
                opacity: 0;
                transition: opacity 0.3s;
            }
            
            .reason-tooltip:hover .tooltip-text {
                visibility: visible;
                opacity: 1;
            }
        </style>
    </head>
    <body class="skin-blue">
        <header class="header">
            <a href="index.html" class="logo">Admin Warehouse</a>
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
                                <span><?php echo htmlspecialchars($username); ?><i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header bg-light-blue">
                                    <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php 
                                        echo htmlspecialchars($pegawai['Nama']) . " - " . htmlspecialchars($pegawai['Jabatan']) . " " . htmlspecialchars($pegawai['Departemen']); ?>
                                        <small>Member since <?php echo htmlspecialchars($pegawai['Tanggal_Masuk']); ?></small>
                                    </p>
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
                    </ul>
                </div>
            </nav>
        </header>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <aside class="left-side sidebar-offcanvas">
                <section class="sidebar">
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
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
                        <li>
                            <a href="history_request.php">
                                <i class="fa fa-archive"></i> <span>Request History</span>
                            </a>
                        </li>
                         <li>
                            <a href="sales_request.php">
                                <i class="fa fa-retweet"></i> <span>Sales Request</span>
                            </a>
                        </li>
                        <li class="active">
                            <a href="purchase_history.php">
                                <i class="fa fa-shopping-cart"></i> <span>Purchase Orders</span>
                            </a>
                        </li>
                        <li>
                            <a href="mailbox.php">
                                <i class="fa fa-comments"></i> <span>Mailbox</span>
                            </a>
                        </li>
                    </ul>
                </section>
            </aside>
            <aside class="right-side">
                <section class="content-header">
                    <h1>
                        Purchase Order History
                        <small>Track orders from purchasing department</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Purchase Orders</li>
                    </ol>
                </section>

                <section class="content">
                    <div class="order-history-container">
                        <div class="filter-container">
                            <div class="filter-form">
                                <button id="exportExcel" class="btn btn-success" title="Download as Excel"><i class="fa fa-file-excel-o"></i> Excel</button>
                                <button id="exportCSV" class="btn btn-info" title="Download as CSV"><i class="fa fa-file-text-o"></i> CSV</button>
                                <button id="exportPDF" class="btn btn-danger" title="Download as PDF"><i class="fa fa-file-pdf-o"></i> PDF</button>
                            
                                <form method="get" action="purchase_history.php" class="form-inline">
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="pending" <?php echo ($status_filter == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                        <option value="approved" <?php echo ($status_filter == 'approved' ? 'selected' : ''); ?>>Approved</option>
                                        <option value="rejected" <?php echo ($status_filter == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                                        <option value="delivered" <?php echo ($status_filter == 'delivered' ? 'selected' : ''); ?>>Delivered</option>
                                    </select>
                                    
                                    <input type="date" name="date_from" class="form-control" placeholder="From Date" value="<?php echo htmlspecialchars($date_from); ?>">
                                    <input type="date" name="date_to" class="form-control" placeholder="To Date" value="<?php echo htmlspecialchars($date_to); ?>">
                            
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-filter"></i> Filter
                                    </button>
                                    <?php if(isset($_GET['status']) || isset($_GET['date_from']) || isset($_GET['date_to'])): ?>
                                        <a href="purchase_history.php" class="btn btn-default">
                                            <i class="fa fa-times"></i> Clear
                                        </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <div class="total-orders">
                                <?php
                                $count_query = "SELECT COUNT(*) as total FROM pemesanan1 WHERE 1=1";
                                
                                if ($status_filter != '') {
                                    if ($status_filter == 'pending') {
                                        $count_query .= " AND status = 'pending'";
                                    } elseif ($status_filter == 'approved') {
                                        $count_query .= " AND status = 'approved' AND delivery_date IS NULL";
                                    } elseif ($status_filter == 'delivered') {
                                        $count_query .= " AND status = 'approved' AND delivery_date IS NOT NULL";
                                    } elseif ($status_filter == 'rejected') {
                                        $count_query .= " AND status = 'rejected'";
                                    }
                                } else {
                                    $count_query .= " AND status IN ('pending', 'approved', 'rejected')";
                                }
                                
                                if ($date_from != '') {
                                    $count_query .= " AND order_date >= '$date_from'";
                                }
                                
                                if ($date_to != '') {
                                    $count_query .= " AND order_date <= '$date_to 23:59:59'";
                                }
                                
                                $count_result = mysqli_query($mysqli, $count_query);
                                $count_row = mysqli_fetch_assoc($count_result);
                                echo "<span class='badge bg-blue'>{$count_row['total']} orders found</span>";
                                ?>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="order-history-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Order ID</th>
                                        <th>Order Date</th>
                                        <th>Item Name</th>
                                    
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total Price</th>
                                        <th>Unit</th>
                                        <th>Supplier</th>
                                        <th>Status</th>
                                        <th>Delivery Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query = "SELECT po.*,  
                                             a1.Nama as approved_by_name, a2.Nama as rejected_by_name
                                             FROM pemesanan1 po
                                           
                                             LEFT JOIN pegawai a1 ON po.approved_by = a1.id_pegawai
                                             LEFT JOIN pegawai a2 ON po.rejected_by = a2.id_pegawai
                                             WHERE 1=1";
                                    
                                    if ($status_filter != '') {
                                        if ($status_filter == 'pending') {
                                            $query .= " AND po.status = 'pending'";
                                        } elseif ($status_filter == 'approved') {
                                            $query .= " AND po.status = 'approved' AND po.delivery_date IS NULL";
                                        } elseif ($status_filter == 'delivered') {
                                            $query .= " AND po.status = 'approved' AND po.delivery_date IS NOT NULL";
                                        } elseif ($status_filter == 'rejected') {
                                            $query .= " AND po.status = 'rejected'";
                                        }
                                    } else {
                                        $query .= " AND po.status IN ('pending', 'approved', 'rejected')";
                                    }
                                    
                                    if ($date_from != '') {
                                        $query .= " AND po.order_date >= '$date_from'";
                                    }
                                    
                                    if ($date_to != '') {
                                        $query .= " AND po.order_date <= '$date_to 23:59:59'";
                                    }
                                    
                                    $query .= " ORDER BY po.order_date DESC";
                                    
                                    $per_page = 15;
                                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    $start = ($page - 1) * $per_page;
                                    $query .= " LIMIT $start, $per_page";
                                    
                                    $result = mysqli_query($mysqli, $query);
                                    $no = $start + 1;
                                    
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $total_price = $row['quantity'] * $row['price'];
                                            $is_delivered = ($row['status'] == 'approved' && $row['delivery_date'] != null);
                                            
                                            echo "<tr style='--row-index: {$no};'>
                                                <td>{$no}</td>
                                                <td>{$row['order_id']}</td>
                                                <td class='order-date'>".date('d M Y H:i', strtotime($row['order_date']))."</td>
                                                <td>{$row['item_name']}</td>
                                               
                                                <td>{$row['quantity']}</td>
                                                <td class='price'>".number_format($row['price'], 2)."</td>
                                                <td class='price total-price'>".number_format($total_price, 2)."</td>
                                                <td>{$row['unit']}</td>
                                                <td>{$row['supplier_name']}</td>
                                                <td>";
                                            
                                            if ($is_delivered) {
                                                echo "<span class='status-badge status-delivered'>Delivered</span>";
                                            } elseif ($row['status'] == 'approved') {
                                                echo "<span class='status-badge status-approved'>Approved</span>";
                                            } elseif ($row['status'] == 'rejected') {
                                                echo "<span class='status-badge status-rejected'>Rejected</span>";
                                                if ($row['rejection_reason']) {
                                                    echo "<div class='reason-tooltip'><i class='fa fa-info-circle'></i>
                                                        <span class='tooltip-text'>{$row['rejection_reason']}</span>
                                                    </div>";
                                                }
                                            } elseif ($row['status'] == 'pending') {
                                                echo "<span class='status-badge status-pending'>Pending</span>";
                                            }
                                            
                                            echo "</td>
                                                <td class='order-date'>".($row['delivery_date'] ? date('d M Y', strtotime($row['delivery_date'])) : 'N/A')."</td>
                                                <td class='action-buttons'>";
                                            
                                            // Show different buttons based on status
                                            if ($is_delivered) {
                                                echo "<button class='btn btn-success btn-xs' onclick='receiveStock({$row['order_id']})'>
                                                    <i class='fa fa-check'></i> Confirm
                                                </button>";
                                            } elseif ($row['status'] == 'approved') {
                                                echo "<span class='text-muted'>Waiting for delivery</span>";
                                            } elseif ($row['status'] == 'rejected') {
                                                echo "<button class='btn btn-default btn-xs' onclick='viewRejectionReason({$row['order_id']})'>
                                                    <i class='fa fa-eye'></i> View Reason
                                                </button>";
                                            } else {
                                                echo "<span class='text-muted'>No action needed</span>";
                                            }
                                            
                                            echo "</td>
                                            </tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr>
                                            <td colspan='13'>
                                                <div class='empty-state'>
                                                    <i class='fa fa-inbox'></i>
                                                    <h4>No Purchase Orders Found</h4>
                                                    <p>There are no orders matching your criteria</p>
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
                                    echo "<li><a href='purchase_history.php?page=".($page-1).
                                        ($status_filter ? "&status=$status_filter" : "").
                                        ($date_from ? "&date_from=$date_from" : "").
                                        ($date_to ? "&date_to=$date_to" : "").
                                        "'>&laquo;</a></li>";
                                }
                                
                                for ($i = 1; $i <= $total_pages; $i++) {
                                    $active = ($i == $page) ? "active" : "";
                                    echo "<li class='$active'><a href='purchase_history.php?page=$i".
                                        ($status_filter ? "&status=$status_filter" : "").
                                        ($date_from ? "&date_from=$date_from" : "").
                                        ($date_to ? "&date_to=$date_to" : "").
                                        "'>$i</a></li>";
                                }
                                
                                if ($page < $total_pages) {
                                    echo "<li><a href='purchase_history.php?page=".($page+1).
                                        ($status_filter ? "&status=$status_filter" : "").
                                        ($date_from ? "&date_from=$date_from" : "").
                                        ($date_to ? "&date_to=$date_to" : "").
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
        
        <!-- SweetAlert for notifications -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
        // Function to get table data for export
        function getTableData() {
            var table = document.querySelector('.order-history-table');
            var data = [];
            var rows = table.querySelectorAll('tr');
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll('th,td');
                for (var j = 0; j < cols.length; j++) {
                    // Skip action buttons column
                    if (j !== cols.length - 1) {
                        row.push(cols[j].innerText.trim());
                    }
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
            XLSX.utils.book_append_sheet(wb, ws, "PurchaseOrders");
            XLSX.writeFile(wb, "purchase-orders.xlsx");
        };

        // CSV Export
        document.getElementById('exportCSV').onclick = function() {
            var data = getTableData();
            var ws = XLSX.utils.aoa_to_sheet(data);
            var csv = XLSX.utils.sheet_to_csv(ws);
            var blob = new Blob([csv], {type: "text/csv"});
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = "purchase-orders.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

        // PDF Export
        document.getElementById('exportPDF').onclick = function() {
            var data = getTableData();
            var doc = new jspdf.jsPDF('l', 'pt', 'a4');
            doc.text("Purchase Order History", 40, 30);
            doc.autoTable({
                head: [data[0]],
                body: data.slice(1),
                startY: 50,
                styles: {fontSize: 8},
                columnStyles: {
                    5: {cellWidth: 'auto'},
                    6: {cellWidth: 'auto'},
                    7: {cellWidth: 'auto'}
                }
            });
            doc.save("purchase-orders.pdf");
        };
        
        // Function to handle stock reception
        function receiveStock(orderId) {
            Swal.fire({
                title: 'Confirm Stock Reception',
                text: 'Are you sure you want to confirm the reception of this order? This will update your inventory.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, confirm!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // AJAX call to update stock
                    $.ajax({
                        url: 'update_stock.php',
                        type: 'POST',
                        data: { order_id: orderId, action: 'receive' },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Success!',
                                    'Stock has been updated in inventory.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message || 'Failed to update stock',
                                    'error'
                                );
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'Failed to communicate with server',
                                'error'
                            );
                        }
                    });
                }
            });
        }
        
        // Function to view rejection reason
        function viewRejectionReason(orderId) {
            // AJAX call to get rejection details
            $.ajax({
                url: 'get_order_details.php',
                type: 'POST',
                data: { order_id: orderId },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Rejection Details',
                            html: `<p><strong>Reason:</strong> ${response.data.rejection_reason}</p>
                                  <p><strong>Rejected by:</strong> ${response.data.rejected_by_name}</p>
                                  <p><strong>Rejected on:</strong> ${new Date(response.data.rejected_date).toLocaleString()}</p>`,
                            icon: 'info'
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            response.message || 'Failed to load rejection details',
                            'error'
                        );
                    }
                },
                error: function() {
                    Swal.fire(
                        'Error!',
                        'Failed to communicate with server',
                        'error'
                    );
                }
            });
        }
        </script>
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
    </body>
</html>