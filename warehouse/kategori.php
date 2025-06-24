<?php
session_start();
include('../konekdb.php');

// Cek login
if (!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    header("Location: ../index.php");
    exit();
}
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Cek otorisasi modul
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Warehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($jmluser);
$stmt->fetch();
$stmt->close();
if ($jmluser == 0) {
    header("Location: ../index.php");
    exit();
}

// Ambil data pegawai
$stmt = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmt->bind_param("s", $idpegawai);
$stmt->execute();
$result = $stmt->get_result();
$pegawai = $result->fetch_assoc();
$stmt->close();

// Tambah kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama_kategori'])) {
    $nama_kategori = trim($_POST['nama_kategori']);
    if ($nama_kategori !== '') {
        $stmt = $mysqli->prepare("SELECT id FROM kategori WHERE nama_kategori = ?");
        $stmt->bind_param("s", $nama_kategori);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $_SESSION['message'] = "<div class='alert alert-danger'>Category already exists!</div>";
        } else {
            $stmt->close();
            $stmt = $mysqli->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            $stmt->bind_param("s", $nama_kategori);
            if ($stmt->execute()) {
                $_SESSION['message'] = "<div class='alert alert-success'>New category added!</div>";
            } else {
                $_SESSION['message'] = "<div class='alert alert-danger'>Failed to add category.</div>";
            }
        }
        $stmt->close();
    }
    header("Location: product.php?tab=category");
    exit();
}

// Stock Management POST handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['nama']) || isset($_POST['edit_id']) || isset($_POST['delete_id']))) {
    // Tambah/Update/Hapus item
    $nama = trim($_POST['nama'] ?? '');
    $stok = intval($_POST['stok'] ?? 0);
    $kategori = trim($_POST['kategori'] ?? '');
    $edit_id = intval($_POST['edit_id'] ?? 0);
    $delete_id = intval($_POST['delete_id'] ?? 0);

    if ($delete_id > 0) {
        $stmt = $mysqli->prepare("DELETE FROM warehouse WHERE id_barang=?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "<div class='alert alert-success'>Item deleted successfully!</div>";
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger'>Error deleting item.</div>";
        }
        $stmt->close();
    } elseif ($edit_id > 0 && $stok > 0) {
        $stmt = $mysqli->prepare("UPDATE warehouse SET Stok=? WHERE id_barang=?");
        $stmt->bind_param("ii", $stok, $edit_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "<div class='alert alert-success'>Stock updated successfully!</div>";
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger'>Error updating stock.</div>";
        }
        $stmt->close();
    } elseif ($nama !== '' && $stok > 0 && $kategori !== '') {
        // Cek apakah item sudah ada
        $stmt = $mysqli->prepare("SELECT id_barang FROM warehouse WHERE Nama=?");
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $stmt = $mysqli->prepare("UPDATE warehouse SET Stok=?, Kategori=? WHERE Nama=?");
            $stmt->bind_param("iss", $stok, $kategori, $nama);
            if ($stmt->execute()) {
                $_SESSION['message'] = "<div class='alert alert-success'>Item updated successfully!</div>";
            } else {
                $_SESSION['message'] = "<div class='alert alert-danger'>Error updating item.</div>";
            }
        } else {
            $stmt->close();
            $stmt = $mysqli->prepare("INSERT INTO warehouse (Nama, Stok, Kategori) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $nama, $stok, $kategori);
            if ($stmt->execute()) {
                $_SESSION['message'] = "<div class='alert alert-success'>New item added successfully!</div>";
            } else {
                $_SESSION['message'] = "<div class='alert alert-danger'>Error adding item.</div>";
            }
        }
        $stmt->close();
    }
    header("Location: product.php?tab=stock");
    exit();
}

// Ambil semua kategori untuk dropdown
$kategori_list = [];
$res = $mysqli->query("SELECT nama_kategori FROM kategori order_id BY nama_kategori ASC");
while ($row = $res->fetch_assoc()) {
    $kategori_list[] = $row['nama_kategori'];
}

