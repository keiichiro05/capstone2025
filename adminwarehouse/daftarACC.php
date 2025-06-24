<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Please Login First");
    exit();
}
require_once('../konekdb.php');
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if ($user['jmluser'] == "0") {
    header("Location: ../index.php?status=Access Declined");
    exit();
}

$stmtPegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmtPegawai->bind_param("i", $idpegawai);
$stmtPegawai->execute();
$resultPegawai = $stmtPegawai->get_result();
$pegawai = $resultPegawai->fetch_assoc();

$column_check = mysqli_query($mysqli, "SHOW COLUMNS FROM list_request LIKE 'tanggal'");
$date_column_exists = (mysqli_num_rows($column_check) > 0);

if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';

    // Initialize the query
    $query = "SELECT p.* FROM list_request p";
    
    if ($status_filter != '') {
        $query .= " WHERE p.status = '$status_filter'";
    }
    $query .= " ORDER BY p.tanggal DESC";
    $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));

    $data = array();
    $headers = array('#', 'Order Date', 'Item Name', 'Category', 'Quantity', 'Branch', 'Status');

    if (mysqli_num_rows($result) > 0) {
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) {
            $status = '';
            if ($row['status'] == '1') {
                $status = 'Accepted';
            } elseif ($row['status'] == '2') {
                $status = 'Declined';
            } elseif ($row['status'] == '0') {
                $status = 'Pending';
            }
            $order_date = 'N/A';
            if ($date_column_exists && !empty($row['tanggal'])) {
                $order_date = date('d M Y H:i', strtotime($row['tanggal']));
            } elseif (!empty($row['tanggal'])) {
                $order_date = date('d M Y H:i', strtotime($row['tanggal']));
            }
            $data[] = array(
                $no++,
                $order_date,
                $row['namabarang'],
                $row['kategori'],
                $row['jumlah'],
                'Cikarang',
                $status
            );
        }
    }

    switch ($export_type) {
        case 'excel':
            exportExcel($headers, $data);
            break;
        case 'csv':
            exportCSV($headers, $data);
            break;
        case 'pdf':
            exportPDF($headers, $data);
            break;
    }
    exit;
}

function exportExcel($headers, $data) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename=request_history_'.date('Y-m-d').'.xls');
    echo '<table border="1">';
    echo '<tr>';
    foreach ($headers as $header) {
        echo '<th>'.htmlspecialchars($header).'</th>';
    }
    echo '</tr>';
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>'.htmlspecialchars($cell).'</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

function exportCSV($headers, $data) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=request_history_'.date('Y-m-d').'.csv');
    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, $headers);
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

