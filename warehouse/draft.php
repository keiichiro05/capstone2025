<?php 
session_start();

// Enhanced session validation
if(!isset($_SESSION['username']) || !isset($_SESSION['idpegawai']) || empty($_SESSION['username']) || empty($_SESSION['idpegawai'])) {
    header("location:../index.php?status=please login first");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

include "../config.php";

// Use existing connection instead of creating new one
$mysqli = $conn;

// Fetch user profile
$profil_query = $mysqli->prepare("SELECT p.*, DATE_FORMAT(p.Tanggal_Masuk, '%b, %Y') as tglmasuk 
                                 FROM pegawai p, authorization a 
                                 WHERE a.username = ? AND a.id_pegawai = p.id_pegawai");
if (!$profil_query) {
    die("Prepare failed: " . $mysqli->error);
}

$profil_query->bind_param("s", $username);
if (!$profil_query->execute()) {
    die("Execute failed: " . $profil_query->error);
}

$profil_result = $profil_query->get_result();
if (!$profil_result) {
    die("Get result failed: " . $profil_query->error);
}

$profil = $profil_result->fetch_assoc();
$profil_query->close();

if (!$profil) {
    die("User profile not found");
}

// Fetch draft messages
$pesan_query = $mysqli->prepare("SELECT id_pesan, pg.nama, isi, DATE_FORMAT(waktu, '%d %b %Y %h:%i %p') as waktu, p.status, a.username
                                FROM pesan p, pegawai pg, authorization a
                                WHERE p.ke = pg.id_pegawai AND a.id_pegawai = p.dari AND a.username = ? AND p.draft = 1
                                order_id BY waktu DESC");
if (!$pesan_query) {
    die("Prepare failed: " . $mysqli->error);
}

$pesan_query->bind_param("s", $username);
if (!$pesan_query->execute()) {
    die("Execute failed: " . $pesan_query->error);
}

$pesan = $pesan_query->get_result();
if (!$pesan) {
    die("Get result failed: " . $pesan_query->error);
}

// Count unread messages
$count_query = $mysqli->prepare("SELECT COUNT(*) as count 
                                FROM pesan p, authorization a
                                WHERE a.id_pegawai = p.ke AND a.username = ? AND p.status = 0");
if (!$count_query) {
    die("Prepare failed: " . $mysqli->error);
}

$count_query->bind_param("s", $username);
if (!$count_query->execute()) {
    die("Execute failed: " . $count_query->error);
}

$count_result = $count_query->get_result();
$count = $count_result ? $count_result->fetch_assoc() : ['count' => 0];
$count_query->close();

// Count drafts
$count_draft_query = $mysqli->prepare("SELECT COUNT(*) as count 
                                      FROM pesan p, authorization a
                                      WHERE a.id_pegawai = p.dari AND a.username = ? AND p.draft = 1");
if (!$count_draft_query) {
    die("Prepare failed: " . $mysqli->error);
}

$count_draft_query->bind_param("s", $username);
if (!$count_draft_query->execute()) {
    die("Execute failed: " . $count_draft_query->error);
}

$count_draft_result = $count_draft_query->get_result();
$count_draft = $count_draft_result ? $count_draft_result->fetch_assoc() : ['count' => 0];
$count_draft_query->close();

// Fetch other users' names
$name_query = $mysqli->prepare("SELECT nama 
                               FROM pegawai p, authorization a 
                               WHERE a.id_pegawai = p.id_pegawai AND a.username != ?");
if (!$name_query) {
    die("Prepare failed: " . $mysqli->error);
}

$name_query->bind_param("s", $username);
if (!$name_query->execute()) {
    die("Execute failed: " . $name_query->error);
}

$name = $name_query->get_result();
if (!$name) {
    die("Get result failed: " . $name_query->error);
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Drafts | E-pharm</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <link href="../css/font-awesome.min.css" rel="stylesheet">
        <link href="../css/AdminLTE.css" rel="stylesheet">
    </head>
    <body class="skin-blue">
        <!-- [Rest of your HTML remains the same until the form] -->

        <!-- COMPOSE MESSAGE MODAL -->
        <div class="modal fade" id="compose-modal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><i class="fa fa-envelope-o"></i> Compose New Message</h4>
                    </div>
                    <form action="insert_pesan.php" method="post" id="form_pesan" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="input-group">
                                    <input name="username" type="hidden" value="<?= htmlspecialchars($username) ?>">
                                    <span class="input-group-addon">TO:</span>
                                    <select name="nama" class="form-control" required>
                                        <?php while($n = $name->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($n['nama']) ?>"><?= htmlspecialchars($n['nama']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <textarea name="message" class="form-control" placeholder="Message" style="height:120px;" required></textarea>
                            </div>
                            <div class="form-group">
                                <div class="btn btn-success btn-file">
                                    <i class="fa fa-paperclip"></i> Attachment
                                    <input type="file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                </div>
                                <p class="help-block">Max. 32MB</p>
                            </div>
                        </div>
                        <div class="modal-footer clearfix">
                            <button type="submit" name="draft" class="btn btn-danger" onclick="return draftsubmit();">
                                <i class="fa fa-times"></i> Send to Draft
                            </button>
                            <button type="submit" name="send" class="btn btn-primary pull-left">
                                <i class="fa fa-envelope"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/AdminLTE.min.js"></script>
        <script>
            function draftsubmit() {
                if(confirm("Pesan akan disimpan ke dalam draft?")) {
                    document.getElementById("form_pesan").submit();
                    return true;
                }
                return false;
            }
            
            $('#check-all').click(function() {
                $('table.table-mailbox input[type="checkbox"]').prop('checked', this.checked);
            });
        </script>
    </body>
</html>
<?php
// Close database resources
$name_query->close();
$pesan_query->close();
// Don't close $mysqli here since it's from config.php which might be used elsewhere
?>