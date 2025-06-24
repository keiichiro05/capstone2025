<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

$iduser = $_SESSION['idpegawai'];
$usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$iduser'");
$hasiluser = mysqli_fetch_array($usersql);

// Pagination setup
$limit = 10; // data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Search keyword
$search = isset($_GET['q']) ? trim(mysqli_real_escape_string($mysqli, $_GET['q'])) : "";

// Base query with search filter if any
$where = "";
if ($search !== "") {
    $where = "WHERE (
        o.opp_id LIKE '%$search%' OR
        o.opp_name LIKE '%$search%' OR
        a.account_name LIKE '%$search%' OR
        o.sales_phase LIKE '%$search%' OR
        o.status LIKE '%$search%'
    )";
}

// Query total data for pagination
$total_sql = "
    SELECT COUNT(*) as total FROM opportunity o
    LEFT JOIN account a ON o.account_id = a.account_id
    $where
";
$total_result = mysqli_query($mysqli, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];
$total_pages = ceil($total_data / $limit);

// Query data opportunity with limit and offset + search filter
$oppsql = mysqli_query($mysqli, "
    SELECT 
        o.opp_id, o.opp_name, o.expected_value, o.close_date, o.sales_phase, o.status,
        o.contact_id, o.start_date, o.business_line, o.source,
        a.account_name
    FROM opportunity o
    LEFT JOIN account a ON o.account_id = a.account_id
    $where
    ORDER BY o.close_date DESC
    LIMIT $limit OFFSET $offset
");

// Ambil pesan notifikasi jika ada
$msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : "";
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Opportunity</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
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
                            <p>
                                <?php echo htmlspecialchars($hasiluser['Nama']) . " - " . htmlspecialchars($hasiluser['Jabatan']); ?>
                                <small>Member since <?php echo htmlspecialchars($hasiluser['Tanggal_Masuk']); ?></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="prosesLogout.php" class="btn btn-default btn-flat">Sign out</a>
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
                    <img src="<?php echo htmlspecialchars($hasiluser['foto']); ?>" class="img-circle" alt="User Image" />
                </div>
                <div class="pull-left info">
                    <p>Hello, <?php echo htmlspecialchars($hasiluser['Nama']); ?></p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>

            <form action="opportunity.php" method="get" class="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Search..." />
                    <span class="input-group-btn">
                        <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </form>

            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li><a href="account.php"><i class="fa fa-users"></i> <span>Account</span></a></li>
                <li><a href="contact.php"><i class="fa fa-envelope"></i> <span>Contact</span></a></li>
                <li><a href="products.php"><i class="fa fa-archive"></i> <span>Product</span></a></li>
                <li><a href="product_request.php"><i class="fa fa-plus-square"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-shopping-cart"></i> <span>Leads</span></a></li>
                <li class="active"><a href="opportunity.php"><i class="fa fa-lightbulb-o"></i> <span>Opportunity</span></a></li>
                <li><a href="quotation.php"><i class="fa fa-truck"></i> <span>Quotation</span></a></li>
                <li><a href="purchase_order.php"><i class="fa fa-clipboard"></i> Purchase Order</a></li>
                
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Opportunity <small>Manage Opportunities</small></h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Opportunity</li>
            </ol>
        </section>

        <section class="content">

            <?php if ($msg): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

           <div class="box-header with-border">
    <h3 class="box-title">List of Opportunities</h3>
    <div class="box-tools pull-right">
        <a href="add_opportunity.php" class="btn btn-success btn-sm">
            <i class="fa fa-plus"></i> Add Opportunity
        </a>
    </div>
</div>
                <div class="box-body table-responsive no-padding">
                    <form method="get" action="opportunity.php" class="form-inline" style="margin: 10px 0;">
                        <div class="form-group">
                            <input type="text" name="q" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search opportunities..." />
                        </div>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Opp ID</th>
                                <th>Opp Name</th>
                                <th>Account Name</th>
                                <th>Contact</th>
                                <th>Start Date</th>
                                <th>Business Line</th>
                                <th>Source</th>
                                <th>Expected Value</th>
                                <th>Sales Phase</th>
                                <th>Status</th>
                                <th>Close Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($oppsql) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($oppsql)) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['opp_id']); ?></td>
                                    <td>
                                        <a href="add_quotation_detail.php?opp_id=<?php echo $row['opp_id']; ?>">
                                            <?php echo htmlspecialchars($row['opp_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['account_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['contact_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['business_line']); ?></td>
                                    <td><?php echo htmlspecialchars($row['source']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($row['expected_value'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($row['sales_phase']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['close_date']); ?></td>
                                    <td>
                                        <a href="edit_opportunity.php?id=<?php echo $row['opp_id']; ?>" class="btn btn-warning btn-xs">Edit</a>
                                        <a href="delete_opportunity.php?id=<?php echo $row['opp_id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this opportunity?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" style="text-align:center;">No opportunities found.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="box-footer clearfix">
                    <ul class="pagination pagination-sm no-margin pull-right">
                        <?php if ($page > 1): ?>
                            <li><a href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>">&laquo; Prev</a></li>
                        <?php else: ?>
                            <li class="disabled"><span>&laquo; Prev</span></li>
                        <?php endif; ?>

                        <?php
                        // Show pages (for example max 5 pages visible)
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li <?php if ($i == $page) echo 'class="active"'; ?>>
                                <a href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li><a href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>">Next &raquo;</a></li>
                        <?php else: ?>
                            <li class="disabled"><span>Next &raquo;</span></li>
                        <?php endif; ?>
                    </ul>
                </div>

            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<script src="../js/AdminLTE/app.js" type="text/javascript"></script>
</body>
</html>
