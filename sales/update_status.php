<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = mysqli_real_escape_string($mysqli, $_GET['id']);
    $status = mysqli_real_escape_string($mysqli, $_GET['status']);

    mysqli_query($mysqli, "UPDATE quotation SET status='$status' WHERE quotation_id='$id'");

    header("Location: quotation.php?msg=Status updated successfully");
    exit();
} else {
    header("Location: quotation.php?msg=Invalid request");
    exit();
}
?>
