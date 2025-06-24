<?php
session_start();
include "../config.php";

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$folder = $_GET['folder'] ?? 'inbox';

// Mark as read if viewing from inbox
if ($folder == 'inbox') {
    $update = $conn->prepare("UPDATE pesan SET status = 1 WHERE id_pesan = ?");
    $update->bind_param("i", $id);
    $update->execute();
    $update->close();
}

// Get message details
$query = "SELECT p.*, 
          dari.nama as dari_nama, 
          ke.nama as ke_nama,
          dari.foto as dari_foto,
          ke.foto as ke_foto
          FROM pesan p
          JOIN pegawai dari ON p.dari = dari.id_pegawai
          JOIN pegawai ke ON p.ke = ke.id_pegawai
          WHERE p.id_pesan = ?";
          
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$message = $result->fetch_assoc();
$stmt->close();

if (!$message) {
    header("Location: mailbox.php");
    exit();
}

// Determine if current user is sender or recipient
$is_sender = ($_SESSION['idpegawai'] == $message['dari']);
$recipient_name = $is_sender ? $message['ke_nama'] : $message['dari_nama'];
$recipient_photo = $is_sender ? $message['ke_foto'] : $message['dari_foto'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Message Detail | E-pharm</title>
    <?php include "../includes/head.php"; ?>
    <style>
        .message-header {
            border_id-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .message-avatar {
            width: 60px;
            height: 60px;
            border_id-radius: 50%;
            object-fit: cover;
        }
        .attachment-box {
            border_id: 1px solid #ddd;
            border_id-radius: 5px;
            padding: 10px;
            margin-top: 20px;
        }
        .reply-box {
            margin-top: 30px;
            border_id-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body class="skin-blue">
    <?php include "../includes/header.php"; ?>
    
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include "../includes/sidebar.php"; ?>
        
        <aside class="right-side">
            <section class="content-header">
                <h1>Message Detail</h1>
                <ol class="breadcrumb">
                    <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="mailbox.php">Mailbox</a></li>
                    <li class="active">Message Detail</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header with-border_id">
                                <h3 class="box-title"><?= htmlspecialchars($message['subject']); ?></h3>
                                <div class="box-tools pull-right">
                                    <a href="mailbox.php?folder=<?= $folder; ?>" class="btn btn-default btn-sm">
                                        <i class="fa fa-arrow-left"></i> Back
                                    </a>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="message-header">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="media">
                                                <div class="media-left">
                                                    <img src="../img/<?= htmlspecialchars($recipient_photo); ?>" class="message-avatar" alt="User Image">
                                                </div>
                                                <div class="media-body">
                                                    <h4 class="media-heading"><?= htmlspecialchars($recipient_name); ?></h4>
                                                    <p class="text-muted"><?= date('d M Y H:i', strtotime($message['waktu'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <div class="btn-group">
                                                <a href="reply.php?id=<?= $message['id_pesan']; ?>" class="btn btn-default btn-sm">
                                                    <i class="fa fa-reply"></i> Reply
                                                </a>
                                                <a href="delete_message.php?id=<?= $message['id_pesan']; ?>&folder=<?= $folder; ?>" 
                                                   class="btn btn-default btn-sm" 
                                                   onclick="return confirm('Are you sure you want to delete this message?');">
                                                    <i class="fa fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="message-content">
                                    <?= nl2br(htmlspecialchars($message['isi'])); ?>
                                </div>
                                
                                <?php if (!empty($message['attachment'])): ?>
                                <div class="attachment-box">
                                    <h5><i class="fa fa-paperclip"></i> Attachment</h5>
                                    <a href="../uploads/attachments/<?= htmlspecialchars($message['attachment']); ?>" 
                                       target="_blank" class="btn btn-default">
                                        <i class="fa fa-download"></i> Download Attachment
                                    </a>
                                </div>
                                <?php endif; ?>
                                
                                <div class="reply-box">
                                    <form action="reply.php" method="post">
                                        <input type="hidden" name="original_id" value="<?= $message['id_pesan']; ?>">
                                        <input type="hidden" name="recipient_id" value="<?= $is_sender ? $message['ke'] : $message['dari']; ?>">
                                        <div class="form-group">
                                            <textarea name="message" class="form-control" rows="3" placeholder="Write your reply here..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-reply"></i> Send Reply
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <?php include "../includes/footer.php"; ?>
    <?php include "../includes/scripts.php"; ?>
</body>
</html>