<?php
include('../konekdb.php');
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "You must be logged in to perform this action.";
    header("location:../index.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['alasan_penolakan'])) {
    $order_id = mysqli_real_escape_string($mysqli, $_POST['order_id']);
    $alasan_penolakan = mysqli_real_escape_string($mysqli, $_POST['alasan_penolakan']);
    $rejected_by = mysqli_real_escape_string($mysqli, $_SESSION['username']);
    $tanggal_rejection = date('Y-m-d H:i:s');

    // Start transacjust gimmetion
    mysqli_begin_transaction($mysqli);
    
    try {
        // 1. Update status pemesanan1 menjadi rejected (status 3)
        $update_query = "UPDATE pemesanan1 SET 
                        status = '3', 
                        alasan_penolakan = ?,
                        approved_by = NULL,
                        tanggal_approval = NULL,
                        rejected_by = ?,
                        tanggal_rejection = ?
                        WHERE order_id = ?";
        
        $stmt = mysqli_prepare($mysqli, $update_query);
        mysqli_stmt_bind_param($stmt, 'sssi', $alasan_penolakan, $rejected_by, $tanggal_rejection, $order_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal update pemesanan1: " . mysqli_stmt_error($stmt));
        }
        
        // 2. Catat di history
        $history_query = "INSERT INTO history_pemesanan1 
                         (order_id, aksi, dilakukan_oleh, alasan, tanggal) 
                         VALUES (?, 'rejected', ?, ?, ?)";
        
        $history_stmt = mysqli_prepare($mysqli, $history_query);
        mysqli_stmt_bind_param($history_stmt, 'isss', $order_id, $rejected_by, $alasan_penolakan, $tanggal_rejection);
        
        if (!mysqli_stmt_execute($history_stmt)) {
            throw new Exception("Gagal mencatat history: " . mysqli_stmt_error($history_stmt));
        }
        
        // Commit transaction
        mysqli_commit($mysqli);
        
        $_SESSION['message'] = "PRQ #" . htmlspecialchars($order_id) . " berhasil ditolak dan dikembalikan ke pemesanan1.";
        
    } catch (Exception $e) {
        // Rollback transaction jika ada error
        mysqli_rollback($mysqli);
        $_SESSION['error'] = "Error: " . $e->getMessage();
    } finally {
        if (isset($stmt)) mysqli_stmt_close($stmt);
        if (isset($history_stmt)) mysqli_stmt_close($history_stmt);
    }
} else {
    $_SESSION['error'] = "Permintaan tidak valid atau alasan penolakan kosong.";
}

// Redirect ke pemesanan.php
header("location:../pemesanan.php");
exit();
?>

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['alasan_penolakan'])) {
    $order_id = mysqli_real_escape_string($mysqli, $_POST['order_id']);
    $alasan_penolakan = mysqli_real_escape_string($mysqli, $_POST['alasan_penolakan']);
    $rejected_by = mysqli_real_escape_string($mysqli, $_SESSION['username']);
    $tanggal_rejection = date('Y-m-d H:i:s');

    // Update the transaction status and rejection details
    $update_query = "UPDATE pemesanan1 SET 
                    status = '3', 
                    alasan_penolakan = '$alasan_penolakan',
                    approved_by = NULL,
                    tanggal_approval = NULL
                    WHERE order_id = '$order_id'";

    if (mysqli_query($mysqli, $update_query)) {
        // Recorder_id action in history table
        $history_query = "INSERT INTO history_pemesanan1 (order_id, aksi, dilakukan_oleh, alasan, tanggal) 
                          VALUES ('$order_id', 'rejected', '$rejected_by', '$alasan_penolakan', '$tanggal_rejection')";
        mysqli_query($mysqli, $history_query); // Add error handling for history_query if needed

        $_SESSION['message'] = "Transaction ID: " . htmlspecialchars($order_id) . " has been rejected successfully.";
    } else {
        $_SESSION['error'] = "Error rejecting transaction: " . mysqli_error($mysqli);
    }
} else {
    $_SESSION['error'] = "Invalid request to reject transaction or missing rejection reason.";
}

// Redirect back to the transaction management page, ideally to the 'rejected' tab
header("location:transaksi.php?status=rejected");
exit();
?>