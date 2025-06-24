<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Cek apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $description = isset($_POST['description']) ? mysqli_real_escape_string($mysqli, trim($_POST['description'])) : '';

    if ($id > 0) {
        // Update description di tabel quotation
        $sql = "UPDATE quotation SET description = '$description' WHERE quotation_id = $id";
        if (mysqli_query($mysqli, $sql)) {
            // Redirect kembali ke halaman quotation dengan pesan sukses
            header("Location: quotation.php?msg=Description updated successfully");
            exit();
        } else {
            // Jika error update
            echo "Error updating description: " . mysqli_error($mysqli);
        }
    } else {
        echo "Invalid quotation ID.";
    }
} else {
    // Jika akses langsung tanpa POST
    header("Location: quotation.php");
    exit();
}
?>
