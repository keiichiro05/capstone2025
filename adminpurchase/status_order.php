<?php
include("koneksi.php"); // koneksi ke database, sesuaikan jika nama file beda

$status = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Validasi input status
$allowed_status = ['pending', 'approved', 'rejected', 'delivered'];
if (!in_array($status, $allowed_status)) {
    echo "Invalid status!";
    exit;
}

// Query data pesanan berdasarkan status
$query = "SELECT * FROM pemesanan1 WHERE status_order_id = ?";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Status order_id - <?php echo ucfirst($status); ?></title>
    <link rel="stylesheet" href="path/to/bootstrap.min.css"> <!-- Sesuaikan jika pakai template -->
</head>
<body>
    <div class="container">
        <h2>Status: <?php echo ucfirst($status); ?></h2>
        <table class="table table-border_ided">
            <thead>
                <tr>
                    <th>No</th>
                    <th>order_id ID</th>
                    <th>Customer</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td>" . htmlspecialchars($row['order_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_pelanggan']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tanggal_pesan']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status_order_id']) . "</td>";
                    echo "</tr>";
                    $no++;
                }
                ?>
            </tbody>
        </table>
        <a href="javascript:history.back()" class="btn btn-secondary">Kembali</a>
    </div>
</body>
</html>
