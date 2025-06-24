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

// Handle compose with predefined recipient
$compose_recipient = null;
if (isset($_GET['compose']) && isset($_GET['to'])) {
    $recipient_id = (int)$_GET['to'];
    $stmt = $conn->prepare("SELECT nama FROM pegawai WHERE id_pegawai = ?");
    $stmt->bind_param("i", $recipient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $compose_recipient = $result->fetch_assoc();
        $compose_recipient['id'] = $recipient_id;
    }
    $stmt->close();
}

$username = $_SESSION['username'] ?? '';

// Fetch user profile data
$stmt = $conn->prepare("SELECT p.nama AS Nama, p.foto, p.Jabatan, p.Departemen, p.Tanggal_Masuk, p.id_pegawai FROM pegawai p JOIN authorization a ON a.id_pegawai = p.id_pegawai WHERE a.username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result_profil = $stmt->get_result();
$pegawai = $result_profil->fetch_assoc();
$stmt->close();

// Set display variables for header
$displayUsername = htmlspecialchars($pegawai['Nama'] ?? '');
$displayPegawaiFoto = htmlspecialchars($pegawai['foto'] ?? 'default.png');
$displayPegawaiNama = htmlspecialchars($pegawai['Nama'] ?? '');
$displayPegawaiJabatan = htmlspecialchars($pegawai['Jabatan'] ?? '');
$displayPegawaiDepartemen = htmlspecialchars($pegawai['Departemen'] ?? '');
$displayPegawaiTanggalMasuk = isset($pegawai['Tanggal_Masuk']) ? date('M Y', strtotime($pegawai['Tanggal_Masuk'])) : '';
$current_user_id = $pegawai['id_pegawai'] ?? 0;

// Fetch message counts
$unread_count = 0;
$draft_count = 0;

// Unread messages - fixed query to get current user's unread messages
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM pesan p WHERE p.ke = ? AND p.status = 0 AND p.draft = 0");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc();
$unread_count = $count['count'] ?? 0;
$stmt->close();

// Draft messages - fixed query
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM pesan p WHERE p.dari = ? AND p.draft = 1");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$count_draft = $result->fetch_assoc();
$draft_count = $count_draft['total'] ?? 0;
$stmt->close();

// Sent messages count - fixed query
$stmt = $conn->prepare("SELECT COUNT(*) as sent_count FROM pesan p WHERE p.dari = ? AND p.draft = 0");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$sent_count = $result->fetch_assoc()['sent_count'] ?? 0;
$stmt->close();

// Starred messages count
$stmt = $conn->prepare("SELECT COUNT(*) as starred_count FROM pesan p WHERE (p.dari = ? OR p.ke = ?) AND p.starred = 1 AND p.draft = 0");
$stmt->bind_param("ii", $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$starred_count = $result->fetch_assoc()['starred_count'] ?? 0;
$stmt->close();

// Fetch recipient names - exclude current user
$stmt = $conn->prepare("SELECT p.id_pegawai, p.nama FROM pegawai p JOIN authorization a ON a.id_pegawai = p.id_pegawai WHERE p.id_pegawai != ?");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$recipients = $stmt->get_result();
$stmt->close();

// Determine mailbox folder
$folder = isset($_GET['folder']) ? $_GET['folder'] : 'inbox';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Fetch messages based on folder - fixed queries
$sql = "";
$count_sql = "";
switch ($folder) {
    case 'draft':
        $sql = "SELECT p.id_pesan, pg.nama, p.subject, p.isi, DATE_FORMAT(p.waktu,'%d %b %Y %h:%i %p') as waktu, p.status, p.starred
                FROM pesan p 
                JOIN pegawai pg ON p.ke = pg.id_pegawai 
                WHERE p.dari = ? AND p.draft = 1
                order_id BY p.waktu DESC
                LIMIT ?, ?";
        
        $count_sql = "SELECT COUNT(*) as total 
                      FROM pesan p 
                      WHERE p.dari = ? AND p.draft = 1";
        break;
    case 'sent':
        $sql = "SELECT p.id_pesan, pg.nama, p.subject, p.isi, DATE_FORMAT(p.waktu,'%d %b %Y %h:%i %p') as waktu, p.status, p.starred
                FROM pesan p 
                JOIN pegawai pg ON p.ke = pg.id_pegawai 
                WHERE p.dari = ? AND p.draft = 0 AND p.status != 3
                order_id BY p.waktu DESC
                LIMIT ?, ?";
                
        $count_sql = "SELECT COUNT(*) as total 
                      FROM pesan p 
                      WHERE p.dari = ? AND p.draft = 0 AND p.status != 3";
        break;
    case 'starred':
        $sql = "SELECT p.id_pesan, 
                CASE 
                    WHEN p.dari = ? THEN pg_to.nama 
                    ELSE pg_from.nama 
                END as nama,
                p.subject, p.isi, DATE_FORMAT(p.waktu,'%d %b %Y %h:%i %p') as waktu, p.status, p.starred
                FROM pesan p 
                LEFT JOIN pegawai pg_from ON p.dari = pg_from.id_pegawai 
                LEFT JOIN pegawai pg_to ON p.ke = pg_to.id_pegawai
                WHERE (p.dari = ? OR p.ke = ?) AND p.starred = 1 AND p.draft = 0 AND p.status != 3
                order_id BY p.waktu DESC
                LIMIT ?, ?";
                
        $count_sql = "SELECT COUNT(*) as total 
                      FROM pesan p 
                      WHERE (p.dari = ? OR p.ke = ?) AND p.starred = 1 AND p.draft = 0 AND p.status != 3";
        break;
    default: // inbox
        $sql = "SELECT p.id_pesan, pg.nama, p.subject, p.isi, DATE_FORMAT(p.waktu,'%d %b %Y %h:%i %p') as waktu, p.status, p.starred
                FROM pesan p 
                JOIN pegawai pg ON p.dari = pg.id_pegawai 
                WHERE p.ke = ? AND p.draft = 0 AND p.status != 3
                order_id BY p.waktu DESC
                LIMIT ?, ?";
                
        $count_sql = "SELECT COUNT(*) as total 
                      FROM pesan p 
                      WHERE p.ke = ? AND p.draft = 0 AND p.status != 3";
        break;
}

// Get total messages for pagination
if ($folder == 'starred') {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param("ii", $current_user_id, $current_user_id);
} else {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param("i", $current_user_id);
}
$stmt->execute();
$total_result = $stmt->get_result();
$total_messages = $total_result->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Get paginated messages
if ($folder == 'starred') {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $current_user_id, $current_user_id, $current_user_id, $offset, $per_page);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $current_user_id, $offset, $per_page);
}
$stmt->execute();
$pesan = $stmt->get_result();
$stmt->close();

