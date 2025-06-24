<?php
session_start();
include "../config.php";

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("UPDATE pesan SET status = 1 WHERE id_pesan = ? AND ke = ?");
$user_id = $_SESSION['idpegawai'];
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
exit();
?>