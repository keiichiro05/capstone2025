<?php
session_start();
require_once('../konekdb.php'); // Pastikan path ini benar

// Memeriksa apakah user sudah login dan memiliki ID pegawai
if (!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    $_SESSION['error'] = "You are not logged in or your session has expired.";
    header("location:../index.php"); // Redirect ke halaman login
    exit();
}

$username = $_SESSION['username'];

// Memeriksa otorisasi pengguna untuk modul 'Purchase'
// Ini penting untuk memastikan hanya pengguna yang berwenang yang dapat menghapus supplier.
$authorized = false;
$stmt_cekuser = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Purchase'");
if ($stmt_cekuser) {
    $stmt_cekuser->bind_param("s", $username);
    $stmt_cekuser->execute();
    $result_cekuser = $stmt_cekuser->get_result();
    $user_auth = $result_cekuser->fetch_assoc();
    if ($user_auth['jmluser'] > 0) {
        $authorized = true;
    }
    $stmt_cekuser->close();
} else {
    // Catat error untuk debugging, jangan tampilkan info sensitif ke user
    error_log("Error preparing statement for user authorization in delete_supplier.php: " . $mysqli->error);
    $_SESSION['error'] = "An unexpected error occurred during authorization check. Please try again.";
    header("location:supplier.php");
    exit();
}

// Redirect jika tidak diotorisasi
if (!$authorized) {
    $_SESSION['error'] = "You are not authorized to delete suppliers.";
    header("location:supplier.php");
    exit();
}

// Memeriksa apakah ID supplier diberikan melalui permintaan GET
if (isset($_GET['id'])) {
    // Kolom ID di tabel supplier Anda adalah 'id_supplier' dan tipenya VARCHAR
    $supplierId = $_GET['id'];

    // Siapkan dan jalankan pernyataan DELETE
    // Perbaikan: Ubah nama tabel dari 'suppliers' menjadi 'supplier' dan kolom 'id' menjadi 'id_supplier'
    $stmt = $mysqli->prepare("DELETE FROM supplier WHERE id_supplier = ?");
    // Jika Anda menggunakan soft delete (yang disarankan), ganti query di atas menjadi:
    // $stmt = $mysqli->prepare("UPDATE supplier SET is_deleted = 1 WHERE id_supplier = ?");

    if ($stmt) {
        // Perbaikan: Ubah tipe bind_param dari "i" (integer) menjadi "s" (string) karena 'id_supplier' adalah VARCHAR
        $stmt->bind_param("s", $supplierId);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Supplier deleted successfully!";
        } else {
            // Tambahkan htmlspecialchars untuk pesan error guna mencegah XSS
            $_SESSION['error'] = "Error deleting supplier: " . htmlspecialchars($stmt->error);
            error_log("Failed to delete supplier ID " . $supplierId . ": " . $stmt->error);
        }
        $stmt->close();
    } else {
        // Tambahkan htmlspecialchars untuk pesan error guna mencegah XSS
        $_SESSION['error'] = "Error preparing statement: " . htmlspecialchars($mysqli->error);
        error_log("Failed to prepare delete statement for supplier: " . $mysqli->error);
    }
} else {
    $_SESSION['error'] = "No supplier ID provided for deletion.";
}

// Redirect kembali ke halaman manajemen supplier
header("location:supplier.php");
exit();
?>