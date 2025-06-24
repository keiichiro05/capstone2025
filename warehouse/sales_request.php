<?php
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

if(!isset($_SESSION['username'])) {
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
$urgency_filter = isset($_GET['urgency']) ? mysqli_real_escape_string($mysqli, $_GET['urgency']) : '';

// Handle fulfillment
if (isset($_GET['fulfill_id'])) {
    $id = mysqli_real_escape_string($mysqli, $_GET['fulfill_id']);
    
    // Get request data
    $request_query = mysqli_query($mysqli, "SELECT * FROM sales_requests WHERE Code='$Code'");
    $request = mysqli_fetch_assoc($request_query);
    
    if ($request) {
        // Generate document number
        $doc_number = 'DOC-' . date('Ymd') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
        
        // Create fulfillment recorder_id
        $current_time = date('Y-m-d H:i:s');
        mysqli_query($mysqli, "UPDATE sales_requests SET 
            status = 'fulfilled',
            fulfilled_by = '$username',
            fulfilled_at = '$current_time',
            doc_number = '$doc_number'
            WHERE id='$id'");
        
        // Generate document content
        $document_content = generateDocumentContent($doc_number, $request, $pegawai);
        
        // Save document to database
        mysqli_query($mysqli, "INSERT INTO request_documents 
            (doc_number, request_id, content, created_by, created_at) 
            VALUES 
            ('$doc_number', '$id', '" . mysqli_real_escape_string($mysqli, $document_content) . "', '$username', '$current_time')");
        
        // Redirect to avoid resubmission
        header("Location: sales_request.php?status=fulfilled&doc=$doc_number");
        exit();
    }
}

function generateDocumentContent($doc_number, $request, $pegawai) {
    return "
        <html>
        <head>
            <title>Purchase Request Document</title>
            <style>
                body { font-family: Arial, sans-serif; }
                .header { text-align: center; margin-bottom: 10px; }
                .doc-number { font-weight: bold; font-size: 15px; }
                .details { margin: 5px 0; }
            table { width: 100%; border-collapse: collapse; }
            table, th, td { border: 1px solid #ddd; }
                th, td { padding: 3px; text-align: left; }
                .signature { margin-top: 35px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>PURCHASE REQUEST DOCUMENT</h2>
                <div class='doc-number'>Document Number: $doc_number</div>
                <div>Date: " . date('d F Y') . "</div>
            </div>
            
            <div class='details'>
                <h3>Request Details</h3>
                <table>
                    <tr>
                        <th>Request Date</th>
                        <td>" . date('d F Y H:i', strtotime($request['request_date'])) . "</td>
                    </tr>
                    <tr>
                        <th>Sales Person</th>
                        <td>{$request['sales_person']}</td>
                    </tr>
                    <tr>
                        <th>Customer</th>
                        <td>{$request['customer_name']} (ID: {$request['customer_id']})</td>
                    </tr>
                </table>
            </div>
            
            <div class='details'>
                <h3>Item Details</h3>
                <table>
                    <tr>
                        <th>Item Code</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Urgency</th>
                    </tr>
                    <tr>
                        <td>{$request['item_code']}</td>
                        <td>{$request['reason']}</td>
                        <td>{$request['quantity']}</td>
                        <td>" . ucfirst($request['urgency']) . "</td>
                    </tr>
                </table>
            </div>
            
            <div class='signature'>
                <table>
                    <tr>
                        <td width='30%'>
                            <div>Warehouse Staff:</div>
                            <div style='margin-top: 30px;'>_________________________</div>
                            <div>{$pegawai['Nama']}</div>
                            <div>" . date('d F Y') . "</div>
                        </td>
                        <td width='30%'>
                            <div>Inventory Manager:</div>
                            <div style='margin-top: 30px;'>_________________________</div>
                            <div>Date: _______________</div>
                        </td>
                        <td width='30%'>
                            <div>Purchasing Dept:</div>
                            <div style='margin-top: 30px;'>_________________________</div>
                            <div>Date: _______________</div>
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>
    ";
}
?>
<!DOCTYPE html>
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
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            margin: 0 auto;
            display: flex;
            justify-content: center;
        }

        .order_id-history-table {
            margin: 0 auto;
            width: auto;
            max-width: 100%;
            border-collapse: collapse;
        }

        .order_id-history-table th, 
        .order_id-history-table td {
            padding: 4px 8px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #ddd;
        }

        .order_id-history-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .order_id-history-table thead {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .urgency-high {
            color: #d9534f;
            font-weight: bold;
        }

        .urgency-medium {
            color: #f0ad4e;
            font-weight: bold;
        }

        .urgency-low {
            color: #5cb85c;
        }

        .status-badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-pending {
            background-color: #f0ad4e;
            color: white;
        }

        .status-fulfilled {
            background-color: #5cb85c;
            color: white;
        }

        .status-rejected {
            background-color: #d9534f;
            color: white;
        }

        .filter-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .total-record {
            margin-left: auto;
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #777;
        }

        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }

        @media (max-width: 768px) {
            .order_id-history-table {
                font-size: 14px;
            }
            
            .order_id-history-table th, 
            .order_id-history-table td {
                padding: 6px 8px;
            }

            .filter-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .total-record {
                margin-left: 0;
                margin-top: 10px;
            }
        }
    </style>
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
                    <li>
                        <a href="history_request.php">
                            <i class="fa fa-archive"></i> <span>Request History</span>
                        </a>
                    </li>
                    <li class="active">
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
                    Sales Request History
                    <small>View and Manage Sales Request History</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Sales Request History</li>
                </ol>
            </section>

            <section class="content">
                <?php if (isset($_GET['status']) && $_GET['status'] == 'fulfilled' && isset($_GET['doc'])): ?>
                    <div class="alert alert-success alert-dismissable">
                        <i class="fa fa-check"></i>
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <b>Request fulfilled!</b> Document <?php echo htmlspecialchars($_GET['doc']); ?> has been generated.
                        <a href="generate_document.php?doc=<?php echo urlencode($_GET['doc']); ?>" target="_blank" class="btn btn-success btn-xs">
                            <i class="fa fa-file-text"></i> View Document
                        </a>
                        <a href="mailto:inventory@company.com?cc=purchasing@company.com&subject=Purchase Request <?php echo urlencode($_GET['doc']); ?>&body=Please find attached the purchase request document." class="btn btn-primary btn-xs">
                            <i class="fa fa-envelope"></i> Send to Inventory & Purchasing
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="order_id-history-container">
                    <div class="filter-container">
                        <div class="filter-form">
                            <button id="exportExcel" class="btn btn-success" title="Download as Excel"><i class="fa fa-file-excel-o"></i> Excel</button>
                            <button id="exportCSV" class="btn btn-info" title="Download as CSV"><i class="fa fa-file-text-o"></i> CSV</button>
                            <button id="exportPDF" class="btn btn-danger" title="Download as PDF"><i class="fa fa-file-pdf-o"></i> PDF</button>
                        
                            <form method="get" action="sales_request.php" class="filter-form">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo ($status_filter == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                    <option value="fulfilled" <?php echo ($status_filter == 'fulfilled' ? 'selected' : ''); ?>>Fulfilled</option>
                                    <option value="rejected" <?php echo ($status_filter == 'rejected' ? 'selected' : ''); ?>>Rejected</option>
                                </select>
                                
                                <select name="urgency" class="form-control">
                                    <option value="">All Urgency</option>
                                    <option value="high" <?php echo ($urgency_filter == 'high' ? 'selected' : ''); ?>>High</option>
                                    <option value="medium" <?php echo ($urgency_filter == 'medium' ? 'selected' : ''); ?>>Medium</option>
                                    <option value="low" <?php echo ($urgency_filter == 'low' ? 'selected' : ''); ?>>Low</option>
                                </select>
                        
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <?php if(isset($_GET['status']) || isset($_GET['urgency'])): ?>
                                    <a href="sales_request.php" class="btn btn-default">
                                        <i class="fa fa-times"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="total-record">
                            <?php
                            $count_query = "SELECT COUNT(*) as total FROM sales_requests WHERE 1=1";
                            
                            if ($status_filter != '') {
                                $count_query .= " AND status = '$status_filter'";
                            } else {
                                $count_query .= " AND status IN ('fulfilled', 'rejected', 'pending')";
                            }
                            
                            if ($urgency_filter != '') {
                                $count_query .= " AND urgency = '$urgency_filter'";
                            }
                            
                            $count_result = mysqli_query($mysqli, $count_query);
                            $count_row = mysqli_fetch_assoc($count_result);
                            echo "<span class='badge bg-blue'>{$count_row['total']} requests found</span>";
                            ?>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="order_id-history-table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Request Date</th>
                                    <th>Sales Person</th>
                                    <th>Customer</th>
                                    <th>Item Code</th>
                                    <th>Quantity</th>
                                    <th>Reason</th>
                                    <th>Urgency</th>
                                    <th>Status</th>
                                    <th>Requested By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM sales_requests WHERE 1=1";
                                
                                if ($status_filter != '') {
                                    $query .= " AND status = '$status_filter'";
                                } else {
                                    $query .= " AND status IN ('fulfilled', 'rejected', 'pending')";
                                }
                                
                                if ($urgency_filter != '') {
                                    $query .= " AND urgency = '$urgency_filter'";
                                }
                                
                                $query .= " ORDER BY 
                                    CASE WHEN urgency = 'high' THEN 1 
                                         WHEN urgency = 'medium' THEN 2
                                         ELSE 3 END,
                                    request_date DESC";
                                
                                $per_page = 15;
                                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $start = ($page - 1) * $per_page;
                                $query .= " LIMIT $start, $per_page";
                                
                                $result = mysqli_query($mysqli, $query);
                                $no = $start + 1;
                                
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $urgency_class = '';
                                        if ($row['urgency'] == 'high') {
                                            $urgency_class = 'urgency-high';
                                        } elseif ($row['urgency'] == 'medium') {
                                            $urgency_class = 'urgency-medium';
                                        } else {
                                            $urgency_class = 'urgency-low';
                                        }
                                        
                                        echo "<tr>
                                            <td>{$no}</td>
                                            <td>".date('d M Y H:i', strtotime($row['request_date']))."</td>
                                            <td>{$row['sales_person']}</td>
                                            <td>{$row['customer_name']}<br><small>ID: {$row['customer_id']}</small></td>
                                            <td>{$row['item_code']}</td>
                                            <td>{$row['quantity']}</td>
                                            <td>{$row['reason']}</td>
                                            <td class='{$urgency_class}'>".ucfirst($row['urgency'])."</td>
                                            <td>";
                                        
                                        if ($row['status'] == 'fulfilled') {
                                            echo "<span class='status-badge status-fulfilled'>Fulfilled</span>";
                                            if ($row['fulfilled_at']) {
                                                echo "<br><small>".date('d M Y', strtotime($row['fulfilled_at']))."</small>";
                                            }
                                        } elseif ($row['status'] == 'rejected') {
                                            echo "<span class='status-badge status-rejected'>Rejected</span>";
                                            if ($row['rejected_at']) {
                                                echo "<br><small>".date('d M Y', strtotime($row['rejected_at']))."</small>";
                                            }
                                        } elseif ($row['status'] == 'pending') {
                                            echo "<span class='status-badge status-pending'>Pending</span>";
                                        }
                                        
                                        echo "</td>
                                            <td>{$row['requested_by']}</td>
                                            <td>";
                                            
                                        if ($row['status'] == 'pending') {
                                            echo "<div class='btn-group'>
                                                <a href='sales_request.php?fulfill_id={$row['Code']}' class='btn btn-success btn-sm fulfill-btn' title='Fulfill'>
                                                    <i class='fa fa-check'></i>
                                                </a>
                                                <button class='btn btn-danger btn-sm reject-btn' data-id='{$row['Code']}' title='Reject'>
                                                    <i class='fa fa-times'></i>
                                                </button>
                                            </div>";
                                        } elseif ($row['status'] == 'rejected') {
                                            echo "<small>{$row['rejection_reason']}</small>";
                                        } elseif ($row['status'] == 'fulfilled') {
                                            echo "<a href='generate_document.php?doc={$row['doc_number']}' target='_blank' class='btn btn-info btn-sm' title='View Document'>
                                                <i class='fa fa-file-text'></i>
                                            </a>";
                                        }
                                        
                                        echo "</td>
                                        </tr>";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr>
                                        <td colspan='11'>
                                            <div class='empty-state'>
                                                <i class='fa fa-inbox'></i>
                                                <h4>No Sales Requests Found</h4>
                                                <p>There are no requests matching your criteria</p>
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
                                echo "<li><a href='sales_request.php?page=".($page-1).
                                    ($status_filter ? "&status=$status_filter" : "").
                                    ($urgency_filter ? "&urgency=$urgency_filter" : "").
                                    "'>&laquo;</a></li>";
                            }
                            
                            for ($i = 1; $i <= $total_pages; $i++) {
                                $active = ($i == $page) ? "active" : "";
                                echo "<li class='$active'><a href='sales_request.php?page=$i".
                                    ($status_filter ? "&status=$status_filter" : "").
                                    ($urgency_filter ? "&urgency=$urgency_filter" : "").
                                    "'>$i</a></li>";
                            }
                            
                            if ($page < $total_pages) {
                                echo "<li><a href='sales_request.php?page=".($page+1).
                                    ($status_filter ? "&status=$status_filter" : "").
                                    ($urgency_filter ? "&urgency=$urgency_filter" : "").
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
        XLSX.utils.book_append_sheet(wb, ws, "SalesRequests");
        XLSX.writeFile(wb, "sales-requests.xlsx");
    };

    // CSV Export
    document.getElementById('exportCSV').onclick = function() {
        var data = getTableData();
        var ws = XLSX.utils.aoa_to_sheet(data);
        var csv = XLSX.utils.sheet_to_csv(ws);
        var blob = new Blob([csv], {type: "text/csv"});
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = "sales-requests.csv";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // PDF Export
    document.getElementById('exportPDF').onclick = function() {
        var data = getTableData();
        var doc = new jspdf.jsPDF('l', 'pt', 'a4');
        doc.text("Sales Request List", 40, 30);
        doc.autoTable({
            head: [data[0]],
            body: data.slice(1),
            startY: 50,
            styles: {fontSize: 8}
        });
        doc.save("sales-requests.pdf");
    };

    // Handle reject button
    document.querySelectorAll('.reject-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const reason = prompt('Please enter rejection reason:');
            if (reason !== null && reason.trim() !== '') {
                window.location.href = `reject_request.php?id=${id}&reason=${encodeURIComponent(reason)}`;
            }
        });
    });
    </script>

</body>
</html>