// Determine active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'category';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kategori - Warehouse</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
    <style>
        .tab-content { padding: 20px 0; }
        .nav-tabs { margin-bottom: 0; }
        .btn-action { padding: 5px 10px; font-size: 12px; }
        .btn-edit { background-color: #f0ad4e; border_id-color: #eea236; color: white; }
        .btn-delete { background-color: #d9534f; border_id-color: #d43f3a; color: white; }
        .input-group { width: auto; display: inline-flex; }
    </style>
</head>
<body class="skin-blue">
<header class="header">
    <a href="index.php" class="logo">PSN</a>
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
                        <span><?php echo htmlspecialchars($username); ?><i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
                            <p>
                                <?php echo htmlspecialchars($pegawai['Nama']) . " - " . htmlspecialchars($pegawai['Jabatan']) . " " . htmlspecialchars($pegawai['Departemen']); ?>
                                <small>Member since <?php echo htmlspecialchars($pegawai['Tanggal_Masuk']); ?></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="logout.php" class="btn btn-default btn-flat">Sign out</a>
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
                    <img src="img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
                </div>
                <div class="pull-left info">
                    <p>Hello, <?php echo htmlspecialchars($username); ?></p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li class="active"><a href="product.php"><i class="fa fa-list-alt"></i> <span>Products</span></a></li>
                 <li class="treeview">
                            <a href="new_request.php">
                                <i class="fa fa-th"></i> <span>Request</span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="history_request.php"><i class="fa fa-archive"></i>Request History</a></li>
                            </ul>
                        </li>
                        <li>
                <li><a href="mailbox.php"><i class="fa fa-comments"></i> <span>Mailbox</span></a></li>
            </ul>
        </section>
    </aside>
    <aside class="right-side">
        <section class="content-header">
            <h1>Warehouse Management</h1>
        </section>
        <section class="content">
            <ul class="nav nav-tabs">
                <li class="<?php echo $active_tab == 'category' ? 'active' : ''; ?>">
                    <a href="product.php?tab=category" data-toggle="tab">Category</a>
                </li>
                <li class="<?php echo $active_tab == 'stock' ? 'active' : ''; ?>">
                    <a href="product.php?tab=stock" data-toggle="tab">Stock</a>
                </li>
            </ul>
            <div class="tab-content">
                <!-- Category Tab -->
                <div class="tab-pane <?php echo $active_tab == 'category' ? 'active' : ''; ?>" id="category">
                    <?php
                    if (isset($_SESSION['message'])) {
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                    }
                    ?>
                    <h3>Add Category</h3>
                    <form method="post" class="form-inline">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="Category Name" name="nama_kategori" required>
                            </div>
                            <div class="col-md-2">
                                <input type="submit" class="btn btn-primary" value="Add">
                            </div>
                        </div>
                    </form>
                    <h3>Category List</h3>
                    <div class="table-responsive">
                        <table class="table table-border_ided table-striped">
                            <tr>
                                <th>ID</th>
                                <th>Category Name</th>
                                <th>Action</th>
                            </tr>
                            <?php
                            $sql = "SELECT * FROM kategori order_id BY nama_kategori ASC";
                            $hasil = $mysqli->query($sql);
                            while ($baris = $hasil->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($baris['id']) . "</td>
                                    <td>" . htmlspecialchars($baris['nama_kategori']) . "</td>
                                    <td>
                                        <a href='edit_product.php?id=" . htmlspecialchars($baris['id']) . "' class='btn btn-warning'>Edit</a>
                                        <a href='hapus_product.php?id=" . htmlspecialchars($baris['id']) . "' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this category?\")'>Delete</a>
                                    </td>
                                </tr>";
                            }   
                            ?>
                        </table>
                    </div>
                </div>
                <!-- Stock Management Tab -->
                <div class="tab-pane <?php echo $active_tab == 'stock' ? 'active' : ''; ?>" id="stock">
                    <?php
                    if (isset($_SESSION['message'])) {
                        echo $_SESSION['message'];
                        unset($_SESSION['message']);
                    }
                    ?>
                    <div class="form-container">
                        <h3>Add/Update Item</h3>
                        <form method="post">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Item Name</label>
                                        <input type="text" class="form-control" placeholder="Enter item name..." name="nama" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select class="form-control" name="kategori" required>
                                            <option value="">-- Select Category --</option>
                                            <?php foreach ($kategori_list as $kat): ?>
                                                <option value="<?php echo htmlspecialchars($kat); ?>"><?php echo htmlspecialchars($kat); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Quantity</label>
                                        <input type="number" class="form-control" placeholder="Enter quantity..." name="stok" min="1" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-container">
                        <?php
                        $sql = "SELECT * FROM warehouse order_id BY id_barang DESC";
                        $hasil = $mysqli->query($sql);
                        if ($hasil) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-border_ided table-striped">';
                            echo '<thead><tr><th>ID</th><th>Item Name</th><th>Stock</th><th>Category</th><th>Warehouse</th><th>Actions</th></tr></thead>';
                            echo '<tbody>';
                            while ($baris = $hasil->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($baris['id_barang']) . "</td>
                                    <td>" . htmlspecialchars($baris['Nama']) . "</td>
                                    <td>" . htmlspecialchars($baris['Stok']) . "</td>
                                    <td>" . htmlspecialchars($baris['Kategori']) . "</td>
                                    <td>" . htmlspecialchars($baris['cabang']) . "</td>
                                    <td>
                                        <form method='post' class='form-inline' style='display:inline-block;'>
                                            <input type='hidden' name='edit_id' value='" . htmlspecialchars($baris['id_barang']) . "'>
                                            <div class='input-group'>
                                                <input type='number' name='stok' class='form-control input-sm' placeholder='New Qty' min='1' required style='width: 80px;'>
                                                <span class='input-group-btn'>
                                                    <button type='submit' class='btn btn-action btn-edit'>Update</button>
                                                </span>
                                            </div>
                                        </form>
                                        <form method='post' style='display:inline-block; margin-left:5px;'>
                                            <input type='hidden' name='delete_id' value='" . htmlspecialchars($baris['id_barang']) . "'>
                                            <button type='submit' class='btn btn-action btn-delete' onclick='return confirm(\"Are you sure you want to delete this item?\")'>Delete</button>
                                        </form>
                                    </td>
                                </tr>";
                            }
                            echo "</tbody>";
                            echo "</table>";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-danger'>Error fetching data: " . htmlspecialchars($mysqli->error) . "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>
    </aside>
</div>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<script src="../js/AdminLTE.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
    // Activate tab based on URL parameter
    var urlParams = new URLSearchParams(window.location.search);
    var activeTab = urlParams.get('tab') || 'category';
    
    // Show the active tab
    $('.nav-tabs a[href="#' + activeTab + '"]').tab('show');
    
    // Update URL when tab is clicked
    $('.nav-tabs a').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).attr('href').substring(1);
        window.history.replaceState(null, null, '?tab=' + tab);
        $(this).tab('show');
    });
});
</script>
</body>
</html>