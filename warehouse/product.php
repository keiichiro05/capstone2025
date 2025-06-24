<?php
session_start();
include('../konekdb.php');

// Check login
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'] ?? null;

// Check module authorization
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

// Get employee data
$stmt = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmt->bind_param("s", $idpegawai);
$stmt->execute();
$result = $stmt->get_result();
$pegawai = $result->fetch_assoc();
$stmt->close();

// Add category
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
    header("Location: product.php?submenu=category");
    exit();
}
// Add unit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama_satuan'])) {
    $nama_satuan = trim($_POST['nama_satuan']);
    if ($nama_satuan !== '') {
        $stmt = $mysqli->prepare("SELECT id FROM unit WHERE nama_satuan = ?");
        $stmt->bind_param("s", $nama_satuan);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $_SESSION['message'] = "<div class='alert alert-danger'>Unit already exists!</div>";
        } else {
            $stmt->close();
            $stmt = $mysqli->prepare("INSERT INTO unit (nama_satuan) VALUES (?)");
            $stmt->bind_param("s", $nama_satuan);
            if ($stmt->execute()) {
                $_SESSION['message'] = "<div class='alert alert-success'>New Unit added!</div>";
            } else {
                $_SESSION['message'] = "<div class='alert alert-danger'>Failed to add unit.</div>";
            }
        }
        $stmt->close();
    }
    header("Location: product.php?submenu=unit");
    exit();
}


// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = mysqli_real_escape_string($mysqli, $_POST['delete_id']);
    mysqli_query($mysqli, "DELETE FROM warehouse WHERE Code = '$delete_id'");
    if (mysqli_affected_rows($mysqli) > 0) {
        $_SESSION['message'] = "<div class='alert alert-success'>Item deleted successfully!</div>";
    } else {
        $_SESSION['message'] = "<div class='alert alert-danger'>Error deleting item.</div>";
    }
    header("Location: product.php?submenu=all-products");
    exit();
} elseif (isset($_POST['edit_id'])) {
    // Update item
    $edit_id = (int)$_POST['edit_id'];
    $stok = (int)($_POST['stok'] ?? 0);
    
    if ($stok > 0) {
        $stmt = $mysqli->prepare("UPDATE warehouse SET Stok=? WHERE Code=?");
        $stmt->bind_param("ii", $stok, $edit_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "<div class='alert alert-success'>Stock updated successfully!</div>";
        } else {
            $_SESSION['message'] = "<div class='alert alert-danger'>Error updating stock.</div>";
        }
        $stmt->close();
    }
    header("Location: product.php?submenu=all-products");
    exit();
} elseif (isset($_POST['nama'])) {
    // Add new item
    $nama = trim($_POST['nama']);
    $stok = (int)($_POST['stok'] ?? 0);
    $kategori = trim($_POST['kategori'] ?? '');
    
    if ($nama !== '' && $stok > 0 && $kategori !== '') {
        // Check if item exists
        $stmt = $mysqli->prepare("SELECT Code FROM warehouse WHERE Nama=?");
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
    header("Location: product.php?submenu=all-products");
    exit();
}

// Get all categories for dropdown
$kategori_list = [];
$res = $mysqli->query("SELECT nama_kategori FROM kategori ORDER BY nama_kategori ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $kategori_list[] = $row['nama_kategori'];
    }
}

