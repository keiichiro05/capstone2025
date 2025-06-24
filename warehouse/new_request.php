<?php
include('../konekdb.php');
session_start();

// Validate session and authorization
if(!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    header("location:../index.php?status=please login first");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check user authorization
$stmt = $mysqli->prepare("SELECT count(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Warehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user['jmluser'] == "0") {
    header("location:../index.php");
    exit();
}

// Fetch employee data
$stmt = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmt->bind_param("s", $idpegawai);
$stmt->execute();
$pegawai = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pegawai) {
    die("Employee data not found");
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    // Validate and sanitize inputs
    $nama = isset($_POST['nama']) ? $mysqli->real_escape_string(trim($_POST['nama'])) : '';
    $kategori = isset($_POST['kategori']) ? $mysqli->real_escape_string(trim($_POST['kategori'])) : '';
    $jumlah = isset($_POST['jumlah']) ? intval($_POST['jumlah']) : 0;
    $satuan = isset($_POST['satuan']) ? $mysqli->real_escape_string(trim($_POST['satuan'])) : '';

    $cabang = isset($_POST['warehouse']) ? $mysqli->real_escape_string(trim($_POST['warehouse'])) : '';
    $pic = isset($_POST['pic']) ? $mysqli->real_escape_string(trim($_POST['pic'])) : '';

    
    // Validate required fields
    if (empty($nama) || empty($kategori) || $jumlah <= 0 || empty($satuan) || 
    empty($cabang) || empty($pic)) {
        $_SESSION['error'] = "Please fill all required fields";
    } else {
        // Insert query with prepared statement
        $insert_query = "INSERT INTO dariwarehouse 
                        (nama, kategori, jumlah, satuan, cabang, pic, status, date_created) 
                        VALUES (?, ?, ?, ?, ?, ?, '0', NOW())";
        $stmt = $mysqli->prepare($insert_query);
        $stmt->bind_param("ssisss", $nama, $kategori, $jumlah, $satuan, $cabang, $pic);
        
        if($stmt->execute()) {
            $stmt->close();
            $_SESSION['message'] = "Request submitted successfully";
            header("Location: new_request.php");
            exit();
        } else {
            $_SESSION['error'] = "Error submitting request: ".$mysqli->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Warehouse</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- CSS Links -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    
    <style>
        .main-container {
            padding: 20px;
        }
        .form-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        .form-title {
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
            display: block;
        }
        .form-control {
            border-radius: 4px;
            border: 1px solid #ddd;
            box-shadow: none;
            height: 40px;
            padding: 8px 12px;
            transition: all 0.3s;
            width: 100%;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        textarea.form-control {
            height: auto;
            min-height: 80px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #3498db);
            transform: translateY(-2px);
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending { background-color: #f39c12; color: white; }
        .status-accepted { background-color: #2ecc71; color: white; }
        .status-rejected { background-color: #e74c3c; color: white; }
        .action-buttons { white-space: nowrap; }
        .action-buttons .btn { margin-right: 5px; }
        .action-buttons .btn:last-child { margin-right: 0; }
        .table-container { border-radius: 8px; overflow: hidden; }
        .table thead th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
            border-bottom: none;
        }
        .table tbody tr:hover { background-color: #f5f5f5; }
        .submit-btn-container {
            display: flex;
            align-items: flex-end;
            height: 100%;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        .form-col {
            padding-right: 15px;
            padding-left: 15px;
            flex: 1;
            min-width: 0;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
    <?php include('styles.php'); ?>
</head>
<body class="skin-blue">
    <?php include('navbar.php'); ?>
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
                    <li>
                        <a href="product.php">
                            <i class="fa fa-list-alt"></i> <span>Products</span>
                        </a>
                    </li>
                    <li class="active">
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
                        <a href="purchase_order.php">
                            <i class="fa fa-shopping-cart"></i> <span>Purchase Orders</span>
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
                    New Request
                    <small>Create New Request</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="new_request.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">New Request</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?= htmlspecialchars($_SESSION['message']) ?>
                            </div>
                            <?php unset($_SESSION['message']); ?>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <?= htmlspecialchars($_SESSION['error']) ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <div class="box box-primary">
                            <div class="box-header">
                                <h3 class="box-title">Request Form</h3>
                            </div>
                            <div class="box-body">
                                <form id="request-form" method="post" action="">
                                    <div class="form-row">
                                        <div class="form-col col-md-6">
                                             <div class="form-group">
                                                <label class="required-field">Product Name</label>
                                                <input type="text" name="nama" class="form-control" placeholder="Product name" value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="required-field">Warehouse</label>
                                                <select name="warehouse" class="form-control" required>
                                                    <option value="">-- Select Warehouse --</option>
                                                    <?php
                                                    $warehouses = $mysqli->query("SELECT nama FROM list_warehouse");
                                                    while ($wh = $warehouses->fetch_assoc()):
                                                    ?>
                                                        <option value="<?= htmlspecialchars($wh['nama']) ?>" <?= isset($_POST['warehouse']) && $_POST['warehouse'] == $wh['nama'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($wh['nama']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label class="required-field">PIC</label>
                                                <select name="pic" class="form-control" required>
                                                    <option value="">-- Select PIC --</option>
                                                    <?php
                                                    $pics = $mysqli->query("SELECT DISTINCT pic FROM list_warehouse WHERE pic IS NOT NULL AND pic <> ''");
                                                    while ($p = $pics->fetch_assoc()):
                                                    ?>
                                                        <option value="<?= htmlspecialchars($p['pic']) ?>" <?= isset($_POST['pic']) && $_POST['pic'] == $p['pic'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($p['pic']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                            
                                           
                                            
                                            <div class="form-group">
                                                <label class="required-field">Category</label>
                                                <select name="kategori" class="form-control" required>
                                                    <option value="">-- Select Category --</option>
                                                    <?php
                                                    $categories = $mysqli->query("SELECT nama_kategori FROM kategori");
                                                    while ($cat = $categories->fetch_assoc()):
                                                    ?>
                                                        <option value="<?= htmlspecialchars($cat['nama_kategori']) ?>" <?= isset($_POST['kategori']) && $_POST['kategori'] == $cat['nama_kategori'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($cat['nama_kategori']) ?>
                                                        </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="form-col col-md-6">
                                            <div class="form-group">
                                                <label class="required-field">Quantity</label>
                                                <input type="number" name="jumlah" class="form-control" placeholder="Quantity" min="1" value="<?= isset($_POST['jumlah']) ? htmlspecialchars($_POST['jumlah']) : '' ?>" required>
                                            </div>
                                            <div class="form-group">
    <label class="required-field">Unit</label>
    <select name="satuan" class="form-control" required>
        <option value="">-- Select Unit --</option>
        <?php
        // Query untuk mengambil data satuan dari tabel unit
        $units = $mysqli->query("SELECT nama_satuan FROM unit ORDER BY nama_satuan ASC");
        
        if ($units && $units->num_rows > 0) {
            while ($unit = $units->fetch_assoc()) {
                $selected = (isset($_POST['satuan']) && $_POST['satuan'] == $unit['nama_satuan']) ? 'selected' : '';
                echo '<option value="'.htmlspecialchars($unit['nama_satuan']).'" '.$selected.'>'.htmlspecialchars($unit['nama_satuan']).'</option>';
            }
        } else {
            // Fallback options jika tabel unit kosong
            $default_units = ['Pcs', 'Box', 'Pack', 'Set', 'Kg', 'Liter'];
            foreach ($default_units as $unit) {
                $selected = (isset($_POST['satuan']) && $_POST['satuan'] == $unit) ? 'selected' : '';
                echo '<option value="'.htmlspecialchars($unit).'" '.$selected.'>'.htmlspecialchars($unit).'</option>';
            }
        }
        ?>
    </select>
</div>
                                      
                                            
                                           
                                        </div>
                                    </div>
                                    
                                    <div class="form-group text-right">
                                        <button type="submit" name="submit_request" class="btn btn-primary">
                                            <i class="fa fa-paper-plane"></i> Submit Request
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="box">
                            <div class="box-header" style="background-color:rgb(109, 182, 255); color: white;">
                                <h3 class="box-title"><i class="fa fa-history"></i> Recent Requests</h3>
                            </div>
                            <div class="box-body table-responsive">
                                <?php
                                $query = "SELECT * FROM dariwarehouse ORDER BY no DESC LIMIT 10";
                                $requests = $mysqli->query($query);
                                
                                if ($requests->num_rows === 0) {
                                    echo "<div class='alert alert-info'>No recent requests found</div>";
                                } else {
                                ?>
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Product Name</th>
                                            <th>Category</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            
                                            <th>Warehouse</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($req = $requests->fetch_assoc()):
                                            $status_class = '';
                                            $status_text = '';
                                            
                                            if ($req['status'] === "0") {
                                                $status_class = 'status-pending';
                                                $status_text = 'Pending';
                                            } elseif ($req['status'] === "1") {
                                                $status_class = 'status-accepted';
                                                $status_text = 'Accepted';
                                            } elseif ($req['status'] === "2") {
                                                $status_class = 'status-rejected';
                                                $status_text = 'Rejected';
                                            }
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($req['no']) ?></td>
                                                <td><?= htmlspecialchars($req['nama']) ?></td>
                                                <td><?= htmlspecialchars($req['kategori']) ?></td>
                                                <td><?= htmlspecialchars($req['jumlah']) ?></td>
                                                <td><?= htmlspecialchars($req['satuan']) ?></td>
                                           
                                                <td><?= htmlspecialchars($req['cabang']) ?></td>
                                                <td><?= date('d M Y H:i', strtotime($req['date_created'])) ?></td>
                                                <td><span class="status-badge <?= $status_class ?>"><?= $status_text ?></span></td>
                                                <td class="action-buttons">
                                                    <a href="request_detail.php?id=<?= $req['no'] ?>" class="btn btn-info btn-xs">
                                                        <i class="fa fa-eye"></i> Detail
                                                    </a>
                                                    <a href="request_edit.php?id=<?= $req['no'] ?>" class="btn btn-warning btn-xs">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>
                                                    <form method="post" action="delete_request.php" style="display:inline;">
                                                        <input type="hidden" name="delete_id" value="<?= $req['no'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this request?')">
                                                            <i class="fa fa-trash-o"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <!-- JavaScript Files -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    <script>
    $(document).ready(function() {
        // Form validation
        $('#request-form').submit(function(e) {
            let valid = true;
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).css('border-color', 'red');
                    valid = false;
                } else {
                    $(this).css('border-color', '');
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Please fill all required fields marked with *!');
            }
        });
        
        // Reset form validation on change
        $('input, select').on('change', function() {
            if ($(this).val()) {
                $(this).css('border-color', '');
            }
        });
    });
    </script>
</body>
</html>