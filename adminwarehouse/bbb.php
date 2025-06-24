<?php
session_start();
require_once('../konekdb.php');

// Check if user is authorized
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Please Login First");
    exit();
}

// Check if user has access to Adminwarehouse module
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if ($user['jmluser'] == "0") {
    header("Location: ../index.php?status=Access Declined");
    exit();
}

// Process warehouse actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $order_id = $_GET['id'];
    
    if ($action == 'accept') {
        // Redirect to purchasing integration
        header("Location: proses_purchasing.php?action=accept&id=".$order_id);
        exit();
    } elseif ($action == 'decline') {
        // Update status to declined
        $update = $mysqli->prepare("UPDATE pemesanan SET status = 2 WHERE order_id = ?");
        $update->bind_param("i", $order_id);
        $update->execute();
        
        $_SESSION['message'] = "order_id #{$order_id} has been declined";
        header("Location: daftarACC.php");
        exit();
    }
}
?>