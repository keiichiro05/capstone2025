<?php
include('../konekdb.php');
session_start();
$username = $_SESSION['username'] ?? null;
$idpegawai = $_SESSION['idpegawai'] ?? null;

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

$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Warehouse'");
$user = mysqli_fetch_assoc($cekuser);

$getpegawai = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$idpegawai'");
$pegawai = mysqli_fetch_array($getpegawai);

if ($user['jmluser'] == "0") {
    header("location:../index.php");
    exit;
}

// Get product code from URL
$Code = isset($_GET['code']) ? mysqli_real_escape_string($mysqli, $_GET['code']) : '';
$Nama = isset($_GET['Nama']) ? mysqli_real_escape_string($mysqli, $_GET['Nama']) : '';

if (empty($Code)) {
    header("location:stock.php?status=Product code not provided");
    exit;
}

// Get product details
$product_query = mysqli_query($mysqli, 
    "SELECT w.*, s.Nama as supplier_name, l.nama as warehouse_name
     FROM warehouse w
     LEFT JOIN supplier s ON w.Supplier = s.Nama
     LEFT JOIN list_warehouse l ON w.cabang = l.nama
     WHERE w.Code = '$Code'");
     
$product = mysqli_fetch_assoc($product_query);

if (!$product) {
    header("location:stock.php?status=Product not found");
    exit;
}

// Get warehouse history
$warehouse_query = mysqli_query($mysqli,
    "SELECT * FROM warehouse 
     WHERE Code = '$Code'
     ORDER BY Tanggal DESC
     LIMIT 50");

$warehouse_history = mysqli_query($mysqli,
    "SELECT * FROM inventory_movement
     WHERE product_code = '$Code'
     ORDER BY movement_date DESC
     LIMIT 50");

// Handle PDF generation if requested
if (isset($_GET['download']) && $_GET['download'] == 'pdf') {
    try {
        require_once('../tcpdf/tcpdf.php'); // Adjust path as needed
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Warehouse System');
        $pdf->SetTitle('Product Details - ' . $product['Nama']);
        $pdf->SetSubject('Product Information');
        
        // Set default header data
        $pdf->SetHeaderData('', 0, 'Product Details', $product['Nama']);
        
        // Set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Add a page
        $pdf->AddPage();
        
        // Generate HTML content for PDF
        $html = '
        <h1>Product Details - ' . htmlspecialchars($product['Nama']) . '</h1>
        <table border="0" cellpadding="5">
            <tr>
                <td><b>Product Code:</b></td>
                <td>' . htmlspecialchars($product['Code']) . '</td>
            </tr>
            <tr>
                <td><b>Product Name:</b></td>
                <td>' . htmlspecialchars($product['Nama']) . '</td>
            </tr>
            <tr>
                <td><b>Current Stock:</b></td>
                <td>' . htmlspecialchars($product['Stok']) . ' ' . htmlspecialchars($product['Satuan']) . '</td>
            </tr>
            <tr>
                <td><b>Category:</b></td>
                <td>' . htmlspecialchars($product['Kategori']) . '</td>
            </tr>
            <tr>
                <td><b>Supplier:</b></td>
                <td>' . ($product['supplier_name'] ? htmlspecialchars($product['supplier_name']) : 'N/A') . '</td>
            </tr>
            <tr>
                <td><b>Reorder Level:</b></td>
                <td>' . htmlspecialchars($product['reorder_level']) . '</td>
            </tr>
            <tr>
                <td><b>Warehouse Location:</b></td>
                <td>' . htmlspecialchars($product['warehouse_name'] ? $product['warehouse_name'] : $product['cabang']) . '</td>
            </tr>
            <tr>
                <td><b>PIC:</b></td>
                <td>' . htmlspecialchars($product['pic']) . '</td>
            </tr>
            <tr>
                <td><b>Date Added:</b></td>
                <td>' . htmlspecialchars($product['Tanggal']) . '</td>
            </tr>
        </table>
        
        <h3>Barcode</h3>
        <img src="https://barcode.tec-it.com/barcode.ashx?data=' . urlencode($product['Code']) . '&code=Code128&dpi=96" width="300" />
        
        <h3>Recent Warehouse History</h3>';
        
        // Re-fetch warehouse history for PDF
        $pdf_history_query = mysqli_query($mysqli,
            "SELECT * FROM inventory_movement
             WHERE product_code = '$Code'
             ORDER BY movement_date DESC
             LIMIT 50");
        
        if (mysqli_num_rows($pdf_history_query) > 0) {
            $html .= '<table border="1" cellpadding="5">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Update Stock</th>
                    <th>Old Stock</th>
                    <th>PIC</th>
                    <th>Warehouse</th>
                </tr>';
            
            while ($history = mysqli_fetch_assoc($pdf_history_query)) {
                $html .= '<tr>
                    <td>' . htmlspecialchars($history['movement_date']) . '</td>
                    <td>' . htmlspecialchars($history['movement_type']) . '</td>
                    <td>' . htmlspecialchars($history['new_stock']) . '</td>
                    <td>' . htmlspecialchars($history['previous_stock']) . '</td>
                    <td>' . htmlspecialchars($history['pic']) . '</td>
                    <td>' . htmlspecialchars($history['warehouse']) . '</td>
                </tr>';
            }
            
            $html .= '</table>';
        } else {
            $html .= '<p>No warehouse history found for this product.</p>';
        }
        
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output('product_details_' . $product['Code'] . '.pdf', 'D');
        exit;
        
    } catch (Exception $e) {
        // Log the error and redirect with message
        error_log("PDF Generation Error: " . $e->getMessage());
        header("Location: details.php?code=" . urlencode($Code) . "&status=Error generating PDF");
        exit;
    }
}
// Handle barcode download if requested
elseif (isset($_GET['download']) && $_GET['download'] == 'barcode') {
    // Set headers for image download
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="barcode_'.htmlspecialchars($product['Code']).'.png"');
    
    // Get the barcode image content
    $barcodeUrl = 'https://barcode.tec-it.com/barcode.ashx?data='.urlencode($product['Code']).'&code=Code128&dpi=96';
    $imageContent = file_get_contents($barcodeUrl);
    
    // Output the image
    echo $imageContent;
    exit;
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Product Details - <?php echo htmlspecialchars($product['Nama']); ?></title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" /> 
        <style>
            .product-details-container {
                background: #fff;
                border_id-radius: 10px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                overflow: hidden;
                margin-bottom: 30px;
            }
            
            .product-header {
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                padding: 15px 20px;
                border_id-bottom: 1px solid rgba(255,255,255,0.2);
            }
            
            .product-header h3 {
                margin: 0;
                font-weight: 600;
                font-size: 18px;
            }
            
            .product-body {
                padding: 20px;
            }
            
            .detail-row {
                display: flex;
                margin-bottom: 15px;
                border_id-bottom: 1px solid #eee;
                padding-bottom: 15px;
            }
            
            .detail-label {
                font-weight: bold;
                width: 200px;
                color: #2c3e50;
            }
            
            .detail-value {
                flex: 1;
            }
            
            .barcode-container {
                text-align: center;
                margin: 20px 0;
            }
            
            .barcode-img {
                max-width: 100%;
                height: auto;
                image-rendering: crisp-edges;
            }
            
            .history-table {
                width: 100%;
                border_id-collapse: collapse;
                margin-top: 20px;
            }
            
            .history-table th {
                background-color: #2c3e50;
                color: white;
                padding: 10px;
                text-align: left;
            }
            
            .history-table td {
                padding: 10px;
                border_id-bottom: 1px solid #eee;
            }
            
            .history-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            
            .back-btn {
                margin-top: 20px;
            }
            
            .pdf-btn {
                margin-top: 20px;
                margin-left: 10px;
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
                        <li>
                            <a href="product.php">
                                <i class="fa fa-list-alt"></i> <span>Products</span>
                            </a>
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
                    </ul>
                </section>
            </aside>
            <aside class="right-side">
                <section class="content-header">
                    <h1>
                        Product Details
                        <small><?php echo htmlspecialchars($product['Nama']); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li><a href="stock.php">Stock</a></li>
                        <li class="active">Details</li>
                    </ol>
                </section>

                <section class="content">
                    <div class="row" style="margin-bottom: 3px;">
                        <div class="col-md-12 text-right">
                            <a href="?code=<?php echo urlencode($Code); ?>&download=pdf" class="btn btn-danger pdf-btn" title="Download as PDF">
                                <i class="fa fa-file-pdf-o"></i> <span class="hidden-xs">Download PDF</span>
                            </a>
                            <a href="?code=<?php echo urlencode($Code); ?>&download=barcode" class="btn btn-default pdf-btn" title="Download Barcode">
                                <i class="fa fa-barcode"></i> <span class="hidden-xs">Download Barcode</span>
                            </a>
                        </div>
                    </div>
                    <div class="product-details-container">
                        <div class="product-header">
                            <h3>
                                <i class="fa fa-info-circle"></i> 
                                Product Information - <?php echo htmlspecialchars($product['Nama']); ?>
                            </h3>
                        </div>
                        
                        <div class="product-body">
                            <div class="barcode-container">
                                <img src="https://barcode.tec-it.com/barcode.ashx?data=<?php echo urlencode($product['Code']); ?>&code=Code128&dpi=96" 
                                     class="barcode-img" 
                                     alt="<?php echo htmlspecialchars($product['Code']); ?>"
                                     title="<?php echo htmlspecialchars($product['Code']); ?>">
                                <h4><?php echo htmlspecialchars($product['Code']); ?></h4>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Product Name:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($product['Nama']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Current Stock:</div>
                                <div class="detail-value">
                                    <?php echo htmlspecialchars($product['Stok']); ?> <?php echo htmlspecialchars($product['Satuan']); ?>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Category:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($product['Kategori']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Supplier:</div>
                                <div class="detail-value">
                                    <?php echo $product['supplier_name'] ? htmlspecialchars($product['supplier_name']) : 'N/A'; ?>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Reorder_id Level:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($product['reorder_level']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Warehouse Location:</div>
                                <div class="detail-value">
                                    <?php echo htmlspecialchars($product['warehouse_name'] ? $product['warehouse_name'] : $product['cabang']); ?>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">PIC:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($product['pic']); ?></div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-label">Date Added:</div>
                                <div class="detail-value"><?php echo htmlspecialchars($product['Tanggal']); ?></div>
                            </div>
                            
                            <h4><center><b>Recent warehouse History</b></center></h4>
                            
                            <?php if (mysqli_num_rows($warehouse_history) > 0): ?>
                                <table class="history-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Update Stock</th>
                                            <th>Old Stock</th>
                                            <th>PIC</th>
                                            <th>Warehouse</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($warehouse = mysqli_fetch_assoc($warehouse_history)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($warehouse['movement_date']); ?></td>
                                                <td>
                                                    <?php
                                                        $type = strtolower($warehouse['movement_type']);
                                                        $color = ($type === 'inbound') ? 'green' : (($type === 'outbound') ? 'red' : 'black');
                                                    ?>
                                                    <span style="color: <?php echo $color; ?>;">
                                                        <?php echo htmlspecialchars($warehouse['movement_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($warehouse['new_stock']); ?></td>
                                                <td><?php echo htmlspecialchars($warehouse['previous_stock']); ?></td>
                                                <td><?php echo htmlspecialchars($warehouse['pic']); ?></td>
                                                <td><?php echo htmlspecialchars($warehouse['warehouse']); ?></td>
                                                
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No warehouse history found for this product.</p>
                            <?php endif; ?>
                            
                            <div>
                                <a href="stock.php" class="btn btn-primary back-btn">
                                    <i class="fa fa-arrow-left"></i> Back to Stock List
                                </a>
                                
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
        
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script src="../js/AdminLTE.min.js" type="text/javascript"></script>
    </body>
</html>