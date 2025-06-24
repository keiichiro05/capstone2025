<?php
session_start();
include "konekdb.php";

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil opp_id dari URL, validasi
$opp_id = isset($_GET['opp_id']) ? trim($_GET['opp_id']) : '';
if ($opp_id === '') {
    die("Invalid opportunity ID");
}

// Daftar status valid sesuai enum di DB
$valid_status = ['in progress', 'lost', 'stopped', 'won'];

// Daftar status_delivery valid (buat contoh, sesuaikan dengan kebutuhan)
$valid_status_delivery = ['Not Shipped', 'pending', 'shipped', 'delivered'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan sanitize data dari form
    $nama_barang = trim($_POST['nama_barang']);
    $tanggal_jual = trim($_POST['tanggal_jual']);
    $kode_barang = trim($_POST['kode_barang']);
    $cabang = trim($_POST['cabang']);
    $quantity = intval($_POST['quantity']);
    $harga = floatval($_POST['harga']);
    $status = trim($_POST['status']);
    $status_delivery = trim($_POST['status_delivery']);

    // Validasi form
    if ($nama_barang === '' || $tanggal_jual === '' || $kode_barang === '' || $cabang === '' || $quantity <= 0 || $harga <= 0) {
        $error = "Please fill all required fields correctly.";
    } elseif (!in_array($status, $valid_status)) {
        $error = "Invalid status value.";
    } elseif (!in_array($status_delivery, $valid_status_delivery)) {
        $error = "Invalid status delivery value.";
    } else {
        // Hitung total harga
        $total_harga = $quantity * $harga;

        // Cek duplicate berdasarkan opp_id + kode_barang
        $cek = $mysqli->prepare("SELECT COUNT(*) FROM sales_order WHERE opp_id = ? AND kode_barang = ?");
        if (!$cek) {
            die("Prepare failed: " . $mysqli->error);
        }
        $cek->bind_param("ss", $opp_id, $kode_barang);
        $cek->execute();
        $cek->bind_result($count);
        $cek->fetch();
        $cek->close();

        if ($count > 0) {
            $error = "Order for this opportunity and product code already exists.";
        } else {
            // Insert data ke tabel sales_order dengan prepared statement
            $stmt = $mysqli->prepare("INSERT INTO sales_order (opp_id, nama_barang, tanggal_jual, kode_barang, cabang, quantity, harga, total_harga, status, status_delivery) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                die("Prepare failed: " . $mysqli->error);
            }

            $stmt->bind_param("sssssiddss", $opp_id, $nama_barang, $tanggal_jual, $kode_barang, $cabang, $quantity, $harga, $total_harga, $status, $status_delivery);

            if ($stmt->execute()) {
                // Redirect ke halaman detail sales order dengan pesan sukses
               header("Location: sales_order.php?opp_id=" . urlencode($opp_id) . "&converted=success");
                exit();
            } else {
                $error = "Failed to add order: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Add New Order</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container">
    <?php if (isset($_GET['converted']) && $_GET['converted'] === 'success'): ?>
    <div class="alert alert-success">Sales order has been added successfully!</div>
<?php endif; ?>
    <h3>Add New Order for Opportunity ID: <?php echo htmlspecialchars($opp_id); ?></h3>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Nama Barang</label>
            <input type="text" name="nama_barang" class="form-control" required />
        </div>

        <div class="form-group">
            <label>Tanggal Jual</label>
            <input type="date" name="tanggal_jual" class="form-control" required />
        </div>

        <div class="form-group">
            <label>Kode Barang</label>
            <input type="text" name="kode_barang" class="form-control" required />
        </div>

        <div class="form-group">
            <label>Cabang</label>
            <input type="text" name="cabang" class="form-control" required />
        </div>

        <div class="form-group">
            <label>Quantity</label>
            <input type="number" name="quantity" class="form-control" min="1" required />
        </div>

        <div class="form-group">
            <label>Harga</label>
            <input type="number" name="harga" step="0.01" class="form-control" min="0" required />
        </div>

        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control" required>
                <option value="in progress">In Progress</option>
                <option value="lost">Lost</option>
                <option value="stopped">Stopped</option>
                <option value="won">Won</option>
            </select>
        </div>

        <div class="form-group">
            <label>Status Delivery</label>
            <select name="status_delivery" class="form-control" required>
                <option value="Not Shipped">Not Shipped</option>
                <option value="pending">Pending</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save Order</button>
        <a href="sales_order_detail.php?opp_id=<?php echo urlencode($opp_id); ?>" class="btn btn-default">Cancel</a>
    </form>
</div>
</body>
</html>
