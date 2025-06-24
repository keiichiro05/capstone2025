<?php
include('../konekdb.php');
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = $_POST['id'];
$name = $_POST['name'];
$stock = $_POST['stock'];
$reorder_id = $_POST['reorder_id'];

$query = "UPDATE barang SET Nama = ?, Stok = ?, reorder_id_level = ? WHERE id_barang = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("siii", $name, $stock, $reorder_id, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $mysqli->error]);
}
?>