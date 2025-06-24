<?php
include "konekdb.php";
session_start();
if(!isset($_SESSION['username'])){
	header("location:../index.php?status=please login first");
	exit();
	}
if (isset($_SESSION['idpegawai'])) {
    $idpegawai = $_SESSION['idpegawai'];
} else {
    header("location:../index.php?status=please login first");
    exit();
}
// Get user data for sidebar
$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];
$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
$pegawai = mysqli_fetch_assoc($getpegawai);

// Check if ID parameter exists and is valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location:product.php");
    exit;
}

$id = (int)$_GET['id'];
$categoryName = "";
$message = "";

// Fetch the current category name
if ($stmt = $mysqli->prepare("SELECT nama_kategori FROM kategori WHERE id = ?")) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($categoryName);
    if (!$stmt->fetch()) {
        // Category not found
        header("location:product.php");
        exit;
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nama'])) {
        $newName = trim($_POST['nama']);
        
        // Validate input
        if (empty($newName)) {
            $message = '<div class="alert alert-danger">Category name cannot be empty!</div>';
        } else {
            // Check if category already exists
            $check_stmt = $mysqli->prepare("SELECT id FROM kategori WHERE nama_kategori = ? AND id != ?");
            $check_stmt->bind_param("si", $newName, $id);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $message = '<div class="alert alert-danger">Category already exists!</div>';
            } else {
                // Update category
                if ($update_stmt = $mysqli->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?")) {
                    $update_stmt->bind_param("si", $newName, $id);
                    if ($update_stmt->execute()) {
                        $message = '<div class="alert alert-success">Category updated successfully! Redirecting...</div>';
                        // Redirect after 2 seconds to show success message
                        header("refresh:2;url=product.php");
                    } else {
                        $message = '<div class="alert alert-danger">Error updating category: '.$mysqli->error.'</div>';
                    }
                    $update_stmt->close();
                }
            }
            $check_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - Warehouse</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
    <style>
        .edit-category-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border_id-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .edit-category-header {
            border_id-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            border_id-radius: 4px;
            box-shadow: none;
            border_id: 1px solid #ddd;
        }
        
        .btn-primary {
            background-color: #3498db;
            border_id-color: #2980b9;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border_id-color: #2472a4;
        }
        
        .btn-back {
            margin-right: 10px;
        }
    </style>
</head>
<body class="skin-blue">
    <header class="header">
        <a href="index.php" class="logo">E-pharm</a>
        <nav class="navbar navbar-static-top" role="navigation">
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
        <!-- Sidebar -->
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
                    <li>
                        <a href="index.php">
                            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="product.php">
                            <i class="fa fa-list-alt"></i> <span>Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="new_request.php">
                            <i class="fa fa-th"></i> <span>order_ids</span>
                        </a>
                    </li>
                    <li>
                        <a href="history_request.php">
                            <i class="fa fa-history"></i> <span>order_id History</span>
                        </a>
                    </li>
                    <li>
                        <a href="cuti.php">
                            <i class="fa fa-suitcase"></i> <span>Leave</span>
                        </a>
                    </li>
                    <li>
                        <a href="mailbox.php">
                            <i class="fa fa-comments"></i> <span>Mailbox</span>
                        </a>
                    </li>
                </ul>
            </section>
        </aside>
        
        <!-- Main Content -->
        <aside class="right-side">
            <section class="content-header">
                <h1>Edit Category</h1>
                <ol class="breadcrumb">
                    <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href="product.php">Categories</a></li>
                    <li class="active">Edit Category</li>
                </ol>
            </section>
            
            <section class="content">
                <div class="edit-category-container">
                    <div class="edit-category-header">
                        <h3><i class="fa fa-edit"></i> Edit Category</h3>
                    </div>
                    
                    <?php echo $message; ?>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="nama">Category Name:</label>
                            <input type="text" class="form-control" id="nama" name="nama" 
                                   value="<?php echo htmlspecialchars($categoryName); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <a href="product.php" class="btn btn-default btn-back">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </aside>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Focus on the input field when page loads
            $('#nama').focus();
        });
    </script>
</body>
</html>