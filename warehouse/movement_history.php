<?php 
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

// Set active submenu for sidebar highlighting
$active_submenu = isset($_GET['submenu']) ? $_GET['submenu'] : 'movement';

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


// Get active submenu from URL
$active_submenu = isset($_GET['submenu']) ? $_GET['submenu'] : '';

// Get filter values
$cabang_filter = isset($_GET['cabang']) ? mysqli_real_escape_string($mysqli, $_GET['cabang']) : '';
$movement_type = isset($_GET['type']) ? mysqli_real_escape_string($mysqli, $_GET['type']) : 'all';
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 100;
$offset = ($current_page - 1) * $limit;

// Handle exports
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    
    $sql = "SELECT m.*, w.Nama as product_name, w.Satuan as unit 
            FROM inventory_movement m
            LEFT JOIN warehouse w ON m.product_code = w.Code
            WHERE 1=1";
                
    if ($movement_type != 'all') {
        $sql .= " AND m.movement_type = '$movement_type'";
    }
    if ($cabang_filter != '') {
        $sql .= " AND m.warehouse = '$cabang_filter'";
    }
    $sql .= " ORDER BY m.movement_date DESC";
    
    $result = mysqli_query($mysqli, $sql);
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    $headers = array('Date', 'Movement', 'Product Code', 'Product Name', 'Quantity', 'Previous Stock', 'New Stock', 'Difference', 'Warehouse', 'PIC', 'Notes');
    $rows = array();
    foreach ($data as $item) {
        $difference = $item['new_stock'] - $item['previous_stock'];
        $rows[] = array(
            date('d M Y H:i', strtotime($item['movement_date'])),
            ucfirst($item['movement_type']),
            $item['product_code'],
            $item['product_name'] ?? 'N/A',
            abs($difference) . ' ' . ($item['unit'] ?? ''),
            $item['previous_stock'],
            $item['new_stock'],
            ($difference > 0 ? '+' : '') . $difference,
            $item['warehouse'],
            $item['pic'],
            $item['notes'] ?? ''
        );
    }
    
    $filename = 'movement_history_export_' . date('Ymd_His');
    
    if ($export_type == 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
        header('Cache-Control: max-age=0');
        
        echo '<table border="1">';
        echo '<tr>';
        foreach ($headers as $header) {
            echo '<th>'.$header.'</th>';
        }
        echo '</tr>';
        
        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>'.$cell.'</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }
    elseif ($export_type == 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="'.$filename.'.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    elseif ($export_type == 'pdf') {
        require_once('../tcpdf/tcpdf.php');
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Warehouse System');
        $pdf->SetTitle('Movement History Export');
        $pdf->SetSubject('Movement History');
        $pdf->SetKeywords('Export, PDF, Movement, History');
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        
        // Add title
        $pdf->Cell(0, 10, 'Movement History Export', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Create table header
        $html = '<table border="1" cellpadding="4">';
        $html .= '<tr>';
        foreach ($headers as $header) {
            $html .= '<th>'.$header.'</th>';
        }
        $html .= '</tr>';
        
        // Add table rows
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>'.$cell.'</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        $pdf->Output($filename.'.pdf', 'D');
        exit;
    }
}
?>


  <!DOCTYPE html>
