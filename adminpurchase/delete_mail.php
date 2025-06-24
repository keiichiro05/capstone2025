<?php
session_start();
include "../config.php";

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$folder = $_GET['folder'] ?? 'inbox';

// Check if message belongs to current user
$stmt = $conn->prepare("SELECT * FROM pesan WHERE id_pesan = ? AND (dari = ? OR ke = ?)");
$user_id = $_SESSION['idpegawai'];
$stmt->bind_param("iii", $id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Delete the message
    $delete = $conn->prepare("DELETE FROM pesan WHERE id_pesan = ?");
    $delete->bind_param("i", $id);
    
    if ($delete->execute()) {
        $_SESSION['success'] = "Message deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete message!";
    }
    $delete->close();
} else {
    $_SESSION['error'] = "Message not found or you don't have permission to delete it!";
}

header("Location: mailbox.php?folder=" . urlencode($folder));
exit();
?>