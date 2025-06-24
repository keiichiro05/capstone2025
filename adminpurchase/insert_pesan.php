<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['username'])){
    header("location:../index.php");
    exit();
}

include "../config.php";
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get current user ID
$current_user_id = $_POST['current_user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $ke = (int)$_POST['ke']; // Recipient ID
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $attachment = null;
    
    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $upload_dir = "../uploads/messages/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $_FILES['attachment']['name'];
        $file_size = $_FILES['attachment']['size'];
        $file_tmp = $_FILES['attachment']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Check file size (32MB max)
        if ($file_size > 33554432) {
            $_SESSION['error'] = "File size too large. Maximum 32MB allowed.";
            header("location:mailbox.php");
            exit();
        }
        
        // Allowed file types
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip', 'rar');
        
        if (in_array($file_ext, $allowed_extensions)) {
            $new_file_name = time() . '_' . $file_name;
            $file_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                $attachment = $new_file_name;
            } else {
                $_SESSION['error'] = "Failed to upload file.";
                header("location:mailbox.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "File type not allowed.";
            header("location:mailbox.php");
            exit();
        }
    }
    
    // Determine if it's a draft or sent message
    $is_draft = isset($_POST['draft']) ? 1 : 0;
    $status = $is_draft ? 0 : 0; // 0 = unread, 1 = read, 2 = replied, 3 = deleted
    
    // Insert message into database
    $sql = "INSERT INTO pesan (dari, ke, subject, isi, waktu, status, draft, attachment, starred) 
            VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, 0)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("location:mailbox.php");
        exit();
    }
    
    $stmt->bind_param("iisssis", $current_user_id, $ke, $subject, $message, $status, $is_draft, $attachment);
    
    if ($stmt->execute()) {
        if ($is_draft) {
            $_SESSION['message'] = "Message saved as draft successfully.";
        } else {
            $_SESSION['message'] = "Message sent successfully.";
            
            // Optional: Send notification (you can implement this based on your needs)
            // notify_user($ke, "New message from " . $_SESSION['username']);
        }
    } else {
        $_SESSION['error'] = "Failed to save message: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid request method.";
}

$conn->close();
header("location:mailbox.php");
exit();
?>