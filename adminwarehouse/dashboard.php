<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Silakan login dulu");
    exit();
}

require_once('../konekdb.php');

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Check if user has access to Adminwarehouse module
$stmt = $mysqli->prepare("SELECT COUNT(username) as jmluser FROM authorization WHERE username = ? AND modul = 'Adminwarehouse'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['jmluser'] == "0") {
    header("Location: ../index.php?status=Akses ditolak");
    exit();
}

// Get employee data
$stmtPegawai = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$stmtPegawai->bind_param("i", $idpegawai);
$stmtPegawai->execute();
$resultPegawai = $stmtPegawai->get_result();
$pegawai = $resultPegawai->fetch_assoc();

// Time-based greeting
date_default_timezone_set('Asia/Jakarta');
$hour = date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 18) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

// Fetch dashboard data
function getDashboardData($mysqli) {
    $data = [];
    
    // Total orders
    $query = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM pemesanan");
    $data['totalOrders'] = mysqli_fetch_array($query)['total'];
    
    // Pending requests
    $query = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM dariwarehouse WHERE status = 0");
    $data['totalPending'] = mysqli_fetch_array($query)['total'];
    
    // Approved requests
    $query = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM pemesanan WHERE status = 1");
    $data['totalAcc'] = mysqli_fetch_array($query)['total'];
    
    // Declined requests
    $query = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM pemesanan WHERE status = 2");
    $data['totalDecline'] = mysqli_fetch_array($query)['total'];
    
    // Total stock
    $query = mysqli_query($mysqli, "SELECT SUM(stok) as total FROM warehouse");
    $data['totalStock'] = mysqli_fetch_array($query)['total'] ?? 0;
    
    // Low stock count
    $query = mysqli_query($mysqli, "SELECT COUNT(*) as total FROM warehouse WHERE stok < reorder_level");
    $data['lowStockCount'] = mysqli_fetch_array($query)['total'];
    
    // Stock overview data (top 10 lowest stock)
    $query = mysqli_query($mysqli, "SELECT nama, stok, reorder_level FROM warehouse ORDER BY stok ASC LIMIT 10");
    $data['barLabels'] = $data['barStockData'] = $data['barReorderData'] = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data['barLabels'][] = $row['nama'];
        $data['barStockData'][] = $row['stok'];
        $data['barReorderData'][] = $row['reorder_level'];
    }
    
    // Stock distribution by category
    $query = mysqli_query($mysqli, "SELECT kategori, SUM(stok) as total FROM warehouse GROUP BY kategori");
    $data['pieLabels'] = $data['pieData'] = $data['pieColors'] = [];
    $colorPalette = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#f8f9fc', '#5a5c69', '#3a3b45', '#2e59d9'];
    $i = 0;
    while ($row = mysqli_fetch_assoc($query)) {
        $data['pieLabels'][] = $row['kategori'];
        $data['pieData'][] = $row['total'];
        $data['pieColors'][] = $colorPalette[$i % count($colorPalette)];
        $i++;
    }
    
    // Recent orders
    $query = mysqli_query($mysqli, "SELECT * FROM dariwarehouse ORDER BY date_created DESC LIMIT 5");
    $data['recentOrders'] = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data['recentOrders'][] = $row;
    }
    
    // Outbound orders
    $query = mysqli_query($mysqli, "SELECT * FROM outbound_log ORDER BY tanggal DESC LIMIT 5");
    $data['outboundOrders'] = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data['outboundOrders'][] = $row;
    }
    
    // Low stock items for alerts
    $query = mysqli_query($mysqli, "SELECT nama, stok, reorder_level FROM warehouse WHERE stok < reorder_level ORDER BY stok ASC LIMIT 8");
    $data['lowStockItems'] = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data['lowStockItems'][] = $row;
    }
    
    return $data;
}

$dashboardData = getDashboardData($mysqli);
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <?php include('styles.php'); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Manager Dashboard</title>
    
    <!-- CSS Links -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
     <link href="../css/dashboard.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    
</head>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Manager Dashboard</title>
    
    <!-- CSS Links -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="../css/AdminLTE.css" rel="stylesheet">
    <link href="../css/modern-3d.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <?php include('styles.php'); ?>
