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
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Search keyword
$search = isset($_GET['q']) ? trim(mysqli_real_escape_string($mysqli, $_GET['q'])) : "";

// Base query with search filter if any
$where = "";
if ($search !== "") {
  $where = "WHERE (
    po.po_no LIKE '%$search%' OR
    q.quotation_no LIKE '%$search%' OR
    o.opp_name LIKE '%$search%' OR
    po.delivery_status LIKE '%$search%' OR
    po.payment_status LIKE '%$search%'
  )";
}

// Query total data for pagination
$total_sql = "
  SELECT COUNT(*) as total
  FROM purchase_order po
  LEFT JOIN quotation q ON po.quotation_id = q.quotation_id
  LEFT JOIN opportunity o ON q.opp_id = o.opp_id
  LEFT JOIN account a ON o.account_id = a.account_id
  $where
";
$total_result = mysqli_query($mysqli, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];
$total_pages = ceil($total_data / $limit);

// Query data purchase order with limit and offset + search filter
$po_sql = "
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
  a.account_name AS name,
  c.first_name AS contact_name,
  IFNULL(SUM(poi.total_price), 0) AS grand_total
FROM purchase_order po
LEFT JOIN quotation q ON po.quotation_id = q.quotation_id
LEFT JOIN opportunity o ON q.opp_id = o.opp_id
LEFT JOIN account a ON o.account_id = a.account_id
LEFT JOIN contact c ON c.account = a.account_name
LEFT JOIN purchase_order_item poi ON po.po_id = poi.po_id
$where
GROUP BY po.po_id
ORDER BY po.po_id DESC
LIMIT $limit OFFSET $offset
";


$result = mysqli_query($mysqli, $po_sql);

// Ambil pesan notifikasi jika ada
$msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : "";
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>U-PSN | Purchase Order</title>
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

      <form action="purchase_order.php" method="get" class="sidebar-form">
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
        <li><a href="leads.php"><i class="fa fa-lightbulb-o"></i> <span>Leads</span></a></li>
        <li><a href="opportunity.php"><i class="fa fa-rocket"></i> <span>Opportunity</span></a></li>
        <li><a href="quotation.php"><i class="fa fa-truck"></i> <span>Quotation</span></a></li>
        <li class="active"><a href="purchase_order.php"><i class="fa fa-clipboard"></i> <span>Purchase Order</span></a></li>
      </ul>
    </section>
  </aside>

  <aside class="right-side">
    <section class="content-header">
      <h1>Purchase Order <small>Manage Purchase Orders</small></h1>
      <ol class="breadcrumb">
        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Purchase Order</li>
      </ol>
    </section>

    <section class="content">

      <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <?php echo $msg; ?>
        </div>
      <?php endif; ?>

      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">List of Purchase Orders</h3>
          <a href="input_purchase_order.php" class="btn btn-primary btn-sm pull-right" style="font-weight:bold; font-size:20px; line-height:20px; padding:2px 10px; border-radius:50%;">+</a>
        </div>
        <div class="box-body table-responsive no-padding">
          <form method="get" action="purchase_order.php" class="form-inline" style="margin: 10px 0;">
            <div class="form-group">
              <input type="text" name="q" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search purchase order..." />
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
          </form>

          <table class="table table-hover">
            <thead>
              <tr>
                <th>No</th>
                <th>PO No</th>
                <th>Opportunity</th>
                <th>Account Name</th>
                <th>Contact Name</th>
                <th>Ship To</th>
                <th>PO Date</th>
                <th>Delivery Status</th>
                <th>Payment Status</th>
                <th>Total (Rp)</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php
            if (mysqli_num_rows($result) > 0):
              $no = $offset + 1;
              while ($row = mysqli_fetch_assoc($result)):
            ?>
              <tr>
                <td><?php echo $no++; ?></td>
                <td>
                  <a href="purchase_order_detail.php?id=<?php echo $row['po_id']; ?>">
                    <?php echo htmlspecialchars($row['po_no']); ?>
                  </a>
                </td>
                <td><?php echo htmlspecialchars($row['opp_name'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['name'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['contact_name'] ?? '-'); ?></td>
               <td><?php echo htmlspecialchars($row['ship_to'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                <td><?php echo htmlspecialchars($row['delivery_status']); ?></td>
                <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
                <td><?php echo number_format($row['grand_total'], 2); ?></td>
                <td>
                  <a href="edit_purchase_order.php?id=<?php echo $row['po_id']; ?>" class="btn btn-xs btn-warning">Edit</a>
                  <a href="delete_purchase_order.php?id=<?php echo $row['po_id']; ?>" onclick="return confirm('Are you sure want to delete this purchase order?');" class="btn btn-xs btn-danger">Delete</a>
                </td>
              </tr>
            <?php
              endwhile;
            else:
            ?>
              <tr><td colspan="8" style="text-align:center;">No purchase orders found.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>

          <nav aria-label="Page navigation">
            <ul class="pagination">
              <?php if ($page > 1): ?>
                <li><a href="?page=<?php echo $page-1; ?>&q=<?php echo urlencode($search); ?>" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
              <?php endif; ?>
              <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li<?php if ($i === $page) echo ' class="active"'; ?>>
                  <a href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                </li>
              <?php endfor; ?>
              <?php if ($page < $total_pages): ?>
                <li><a href="?page=<?php echo $page+1; ?>&q=<?php echo urlencode($search); ?>" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>
      </div>

    </section>
  </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
</body>
</html>
