<?php
include('../konekdb.php');
session_start();

if(!isset($_SESSION['username'])) {
    header("location:../index.php?status=please login first");
    exit();
}

if (isset($_GET['id']) && isset($_GET['reason'])) {
    $id = mysqli_real_escape_string($mysqli, $_GET['id']);
    $reason = mysqli_real_escape_string($mysqli, $_GET['reason']);
    $username = $_SESSION['username'];
    $current_time = date('Y-m-d H:i:s');
    
    mysqli_query($mysqli, "UPDATE sales_requests SET 
        status = 'rejected',
        rejected_by = '$username',
        rejected_at = '$current_time',
        rejection_reason = '$reason'
        WHERE Code='$id'");
    
    header("Location: sales_request1.php?status=rejected");
    exit();
} else {
    header("Location: sales_request1.php?status=reject_failed");
    exit();
}
?>