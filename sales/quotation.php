<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quotation_date = date('Y-m-d');
    $due_date = mysqli_real_escape_string($mysqli, $_POST['due_date']);
    $opp_id = mysqli_real_escape_string($mysqli, $_POST['opp_id']);
    $description = mysqli_real_escape_string($mysqli, $_POST['description']);

    // ✅ Generate the next quotation number properly
    $result = mysqli_query($mysqli, "
        SELECT MAX(CAST(SUBSTRING_INDEX(quotation_no, '-', -1) AS UNSIGNED)) AS max_no 
        FROM quotation
    ");
    $row = mysqli_fetch_assoc($result);
    $next_number = $row['max_no'] ? (int)$row['max_no'] + 1 : 1;
    $quotation_no = 'Q-' . str_pad($next_number, 4, '0', STR_PAD_LEFT);

    // Insert quotation
    $insert = mysqli_query($mysqli, "
        INSERT INTO quotation (quotation_no, quotation_date, due_date, status, opp_id, description) 
        VALUES ('$quotation_no', '$quotation_date', '$due_date', 'Draft', '$opp_id', '$description')
    ");

    if ($insert) {
        header("Location: quotation.php?msg=Quotation+added+successfully");
        exit();
    } else {
        $error = "Failed to insert quotation: " . mysqli_error($mysqli);
    }
}

// Fetch logged-in user info
$iduser = $_SESSION['idpegawai'];
$usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$iduser'");
$hasiluser = mysqli_fetch_array($usersql);

// Pagination and Filtering Setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['q']) ? trim(mysqli_real_escape_string($mysqli, $_GET['q'])) : "";
$filter_opp_id = isset($_GET['opp_id']) ? mysqli_real_escape_string($mysqli, $_GET['opp_id']) : "";

// Build WHERE clause
$where = [];
if ($search !== "") {
    $where[] = "(q.quotation_no LIKE '%$search%' OR o.opp_name LIKE '%$search%' OR q.opp_id LIKE '%$search%')";
}
if ($filter_opp_id !== "") {
    $where[] = "q.opp_id = '$filter_opp_id'";
}
$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Count total results
$total_sql = "
    SELECT COUNT(DISTINCT q.quotation_id) as total
    FROM quotation q
    LEFT JOIN opportunity o ON q.opp_id = o.opp_id
    $where_sql
";
$total_result = mysqli_query($mysqli, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];
$total_pages = ceil($total_data / $limit);

// Fetch quotations with pagination
$sql = "
    SELECT 
        q.quotation_id,
        q.quotation_no,
        q.quotation_date,
        q.due_date,
        q.status,
        q.po_status,
        q.description,
        q.opp_id,
        o.opp_name
    FROM quotation q
    LEFT JOIN opportunity o ON q.opp_id = o.opp_id
    $where_sql
    GROUP BY q.quotation_id
    ORDER BY q.quotation_id DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($mysqli, $sql);

// Pass messages to front-end
$msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : "";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Quotation</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" />
    <link href="../css/ionicons.min.css" rel="stylesheet" />
    <link href="../css/AdminLTE.css" rel="stylesheet" />
    <link href="../css/modern-3d.css" rel="stylesheet" />
</head>
<body class="skin-blue">
<header class="header">
    <a href="index.php" class="logo">U-PSN</a>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
        </a>
        <div class="navbar-right">
            <ul class="nav navbar-nav">
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span><?php echo htmlspecialchars($hasiluser['Nama']); ?> <i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="<?php echo htmlspecialchars($hasiluser['foto']); ?>" class="img-circle" alt="User Image" />
                            <p><?php echo htmlspecialchars($hasiluser['Nama']) . " - " . htmlspecialchars($hasiluser['Jabatan']); ?>
                            <small>Member since <?php echo htmlspecialchars($hasiluser['Tanggal_Masuk']); ?></small></p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left"><a href="profil.php" class="btn btn-default btn-flat">Profile</a></div>
                            <div class="pull-right"><a href="prosesLogout.php" class="btn btn-default btn-flat">Sign out</a></div>
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
                    <img src="<?php echo htmlspecialchars($hasiluser['foto']); ?>" class="img-circle" alt="User Image" />
                </div>
                <div class="pull-left info">
                    <p>Hello, <?php echo htmlspecialchars($hasiluser['Nama']); ?></p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>

            <form action="quotation.php" method="get" class="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Search quotations, opportunity..."/>
                    <?php if($filter_opp_id !== ""): ?>
                        <input type="hidden" name="opp_id" value="<?php echo htmlspecialchars($filter_opp_id); ?>">
                    <?php endif; ?>
                    <span class="input-group-btn">
                        <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </form>

            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                <li><a href="account.php"><i class="fa fa-users"></i> Account</a></li>
                <li><a href="contact.php"><i class="fa fa-envelope"></i> Contact</a></li>
                <li><a href="products.php"><i class="fa fa-archive"></i> Product</a></li>
                <li><a href="product_request.php"><i class="fa fa-plus-square"></i> Product Request</a></li>
                <li><a href="leads.php"><i class="fa fa-shopping-cart"></i> Leads</a></li>
                <li><a href="opportunity.php"><i class="fa fa-lightbulb-o"></i> Opportunity</a></li>
                <li class="active"><a href="quotation.php"><i class="fa fa-truck"></i> Quotation</a></li>
                <li><a href="purchase_order.php"><i class="fa fa-clipboard"></i> Purchase Order</a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Quotation <small>Manage Quotations</small></h1>
        </section>

        <section class="content">
            <?php if ($msg): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="box-header with-border">
                <h3 class="box-title">List of Quotations</h3>
                <div class="box-tools pull-right">
                    
                    </a>
                </div>
            </div>

            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Quotation No</th>
                            <th>Opportunity Name</th>
                            <th>Tanggal Buat</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>PO Status</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $no = $offset + 1;
                    while ($row = mysqli_fetch_assoc($result)) : 
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($row['quotation_no']); ?></td>
                            <td><?php echo htmlspecialchars($row['opp_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['quotation_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                            <td>
                                <?php
                                    $status = htmlspecialchars($row['status']);
                                    $badge_class = ($status == 'Sent') ? 'success' : 'warning';
                                ?>
                                <span class="label label-<?php echo $badge_class; ?>"><?php echo $status; ?></span>

                                <?php if ($status != 'Sent'): ?>
                                    <a href="update_status.php?id=<?php echo $row['quotation_id']; ?>&status=Sent"
                                       class="btn btn-xs btn-info"
                                       style="margin-left: 5px;"
                                       onclick="return confirm('Mark this as Sent?')">
                                       <i class="fa fa-paper-plane"></i> Send
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $po_status = htmlspecialchars($row['po_status']);
                                    switch ($po_status) {
                                        case 'Created': $po_badge_class = 'info'; break;
                                        case 'Pending': $po_badge_class = 'warning'; break;
                                        case 'Completed': $po_badge_class = 'success'; break;
                                        case 'Canceled': $po_badge_class = 'danger'; break;
                                        default: $po_badge_class = 'default';
                                    }
                                ?>
                                <span class="label label-<?php echo $po_badge_class; ?>"><?php echo $po_status ?: 'N/A'; ?></span>
                            </td>
                            <td>
                                <form action="edit_quotation_description.php" method="post" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $row['quotation_id']; ?>">
                                    <input type="text" name="description" value="<?php echo htmlspecialchars($row['description']); ?>" class="form-control input-sm" style="width:180px; display:inline-block;">
                                    <button type="submit" class="btn btn-xs btn-primary">
                                        <i class="fa fa-save"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="box-footer clearfix">
                <ul class="pagination pagination-sm no-margin pull-right">
                    <?php if ($page > 1): ?>
                        <li><a href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?><?php echo $filter_opp_id ? '&opp_id=' . urlencode($filter_opp_id) : ''; ?>">&laquo; Prev</a></li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li <?php if ($i == $page) echo 'class="active"'; ?>>
                            <a href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?><?php echo $filter_opp_id ? '&opp_id=' . urlencode($filter_opp_id) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li><a href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?><?php echo $filter_opp_id ? '&opp_id=' . urlencode($filter_opp_id) : ''; ?>">Next &raquo;</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/AdminLTE/app.js"></script>
</body>
</html>
