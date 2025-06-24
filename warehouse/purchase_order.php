<?php
include('../konekdb.php');
session_start();

$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

if (!$username || !$idpegawai) {
    header("location:../index.php?status=please login first");
    exit();
}

// Cek otorisasi user Warehouse
$cekuser = mysqli_query($mysqli, "SELECT COUNT(username) AS jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
$user = mysqli_fetch_assoc($cekuser);
if ($user['jmluser'] == 0) {
    header("location:../index.php");
    exit();
}

// Ambil data pegawai
$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
$pegawai = mysqli_fetch_array($getpegawai);

// Filter dari form
$delivery_status_filter = isset($_GET['delivery_status']) ? mysqli_real_escape_string($mysqli, $_GET['delivery_status']) : '';
$payment_status_filter = isset($_GET['payment_status']) ? mysqli_real_escape_string($mysqli, $_GET['payment_status']) : '';

// Pagination setup
$per_page = 15;
$page = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

// Query total data dengan filter
$count_query = "SELECT COUNT(DISTINCT po.po_id) AS total
FROM purchase_order po
LEFT JOIN quotation q ON po.quotation_id = q.quotation_id
LEFT JOIN opportunity o ON q.opp_id = o.opp_id
LEFT JOIN account a ON o.account_id = a.account_id
LEFT JOIN contact c ON c.account = a.account_name
WHERE 1=1 ";

if ($delivery_status_filter != '') {
    $count_query .= " AND po.delivery_status = '$delivery_status_filter'";
}
if ($payment_status_filter != '') {
    $count_query .= " AND po.payment_status = '$payment_status_filter'";
}

$count_result = mysqli_query($mysqli, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $per_page);

// Query data PO dengan join lengkap, filter, limit, offset
$data_query = "
SELECT 
    po.po_id, po.po_no, po.order_date, po.delivery_date, po.delivery_status, po.payment_status,
    q.quotation_no,
    o.opp_name,
    CONCAT(
        COALESCE(a.address, ''), ', ',
        COALESCE(a.city, ''), ', ',
        COALESCE(a.state, ''), ', ',
        COALESCE(a.country, '')
    ) AS ship_to,
    a.account_name AS account_name,
    c.first_name AS contact_name,
    IFNULL(SUM(poi.total_price), 0) AS grand_total
FROM purchase_order po
LEFT JOIN quotation q ON po.quotation_id = q.quotation_id
LEFT JOIN opportunity o ON q.opp_id = o.opp_id
LEFT JOIN account a ON o.account_id = a.account_id
LEFT JOIN contact c ON c.account = a.account_name
LEFT JOIN purchase_order_item poi ON po.po_id = poi.po_id
WHERE 1=1
";

if ($delivery_status_filter != '') {
    $data_query .= " AND po.delivery_status = '$delivery_status_filter'";
}
if ($payment_status_filter != '') {
    $data_query .= " AND po.payment_status = '$payment_status_filter'";
}

$data_query .= " GROUP BY po.po_id
ORDER BY po.created_at DESC
LIMIT $start, $per_page
";

$result = mysqli_query($mysqli, $data_query);

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
        .status-badge {
            padding: 4px 8px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            color: white;
            display: inline-block;
        }
        .status-pending { background-color: #ffc107; }
        .status-success { background-color: #28a745; }
        .status-paid { background-color: #007bff; }
        .status-other { background-color: #6c757d; }
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
                    <li>
                        <a href="sales_request.php">
                            <i class="fa fa-retweet"></i> <span>Sales Request</span>
                        </a>
                    </li>
                      <li class="active">
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
            <h1>Purchase Orders <small>Warehouse View</small></h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Purchase Orders</li>
            </ol>
        </section>

        <section class="content">
            <div class="filter-container" style="margin-bottom:15px;">
                <form method="get" action="purchase_orders.php" class="form-inline">
                    <select name="delivery_status" class="form-control" style="margin-right:10px;">
                        <option value="">All Delivery Status</option>
                        <option value="Pending" <?php if ($delivery_status_filter=='Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Success" <?php if ($delivery_status_filter=='Success') echo 'selected'; ?>>Success</option>
                    </select>

            

                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>

                    <?php if ($delivery_status_filter || $payment_status_filter): ?>
                        <a href="purchase_orders.php" class="btn btn-default" style="margin-left:10px;"><i class="fa fa-times"></i> Clear</a>
                    <?php endif; ?>
                </form>
                <div style="margin-top:10px;">
                    <span class="badge bg-blue"><?php echo $total_records; ?> records found</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>PO No</th>
                            <th>Opportunity</th>
                            <th>Account Name</th>
                            <th>Contact Name</th>
                            <th>Ship To</th>
                            <th>Order Date</th>
                            <th>Delivery Date</th>
                            <th>Delivery Status</th>
                        
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            $no = $start + 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>$no</td>";
                                echo "<td><a href='purchase_order_details.php?po_no=" . urlencode($row['po_no']) . "'>" . htmlspecialchars($row['po_no']) . "</a></td>";
                                echo "<td>" . htmlspecialchars($row['opp_name'] ?? '-') . "</td>";
                                echo "<td>" . htmlspecialchars($row['account_name'] ?? '-') . "</td>";
                                echo "<td>" . htmlspecialchars($row['contact_name'] ?? '-') . "</td>";
                                echo "<td>" . htmlspecialchars($row['ship_to'] ?? '-') . "</td>";
                                echo "<td>" . ($row['order_date'] ? date('d M Y', strtotime($row['order_date'])) : '-') . "</td>";
                                echo "<td>" . ($row['delivery_date'] ? date('d M Y', strtotime($row['delivery_date'])) : '-') . "</td>";
                                // Delivery Status with badge
                                $ds = $row['delivery_status'];
                                if ($ds == 'Pending') {
                                    echo "<td><span class='status-badge status-pending'>Pending</span></td>";
                                } elseif ($ds == 'Success') {
                                    echo "<td><span class='status-badge status-success'>Success</span></td>";
                                } else {
                                    echo "<td><span class='status-badge status-other'>" . htmlspecialchars($ds) . "</span></td>";
                                }
                                
                            }
                        } else {
                            echo "<tr><td colspan='12' style='text-align:center;'>No purchase orders found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Page navigation" style="text-align:center;">
                <ul class="pagination">
                    <?php
                    if ($page > 1) {
                        echo '<li><a href="?page=' . ($page - 1) .
                            ($delivery_status_filter ? "&delivery_status=$delivery_status_filter" : "") .
                            ($payment_status_filter ? "&payment_status=$payment_status_filter" : "") .
                            '">&laquo;</a></li>';
                    }

                    for ($i = 1; $i <= $total_pages; $i++) {
                        $active = ($i == $page) ? "active" : "";
                        echo '<li class="' . $active . '"><a href="?page=' . $i .
                            ($delivery_status_filter ? "&delivery_status=$delivery_status_filter" : "") .
                            ($payment_status_filter ? "&payment_status=$payment_status_filter" : "") .
                            '">' . $i . '</a></li>';
                    }

                    if ($page < $total_pages) {
                        echo '<li><a href="?page=' . ($page + 1) .
                            ($delivery_status_filter ? "&delivery_status=$delivery_status_filter" : "") .
                            ($payment_status_filter ? "&payment_status=$payment_status_filter" : "") .
                            '">&raquo;</a></li>';
                    }
                    ?>
                </ul>
            </nav>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
</body>
</html>
