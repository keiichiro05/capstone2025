<?php
include('../konekdb.php');

$warehouse_id = $_GET['warehouse_id'] ?? null;

if (!$warehouse_id) {
    echo json_encode([]);
    exit;
}

// Query untuk mengambil item berdasarkan warehouse
$query = "SELECT s.id_barang, p.Nama, s.Stok 
          FROM stock s 
          JOIN produk p ON s.id_barang = p.id_barang 
          WHERE s.id_warehouse = ? AND s.Stok > 0
          order_id BY p.Nama";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $warehouse_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = [
        'id_barang' => $row['id_barang'],
        'Nama' => $row['Nama'],
        'Stok' => $row['Stok']
    ];
}

echo json_encode($items);
?>