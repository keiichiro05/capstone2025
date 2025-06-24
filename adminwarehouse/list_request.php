
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../list_request.php?status=Please Login First");
    exit();
}

require_once('../konekdb.php');

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check if user has access to Adminwarehouse module
$stmt = $mysqli->prepare("SELECT COUNT(username) as usercount FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['usercount'] == "0") {
    header("Location: ../list_request.php?status=Access Declined");
    exit();
}

// Get employee data
$stmtPegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmtPegawai->bind_param("i", $idpegawai);
$stmtPegawai->execute();
$resultPegawai = $stmtPegawai->get_result();
$pegawai = $resultPegawai->fetch_assoc();

// Handle accept action
if (isset($_GET['accept']) && isset($_GET['no'])) {
    $no = intval($_GET['no']);
    $getorder_id = $mysqli->prepare("SELECT * FROM dariwarehouse WHERE no = ?");
    $getorder_id->bind_param("i", $no);
    $getorder_id->execute();
    $order_id = $getorder_id->get_result()->fetch_assoc();

    if ($order_id) {
        $currentDate = date('Y-m-d H:i:s');
        $stmt = $mysqli->prepare("INSERT INTO list_request 
            (namabarang, kategori, jumlah, satuan, tanggal, status, cabang) 
            VALUES (?, ?, ?, ?, ?, '1', ?)");
        $stmt->bind_param("sssisi",
            $order_id['nama'],
            $order_id['kategori'],
            $order_id['jumlah'],
            $order_id['satuan'],
           
            $currentDate,
            $order_id['cabang']
        );
        if ($stmt->execute()) {
            $stmt2 = $mysqli->prepare("DELETE FROM dariwarehouse WHERE no = ?");
            $stmt2->bind_param("i", $no);
            if ($stmt2->execute()) {
                $_SESSION['message'] = '<div class="alert alert-success">order_id has been accepted and moved to the order_id database.</div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger">Failed to delete order_id from the list.</div>';
            }
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger">Failed to move order_id to the order_id database.</div>';
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">order_id not found.</div>';
    }
    header("Location: list_request.php");
    exit();
}

// Handle reject action
if (isset($_GET['reject']) && isset($_GET['no'])) {
    $no = intval($_GET['no']);
    $getorder_id = $mysqli->prepare("SELECT * FROM dariwarehouse WHERE no = ?");
    $getorder_id->bind_param("i", $no);
    $getorder_id->execute();
    $order_id = $getorder_id->get_result()->fetch_assoc();

    if ($order_id) {
        $currentDate = date('Y-m-d H:i:s');
        $stmt = $mysqli->prepare("INSERT INTO list_request 
            (namabarang, kategori, jumlah, satuan, tanggal, status, cabang) 
            VALUES (?, ?, ?, ?, ?, '2', ?)");
        $stmt->bind_param("sssissi",
            $order_id['nama'],
            $order_id['kategori'],
            $order_id['jumlah'],
            $order_id['satuan'],
        
            $currentDate,
            $order_id['cabang']
        );
        if ($stmt->execute()) {
            $stmt2 = $mysqli->prepare("DELETE FROM dariwarehouse WHERE no = ?");
            $stmt2->bind_param("i", $no);
            if ($stmt2->execute()) {
                $_SESSION['message'] = '<div class="alert alert-success">order_id has been rejected and moved to the order_id database.</div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger">Failed to delete order_id from the list.</div>';
            }
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger">Failed to move order_id to the order_id database.</div>';
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">order_id not found.</div>';
    }
    header("Location: list_request.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Manager Dashboard</title>
    
    <!-- CSS Links -->
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

        <!-- Sidebar -->
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
                        <a href="streamlit.php">
                            <i class="fa fa-signal"></i> <span>Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="dashboard.php">
                            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="list_request.php">
                            <i class="fa fa-list"></i> <span>List Request</span>
                        </a>
                    </li>
                     <li>
                <a href="daftarACC.php">
                    <i class="fa fa-undo"></i> <span>Request History</span>
                </a>
            </li>
            <li>
                <a href="frompurchase.php">
                    <i class="fa fa-tasks"></i> <span>Purchase Order</span>
                </a>
            </li>
              
                    <li>
                        <a href="stock.php">
                           <i class="fa fa-archive"></i> <span>Inventory</span>
                        </a>
                    </li>
              
                </ul>
            </section>
        </aside>
        <aside class="right-side">
            <section class="content-header">
                <h1>
                    List Request
                    <small>Warehouse Manager</small>
                </h1>
            </section>
            <section class="content">
                <?php 
                if (isset($_SESSION['message'])) {
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                }
                ?>
                <p>List Request From Warehouse</p>
                <div class="table-responsive">
                    <table class="table table-border_ided table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>order_id Date</th>
                                <th>Name</th>
                                <th>Qty</th>
                                <th>Unit</th>
                        
                                <th>Status</th>
                                <th>Branch</th>
                                <th>Category</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM dariwarehouse WHERE status = 0";
                            $hasil = mysqli_query($mysqli, $sql);

                            if (mysqli_num_rows($hasil) > 0) {
                                while ($baris = mysqli_fetch_array($hasil)) {
                                    echo "<tr>
                                        <td>{$baris['no']}</td>
                                        <td>{$baris['date_created']}</td>
                                        <td>".htmlspecialchars($baris['nama'])."</td>
                                        <td>{$baris['jumlah']}</td>
                                        <td>".htmlspecialchars($baris['satuan'])."</td>
                                       
                                        <td><button class='btn btn-warning'>Pending</button></td>
                                        <td>".htmlspecialchars($baris['cabang'])."</td>
                                        <td>".htmlspecialchars($baris['kategori'])."</td>
                                        <td>
                                            <a href='/adminwarehouse/list_request.php?accept=true&no={$baris['no']}' class='btn btn-primary'>Accept</a>
                                            <a href='/adminwarehouse/list_request.php?reject=true&no={$baris['no']}' onclick='return confirm(\"Are you sure to reject this?\")' class='btn btn-danger'>Reject</a>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='10' style='text-align:center;'>No order_id Requests</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </aside>
    </div>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
</body>
</html>