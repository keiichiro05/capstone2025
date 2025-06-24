<?php 
session_start();

$idpegawai = $_SESSION['idpegawai'] ?? '';
if(!isset($_SESSION['username'])){
    header("location:../index.php");
    exit();
}

include "../config.php";
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$username = $_SESSION['username'] ?? '';

// Fetch user profile data
$stmt = $conn->prepare("SELECT p.nama AS Nama, p.foto, p.Jabatan, p.Departemen, p.Tanggal_Masuk, p.id_pegawai FROM pegawai p JOIN authorization a ON a.id_pegawai = p.id_pegawai WHERE a.username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result_profil = $stmt->get_result();
$pegawai = $result_profil->fetch_assoc();
$stmt->close();

$current_user_id = $pegawai['id_pegawai'] ?? 0;
$folder = $_GET['folder'] ?? 'inbox';
$message_id = (int)$_GET['id'];

// Fetch message details
$stmt = $conn->prepare("
    SELECT p.*, 
           pg_from.nama as sender_name, 
           pg_to.nama as recipient_name,
           DATE_FORMAT(p.waktu,'%d %b %Y at %h:%i %p') as formatted_time
    FROM pesan p 
    JOIN pegawai pg_from ON p.dari = pg_from.id_pegawai 
    JOIN pegawai pg_to ON p.ke = pg_to.id_pegawai 
    WHERE p.id_pesan = ? AND (p.dari = ? OR p.ke = ?)
");
$stmt->bind_param("iii", $message_id, $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Message not found or access denied.";
    header("location:mailbox.php");
    exit();
}

$message = $result->fetch_assoc();
$stmt->close();

// Mark as read if it's in inbox and unread
if ($folder == 'inbox' && $message['ke'] == $current_user_id && $message['status'] == 0) {
    $stmt = $conn->prepare("UPDATE pesan SET status = 1 WHERE id_pesan = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();
}

// Set display variables
$displayUsername = htmlspecialchars($pegawai['Nama'] ?? '');
$displayPegawaiFoto = htmlspecialchars($pegawai['foto'] ?? 'default.png');
$displayPegawaiNama = htmlspecialchars($pegawai['Nama'] ?? '');
$displayPegawaiJabatan = htmlspecialchars($pegawai['Jabatan'] ?? '');
$displayPegawaiDepartemen = htmlspecialchars($pegawai['Departemen'] ?? '');
$displayPegawaiTanggalMasuk = isset($pegawai['Tanggal_Masuk']) ? date('M Y', strtotime($pegawai['Tanggal_Masuk'])) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Read Message</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/ionicons.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <style>
        .message-container {
            background: white;
            border_id-radius: 4px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .message-header {
            background: #3c8dbc;
            color: white;
            padding: 15px;
            border_id-radius: 4px 4px 0 0;
        }
        .message-header h3 {
            margin: 0;
            font-size: 18px;
        }
        .message-meta {
            background: #f9f9f9;
            padding: 10px 15px;
            border_id-bottom: 1px solid #ddd;
            font-size: 13px;
        }
        .message-content {
            padding: 20px 15px;
            min-height: 200px;
        }
        .message-actions {
            background: #f9f9f9;
            padding: 10px 15px;
            border_id-top: 1px solid #ddd;
            border_id-radius: 0 0 4px 4px;
        }
        .attachment-item {
            display: inline-block;
            background: #e7f3ff;
            border_id: 1px solid #b3d9ff;
            padding: 8px 12px;
            border_id-radius: 4px;
            margin: 5px 0;
            text-decoration: none;
            color: #0066cc;
        }
        .attachment-item:hover {
            background: #d4edff;
            text-decoration: none;
            color: #004499;
        }
        .reply-form {
            margin-top: 20px;
            background: white;
            padding: 15px;
            border_id-radius: 4px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="skin-blue">
    <div class="wrapper">
        <!-- Header -->
        <header class="header">
            <a href="index.php" class="logo">U-PSN</a>
            <nav class="navbar navbar-static-top" role="navigation">
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?= $displayUsername; ?> <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="user-header bg-light-blue">
                                    <img src="../img/<?= $displayPegawaiFoto; ?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?= $displayPegawaiNama . " - " . $displayPegawaiJabatan . " " . $displayPegawaiDepartemen; ?>
                                        <small>Member since <?= $displayPegawaiTanggalMasuk; ?></small>
                                    </p>
                                </li>
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="../logout.php" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- Sidebar (simplified for message reading) -->
        <aside class="left-side sidebar-offcanvas">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="../img/<?= $displayPegawaiFoto; ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?= $displayPegawaiNama; ?></p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
                
                <ul class="sidebar-menu">
                    <li><a href="mailbox.php"><i class="fa fa-arrow-left"></i> <span>Back to Mailbox</span></a></li>
                    <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                </ul>
            </section>
        </aside>

        <!-- Content -->
        <div class="right-side">
            <section class="content-header">
                <h1>Read Message</h1>
                <ol class="breadcrumb">
                    <li><a href="../index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="mailbox.php">Mailbox</a></li>
                    <li class="active">Read Message</li>
                </ol>
            </section>

            <section class="content">
                <!-- Message Display -->
                <div class="row">
                    <div class="col-xs-12">
                        <div class="message-container">
                            <div class="message-header">
                                <h3><?= htmlspecialchars($message['subject'] ?: '(No Subject)'); ?></h3>
                            </div>
                            
                            <div class="message-meta">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>From:</strong> <?= htmlspecialchars($message['sender_name']); ?><br>
                                        <strong>To:</strong> <?= htmlspecialchars($message['recipient_name']); ?>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <strong>Date:</strong> <?= $message['formatted_time']; ?><br>
                                        <?php if($message['starred']): ?>
                                            <i class="fa fa-star" style="color: #f39c12;"></i> Starred
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if($message['attachment']): ?>
                                <div class="message-meta">
                                    <strong>Attachment:</strong>
                                    <a href="../uploads/messages/<?= htmlspecialchars($message['attachment']); ?>" 
                                       class="attachment-item" target="_blank">
                                        <i class="fa fa-paperclip"></i> <?= htmlspecialchars($message['attachment']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="message-content">
                                <?= nl2br(htmlspecialchars($message['isi'])); ?>
                            </div>
                            
                            <div class="message-actions">
                                <a href="mailbox.php?folder=<?= $folder; ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> Back to <?= ucfirst($folder); ?>
                                </a>
                                
                                <?php if($folder != 'sent' && $folder != 'draft'): ?>
                                    <button id="reply-btn" class="btn btn-primary">
                                        <i class="fa fa-reply"></i> Reply
                                    </button>
                                <?php endif; ?>
                                
                                <button onclick="toggleStar(<?= $message['id_pesan']; ?>)" 
                                        class="btn btn-warning <?= $message['starred'] ? 'active' : ''; ?>">
                                    <i class="fa fa-star"></i> 
                                    <?= $message['starred'] ? 'Unstar' : 'Star'; ?>
                                </button>
                                
                                <button onclick="deleteMessage(<?= $message['id_pesan']; ?>)" class="btn btn-danger">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        
                        <!-- Reply Form (hidden by default) -->
                        <div id="reply-form" class="reply-form" style="display: none;">
                            <h4><i class="fa fa-reply"></i> Reply to this message</h4>
                            <form action="insert_pesan.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="current_user_id" value="<?= $current_user_id; ?>">
                                <input type="hidden" name="ke" value="<?= $message['dari']; ?>">
                                
                                <div class="form-group">
                                    <label>Subject:</label>
                                    <input type="text" name="subject" class="form-control" 
                                           value="Re: <?= htmlspecialchars($message['subject']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Message:</label>
                                    <textarea name="message" class="form-control" rows="5" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Attachment:</label>
                                    <input type="file" name="attachment" class="form-control">
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="send" class="btn btn-primary">
                                        <i class="fa fa-send"></i> Send Reply
                                    </button>
                                    <button type="button" id="cancel-reply" class="btn btn-default">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#reply-btn').click(function() {
                $('#reply-form').slideToggle();
            });
            
            $('#cancel-reply').click(function() {
                $('#reply-form').slideUp();
            });
        });

        function toggleStar(messageId) {
            $.post('toggle_star.php', {
                id: messageId,
                action: $('.btn-warning').hasClass('active') ? 'unstar' : 'star'
            }, function(response) {
                if(response.success) {
                    location.reload();
                }
            }, 'json');
        }

        function deleteMessage(messageId) {
            if(confirm('Are you sure you want to delete this message?')) {
                $.post('delete_message.php', {
                    id: messageId
                }, function(response) {
                    if(response.success) {
                        window.location.href = 'mailbox.php?folder=<?= $folder; ?>';
                    } else {
                        alert('Failed to delete message');
                    }
                }, 'json');
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>