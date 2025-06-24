<?php
session_start();
include "../config.php";

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

// Get all contacts (pegawai) except current user
$current_user = $_SESSION['idpegawai'];
$query = "SELECT p.id_pegawai, p.nama, p.Jabatan, p.Departemen, p.foto 
          FROM pegawai p 
          WHERE p.id_pegawai != ? 
          order_id BY p.nama";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_user);
$stmt->execute();
$contacts = $stmt->get_result();
$stmt->close();

// Get suppliers if needed
$suppliers = [];
$query = "SELECT id_supplier, nama_perusahaan, kontak_person FROM supplier order_id BY nama_perusahaan";
$stmt = $conn->prepare($query);
$stmt->execute();
$suppliers = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contacts | E-pharm</title>
    <?php include "../includes/head.php"; ?>
    <style>
        .contact-card {
            border_id: 1px solid #ddd;
            border_id-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .contact-card:hover {
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        .contact-avatar {
            width: 80px;
            height: 80px;
            border_id-radius: 50%;
            object-fit: cover;
        }
        .tab-content {
            padding: 20px 0;
        }
    </style>
</head>
<body class="skin-blue">
    <?php include "../includes/header.php"; ?>
    
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include "../includes/sidebar.php"; ?>
        
        <aside class="right-side">
            <section class="content-header">
                <h1>Contacts</h1>
                <ol class="breadcrumb">
                    <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Contacts</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-header with-border_id">
                                <h3 class="box-title">All Contacts</h3>
                                <div class="box-tools pull-right">
                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#newContactModal">
                                        <i class="fa fa-plus"></i> Add Contact
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#employees" data-toggle="tab">Employees</a></li>
                                    <li><a href="#suppliers" data-toggle="tab">Suppliers</a></li>
                                </ul>
                                
                                <div class="tab-content">
                                    <div class="tab-pane active" id="employees">
                                        <div class="row">
                                            <?php while($contact = $contacts->fetch_assoc()): ?>
                                            <div class="col-md-4">
                                                <div class="contact-card">
                                                    <div class="media">
                                                        <div class="media-left">
                                                            <img src="../img/<?= htmlspecialchars($contact['foto']); ?>" class="contact-avatar" alt="User Image">
                                                        </div>
                                                        <div class="media-body">
                                                            <h4 class="media-heading"><?= htmlspecialchars($contact['nama']); ?></h4>
                                                            <p><?= htmlspecialchars($contact['Jabatan']); ?></p>
                                                            <p><small><?= htmlspecialchars($contact['Departemen']); ?></small></p>
                                                            <a href="mailbox.php?compose=1&to=<?= $contact['id_pegawai']; ?>" class="btn btn-xs btn-primary">
                                                                <i class="fa fa-envelope"></i> Send Message
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="tab-pane" id="suppliers">
                                        <div class="row">
                                            <?php while($supplier = $suppliers->fetch_assoc()): ?>
                                            <div class="col-md-4">
                                                <div class="contact-card">
                                                    <div class="media">
                                                        <div class="media-left">
                                                            <img src="../img/supplier-icon.png" class="contact-avatar" alt="Supplier">
                                                        </div>
                                                        <div class="media-body">
                                                            <h4 class="media-heading"><?= htmlspecialchars($supplier['nama_perusahaan']); ?></h4>
                                                            <p><?= htmlspecialchars($supplier['kontak_person']); ?></p>
                                                            <a href="mailbox.php?compose=1&supplier=<?= $supplier['id_supplier']; ?>" class="btn btn-xs btn-primary">
                                                                <i class="fa fa-envelope"></i> Contact Supplier
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <!-- New Contact Modal -->
    <div class="modal fade" id="newContactModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Add New Contact</h4>
                </div>
                <form action="add_contact.php" method="post">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Contact Type</label>
                            <select name="contact_type" class="form-control" required>
                                <option value="employee">Employee</option>
                                <option value="supplier">Supplier</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Contact</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include "../includes/footer.php"; ?>
    <?php include "../includes/scripts.php"; ?>
</body>
</html>