// Determine active submenu
$active_submenu = $_GET['submenu'] ?? 'all-products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Warehouse</title>
    
    <!-- CSS Links -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        /* Center tables and improve their appearance */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            -webkit-overflow-scrolling: touch;
            -ms-overflow-style: -ms-autohiding-scrollbar;
        }

        .table {
            margin: 0 auto;
            width: auto !important;
            max-width: 100%;
            border-collapse: collapse;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .table th, 
        .table td {
            padding: 12px 15px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        /* Make tables responsive on small screens */
        @media (max-width: 768px) {
            .table {
                font-size: 14px;
            }
            
            .table th, 
            .table td {
                padding: 8px 10px;
            }
            
            .table-responsive {
                width: 100%;
                margin: 0;
            }
        }

        /* Layout improvements */
        .submenu-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .box {
            margin-bottom: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
        }

        .box-header {
            padding: 15px;
            border-bottom: 1px solid #f4f4f4;
        }

        .box-title {
            margin: 0;
            font-size: 18px;
        }

        .box-body {
            padding: 15px;
        }

        /* Barcode styling */
        .barcode-cell {
            text-align: center;
        }

        .barcode-container {
            display: inline-block;
            padding: 5px;
            background: white;
            border: 1px solid #ddd;
        }

        .barcode-img {
            height: 40px;
            width: auto;
        }

        /* Action buttons */
        .action-buttons {
            white-space: nowrap;
        }

        .btn-action {
            margin: 0 3px;
            padding: 3px 6px;
            font-size: 14px;
        }

        /* Form styling */
        .form-inline .form-group {
            margin-right: 10px;
            margin-bottom: 10px;
        }
    </style>

<?php include('styles.php'); ?>
</head>
<body class="skin-blue">
    <?php include('navbar.php'); ?>
    
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <div class="wrapper row-offcanvas row-offcanvas-left">
     <aside class="left-side sidebar-offcanvas">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <img src="../img/<?php echo htmlspecialchars($pegawai['foto']); ?>" class="img-circle" alt="User Image" />
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
                    <a href="stock.php">
                        <i class="fa fa-folder"></i> <span>Stock</span>
                    </a>
                </li>
                <li>
                    <a href="movement.php">
                        <i class="fa fa-exchange"></i> <span>Movement</span>
                    </a>
                </li>
                <li class="treeview active">
                    <a href="#">
                        <i class="fa fa-list-alt"></i> <span>Products</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu" style="<?php echo in_array($active_submenu, ['all-products','add-products','category','unit']) ? 'display: block;' : ''; ?>">
                        <li class="<?php echo $active_submenu == 'all-products' ? 'active' : ''; ?>">
                            <a href="product.php?submenu=all-products"><i class="fa fa-folder"></i> All Products</a>
                        </li>
                        <li class="<?php echo $active_submenu == 'add-products' ? 'active' : ''; ?>">
                            <a href="product.php?submenu=add-products"><i class="fa fa-plus-square"></i> Add Products</a>
                        </li>
                        <li class="<?php echo $active_submenu == 'category' ? 'active' : ''; ?>">
                            <a href="product.php?submenu=category"><i class="fa fa-caret-square-o-right "></i> Category</a>
                        </li>
                        <li class="<?php echo $active_submenu == 'unit' ? 'active' : ''; ?>">
                            <a href="product.php?submenu=unit"><i class="fa fa-caret-square-o-right "></i> Unit</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="new_request.php">
                        <i class="fa fa-plus-square"></i> <span>New Request</span>
                    </a>
                </li>
                <li>
                    <a href="history_request.php">
                        <i class="fa fa-archive"></i> <span>Request History</span>
                    </a>
                </li>
                 <li>
                     <a href="sales_request.php">
                        <i class="fa fa-retweet"></i> <span>Sales Request</span>
                        </a>
                    </li>
                <li>
                    <a href="mailbox.php">
                        <i class="fa fa-comments"></i> <span>Mailbox</span>
                    </a>
                </li>
                    <li>
                        <a href="frompurchase.php">
                            <i class="fa fa-share-square-o"></i> <span>Goods Delivered</span>
                        </a>
                    </li>
                   
            </ul>
        </section>
    </aside>
    
    <aside class="right-side">
        <section class="content-header">
                    <h1>
                        Products Management 
                        <small>View and Manage Product</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="product.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Products</li>
                    </ol>
           <small>  
            <?php 
                $submenu_titles = [
                    'all-products' => 'All Products List',
                    'add-products' => 'Add Products',
                    'category' => 'Product Categories',
                    'unit' => 'Measurement Units'
                ];
                echo $submenu_titles[$active_submenu] ?? 'Products Management';
            ?></small>
            </h1>
        </section>
        <section class="content">
            <?php
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']);
            }
            ?>
            
            <div class="submenu-content">
                <?php if ($active_submenu == 'category'): ?>
                    <!-- Category Content -->
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Add Category</h3>
                        </div>
                        <div class="box-body">
                            <form method="post" class="form-inline">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Category Name" name="nama_kategori" required style="width: 300px;">
                                </div>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Category List</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Category Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM kategori ORDER BY nama_kategori ASC";
                                        $hasil = $mysqli->query($sql);
                                        while ($baris = $hasil->fetch_assoc()) {
                                            echo "<tr>
                                                <td>" . htmlspecialchars($baris['id']) . "</td>
                                                <td>" . htmlspecialchars($baris['nama_kategori']) . "</td>
                                                <td>
                                                    <a href='edit_product.php?id=" . htmlspecialchars($baris['id']) . "' class='btn btn-warning btn-sm'>Edit</a>
                                                    <a href='hapus_product.php?id=" . htmlspecialchars($baris['id']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this category?\")'>Delete</a>
                                                </td>
                                            </tr>";
                                        }   
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                
                <?php elseif ($active_submenu == 'all-products'): ?>
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">All Products</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Barcode</th>
                                            <th>Name</th>
                                            <th>Stock</th>
                                            <th>Category</th>
                                            <th>Unit</th>
                                            <th>Minimum</th>
                                            <th>Warehouse</th>
                                            <th>Date Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = mysqli_query($mysqli, "
                                            SELECT w.*, s.Nama as supplier_name 
                                            FROM warehouse w
                                            LEFT JOIN supplier s ON w.Supplier = s.Nama
                                            ORDER BY w.no DESC
                                        ");
                                        
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>
                                                    <td>".htmlspecialchars($row['no'])."</td>
                                                    <td class='barcode-cell'>
                                                        <div class='barcode-container'>
                                                            <img src='https://barcode.tec-it.com/barcode.ashx?data=".urlencode($row['Code'])."&code=Code128&dpi=96' 
                                                                class='barcode-img' 
                                                                alt='Barcode ".htmlspecialchars($row['Code'])."'>
                                                        </div>
                                                    </td>
                                                    <td>".htmlspecialchars($row['Nama'])."</td>
                                                    <td>".htmlspecialchars($row['Stok'])."</td>
                                                    <td>".htmlspecialchars($row['Kategori'])."</td>
                                                    <td>".htmlspecialchars($row['Satuan'])."</td>
                                                    <td>".htmlspecialchars($row['reorder_level'])."</td>
                                                    <td>".htmlspecialchars($row['cabang'])."</td>
                                                    <td>".htmlspecialchars(date('d M Y H:i', strtotime($row['Tanggal'])))."</td>
                                                    <td class='action-buttons'>";
                                            
                                            // View button
                                            echo "<a href='details.php?code=".urlencode($row['Code'])."' class='btn-action btn-view' title='View Details'><i class='fa fa-eye'></i></a>";
                                            // Delete button
                                            echo "<form method='post' style='display:inline;'>
                                                    <input type='hidden' name='delete_id' value='".$row['no']."'>
                                                    <button type='submit' class='btn-action btn-delete' title='Delete id_pemesanan'><i class='fa fa-trash-o'></i></button>
                                                </form>";
                                            
                                            echo "</td>
                                                </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
               <?php elseif ($active_submenu == 'add-products'): ?>
                    <!-- Add Products Content -->
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Add New Product</h3>
                        </div>
                        <div class="box-body">
                            <form method="post" action="new_request.php" id="productForm">
                                <div class="row">
                                    <!-- LEFT COLUMN -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="warehouse2">Warehouse *</label>
                                            <select name="warehouse2" id="warehouse2" class="form-control select2" required>
                                                <option value="">Select Warehouse</option>
                                                <?php
                                                $query = $mysqli->query("SELECT nama FROM list_warehouse");
                                                while ($row = $query->fetch_assoc()) {
                                                    echo "<option value=\"".htmlspecialchars($row['nama'])."\">".htmlspecialchars($row['nama'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="code2">Product ID *</label>
                                            <input type="text" name="code2" id="code2" class="form-control" placeholder="Enter Product ID" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="nama2">Product Name *</label>
                                            <input type="text" name="nama2" id="nama2" class="form-control" placeholder="Enter Product Name" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="kategori2">Category *</label>
                                            <select name="kategori2" id="kategori2" class="form-control select2" required>
                                                <option value="">Select Category</option>
                                                <?php
                                                $query = $mysqli->query("SELECT nama_kategori FROM kategori");
                                                while ($row = $query->fetch_assoc()) {
                                                    echo "<option value=\"".htmlspecialchars($row['nama_kategori'])."\">".htmlspecialchars($row['nama_kategori'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- RIGHT COLUMN -->
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="Stok">Initial Stock *</label>
                                            <input type="number" name="Stok" id="Stok" class="form-control" placeholder="Enter quantity" min="1" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="satuan">Unit</label>
                                            <select name="satuan" id="satuan" class="form-control select2" required>
                                                <option value="">Select Unit</option>
                                                <?php
                                                $query = $mysqli->query("SELECT nama_satuan FROM unit");
                                                while ($row = $query->fetch_assoc()) {
                                                    echo "<option value=\"".htmlspecialchars($row['nama_satuan'])."\">".htmlspecialchars($row['nama_satuan'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="reorder-level">Reorder Level *</label>
                                            <div class="input-group">
                                                <input type="number" name="reorder-level" id="reorder-level" class="form-control" placeholder="Minimum stock level" min="5" required>
                                                <div class="input-group-addon">units</div>
                                            </div>
                                            <small class="form-text text-muted">Alert when stock reaches this level</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="supplier">Supplier *</label>
                                            <select name="supplier" id="supplier" class="form-control select2" required>
                                                <option value="">Select Supplier</option>
                                                <?php
                                                $query = $mysqli->query("SELECT Nama FROM supplier");
                                                while ($row = $query->fetch_assoc()) {
                                                    echo "<option value=\"".htmlspecialchars($row['Nama'])."\">".htmlspecialchars($row['Nama'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- FULL WIDTH SECTION -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pic">Person in Charge (PIC) *</label>
                                            <select name="pic" id="pic" class="form-control select2" required>
                                                <option value="">Select PIC</option>
                                                <?php
                                                $query = $mysqli->query("SELECT pic FROM list_warehouse");
                                                while ($row = $query->fetch_assoc()) {
                                                    echo "<option value=\"".htmlspecialchars($row['pic'])."\">".htmlspecialchars($row['pic'])."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea name="description" id="description" class="form-control" rows="2" placeholder="Optional product description"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- BUTTONS -->
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fa fa-save"></i> Save Product
                                    </button>
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fa fa-undo"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php elseif ($active_submenu == 'unit'): ?>
                    <!-- Unit Content -->
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Measurement Units</h3>
                        </div>
                        <div class="box-body">
                            <form method="post" class="form-inline">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Unit Name" name="nama_satuan" required style="width: 300px;">
                                </div>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Units List</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Unit Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM unit ORDER BY nama_satuan ASC";
                                        $hasil = $mysqli->query($sql);
                                        while ($baris = $hasil->fetch_assoc()) {
                                            echo "<tr>
                                                <td>" . htmlspecialchars($baris['id']) . "</td>
                                                <td>" . htmlspecialchars($baris['nama_satuan']) . "</td>
                                                <td>
                                                    <a href='edit_product.php?id=" . htmlspecialchars($baris['id']) . "' class='btn btn-warning btn-sm'>Edit</a>
                                                    <form method='post' style='display:inline-block; margin-left:5px;'>
                                                        <input type='hidden' name='delete_id' value='" . htmlspecialchars($baris['id']) . "'>
                                                        <button type='submit' class='btn btn-danger btn-sm'>Delete</button>
                                                    </form>
                                                </td>
                                            </tr>";
                                        }   
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </aside>
</div>
<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<script src="../js/AdminLTE.min.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
    // Initialize sidebar treeview
    $('.sidebar-menu').tree();
    
    // Highlight active menu based on URL parameter
    var urlParams = new URLSearchParams(window.location.search);
    var activeSubmenu = urlParams.get('submenu') || 'all-products';
    
    // Automatically expand the Products menu if on product.php
    if (window.location.pathname.includes('product.php')) {
        $('.sidebar-menu li.treeview').addClass('active');
        $('.treeview-menu').show();
    }
    
    // Highlight the active submenu item
    $('.treeview-menu li').removeClass('active');
    $('.treeview-menu li a').each(function() {
        if (this.href.includes(activeSubmenu)) {
            $(this).parent().addClass('active');
        }
    });
    
    // Confirm before deleting - using event delegation
    $(document).on('submit', 'form', function(e) {
        if ($(this).find('button[type=submit].btn-danger').length) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        }
    });
    
    // Form validation for product form
    $('#productForm').on('submit', function(e) {
        var isValid = true;
        var firstInvalid = null;
        
        $(this).find('input[required], select[required]').each(function() {
            if ($(this).val() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
                if (!firstInvalid) {
                    firstInvalid = this;
                }
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields!');
            if (firstInvalid) {
                $(firstInvalid).focus();
            }
            return false;
        }
        return true;
    });
    
    // Handle view/edit/download buttons
    $(document).on('click', '.btn-view', function() {
        var no = $(this).closest('tr').find('td:first').text();
        window.location.href = 'view_product.php?no=' + no;
    });
    
    $(document).on('click', '.btn-edit', function() {
        var no = $(this).closest('tr').find('td:first').text();
        window.location.href = 'edit_product.php?no=' + no;
    });
    
    $(document).on('click', '.btn-download', function() {
        var code = $(this).data('code');
        var url = 'https://barcode.tec-it.com/barcode.ashx?data=' + encodeURIComponent(code) + '&code=Code128&dpi=150&download=true';
        window.open(url, '_blank');
    });
});
</script>
</body>
</html>