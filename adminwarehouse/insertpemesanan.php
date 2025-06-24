<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Please Login First");
    exit();
}

require_once('../konekdb.php');

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Cek apakah user memiliki hak akses ke modul Adminwarehouse (menggunakan prepared statement)
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['jmluser'] == "0") {
    header("Location: ../index.php?status=Access Declined");
    exit();
}


if(isset($_GET['no'])) {
    $no = intval($_GET['no']);
    
    // Get the order_id data first
    $getorder_id = mysqli_query($mysqli, "SELECT * FROM dariwarehouse WHERE no = $no");
    $order_id = mysqli_fetch_assoc($getorder_id);
    
    if($order_id) {
        $currentDate = date('Y-m-d H:i:s');
        
        // Prepare statement for pemesanan table
        $stmt = $mysqli->prepare("INSERT INTO pemesanan 
                        (code, namabarang, kategori, jumlah, satuan, id_supplier, tanggal, status, cabang) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, '1', ?)");
        $stmt->bind_param("sssissis", 
            $order_id['code'],
            $order_id['nama'],
            $order_id['kategori'],
            $order_id['jumlah'],
            $order_id['satuan'],
            $order_id['supplier'],
            $currentDate,
            $order_id['cabang']
        );
        
        if($stmt->execute()) {
            // Delete from dariwarehouse table
            $stmt2 = $mysqli->prepare("DELETE FROM dariwarehouse WHERE no = ?");
            $stmt2->bind_param("i", $no);
            
            if($stmt2->execute()) {
                $_SESSION['message'] = '<div class="alert alert-success">Pesanan telah diterima dan dipindahkan ke database pemesanan.</div>';
            } else {
                $_SESSION['message'] = '<div class="alert alert-danger">Gagal menghapus pesanan dari daftar.</div>';
            }
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger">Gagal memindahkan pesanan ke database pemesanan.</div>';
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">Pesanan tidak ditemukan.</div>';
    }
} else {
    $_SESSION['message'] = '<div class="alert alert-danger">Parameter tidak valid.</div>';
}

header("Location: daftarpermintaan.php");
exit();
?>