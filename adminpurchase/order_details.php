<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include('../konekdb.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['username'] == '') {
    header("location:../index.php");
    exit();
}

// Get order_id ID from URL with validation
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid or missing order_id ID";
    header("location:pemesanan.php");
    exit();
}

$order_id = (int)$_GET['id'];

// Fetch order_id details with proper supplier join using prepared statements
// First, let's use a simpler query to avoid column name issues
$order_id_query = "SELECT p.*, 
                s.nama_perusahaan, s.alamat, 
                s.telepon as supplier_phone,
                s.email as supplier_email
                FROM pemesanan1 p
                LEFT JOIN supplier s ON p.supplier_id = s.id_supplier
                WHERE p.order_id = ?";

$stmt = mysqli_prepare($mysqli, $order_id_query);
if (!$stmt) {
    $_SESSION['error'] = "Database prepare error: " . mysqli_error($mysqli);
    header("location:pemesanan.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$order_id_result = mysqli_stmt_get_result($stmt);

if (!$order_id_result) {
    $_SESSION['error'] = "Database error: " . mysqli_error($mysqli);
    header("location:pemesanan.php");
    exit();
}

if (mysqli_num_rows($order_id_result) == 0) {
    $_SESSION['error'] = "order_id not found";
    header("location:pemesanan.php");
    exit();
}

$order_id = mysqli_fetch_assoc($order_id_result);
mysqli_stmt_close($stmt);

// Get user data from session
$username = $_SESSION['username'];
$idpegawai = isset($_SESSION['idpegawai']) ? $_SESSION['idpegawai'] : '';

// Get employee data for profile display with prepared statement
$pegawai = array();
if ($idpegawai) {
    $pegawai_query = "SELECT * FROM pegawai WHERE id_pegawai = ?";
    $stmt = mysqli_prepare($mysqli, $pegawai_query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $idpegawai);
        mysqli_stmt_execute($stmt);
        $pegawai_result = mysqli_stmt_get_result($stmt);
        if ($pegawai_result && mysqli_num_rows($pegawai_result) > 0) {
            $pegawai = mysqli_fetch_assoc($pegawai_result);
        }
        mysqli_stmt_close($stmt);
    }
}

// Set default values for pegawai if not found
$default_pegawai = array(
    'foto' => 'default.png',
    'Nama' => 'Unknown User',
    'Jabatan' => 'Employee',
    'Departemen' => '',
    'Tanggal_Masuk' => date('Y-m-d'),
    'email' => 'email@example.com',
    'telepon' => '000-000-0000'
);

foreach ($default_pegawai as $key => $value) {
    if (!isset($pegawai[$key]) || empty($pegawai[$key])) {
        $pegawai[$key] = $value;
    }
}

// Format dates safely
$order_date = isset($order_id['order_date']) && $order_id['order_date'] 
    ? date('m/d/Y', strtotime($order_id['order_date'])) 
    : date('m/d/Y');

$delivery_date = isset($order_id['delivery_date']) && $order_id['delivery_date'] 
    ? date('m/d/Y', strtotime($order_id['delivery_date'])) 
    : 'N/A';

// Determine status
$status_class = '';
$status_text = '';
$order_id_status = isset($order_id['status']) ? (int)$order_id['status'] : 0;

switch($order_id_status) {
    case 1: 
        $status_class = 'status-pending';
        $status_text = 'Pending Approval'; 
        break;
    case 2: 
        $status_class = 'status-approved';
        $status_text = 'Approved'; 
        break;
    case 3: 
        $status_class = 'status-rejected';
        $status_text = 'Rejected'; 
        break;
    default: 
        $status_class = 'status-unknown';
        $status_text = 'Unknown'; 
        break;
}

// Calculate financial details safely
$total_price = isset($order_id['total_price']) ? (float)$order_id['total_price'] : 0;
$tax_rate = 6.875;
$tax_amount = ($total_price * $tax_rate) / 100;
$shipping = 50.00; // Example shipping cost
$other_fees = 50.00; // Example other fees
$grand_total = $total_price + $tax_amount + $shipping + $other_fees;

// Get order_id item details safely
$item_name = isset($order_id['item_name']) ? $order_id['item_name'] : 'N/A';
$category = isset($order_id['category']) ? $order_id['category'] : 'N/A';
$quantity = isset($order_id['quantity']) ? (int)$order_id['quantity'] : 0;
$price = isset($order_id['price']) ? (float)$order_id['price'] : 0;

// Get supplier details safely
$supplier_name = isset($order_id['nama_perusahaan']) ? $order_id['nama_perusahaan'] : 'N/A';
$supplier_address = isset($order_id['alamat']) ? $order_id['alamat'] : 'N/A';
$supplier_phone = isset($order_id['supplier_phone']) ? $order_id['supplier_phone'] : 'N/A';
$supplier_email = isset($order_id['supplier_email']) ? $order_id['supplier_email'] : 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Purchase order_id #PRQ-<?php echo $order_id; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/ionicons.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }
        
        .purchase-order_id-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .company-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border_id-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .po-info {
            text-align: right;
        }
        
        .po-info div {
            margin-bottom: 5px;
        }
        
        .po-number {
            font-size: 18px;
            font-weight: bold;
        }
        
        .section-title {
            font-weight: bold;
            margin: 15px 0 5px 0;
            color: #333;
        }
        
        .address-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .address-box {
            width: 48%;
            border_id: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        
        .address-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        table {
            width: 100%;
            border_id-collapse: collapse;
            margin: 20px 0;
        }
        
        th {
            background-color: #3c8dbc;
            color: white;
            padding: 8px;
            text-align: left;
        }
        
        td {
            padding: 8px;
            border_id-bottom: 1px solid #ddd;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals-table {
            width: 50%;
            margin-left: auto;
        }
        
        .totals-table td {
            border_id-bottom: none;
        }
        
        .grand-total {
            font-weight: bold;
            font-size: 16px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border_id-top: 1px solid #eee;
        }
        
        .status-badge {
            padding: 5px 10px;
            border_id-radius: 3px;
            font-size: 0.9em;
            font-weight: 600;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-unknown { background-color: #e2e3e5; color: #6c757d; }
        
        .action-buttons {
            margin-top: 20px;
            text-align: center;
        }
        
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-line {
            border_id-top: 1px solid #333;
            width: 200px;
            text-align: center;
            padding-top: 5px;
        }
        
        .company-address {
            margin-bottom: 20px;
            color: #666;
        }
        
        .company-address div {
            margin-bottom: 2px;
        }
        
        @media print {
            body {
                background-color: white;
            }
            
            .purchase-order_id-container {
                box-shadow: none;
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .address-section {
                flex-direction: column;
            }
            
            .address-box {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .company-header {
                flex-direction: column;
            }
            
            .po-info {
                text-align: left;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body class="skin-blue">
    <header class="header no-print">
        <a href="../index.html" class="logo">U-PSN</a>
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
                            <span><?= htmlspecialchars($username); ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header bg-light-blue">
                                <img src="../img/<?= htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" onerror="this.src='../img/default.png'">
                                <p>
                                    <?= htmlspecialchars($pegawai['Nama'] . " - " . $pegawai['Jabatan'] . " " . $pegawai['Departemen']); ?>
                                    <small>Member since <?= htmlspecialchars($pegawai['Tanggal_Masuk']); ?></small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="../logout.php" class="btn btn-default btn-flat">Sign out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    
    <div class="wrapper row-offcanvas row-offcanvas-left no-print">
        <aside class="left-side sidebar-offcanvas">              
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="../img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" onerror="this.src='../img/default.png'" />
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
                        <a href="supplier.php">
                            <i class="fa fa-truck"></i> <span>Supplier</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="pemesanan.php">
                            <i class="fa fa-shopping-cart"></i> <span>order_ids</span>
                        </a>
                    </li>
                    <li>
                        <a href="transaksi.php">
                            <i class="fa fa-check-square"></i> <span>Transaction Approval</span>
                        </a>
                    </li>
                    <li>
                        <a href="received.php">
                            <i class="fa fa-shopping-cart"></i> <span>Received Item</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan.php">
                           <i class="fa fa-file-text"></i> <span>Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="cuti.php">
                            <i class="fa fa-calendar-times-o"></i> <span>Leave Requests</span>
                        </a>
                    </li>
                    <li>
                        <a href="mailbox.php">
                            <i class="fa fa-envelope"></i> <span>Mailbox</span>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>

        <aside class="right-side">              
            <section class="content-header">
                <h1>Purchase order_id #PRQ-<?php echo $order_id; ?></h1>
                <ol class="breadcrumb">
                    <li><a href="../index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="pemesanan.php">Purchase order_ids</a></li>
                    <li class="active">order_id Details</li>
                </ol>
            </section>

            <section class="content">
                <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fa fa-check"></i> <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <i class="icon fa fa-ban"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>
                
                <div class="action-buttons no-print">
                    <a href="pemesanan.php" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to order_ids
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fa fa-print"></i> Print
                    </button>
                    <?php if($order_id_status == 1): ?>
                    <form method="POST" action="pemesanan.php" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <button type="submit" name="approve_order_id" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this order_id?')">
                            <i class="fa fa-check"></i> Approve
                        </button>
                    </form>
                    <button class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                        <i class="fa fa-times"></i> Reject
                    </button>
                    <?php endif; ?>
                </div>
                
                <!-- Reject Modal (only for pending order_ids) -->
                <?php if($order_id_status == 1): ?>
                <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="POST" action="pemesanan.php">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <h4 class="modal-title" id="rejectModalLabel">Reject order_id #PRQ-<?php echo $order_id; ?></h4>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                    <div class="form-group">
                                        <label for="rejection_reason">Reason for Rejection:</label>
                                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" required placeholder="Please specify the reason for rejecting this order_id" maxlength="500"></textarea>
                                        <small class="form-text text-muted">Maximum 500 characters</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="submit" name="reject_order_id" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this order_id?')">Confirm Rejection</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </section>
        </aside>
    </div>

    <!-- Purchase order_id Document -->
    <div class="purchase-order_id-container">
        <div class="company-header">
            <div class="company-name">U-PSN COMPANY</div>
            <div class="po-info">
                <div class="po-number">PURCHASE order_id</div>
                <div>DATE: <?php echo $order_date; ?></div>
                <div>P.O. #: PRQ-<?php echo $order_id; ?></div>
            </div>
        </div>
        
        <div class="company-address">
            <div>123 Business Street</div>
            <div>Jakarta, ID 12345</div>
            <div>Phone: (021) 123-4567</div>
            <div>Fax: (021) 123-4568</div>
        </div>
        
        <div class="address-section">
            <div class="address-box">
                <div class="address-label">VENDOR</div>
                <div><?php echo htmlspecialchars($supplier_name); ?></div>
                <div><?php echo htmlspecialchars($supplier_address); ?></div>
                <div>Phone: <?php echo htmlspecialchars($supplier_phone); ?></div>
                <div>Email: <?php echo htmlspecialchars($supplier_email); ?></div>
            </div>
            
            <div class="address-box">
                <div class="address-label">SHIP TO</div>
                <div>U-PSN Warehouse</div>
                <div>456 Industrial Park</div>
                <div>Jakarta, ID 12345</div>
                <div>Phone: (021) 123-4567</div>
            </div>
        </div>
        
        <div class="section-title">PRODUCTS / SERVICES</div>
        
        <table>
            <thead>
                <tr>
                    <th>ITEM #</th>
                    <th>DESCRIPTION</th>
                    <th>QTY</th>
                    <th>UNIT PRICE</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($category); ?></td>
                    <td><?php echo htmlspecialchars($item_name); ?></td>
                    <td class="text-center"><?php echo $quantity; ?></td>
                    <td class="text-right">Rp<?php echo number_format($price, 2, '.', ','); ?></td>
                    <td class="text-right">Rp<?php echo number_format($total_price, 2, '.', ','); ?></td>
                </tr>
                <!-- Empty rows for additional items -->
                <?php for ($i = 0; $i < 5; $i++): ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
        
        <div class="section-title">OTHER COMMENTS OR SPECIAL INSTRUCTIONS</div>
        <div style="min-height: 50px; border_id: 1px solid #ddd; padding: 10px; margin-bottom: 20px;">
            <?php if($order_id_status == 3 && isset($order_id['rejection_reason']) && !empty($order_id['rejection_reason'])): ?>
                <strong>Rejection Reason:</strong> <?php echo htmlspecialchars($order_id['rejection_reason']); ?>
            <?php else: ?>
                <em>No special instructions</em>
            <?php endif; ?>
        </div>
        
        <table class="totals-table">
            <tr>
                <td>SUBTOTAL</td>
                <td class="text-right">Rp<?php echo number_format($total_price, 2, '.', ','); ?></td>
            </tr>
            <tr>
                <td>TAX RATE (<?php echo $tax_rate; ?>%)</td>
                <td class="text-right">Rp<?php echo number_format($tax_amount, 2, '.', ','); ?></td>
            </tr>
            <tr>
                <td>SHIPPING</td>
                <td class="text-right">Rp<?php echo number_format($shipping, 2, '.', ','); ?></td>
            </tr>
            <tr>
                <td>OTHER FEES</td>
                <td class="text-right">Rp<?php echo number_format($other_fees, 2, '.', ','); ?></td>
            </tr>
            <tr class="grand-total">
                <td>TOTAL</td>
                <td class="text-right">Rp<?php echo number_format($grand_total, 2, '.', ','); ?></td>
            </tr>
        </table>
        
        <div class="signature-section">
            <div>
                <div class="signature-line">Authorized by</div>
                <div style="text-align: center; margin-top: 5px;"><?php echo htmlspecialchars($pegawai['Nama']); ?></div>
            </div>
            <div>
                <div class="signature-line">Date</div>
                <div style="text-align: center; margin-top: 5px;"><?php echo date('m/d/Y'); ?></div>
            </div>
        </div>
        
        <div class="footer">
            <div><strong>Status:</strong> <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></div>
            <?php if($order_id_status == 2): ?>
                <div><strong>Approved by:</strong> <?php echo isset($order_id['approved_by']) ? htmlspecialchars($order_id['approved_by']) : 'N/A'; ?></div>
                <div><strong>Approval date:</strong> <?php echo isset($order_id['tanggal_approval']) && $order_id['tanggal_approval'] ? date('m/d/Y H:i', strtotime($order_id['tanggal_approval'])) : 'N/A'; ?></div>
            <?php elseif($order_id_status == 3): ?>
                <div><strong>Rejected by:</strong> <?php echo isset($order_id['approved_by']) ? htmlspecialchars($order_id['approved_by']) : 'N/A'; ?></div>
                <div><strong>Rejection date:</strong> <?php echo isset($order_id['tanggal_approval']) && $order_id['tanggal_approval'] ? date('m/d/Y H:i', strtotime($order_id['tanggal_approval'])) : 'N/A'; ?></div>
            <?php endif; ?>
            <div><strong>Requested delivery date:</strong> <?php echo $delivery_date; ?></div>
            <div style="margin-top: 20px;">
                <strong>Notes:</strong> If you have any questions about this purchase order_id, please contact 
                <?php 
                $contact_info = htmlspecialchars($pegawai['Nama']);
                if (!empty($pegawai['Jabatan'])) {
                    $contact_info .= ", " . htmlspecialchars($pegawai['Jabatan']);
                }
                $contact_info .= " at " . htmlspecialchars($pegawai['email']) . " or phone " . htmlspecialchars($pegawai['telepon']);
                echo $contact_info;
                ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Print functionality
        $('#printBtn').click(function() {
            window.print();
        });
        
        // Confirmation for form submissions
        $('form[name="approve_order_id"], form[name="reject_order_id"]').submit(function(e) {
            var action = $(this).find('button[type="submit"]').text().trim();
            if (!confirm('Are you sure you want to ' + action.toLowerCase() + ' this order_id?')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Character counter for rejection reason
        $('#rejection_reason').on('input', function() {
            var remaining = 500 - $(this).val().length;
            var helpText = $(this).siblings('.form-text');
            helpText.text('Characters remaining: ' + remaining);
            
            if (remaining < 50) {
                helpText.addClass('text-warning');
            } else {
                helpText.removeClass('text-warning');
            }
            
            if (remaining < 0) {
                helpText.addClass('text-danger').removeClass('text-warning');
            } else {
                helpText.removeClass('text-danger');
            }
        });
    });
    </script>
</body>
</html>