<html lang="en">
<head>
        <?php include('styles.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Warehouse</title>
    
    <!-- CSS Links -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
     <link href="../css/dashboard.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    
</head>
<body class="skin-blue">
    <?php include('navbar.php'); ?>
    
    <div class="wrapper row-offcanvas row-offcanvas-left">
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
                        <li class="treeview active">
                            <a href="#">
                                <i class="fa fa-exchange"></i> <span>Movement</span>

                            </a>
                            <ul class="treeview-menu" style="<?php echo in_array($active_submenu, ['movement','movement-history','inbound','outbound']) ? 'display: block;' : ''; ?>">
                                <li class="<?php echo $active_submenu == 'movement' ? 'active' : ''; ?>">
                                    <a href="movement.php?submenu=movement"><i class="fa fa-th"></i> All Movement</a>
                                </li>
                                <li class="<?php echo $active_submenu == 'movement-history' ? 'active' : ''; ?>">
                                    <a href="movement_history.php?submenu=movement-history"><i class="fa fa-undo"></i> Movement History</a>
                                </li>
                                <li class="<?php echo $active_submenu == 'inbound' ? 'active' : ''; ?>">
                                    <a href="movement_inbound.php?submenu=inbound"><i class="fa fa-sign-in"></i> Inbound</a>
                                </li>
                                <li class="<?php echo $active_submenu == 'outbound' ? 'active' : ''; ?>">
                                    <a href="movement_outbound.php?submenu=outbound"><i class="fa fa-sign-out"></i> Outbound</a>
                                </li>
                            </ul>
                        </li>
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
                        Movement History
                        <small>Track all inventory movements</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Movement History</li>
                    </ol>
                </section>

                <section class="content">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if(isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Movement History</h3>
                                  
                                </div>
                                <div class="box-body">
                                    <!-- Filter Form -->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <form method="get" action="movement_history.php" class="form-inline">
                                                <input type="hidden" name="submenu" value="movement-history">
                                                <div class="form-group">
                                                    <button id="exportExcel" class="btn btn-success" title="Download as Excel"><i class="fa fa-file-excel-o"></i> Excel</button>
                                                    <button id="exportCSV" class="btn btn-info" title="Download as CSV"><i class="fa fa-file-text-o"></i> CSV</button>
                                                    <button id="exportPDF" class="btn btn-danger" title="Download as PDF"><i class="fa fa-file-pdf-o"></i> PDF</button>
                                                    <label for="cabang">Warehouse: </label>
                                                    <select name="cabang" class="form-control input-sm">
                                                        <option value="">All</option>
                                                        <?php
                                                        $warehouse_query = mysqli_query($mysqli, "SELECT nama FROM list_warehouse ORDER BY nama ASC");
                                                        while ($wh = mysqli_fetch_assoc($warehouse_query)): ?>
                                                            <option value="<?php echo htmlspecialchars($wh['nama']); ?>" <?php echo ($cabang_filter == $wh['nama'] ? 'selected' : ''); ?>>
                                                                <?php echo htmlspecialchars($wh['nama']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group" style="margin-left:10px;">
                                                    <label for="type">Movement Type: </label>
                                                    <select name="type" class="form-control input-sm">
                                                        <option value="all" <?php echo ($movement_type == 'all' ? 'selected' : ''); ?>>All</option>
                                                        <option value="inbound" <?php echo ($movement_type == 'inbound' ? 'selected' : ''); ?>>Inbound</option>
                                                        <option value="outbound" <?php echo ($movement_type == 'outbound' ? 'selected' : ''); ?>>Outbound</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm" style="margin-left:10px;">
                                                    <i class="fa fa-filter"></i> Filter
                                                </button>
                                                <a href="movement_history.php?submenu=movement-history" class="btn btn-default btn-sm" style="margin-left:10px;">
                                                    <i class="fa fa-times"></i> Clear
                                                </a>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="table-responsive" style="margin-top:20px;">
                                        <table class="table table-bordered table-striped" id="movementsTable">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Product Code</th>
                                                    <th>Product Name</th>
                                                    <th>Quantity</th>
                                                    <th>Previous Stock</th>
                                                    <th>New Stock</th>
                                                    <th>Difference</th>
                                                    <th>Warehouse</th>
                                                    <th>PIC</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $movement_sql = "SELECT m.*, w.Nama as product_name, w.Satuan as unit 
                                                            FROM inventory_movement m
                                                            LEFT JOIN warehouse w ON m.product_code = w.Code
                                                            WHERE 1=1";
                                                if ($movement_type != 'all') {
                                                    $movement_sql .= " AND m.movement_type = '$movement_type'";
                                                }
                                                if ($cabang_filter != '') {
                                                    $movement_sql .= " AND m.warehouse = '$cabang_filter'";
                                                }
                                                $movement_sql .= " ORDER BY m.movement_date DESC LIMIT $limit OFFSET $offset";
                                                
                                                $movement_result = $mysqli->query($movement_sql);
                                                if ($movement_result && $movement_result->num_rows > 0) {
                                                    while ($movement = $movement_result->fetch_assoc()) {
                                                        $type_class = ($movement['movement_type'] == 'inbound') ? 'label-success' : (($movement['movement_type'] == 'outbound') ? 'label-danger' : 'label-default');
                                                        $difference = $movement['new_stock'] - $movement['previous_stock'];
                                                        $arrow = ($difference > 0) ? '↑' : '↓';
                                                        
                                                        echo "<tr>
                                                            <td>" . date('d M Y H:i', strtotime($movement['movement_date'])) . "</td>
                                                            <td><span class='label $type_class'>" . ucfirst($movement['movement_type']) . "</span></td>
                                                            <td>" . htmlspecialchars($movement['product_code']) . "</td>
                                                            <td>" . htmlspecialchars($movement['product_name'] ?? 'N/A') . "</td>
                                                            <td>" . abs($difference) . " " . htmlspecialchars($movement['unit'] ?? '') . "</td>
                                                            <td>" . htmlspecialchars($movement['previous_stock']) . "</td>
                                                            <td>" . htmlspecialchars($movement['new_stock']) . "</td>
                                                            <td><span class='" . ($difference > 0 ? 'text-success' : 'text-danger') . "'>$arrow " . abs($difference) . "</span></td>
                                                            <td>" . htmlspecialchars($movement['warehouse']) . "</td>
                                                            <td>" . htmlspecialchars($movement['pic']) . "</td>
                                                            <td>" . htmlspecialchars($movement['notes'] ?? '') . "</td>
                                                        </tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='11' class='text-center'>No movement history found</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Pagination -->
                                    <div class="text-center">
                                        <ul class="pagination">
                                            <?php
                                            // Count total records
                                            $count_sql = "SELECT COUNT(*) as total FROM inventory_movement WHERE 1=1";
                                            if ($movement_type != 'all') {
                                                $count_sql .= " AND movement_type = '$movement_type'";
                                            }
                                            if ($cabang_filter != '') {
                                                $count_sql .= " AND warehouse = '$cabang_filter'";
                                            }
                                            
                                            $count_result = $mysqli->query($count_sql);
                                            $total_records = $count_result->fetch_assoc()['total'];
                                            $total_pages = ceil($total_records / $limit);
                                            
                                            for ($i = 1; $i <= $total_pages; $i++) {
                                                echo "<li" . ($current_page == $i ? " class='active'" : "") . ">
                                                    <a href='movement_history.php?submenu=movement-history&cabang=" . urlencode($cabang_filter) . "&
                                                    type=" . urlencode($movement_type) . "&page=$i'>$i</a>
                                                </li>";
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <!-- jQuery and Bootstrap JS -->
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/AdminLTE.min.js" type="text/javascript"></script>

        <!-- SheetJS & jsPDF CDN -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

        <script>
        // Initialize jsPDF
        const { jsPDF } = window.jspdf;
        
        function getTableData() {
            var table = document.getElementById('movementsTable');
            var data = [];
            var headers = [];
            
            // Get headers
            var headerRow = table.querySelectorAll('thead tr th');
            headerRow.forEach(function(th) {
                headers.push(th.innerText.trim());
            });
            data.push(headers);
            
            // Get rows data
            var rows = table.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                var rowData = [];
                var cols = row.querySelectorAll('td');
                cols.forEach(function(td) {
                    // For the difference column, we want to keep the arrow symbol
                    if (td.querySelector('span.text-success, span.text-danger')) {
                        rowData.push(td.querySelector('span').innerText.trim());
                    } else {
                        rowData.push(td.innerText.trim());
                    }
                });
                data.push(rowData);
            });
            return data;
        }

        // Excel Export
        document.getElementById('exportExcel').addEventListener('click', function(e) {
            e.preventDefault();
            var data = getTableData();
            var ws = XLSX.utils.aoa_to_sheet(data);
            var wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "MovementHistory");
            XLSX.writeFile(wb, "movement-history.xlsx");
        });

        // CSV Export
        document.getElementById('exportCSV').addEventListener('click', function(e) {
            e.preventDefault();
            var data = getTableData();
            var csvContent = "";
            
            data.forEach(function(rowArray) {
                var row = rowArray.map(function(item) {
                    return '"' + item.replace(/"/g, '""') + '"';
                }).join(",");
                csvContent += row + "\r\n";
            });
            
            var blob = new Blob([csvContent], {type: "text/csv;charset=utf-8;"});
            var link = document.createElement("a");
            var url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "movement-history.csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // PDF Export
        document.getElementById('exportPDF').addEventListener('click', function(e) {
            e.preventDefault();
            var data = getTableData();
            var headers = data[0];
            var rows = data.slice(1);
            
            var doc = new jsPDF('l');
            
            // Title
            doc.setFontSize(16);
            doc.text('Movement History Report', 14, 15);
            doc.setFontSize(10);
            doc.text('Generated on: ' + new Date().toLocaleString(), 14, 22);
            
            // Table
            doc.autoTable({
                head: [headers],
                body: rows,
                startY: 30,
                margin: { left: 10 },
                styles: { 
                    fontSize: 8,
                    cellPadding: 2,
                    valign: 'middle'
                },
                columnStyles: {
                    0: { cellWidth: 25 },
                    1: { cellWidth: 15 },
                    2: { cellWidth: 25 },
                    3: { cellWidth: 40 },
                    4: { cellWidth: 20 },
                    5: { cellWidth: 25 },
                    6: { cellWidth: 25 },
                    7: { cellWidth: 20 },
                    8: { cellWidth: 25 },
                    9: { cellWidth: 25 },
                    10: { cellWidth: 40 }
                },
                didDrawCell: function(data) {
                    // Highlight difference column
                    if (data.column.index === 7) {
                        var cellText = data.cell.text[0];
                        if (cellText.includes('↑')) {
                            doc.setTextColor(0, 166, 90); // Green
                        } else if (cellText.includes('↓')) {
                            doc.setTextColor(221, 75, 57); // Red
                        }
                        doc.text(data.cell.text, data.cell.x + data.cell.padding('left'), data.cell.y + data.cell.padding('top') + 2);
                        doc.setTextColor(0, 0, 0); // Reset to black
                    }
                }
            });
            
            doc.save("movement-history.pdf");
        });

        $(document).ready(function() {
            // Initialize any other jQuery plugins or functions here
        });
        </script>
    </body>
</html>