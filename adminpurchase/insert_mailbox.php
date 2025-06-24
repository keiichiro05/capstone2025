<?php
session_start();
include "../config.php";

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dari = $_SESSION['idpegawai'];
    $ke = $_POST['ke'];
    $subject = $_POST['subject'] ?? '';
    $isi = $_POST['message'];
    $draft = isset($_POST['draft']) ? 1 : 0;
    $waktu = date('Y-m-d H:i:s');
    $status = 0; // 0 = unread, 1 = read

    // Handle file attachment
    $attachment_name = '';
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/attachments/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $attachment_name = uniqid() . '.' . $file_ext;
        $target_file = $target_dir . $attachment_name;
        
        move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file);
    }

    $stmt = $conn->prepare("INSERT INTO pesan (dari, ke, subject, isi, waktu, status, draft, attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssiis", $dari, $ke, $subject, $isi, $waktu, $status, $draft, $attachment_name);
    
    if ($stmt->execute()) {
        if ($draft) {
            $_SESSION['success'] = "Message saved to drafts successfully!";
            header("Location: mailbox.php?folder=draft");
        } else {
            $_SESSION['success'] = "Message sent successfully!";
            header("Location: mailbox.php?folder=sent");
        }
    } else {
        $_SESSION['error'] = "Failed to send message: " . $conn->error;
        header("Location: mailbox.php");
    }
    $stmt->close();
    exit();
} else {
    header("Location: mailbox.php");
    exit();
}
?>