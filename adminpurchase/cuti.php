<?php
// Start session at the very beginning
session_start();

// Error reporting for development (remove or configure in production)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// --- User Authentication ---
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php?status=please_login_first");
    exit();
}

// Include database connection
include('../konekdb.php');

// Check if database connection was successful
if (!isset($mysqli) || !$mysqli) {
    error_log("Database connection failed in cuti.php");
    header("Location: ../index.php?status=db_connection_error");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'] ?? null;

// Check authorization for Purchase module
$auth_check = $mysqli->prepare("SELECT COUNT(username) AS jmluser FROM authorization WHERE username = ? AND modul = 'Purchase'");
if (!$auth_check) {
    error_log("Failed to prepare authorization statement: " . $mysqli->error);
    header("Location: ../index.php?status=db_connection_error");
    exit();
}

$auth_check->bind_param("s", $username);
$auth_check->execute();
$auth_result = $auth_check->get_result();
$auth_data = $auth_result->fetch_assoc();
$auth_check->close();

if ($auth_data['jmluser'] == 0) {
    header("Location: ../index.php?status=unauthorized");
    exit();
}

// Get employee data
$pegawai = [];
if ($idpegawai) {
    $emp_query = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
    if ($emp_query) {
        $emp_query->bind_param("i", $idpegawai);
        $emp_query->execute();
        $emp_result = $emp_query->get_result();
        $pegawai = $emp_result->fetch_assoc() ?? [];
        $emp_query->close();
    } else {
        error_log("Failed to prepare employee data statement: " . $mysqli->error);
    }
}

// Set display variables
$displayUsername = htmlspecialchars($username);
$displayPegawaiFoto = htmlspecialchars($pegawai['foto'] ?? 'default.png');
$displayPegawaiNama = htmlspecialchars($pegawai['Nama'] ?? 'User');
$displayPegawaiJabatan = htmlspecialchars($pegawai['Jabatan'] ?? '');
$displayPegawaiDepartemen = htmlspecialchars($pegawai['Departemen'] ?? '');
$displayPegawaiTanggalMasuk = htmlspecialchars($pegawai['Tanggal_Masuk'] ?? '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mulai = $_POST['mulai'] ?? '';
    $selesai = $_POST['selesai'] ?? '';
    $detail = $_POST['detail'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($mulai)) {
        $errors[] = 'empty_start_date';
    }
    
    if (empty($selesai)) {
        $errors[] = 'empty_end_date';
    }
    
    if (empty($detail)) {
        $errors[] = 'empty_leave_type';
    }
    
    // Date validation
    if (!empty($mulai) && !empty($selesai)) {
        $startDate = DateTime::createFromFormat('Y-m-d', $mulai);
        $endDate = DateTime::createFromFormat('Y-m-d', $selesai);
        
        if (!$startDate || !$endDate) {
            $errors[] = 'invalid_date_format';
        } elseif ($startDate > $endDate) {
            $errors[] = 'date_error';
        }
    }
    
    if (!empty($errors)) {
        header("Location: cuti.php?status=" . $errors[0]);
        exit();
    }
    
    // Calculate total days
    $start = new DateTime($mulai);
    $end = new DateTime($selesai);
    $interval = $start->diff($end);
    $total_days = $interval->days + 1; // Including both start and end dates
    
    // Prepare data for insertion
    $nama_pegawai = $pegawai['Nama'] ?? 'N/A';
    $departemen_pegawai = $pegawai['Departemen'] ?? 'N/A';

    // Insert leave request using prepared statement
    $insert_query = $mysqli->prepare(
        "INSERT INTO cuti (id_pegawai, Nama, Departemen, Tanggal_Mulai, Tanggal_Selesai, Detail_cuti, Aksi, Total) 
        VALUES (?, ?, ?, ?, ?, ?, 0, ?)"
    );
    
    if (!$insert_query) {
        error_log("Failed to prepare insert statement: " . $mysqli->error);
        header("Location: cuti.php?status=db_error");
        exit();
    }
    
    $insert_query->bind_param(
        "isssssi", 
        $idpegawai, 
        $nama_pegawai, 
        $departemen_pegawai, 
        $mulai, 
        $selesai, 
        $detail, 
        $total_days
    );
    
    if (!$insert_query->execute()) {
        error_log("Error inserting leave request: " . $mysqli->error);
        header("Location: cuti.php?status=db_error");
        exit();
    }
    
    $insert_query->close();
    header("Location: cuti.php?status=success");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Leave Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/ionicons.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #3c8dbc;
            --secondary-color: #2c3e50;
            --accent-color: #00c0ef;
            --success-color: #00a65a;
            --warning-color: #f39c12;
            --danger-color: #dd4b39;
            --light-gray: #f5f5f5;
            --dark-gray: #333;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
        }
        
        .leave-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            border_id-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .header-section h1 {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .header-section small {
            opacity: 0.8;
            font-size: 14px;
        }
        
        .info-card {
            background: white;
            border_id-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border_id-left: 4px solid var(--accent-color);
        }
        
        .info-card .card-icon {
            font-size: 40px;
            color: var(--accent-color);
            margin-bottom: 15px;
        }
        
        .form-card {
            background: white;
            border_id-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border_id-top: 3px solid var(--primary-color);
        }
        
        .form-card h3 {
            color: var(--secondary-color);
            margin-top: 0;
            padding-bottom: 15px;
            border_id-bottom: 1px solid #eee;
            font-weight: 600;
        }
        
        .form-group label {
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 8px;
        }
        
        .form-control {
            border_id-radius: 4px;
            border_id: 1px solid #ddd;
            padding: 10px 15px;
            height: auto;
        }
        
        .btn-submit {
            background-color: var(--primary-color);
            border_id: none;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
            margin-top: 28px;
        }
        
        .btn-submit:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .quote-box {
            background: linear-gradient(to right, #f8f9fa, #e9f5ff);
            border_id-left: 4px solid var(--accent-color);
            padding: 20px;
            border_id-radius: 4px;
            margin: 25px 0;
            font-style: italic;
            color: var(--secondary-color);
        }
        
        .history-card {
            background: white;
            border_id-radius: 8px;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .history-card .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            border_id-bottom: none;
        }
        
        .history-card .card-header h3 {
            margin: 0;
            font-weight: 600;
        }
        
        .table-cuti-status {
            width: 100%;
        }
        
        .table-cuti-status th {
            background-color: #f8f9fa;
            color: var(--secondary-color);
            font-weight: 600;
            padding: 12px 15px;
        }
        
        .table-cuti-status td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 5px 10px;
            border_id-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background-color: var(--warning-color);
            color: white;
        }
        
        .status-approved {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-rejected {
            background-color: var(--danger-color);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
            .form-card .col-md-1 {
                margin-top: 0;
            }
            
            .btn-submit {
                margin-top: 15px;
                width: 100%;
            }
        }
    </style>
</head>
<body class="skin-blue">
    <header class="header">
        <a href="index.php" class="logo">U-PSN</a>
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
                            <span><?php echo $displayUsername; ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header bg-light-blue">
                                <img src="../img/<?php echo $displayPegawaiFoto; ?>" class="img-circle" alt="User Image" />
                                <p>
                                    <?php echo $displayPegawaiNama . " - " . $displayPegawaiJabatan . " " . $displayPegawaiDepartemen; ?>
                                    <small>Member since <?php echo $displayPegawaiTanggalMasuk; ?></small>
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
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <aside class="left-side sidebar-offcanvas">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="../img/<?php echo $displayPegawaiFoto; ?>" class="img-circle" alt="User Image">
                    </div>
                    <div class="pull-left info">
                        <p><?php echo $displayUsername; ?></p>
                        <p style="font-size: 12px;">Hello, <?php echo $displayPegawaiNama; ?></p>
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
                    <li>
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
                    <li class="active">
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
            <section class="content">
                <div class="leave-container">
                    <div class="header-section">
                        <h1><i class="fa fa-calendar"></i> Leave Management</h1>
                        <small>Submit and track your leave requests</small>
                    </div>
                    
                    <div class="info-card">
                        <div class="row">
                            <div class="col-md-1">
                                <div class="card-icon">
                                    <i class="fa fa-info-circle"></i>
                                </div>
                            </div>
                            <div class="col-md-11">
                                <p>Easily submit leave requests and track your application status here. Your requests will be reviewed by HR and you'll receive notifications about the status.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-card">
                        <h3><i class="fa fa-paper-plane"></i> New Leave Request</h3>
                        <form method="post" id="formCuti">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Start Date</label>
                                        <input type="text" name="mulai" id="mulai" class="form-control" placeholder="Select start date" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>End Date</label>
                                        <input type="text" name="selesai" id="selesai" class="form-control" placeholder="Select end date" required disabled>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Leave Type</label>
                                        <select name="detail" class="form-control" required>
                                            <option value="">--- Select Type ---</option>
                                            <option value="1">Sick Leave</option>
                                            <option value="2">Maternity Leave</option>
                                            <option value="3">Annual Leave</option>
                                            <option value="4">Personal Leave</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary btn-submit">
                                        <i class="fa fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="quote-box">
                            <i class="fa fa-quote-left"></i> Take care of your health and use your leave wisely. A well-rested employee is a productive employee.
                        </div>
                    </div>
                    
                    <div class="history-card">
                        <div class="card-header">
                            <h3><i class="fa fa-history"></i> Leave Request History</h3>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <table class="table table-cuti-status">
                                <thead>
                                    <tr>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Days</th>
                                        <th>Leave Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch leave history
                                    $history_query = $mysqli->prepare("SELECT * FROM cuti WHERE id_pegawai = ? order_id BY Tanggal_Mulai DESC");
                                    if ($history_query) {
                                        $history_query->bind_param("i", $idpegawai);
                                        $history_query->execute();
                                        $history_result = $history_query->get_result();
                                        
                                        if ($history_result->num_rows > 0) {
                                            while ($row = $history_result->fetch_assoc()) {
                                                echo '<tr>';
                                                echo '<td>'.htmlspecialchars($row['Tanggal_Mulai']).'</td>';
                                                echo '<td>'.htmlspecialchars($row['Tanggal_Selesai']).'</td>';
                                                echo '<td>'.htmlspecialchars($row['Total'] ?? 'N/A').'</td>';
                                                
                                                // Leave type
                                                echo '<td>';
                                                switch ($row['Detail_cuti']) {
                                                    case 1: echo 'Sick Leave'; break;
                                                    case 2: echo 'Maternity Leave'; break;
                                                    case 3: echo 'Annual Leave'; break;
                                                    case 4: echo 'Personal Leave'; break;
                                                    default: echo 'Other'; break;
                                                }
                                                echo '</td>';
                                                
                                                // Status
                                                echo '<td>';
                                                switch ($row['Aksi']) {
                                                    case 0: 
                                                        echo '<span class="status-badge status-pending">Pending</span>';
                                                        break;
                                                    case 1: 
                                                        echo '<span class="status-badge status-approved">Approved</span>';
                                                        break;
                                                    case 2: 
                                                        echo '<span class="status-badge status-rejected">Rejected</span>';
                                                        break;
                                                    default: 
                                                        echo '<span class="status-badge">Unknown</span>';
                                                        break;
                                                }
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            echo '<tr>
                                                <td colspan="5">
                                                    <div class="empty-state">
                                                        <i class="fa fa-calendar-times-o"></i>
                                                        <h4>No leave requests found</h4>
                                                        <p>You haven\'t submitted any leave requests yet</p>
                                                    </div>
                                                </td>
                                            </tr>';
                                        }
                                        $history_query->close();
                                    } else {
                                        error_log("Failed to prepare leave history statement: " . $mysqli->error);
                                        echo '<tr><td colspan="5" class="text-center">Error fetching leave history.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize date pickers
            const startDatePicker = flatpickr("#mulai", {
                dateFormat: "Y-m-d",
                minDate: "today",
                onChange: function(selectedDates, dateStr) {
                    if (dateStr) {
                        // Enable and update end date min date when start date changes
                        endDatePicker.set("minDate", dateStr);
                        $("#selesai").prop("disabled", false);

                        // If end date is before start date, reset it
                        if ($("#selesai").val() && new Date($("#selesai").val()) < new Date(dateStr)) {
                            endDatePicker.setDate(dateStr);
                        }
                    } else {
                        // If start date is cleared, disable end date and clear its value
                        $("#selesai").val("").prop("disabled", true);
                    }
                }
            });
            
            const endDatePicker = flatpickr("#selesai", {
                dateFormat: "Y-m-d",
                minDate: "today"
            });
            
            // Handle form validation using SweetAlert2
            $("#formCuti").on("submit", function(e) {
                let isValid = true;
                let errorMessage = '';
                
                if (!$("#mulai").val()) {
                    errorMessage = 'Please select a start date!';
                    isValid = false;
                } else if (!$("#selesai").val()) {
                    errorMessage = 'Please select an end date!';
                    isValid = false;
                } else if (!$("select[name='detail']").val()) {
                    errorMessage = 'Please select a leave type!';
                    isValid = false;
                } else {
                    // Client-side date comparison
                    const startDate = new Date($("#mulai").val());
                    const endDate = new Date($("#selesai").val());

                    if (startDate > endDate) {
                        errorMessage = 'End date cannot be before start date!';
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
            
            // Show status messages
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            
            if (status) {
                let title = '', text = '', icon = 'info';
                
                switch(status) {
                    case 'success':
                        title = 'Success';
                        text = 'Leave request submitted successfully!';
                        icon = 'success';
                        break;
                    case 'date_error':
                        title = 'Error';
                        text = 'End date cannot be before start date!';
                        icon = 'error';
                        break;
                    case 'empty_start_date':
                        title = 'Error';
                        text = 'Please select a start date!';
                        icon = 'error';
                        break;
                    case 'empty_end_date':
                        title = 'Error';
                        text = 'Please select an end date!';
                        icon = 'error';
                        break;
                    case 'empty_leave_type':
                        title = 'Error';
                        text = 'Please select a leave type!';
                        icon = 'error';
                        break;
                    case 'invalid_date_format':
                        title = 'Error';
                        text = 'Invalid date format!';
                        icon = 'error';
                        break;
                    case 'db_error':
                        title = 'Error';
                        text = 'A database error occurred. Please try again.';
                        icon = 'error';
                        break;
                    case 'please_login_first':
                        title = 'Authentication Required';
                        text = 'Please log in first to access this page.';
                        icon = 'warning';
                        break;
                    case 'unauthorized':
                        title = 'Unauthorized Access';
                        text = 'You do not have permission to access the Purchase module.';
                        icon = 'warning';
                        break;
                    case 'db_connection_error':
                        title = 'Database Connection Error';
                        text = 'Could not connect to the database. Please try again later.';
                        icon = 'error';
                        break;
                    default:
                        return;
                }
                
                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    confirmButtonText: 'OK'
                }).then(() => {
                    // Remove the status parameter from URL after showing the alert
                    if (history.replaceState) {
                        const newUrl = window.location.pathname;
                        history.replaceState(null, '', newUrl);
                    }
                });
            }
        });
    </script>
</body>
</html>