$total_pages = ceil($total_messages / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>U-PSN | Mailbox</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/ionicons.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
        }
        .dashboard-section {
            margin-bottom: 20px;
            border_id: 1px solid #e0e0e0;
            border_id-radius: 4px;
            padding: 15px;
            background: white;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }
        .dashboard-section h3 {
            margin-top: 0;
            border_id-bottom: 1px solid #eee;
            padding-bottom: 10px;
            color: #3c8dbc;
            font-size: 20px;
        }
        .info-box {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 15px;
            border_id-radius: 8px;
            color: white;
            min-height: 90px;
        }
        .info-box .info-box-icon {
            font-size: 45px;
            width: 70px;
            text-align: center;
            line-height: 70px;
            background: rgba(0,0,0,0.2);
            border_id-radius: 4px;
            padding: 10px;
        }
        .info-box .info-box-content {
            flex-grow: 1;
            padding-left: 15px;
        }
        .info-box .info-box-text {
            font-size: 16px;
            margin-bottom: 5px;
            opacity: 0.9;
        }
        .info-box .info-box-number {
            font-size: 24px;
            font-weight: bold;
        }
        .bg-aqua { background-color: #00c0ef !important; }
        .bg-green { background-color: #00a65a !important; }
        .bg-yellow { background-color: #f39c12 !important; }
        .bg-blue { background-color: #3c8dbc !important; }
        .bg-red { background-color: #dd4b39 !important; }
        
        .table-responsive {
            border_id-radius: 4px;
            overflow: hidden;
        }
        .table thead th {
            background-color: #3c8dbc;
            color: white;
            vertical-align: middle;
        }
        .btn-action {
            padding: 5px 10px;
            font-size: 12px;
            border_id-radius: 3px;
            margin: 2px;
            min-width: 70px;
        }
        .filter-controls {
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border_id-radius: 4px;
        }
        .filter-group {
            margin-right: 15px;
            display: inline-block;
            vertical-align: top;
        }
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: normal;
        }
        .form-control {
            height: 30px;
            padding: 3px 6px;
        }
        .table > tbody > tr > td {
            vertical-align: middle;
        }
        .input-group-addon {
            padding: 3px 6px;
            font-size: 12px;
        }
        .btn-filter {
            margin-top: 23px;
        }
        .message-item {
            padding: 10px;
            border_id-bottom: 1px solid #eee;
            transition: all 0.3s;
            cursor: pointer;
        }
        .message-item:hover {
            background-color: #f5f5f5;
        }
        .message-item.unread {
            background-color: #f0f8ff;
            font-weight: 500;
        }
        .message-sender {
            font-weight: 600;
            color: #333;
        }
        .message-preview {
            color: #666;
            font-size: 13px;
        }
        .message-time {
            color: #999;
            font-size: 12px;
        }
        .compose-form {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border_id-radius: 4px;
            border_id: 1px solid #ddd;
        }
        .compose-form .form-control {
            height: auto;
            padding: 8px 12px;
        }
        .attachment-preview {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #999;
        }
        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }
        .star-icon {
            cursor: pointer;
            color: #ddd;
        }
        .star-icon.starred {
            color: #f39c12;
        }
        .message-subject {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }
        .d-flex {
            display: flex;
        }
        .justify-content-between {
            justify-content: space-between;
        }
        .align-items-center {
            align-items: center;
        }
        .mb-3 {
            margin-bottom: 15px;
        }
        @media (max-width: 768px) {
            .filter-group {
                display: block;
                margin-bottom: 10px;
                margin-right: 0;
            }
            .btn-filter {
                margin-top: 0;
            }
            .d-flex {
                display: block;
            }
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

        <!-- Sidebar -->
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
                    <li>
                        <a href="index.php">
                            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="supplier.php">
                            <i class="fa fa-truck"></i> <span>Suppliers</span>
                        </a>
                    </li>
                    <li>
                        <a href="pemesanan.php">
                            <i class="fa fa-shopping-cart"></i> <span>order_ids</span>
                        </a>
                    </li>
                    <li>
                        <a href="transaksi.php">
                            <i class="fa fa-check-square"></i> <span>Purchase Approval</span>
                        </a>
                    </li>
                    <li>
                        <a href="received.php">
                            <i class="fa fa-check-circle"></i> <span>Received Items</span>
                        </a>
                    </li>
                    <li>
                        <a href="laporan.php">
                           <i class="fa fa-file-text"></i> <span>Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="cuti.php">
                            <i class="fa fa-calendar-times-o"></i> <span>Leave Requests</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="mailbox.php">
                            <i class="fa fa-envelope"></i> <span>Mailbox</span>
                            <?php if($unread_count > 0): ?>
                                <small class="badge pull-right bg-yellow"><?= $unread_count; ?></small>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>

        <!-- Content -->
        <div class="right-side">
            <section class="content-header">
                <h1>
                    Mailbox
                    <small>Internal messaging system</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="../index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Mailbox</li>
                </ol>
            </section>

            <section class="content">
                <!-- Notifications -->
                <?php if(isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissable">
                    <i class="fa fa-check"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissable">
                    <i class="fa fa-ban"></i>
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
                <?php endif; ?>
               
                
                <!-- Main Content -->
                <div class="row">
                    <div class="col-xs-12">
                        <div class="dashboard-section">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3>
                                    <i class="fa fa-envelope"></i> 
                                    <?= ucfirst($folder); ?>
                                    <?php if($folder == 'inbox' && $unread_count > 0): ?>
                                        <small class="badge bg-yellow"><?= $unread_count; ?> unread</small>
                                    <?php endif; ?>
                                </h3>
                                
                                <button id="compose-btn" class="btn btn-primary btn-sm">
                                    <i class="fa fa-pencil"></i> Compose
                                </button>
                            </div>
                            
                            <!-- Compose Form -->
                            <div id="compose-form" class="compose-form" style="<?= (isset($_GET['compose']) || $compose_recipient) ? 'display:block;' : 'display:none;' ?>">
                                <form action="insert_pesan.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="current_user_id" value="<?= $current_user_id; ?>">
                                    
                                    <div class="form-group">
                                        <label>To:</label>
                                        <select name="ke" class="form-control" required>
                                            <option value="">Select Recipient</option>
                                            <?php 
                                            mysqli_data_seek($recipients, 0);
                                            while($recipient = $recipients->fetch_assoc()): 
                                            ?>
                                                <option value="<?= $recipient['id_pegawai']; ?>" 
                                                    <?= ($compose_recipient && $compose_recipient['id'] == $recipient['id_pegawai']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($recipient['nama']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Subject:</label>
                                        <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Message:</label>
                                        <textarea name="message" class="form-control" rows="5" placeholder="Write your message here..." required></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Attachment:</label>
                                        <input type="file" name="attachment" class="form-control">
                                        <p class="help-block">Max. 32MB</p>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="submit" name="send" class="btn btn-primary">
                                            <i class="fa fa-send"></i> Send
                                        </button>
                                        <button type="submit" name="draft" class="btn btn-default">
                                            <i class="fa fa-save"></i> Save Draft
                                        </button>
                                        <button type="button" id="cancel-compose" class="btn btn-danger pull-right">
                                            <i class="fa fa-times"></i> Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Folder Navigation -->
                            <div class="filter-controls">
                                <div class="filter-group">
                                    <a href="mailbox.php?folder=inbox" class="btn btn-sm <?= ($folder=='inbox')?'btn-primary':'btn-default'; ?>">
                                        <i class="fa fa-inbox"></i> Inbox
                                        <?php if($unread_count > 0): ?>
                                            <span class="badge"><?= $unread_count; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="filter-group">
                                    <a href="mailbox.php?folder=sent" class="btn btn-sm <?= ($folder=='sent')?'btn-primary':'btn-default'; ?>">
                                        <i class="fa fa-paper-plane"></i> Sent
                                        <?php if($sent_count > 0): ?>
                                            <span class="badge"><?= $sent_count; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="filter-group">
                                    <a href="mailbox.php?folder=draft" class="btn btn-sm <?= ($folder=='draft')?'btn-primary':'btn-default'; ?>">
                                        <i class="fa fa-file-text"></i> Drafts
                                        <?php if($draft_count > 0): ?>
                                            <span class="badge"><?= $draft_count; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="filter-group">
                                    <a href="mailbox.php?folder=starred" class="btn btn-sm <?= ($folder=='starred')?'btn-primary':'btn-default'; ?>">
                                        <i class="fa fa-star"></i> Starred
                                        <?php if($starred_count > 0): ?>
                                            <span class="badge"><?= $starred_count; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Messages List -->
                            <div class="table-responsive">
                                <?php if($pesan->num_rows > 0): ?>
                                    <table class="table table-hover">
                                        <tbody>
                                            <?php while($message = $pesan->fetch_assoc()): ?>
                                                <tr class="message-item <?= ($message['status']==0 && $folder=='inbox')?'unread':''; ?>" 
                                                    onclick="window.location.href='isi.php?id=<?= $message['id_pesan']; ?>&folder=<?= $folder; ?>'">
                                                    <td width="40">
                                                        <i class="fa fa-star star-icon <?= ($message['starred']==1)?'starred':''; ?>"                                                        onclick="event.stopPropagation(); toggleStar(<?= $message['id_pesan']; ?>, this)"></i>
                                                    </td>
                                                    <td>
                                                        <div class="message-subject">
                                                            <?= htmlspecialchars($message['subject'] ?? '(No Subject)'); ?>
                                                        </div>
                                                        <div class="message-sender">
                                                            <?= htmlspecialchars($message['nama']); ?>
                                                        </div>
                                                        <div class="message-preview">
                                                            <?= htmlspecialchars(substr($message['isi'], 0, 100)); ?>
                                                            <?= (strlen($message['isi']) > 100) ? '...' : ''; ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="message-time">
                                                            <?= $message['waktu']; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                    
                                    <!-- Pagination -->
                                    <?php if($total_pages > 1): ?>
                                        <div class="text-center">
                                            <ul class="pagination pagination-sm no-margin">
                                                <?php if($page > 1): ?>
                                                    <li><a href="mailbox.php?folder=<?= $folder; ?>&page=<?= $page-1; ?>">&laquo;</a></li>
                                                <?php endif; ?>
                                                
                                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                                    <li class="<?= ($i == $page) ? 'active' : ''; ?>">
                                                        <a href="mailbox.php?folder=<?= $folder; ?>&page=<?= $i; ?>"><?= $i; ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                                
                                                <?php if($page < $total_pages): ?>
                                                    <li><a href="mailbox.php?folder=<?= $folder; ?>&page=<?= $page+1; ?>">&raquo;</a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fa fa-envelope-o fa-4x"></i>
                                        <h4>No messages found</h4>
                                        <p>Your <?= $folder; ?> is empty</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <footer class="main-footer">
            <div class="pull-right hidden-xs">
                <b>Version</b> 1.0.0
            </div>
            <strong>Copyright &copy; <?= date('Y'); ?> <a href="#">U-PSN</a>.</strong> All rights reserved.
        </footer>
    </div>

    <!-- JavaScript -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle compose form
            $('#compose-btn').click(function(e) {
                e.preventDefault();
                $('#compose-form').slideToggle();
            });
            
            $('#cancel-compose').click(function() {
                $('#compose-form').slideUp();
            });
        });

        function toggleStar(messageId, element) {
            const isStarred = $(element).hasClass('starred');
            const action = isStarred ? 'unstar' : 'star';
            
            $.post('toggle_star.php', {
                id: messageId,
                action: action
            }, function(response) {
                if(response.success) {
                    $(element).toggleClass('starred');
                    
                    // Update the star count in the UI
                    const starCountElement = $('.info-box.bg-red .info-box-number');
                    let currentCount = parseInt(starCountElement.text());
                    if(isStarred) {
                        starCountElement.text(currentCount - 1);
                    } else {
                        starCountElement.text(currentCount + 1);
                    }
                }
            }, 'json');
        }
    </script>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>