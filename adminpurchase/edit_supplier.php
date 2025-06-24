<?php
session_start();
require_once('../konekdb.php'); // Pastikan path ini benar

// Redirect jika user belum login atau tidak memiliki ID pegawai
if (!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    $_SESSION['error'] = "You are not logged in.";
    header("location:../index.php");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Mengambil detail pegawai untuk sidebar
$pegawai = [];
// Perbaikan: Pastikan nama kolom 'id_pegawai' sesuai dengan skema database Anda
$stmt_pegawai = $mysqli->prepare("SELECT Nama, Jabatan, Departemen, Tanggal_Masuk, foto FROM pegawai WHERE id_pegawai = ?");
if ($stmt_pegawai) {
    $stmt_pegawai->bind_param("i", $idpegawai);
    $stmt_pegawai->execute();
    $result_pegawai = $stmt_pegawai->get_result();
    if ($result_pegawai->num_rows > 0) {
        $pegawai = $result_pegawai->fetch_assoc();
    }
    $stmt_pegawai->close();
} else {
    error_log("Error preparing statement for employee details in edit_supplier.php: " . $mysqli->error);
}

// Memeriksa otorisasi pengguna untuk modul 'Purchase'
$authorized = false;
$stmt_cekuser = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Purchase'");
if ($stmt_cekuser) {
    $stmt_cekuser->bind_param("s", $username);
    $stmt_cekuser->execute();
    $result_cekuser = $stmt_cekuser->get_result();
    $user_auth = $result_cekuser->fetch_assoc();
    if ($user_auth['jmluser'] > 0) {
        $authorized = true;
    }
    $stmt_cekuser->close();
} else {
    error_log("Error preparing statement for user authorization in edit_supplier.php: " . $mysqli->error);
    $_SESSION['error'] = "An unexpected error occurred during authorization check.";
    header("location:supplier.php");
    exit();
}

// Redirect jika tidak diotorisasi
if (!$authorized) {
    $_SESSION['error'] = "You are not authorized to edit suppliers.";
    header("location:supplier.php");
    exit();
}

$message = ''; // Inisialisasi variabel pesan
$supplier = null; // Inisialisasi data supplier

// Menangani permintaan GET untuk mengambil data supplier yang akan diedit
if (isset($_GET['id'])) {
    $supplierId = $_GET['id'];

    $stmt_fetch = $mysqli->prepare("SELECT id_supplier, Nama, Alamat, Telepon, Nama_perusahaan, Produk FROM supplier WHERE id_supplier = ?");
    if ($stmt_fetch) {
        // Asumsi id_supplier adalah string (varchar)
        // JIKA id_supplier di DB Anda adalah INT, ganti "s" menjadi "i"
        $stmt_fetch->bind_param("s", $supplierId);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        if ($result_fetch->num_rows > 0) {
            $supplier = $result_fetch->fetch_assoc();
        } else {
            $_SESSION['error'] = "Supplier not found.";
            header("location:supplier.php");
            exit();
        }
        $stmt_fetch->close();
    } else {
        $_SESSION['error'] = "Error preparing to fetch supplier data: " . htmlspecialchars($mysqli->error);
        header("location:supplier.php");
        exit();
    }
}
// Menangani permintaan POST untuk memperbarui data supplier
else if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_supplier_submit'])) {
    $supplierId = $_POST['id_supplier'] ?? ''; // ID asli supplier (tidak dapat diedit)
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $telepon = filter_var($_POST['telepon'] ?? '', FILTER_SANITIZE_NUMBER_INT); // Sanitize dan pastikan integer
    $nama_perusahaan = $_POST['nama_perusahaan'] ?? '';
    $produk = $_POST['produk'] ?? '';

    // Isi ulang variabel $supplier dengan data POST untuk form "sticky" jika validasi gagal
    $supplier = [
        'id_supplier' => $supplierId,
        'Nama' => $nama,
        'Alamat' => $alamat,
        'Telepon' => $telepon,
        'Nama_perusahaan' => $nama_perusahaan,
        'Produk' => $produk
    ];

    // Validasi input
    if (empty($supplierId) || empty($nama) || empty($alamat) || empty($telepon) || empty($nama_perusahaan) || empty($produk)) {
        $message = "<div class='alert alert-danger'>All fields are required!</div>";
    } else {
        // Siapkan pernyataan UPDATE
        // PASTIKAN TIPE PARAMETER SESUAI DENGAN TIPE KOLOM DI DATABASE ANDA
        // "ssisss" berarti: string, string, integer, string, string, string
        // JIKA 'Telepon' adalah VARCHAR di DB, ganti 'i' menjadi 's'.
        // JIKA 'id_supplier' adalah INT di DB, ganti 's' terakhir menjadi 'i'.
        $stmt_update = $mysqli->prepare("UPDATE supplier SET Nama = ?, Alamat = ?, Telepon = ?, Nama_perusahaan = ?, Produk = ? WHERE id_supplier = ?");
        if ($stmt_update) {
            // Urutan parameter harus cocok dengan urutan '?' di query
            $stmt_update->bind_param("ssisss", $nama, $alamat, $telepon, $nama_perusahaan, $produk, $supplierId);
            
            if ($stmt_update->execute()) {
                $_SESSION['success'] = "Supplier updated successfully!";
                header("location:supplier.php"); // Redirect ke daftar supplier setelah update berhasil
                exit();
            } else {
                $message = "<div class='alert alert-danger'>Error updating supplier: " . htmlspecialchars($stmt_update->error) . "</div>";
                error_log("Failed to update supplier ID " . $supplierId . ": " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            $message = "<div class='alert alert-danger'>Error preparing update statement: " . htmlspecialchars($mysqli->error) . "</div>";
            error_log("Failed to prepare update statement for supplier: " . $mysqli->error);
        }
    }
} else {
    // Blok ini berjalan jika tidak ada ID yang diberikan di GET atau POST, atau jika permintaan POST bukan untuk update_supplier_submit
    if (!isset($_GET['id'])) {
        $_SESSION['error'] = "Invalid request. No supplier ID provided for editing.";
        header("location:supplier.php");
        exit();
    }
}

// Mengambil jumlah untuk badge sidebar (placeholder, implementasikan query aktual jika diperlukan)
$tot1 = ['count_pemesanan1' => 0];
$tot2 = ['jml' => 0];
$tot3 = ['jml' => 0];
$tot5 = ['jml' => 0];

// Mengambil jumlah supplier untuk sidebar (jika diperlukan)
$totSupplier = ['jml' => 0];
$stmt_count_supplier = $mysqli->prepare("SELECT COUNT(*) AS jml FROM supplier");
if ($stmt_count_supplier) {
    $stmt_count_supplier->execute();
    $result_count_supplier = $stmt_count_supplier->get_result();
    $totSupplier = $result_count_supplier->fetch_assoc();
    $stmt_count_supplier->close();
} else {
    error_log("Error counting suppliers for sidebar in edit_supplier.php: " . $mysqli->error);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>E-pharm | Edit Supplier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/ionicons.min.css" rel="stylesheet">
    <link href="../css/datatables/dataTables.bootstrap.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
</head>
<body class="skin-blue">
    <header class="header">
        <a href="../index.html" class="logo">E-pharm</a>
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
                            <span><?= htmlspecialchars($_SESSION['username']); ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="user-header bg-light-blue">
                                <img src="../img/<?= htmlspecialchars($pegawai['foto'] ?? 'default.jpg'); ?>" class="img-circle" alt="User Image">
                                <p>
                                    <?= htmlspecialchars($pegawai['Nama'] ?? 'N/A') . " - " . htmlspecialchars($pegawai['Jabatan'] ?? 'N/A') . " " . htmlspecialchars($pegawai['Departemen'] ?? 'N/A'); ?>
                                    <small>Member since <?= htmlspecialchars($pegawai['Tanggal_Masuk'] ?? 'N/A'); ?></small>
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
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <aside class="left-side sidebar-offcanvas">
            <section class="sidebar">
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="../img/<?php echo htmlspecialchars($pegawai['foto'] ?? 'default.jpg'); ?>" class="img-circle" alt="User Image" />
                    </div>
                    <div class="pull-left info">
                        <p>Hello, <?php echo htmlspecialchars($username); ?></p>
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
                        <a href="pemesanan.php">
                            <i class="fa fa-shopping-cart"></i> <span>order_ids</span>
                            <?php if(isset($tot1['count_pemesanan1']) && $tot1['count_pemesanan1'] != 0){ ?>
                            <small class="badge pull-right"><?php echo htmlspecialchars($tot1['count_pemesanan1']); ?></small>
                            <?php }?>
                        </a>
                    </li>
                    <li>
                        <a href="transaksi.php">
                            <i class="fa fa-check-square"></i> <span>Transaction Approval</span>
                            <?php if(isset($tot2['jml']) && $tot2['jml'] != 0){ ?>
                            <small class="badge pull-right"><?php echo htmlspecialchars($tot2['jml']); ?></small>
                            <?php } ?>
                        </a>
                    </li>
                    <li class="active">
                        <a href="supplier.php">
                            <i class="fa fa-truck"></i> <span>Supplier</span>
                            <?php if(isset($totSupplier['jml']) && $totSupplier['jml'] != 0){ ?>
                            <small class="badge pull-right"><?php echo htmlspecialchars($totSupplier['jml']); ?></small>
                            <?php } ?>
                        </a>
                    </li>
                    <li>
                        <a href="laporan.php">
                            <i class="fa fa-file-text"></i> <span>Reports</span>
                            <?php if(isset($tot3['jml']) && $tot3['jml'] != 0){ ?>
                            <small class="badge pull-right"><?php echo htmlspecialchars($tot3['jml']); ?></small>
                            <?php } ?>
                        </a>
                    </li>
                    <li>
                        <a href="mailbox.php">
                            <i class="fa fa-envelope"></i> <span>Mailbox</span>
                            <?php if(isset($tot5['jml']) && $tot5['jml'] != 0){ ?>
                            <small class="badge pull-right"><?php echo htmlspecialchars($tot5['jml']); ?></small>
                            <?php } ?>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>
        <aside class="right-side">
            <section class="content-header">
                <h1>Edit Supplier</h1>
            </section>
            <section class="content">
                <?php
                // Tampilkan pesan sukses atau error dari sesi atau operasi saat ini
                if (isset($_SESSION['success'])) {
                    echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
                    unset($_SESSION['success']);
                }
                if (isset($_SESSION['error'])) {
                    echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
                    unset($_SESSION['error']);
                }
                if (!empty($message)) echo $message;
                ?>

                <?php if ($supplier): ?>
                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_supplier">Supplier ID</label>
                                <input type="text" class="form-control" id="id_supplier" name="id_supplier" value="<?= htmlspecialchars($supplier['id_supplier']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama">Supplier Name</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($supplier['Nama']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="alamat">Address</label>
                                <input type="text" class="form-control" id="alamat" name="alamat" value="<?= htmlspecialchars($supplier['Alamat']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telepon">Phone Number</label>
                                <input type="number" class="form-control" id="telepon" name="telepon" value="<?= htmlspecialchars($supplier['Telepon']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_perusahaan">Company Name</label>
                                <input type="text" class="form-control" id="nama_perusahaan" name="nama_perusahaan" value="<?= htmlspecialchars($supplier['Nama_perusahaan']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="produk">Product</label>
                                <input type="text" class="form-control" id="produk" name="produk" value="<?= htmlspecialchars($supplier['Produk']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary" name="update_supplier_submit">Update Supplier</button>
                            <a href="supplier.php" class="btn btn-default">Cancel</a>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                    <div class="alert alert-warning">No supplier data found to edit.</div>
                <?php endif; ?>
            </section>
        </aside>
    </div>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/plugins/datatables/jquery.dataTables.js"></script>
    <script src="../js/plugins/datatables/dataTables.bootstrap.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
</body>
</html>