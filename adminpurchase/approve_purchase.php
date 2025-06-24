<?php
include('../konekdb.php');
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = mysqli_real_escape_string($mysqli, $_POST['order_id']);
    
    // Update status dari draft ke pending
    $update_query = "UPDATE pemesanan1 SET status='pending' WHERE order_id='$order_id'";
    
    if (mysqli_query($mysqli, $update_query)) {
        $_SESSION['message'] = "Pesanan berhasil diajukan untuk persetujuan";
    } else {
        $_SESSION['error'] = "Gagal mengajukan pesanan: " . mysqli_error($mysqli);
    }
    
    header("location:pemesanan.php");
    exit();
}
?>