function exportPDF($headers, $data) {
    require_once('../tcpdf/tcpdf.php');
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Warehouse System');
    $pdf->SetAuthor('Warehouse Manager');
    $pdf->SetTitle('Request History');
    $pdf->SetSubject('Request History Export');
    $pdf->SetKeywords('Request, History, Warehouse');
    $pdf->SetHeaderData('', 0, 'Request History', 'Generated on '.date('Y-m-d H:i:s'));
    $pdf->setHeaderFont(Array('helvetica', '', 10));
    $pdf->setFooterFont(Array('helvetica', '', 8));
    $pdf->SetDefaultMonospacedFont('courier');
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->AddPage();
    $html = '<h2>Request History</h2>';
    $html .= '<table border="1" cellpadding="4">';
    $html .= '<thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th style="background-color:#f2f2f2;font-weight:bold;">'.htmlspecialchars($header).'</th>';
    }
    $html .= '</tr></thead><tbody>';
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>'.htmlspecialchars($cell).'</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('request_history_'.date('Y-m-d').'.pdf', 'D');
    exit;
}
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
                    <li><a href="streamlit.php"><i class="fa fa-signal"></i> <span>Analytics</span></a></li>
                    <li><a href="dashboard.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                    <li><a href="list_request.php"><i class="fa fa-list"></i> <span>List Request</span></a></li>
                    <li class="active"><a href="daftarACC.php"><i class="fa fa-undo"></i> <span>Request History</span></a></li>
          
            <li>
                <a href="frompurchase.php">
                    <i class="fa fa-tasks"></i> <span>Purchase Order</span>
                </a>
            </li>
                    <li><a href="stock.php"><i class="fa fa-archive"></i> <span>Inventory</span></a></li>

                </ul>
            </section>
        </aside>
        <aside class="right-side">
            <section class="content-header">
                <h1>
                    Request History
                    <small>Warehouse Manager</small>
                </h1>
            </section>
            <section class="content">
                <?php
                if (isset($_SESSION['message'])) {
                    echo '<div class="alert alert-info alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            ' . $_SESSION['message'] . '
                        </div>';
                    unset($_SESSION['message']);
                }
                ?>
                <div class="order-history-container">
                    <div class="filter-container">
                        <div class="filter-form">
                            <div class="export-buttons">
                                <a href="?export=excel<?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>" class="btn btn-success" title="Download as Excel"><i class="fa fa-file-excel-o"></i> Excel</a>
                                <a href="?export=csv<?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>" class="btn btn-info" title="Download as CSV"><i class="fa fa-file-text-o"></i> CSV</a>
                                <a href="?export=pdf<?php echo isset($_GET['status']) ? '&status='.$_GET['status'] : ''; ?>" class="btn btn-danger" title="Download as PDF"><i class="fa fa-file-pdf-o"></i> PDF</a>
                            </div>
                            <form method="get" action="daftarACC.php" class="form-inline">
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] == '0' ? 'selected' : ''); ?>>Pending</option>
                                    <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] == '1' ? 'selected' : ''); ?>>Accepted</option>
                                    <option value="2" <?php echo (isset($_GET['status']) && $_GET['status'] == '2' ? 'selected' : ''); ?>>Declined</option>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                                <?php if(isset($_GET['status'])): ?>
                                    <a href="daftarACC.php" class="btn btn-default">
                                        <i class="fa fa-times"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="total-records">
                            <?php
                            $count_query = "SELECT COUNT(*) as total FROM list_request";
                            if (isset($_GET['status']) && $_GET['status'] != '') {
                                $status = mysqli_real_escape_string($mysqli, $_GET['status']);
                                $count_query .= " WHERE status = '$status'";
                            }
                            $count_result = mysqli_query($mysqli, $count_query);
                            $count_row = mysqli_fetch_assoc($count_result);
                            echo "<span class='badge bg-blue'>{$count_row['total']} records found</span>";
                            ?>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Order Date</th>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                    <th>Branch</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Initialize the query for the main table
                                $query = "SELECT p.* FROM list_request p";
                                
                                if (isset($_GET['status']) && $_GET['status'] != '') {
                                    $status = mysqli_real_escape_string($mysqli, $_GET['status']);
                                    $query .= " WHERE p.status = '$status'";
                                }
                                $query .= " ORDER BY p.tanggal DESC";
                                $per_page = 5;
                                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $start = ($page - 1) * $per_page;
                                $query .= " LIMIT $start, $per_page";
                                $result = mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
                                $no = $start + 1;
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>
                                            <td>{$no}</td>
                                            <td>";
                                        if ($date_column_exists && !empty($row['tanggal'])) {
                                            echo date('d M Y H:i', strtotime($row['tanggal']));
                                        } elseif (!empty($row['tanggal'])) {
                                            echo date('d M Y H:i', strtotime($row['tanggal']));
                                        } else {
                                            echo 'N/A';
                                        }
                                        echo "</td>
                                            <td>{$row['namabarang']}</td>
                                            <td>{$row['kategori']}</td>
                                            <td>{$row['jumlah']}</td>
                                            <td>Cikarang</td>
                                            <td>";
                                        if ($row['status'] == '1') {
                                            echo "<span class='status-badge status-accepted'>Accepted</span>";
                                        } elseif ($row['status'] == '2') {
                                            echo "<span class='status-badge status-declined'>Declined</span>";
                                        } elseif ($row['status'] == '0') {
                                            echo "<span class='status-badge status-pending'>Pending</span>";
                                        }
                                        echo "</td>
                                            <td>";
                                        if ($row['status'] == '0') {
                                            echo "<a href='proses_pesanan.php?action=accept&id=" . $row['id_list_request'] . "' class='btn btn-success btn-xs' title='Accept'><i class='fa fa-check'></i></a>
                                                <a href='proses_pesanan.php?action=decline&id=" . $row['id_list_request'] . "' class='btn btn-danger btn-xs' title='Decline'><i class='fa fa-times'></i></a>";
                                        } else {
                                            echo "<span class='text-muted small'>Process completed</span>";
                                        }
                                        echo "</td>
                                        </tr>";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr>
                                        <td colspan='8'>
                                            <div class='empty-state'>
                                                <i class='fa fa-inbox'></i>
                                                <h4>No orders Found</h4>
                                                <p>There are no orders matching your criteria</p>
                                            </div>
                                        </td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        <?php
                        $total_query = "SELECT COUNT(*) as total FROM list_request";
                        if (isset($_GET['status']) && $_GET['status'] != '') {
                            $status = mysqli_real_escape_string($mysqli, $_GET['status']);
                            $total_query .= " WHERE status = '$status'";
                        }
                        $total_result = mysqli_query($mysqli, $total_query);
                        $total_row = mysqli_fetch_assoc($total_result);
                        $total_pages = ceil($total_row['total'] / $per_page);
                        if ($total_pages > 1) {
                            echo '<ul class="pagination">';
                            if ($page > 1) {
                                $prev = $page - 1;
                                echo '<li><a href="daftarACC.php?page='.$prev.(isset($_GET['status']) ? '&status='.$_GET['status'] : '').'">«</a></li>';
                            }
                            for ($i = 1; $i <= $total_pages; $i++) {
                                $active = ($i == $page) ? 'class="active"' : '';
                                echo '<li '.$active.'><a href="daftarACC.php?page='.$i.(isset($_GET['status']) ? '&status='.$_GET['status'] : '').'">'.$i.'</a></li>';
                            }
                            if ($page < $total_pages) {
                                $next = $page + 1;
                                echo '<li><a href="daftarACC.php?page='.$next.(isset($_GET['status']) ? '&status='.$_GET['status'] : '').'">»</a></li>';
                            }
                            echo '</ul>';
                        }
                        ?>
                    </div>
                </div>
            </section>
        </aside>
    </div>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js" type="text/javascript"></script>
    <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
</body>
</html>