</head>
<body class="skin-blue">
    <?php include('navbar.php'); ?>
    
    <div class="wrapper row-offcanvas row-offcanvas-left">
        <?php include('sidebar.php'); ?>

        <aside class="right-side">
            <section class="content-header custom-dashboard-header">
                <div class="row">
                    <div class="col-xs-12">
                    <h1><?php echo htmlspecialchars($greeting); ?>, <?php echo htmlspecialchars($username); ?>! 
                        <small>Welcome to Warehouse Dashboard</small>
                    </h1>
            
                    <h3 class="fa fa-calendar"></h3> <?php echo date('l, F j, Y'); ?>  |  
                    <i class="fa fa-clock-o"></i> <span id="live-clock"><?php echo date('H:i:s'); ?></span>
                    </div>
                </div>
            </section>
            
            <section class="content">
                <!-- Quick Stats Row -->
                <div class="row">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-blue animate__animated animate__fadeIn">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($dashboardData['totalOrders']); ?></h3>
                                <p>Total Request</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <a href="daftarACC.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-yellow animate__animated animate__fadeIn animate__delay">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($dashboardData['totalPending']); ?></h3>
                                <p>Pending Request</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-clock-o"></i>
                            </div>
                            <a href="list_request.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-red animate__animated animate__fadeIn animate__delay">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($dashboardData['totalDecline']); ?></h3>
                                <p>Decline Request</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-exclamation-circle"></i>
                            </div>
                            <a href="daftarACC.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-green animate__animated animate__fadeIn animate__delay">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($dashboardData['totalAcc']); ?></h3>
                                <p>Approved Request</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <a href="daftarACC.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-purple animate__animated animate__fadeIn animate__delay">
                            <div class="inner">
                                <h3><?php echo htmlspecialchars($dashboardData['totalStock']); ?></h3>
                                <p>Total Inventory Items</p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-cubes"></i>
                            </div>
                            <a href="stock.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                
        <div class="row">
              
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-bar-chart text-primary"></i> Warehouse Stock Overview
                                    <small class="text-muted">(Top 10 Lowest Stock)</small>
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="chart-container">
                                    <canvas id="stockBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stock Alerts -->
                    <div class="col-md-4">
                        <div class="box box-danger">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-exclamation-triangle text-danger"></i> Stock Alerts
                                </h3>
                                <span class="label label-danger pull-right">
                                    <?php echo htmlspecialchars($dashboardData['lowStockCount']); ?> Alerts
                                </span>
                            </div>
                            <div class="box-body">
                                <div class="info-box bg-red">
                                    <span class="info-box-icon">
                                        <i class="fa fa-exclamation-circle"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Critical Items</span>
                                        <span class="info-box-number"><?php echo htmlspecialchars($dashboardData['lowStockCount']); ?></span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: <?php echo htmlspecialchars(min(100, ($dashboardData['totalStock'] > 0 ? ($dashboardData['lowStockCount'] / $dashboardData['totalStock']) * 100 : 0))); ?>%"></div>
                                        </div>
                                        <span class="progress-description">Items below reorder level</span>
                                    </div>
                                </div>
                                
                                <ul class="list-group stock-alert-list">
                                    <?php if (!empty($dashboardData['lowStockItems'])): ?>
                                        <?php foreach ($dashboardData['lowStockItems'] as $lowStock): ?>
                                            <?php
                                            $percent = $lowStock['reorder_level'] > 0 ? ($lowStock['stok'] / $lowStock['reorder_level']) * 100 : 0;
                                            $alertClass = $percent < 50 ? 'stock-critical' : 'stock-warning';
                                            ?>
                                            <li class="list-group-item alert-item <?php echo htmlspecialchars($alertClass); ?>">
                                                <div class="clearfix">
                                                    <strong><?php echo htmlspecialchars($lowStock['nama']); ?></strong>
                                                    <span class="pull-right text-danger">
                                                        <?php echo htmlspecialchars($lowStock['stok'] . '/' . $lowStock['reorder_level']); ?>
                                                    </span>
                                                </div>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar progress-bar-danger" style="width: <?php echo htmlspecialchars(min(100, $percent)); ?>%"></div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item list-group-item-success">
                                            <i class="fa fa-check-circle text-success"></i> All stock levels are good
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="box-footer text-center">
                                <a href="stock.php" class="uppercase">View All Inventory</a>
                            </div>
                        </div>
                    </div>
                
                
                    <div class="col-md-4">
                        <div class="box">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-pie-chart text-info"></i> Stock Distribution
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="chart-container">
                                    <canvas id="stockPieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Orders -->
                    <div class="col-md-4">
                        <div class="nav-tabs-custom">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-list-alt text-warning"></i> Recent Requests
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="box-body">
                                <?php if (!empty($dashboardData['recentOrders'])): ?>
                                    <ul class="products-list product-list-in-box">
                                        <?php foreach ($dashboardData['recentOrders'] as $order): ?>
                                            <?php
                                            $statusClass = $order['status'] == 1 ? 'success' : 'warning';
                                            $statusText = $order['status'] == 1 ? 'Approved' : 'Pending';
                                            ?>
                                            <li class="item">
                                                <div class="product-info">
                                                    <a href="order_detail.php?nama=<?php echo htmlspecialchars($order['nama']); ?>" class="product-title">
                                                        Order #<?php echo htmlspecialchars($order['nama']); ?>
                                                        <span class="label label-<?php echo htmlspecialchars($statusClass); ?> pull-right">
                                                            <?php echo htmlspecialchars($statusText); ?>
                                                        </span>
                                                    </a>
                                                    <span class="product-description">
                                                        Placed on <?php echo htmlspecialchars(date('M j, Y', strtotime($order['date_created']))); ?>
                                                    </span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="alert alert-info">No recent request found</div>
                                <?php endif; ?>
                            </div>
                            <div class="box-footer text-center">
                                <a href="daftarACC.php" class="uppercase">View All Orders</a>
                
                        <div class="nav-tabs-custom">
                            <div class="box-header with-border">
                                <h3 class="box-title">
                                    <i class="fa fa-list-alt text-warning"></i> Recent Transactions
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>  
                            <ul class="nav nav-tabs pull-right">
                                <li class="active"><a href="#inbound-tab" data-toggle="tab">Inbound</a></li>
                                <li><a href="#outbound-tab" data-toggle="tab">Outbound</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="inbound-tab">
                                    <div class="alert alert-info">No inbound orders found</div>
                                </div>
                                <div class="tab-pane" id="outbound-tab">
                                    <?php if (!empty($dashboardData['outboundOrders'])): ?>
                                        <ul class="products-list product-list-in-box">
                                            <?php foreach ($dashboardData['outboundOrders'] as $order): ?>
                                                <li class="item">
                                                    <div class="product-info">
                                                        <a href="#" class="product-title">
                                                            Outbound #<?php echo htmlspecialchars($order['id']); ?>
                                                            <span class="label label-primary pull-right">Shipped</span>
                                                        </a>
                                                        <span class="product-description">
                                                            <?php echo htmlspecialchars(date('M j, Y', strtotime($order['tanggal']))); ?>
                                                        </span>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <div class="alert alert-info">No outbound orders found</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="box-footer text-center">
                                <a href="#" class="uppercase">View All Transactions</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <!-- JavaScript Libraries -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/AdminLTE.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Live clock
        function updateClock() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();
            
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            
            document.getElementById('live-clock').textContent = hours + ':' + minutes + ':' + seconds;
            setTimeout(updateClock, 1000);
        }
        updateClock();
        
        // Charts
        document.addEventListener("DOMContentLoaded", function () {
            // Pie Chart
            const pieCtx = document.getElementById('stockPieChart').getContext('2d');
            const stockPieChart = new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($dashboardData['pieLabels']); ?>,
                    datasets: [{
                        data: <?php echo json_encode($dashboardData['pieData']); ?>,
                        backgroundColor: <?php echo json_encode($dashboardData['pieColors']); ?>,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 12,
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Bar Chart
            const barCtx = document.getElementById('stockBarChart').getContext('2d');
            const stockBarChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($dashboardData['barLabels']); ?>,
                    datasets: [
                        {
                            label: 'Current Stock',
                            data: <?php echo json_encode($dashboardData['barStockData']); ?>,
                            backgroundColor: 'rgba(78, 115, 223, 0.7)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: 'Reorder Level',
                            data: <?php echo json_encode($dashboardData['barReorderData']); ?>,
                            backgroundColor: 'rgba(231, 74, 59, 0.7)',
                            borderColor: 'rgba(231, 74, 59, 1)',
                            borderWidth: 1,
                            type: 'line',
                            fill: false,
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Products',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    const datasetIndex = context.datasetIndex;
                                    if (datasetIndex === 0) {
                                        const reorderLevel = <?php echo json_encode($dashboardData['barReorderData']); ?>[context.dataIndex];
                                        const currentStock = context.raw;
                                        if (currentStock < reorderLevel) {
                                            return '⚠️ Below reorder level by ' + (reorderLevel - currentStock);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });
            
            // Smooth animations for elements
            $('.small-box').hover(
                function() {
                    $(this).find('.icon').css('font-size', '80px');
                },
                function() {
                    $(this).find('.icon').css('font-size', '70px');
                }
            );
        });
    </script>
</body>
</html>