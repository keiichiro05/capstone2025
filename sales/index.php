<?php
session_start();
require_once('../konekdb.php');

// Cek login
if (!isset($_SESSION['username'], $_SESSION['idpegawai'])) {
    header("Location: ../index.php?status=Silakan login dulu");
    exit();
}

$username = $_SESSION['username'];
$idpegawai = $_SESSION['idpegawai'];

// Ambil data user yang sedang login
$usersql = $mysqli->prepare("SELECT * FROM pegawai WHERE id_pegawai = ?");
$usersql->bind_param("i", $idpegawai);
$usersql->execute();
$userresult = $usersql->get_result();
$hasiluser = $userresult->fetch_assoc();

// Ambil filter dari GET
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

// Query Total Sales berdasarkan bulan dan tahun dari tabel purchase_order
$stmt_total_sales = $mysqli->prepare("
    SELECT SUM(poi.total_price) AS total_sales
    FROM purchase_order_item poi
    JOIN purchase_order po ON poi.po_id = po.po_id
    WHERE MONTH(po.order_date) = ? AND YEAR(po.order_date) = ?
");

$stmt_total_sales->bind_param("ii", $selected_month, $selected_year);
$stmt_total_sales->execute();
$result_total_sales = $stmt_total_sales->get_result();
$row_total_sales = $result_total_sales->fetch_assoc();
$total_sales = $row_total_sales['total_sales'] ?? 0;

// Get current time for greeting
$hour = date('G');
if ($hour >= 5 && $hour < 12) {
    $greeting = 'Good Morning';
    $greetingClass = 'morning-greeting';
    $icon = 'fa-sun-o';
    $bgColor = 'bg-aqua';
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = 'Good Afternoon';
    $greetingClass = 'afternoon-greeting';
    $icon = 'fa-sun-o';
    $bgColor = 'bg-yellow';
} elseif ($hour >= 17 && $hour < 20) {
    $greeting = 'Good Evening';
    $greetingClass = 'evening-greeting';
    $icon = 'fa-moon-o';
    $bgColor = 'bg-purple';
} else {
    $greeting = 'Good Night';
    $greetingClass = 'night-greeting';
    $icon = 'fa-star-o';
    $bgColor = 'bg-navy';
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>U-PSN | SALES DASHBOARD</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <!-- Bootstrap 3.0.2 -->
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome -->
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
    
    <style>
        .greeting-container {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: white;
        }
        .greeting {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .greeting-time {
            font-size: 16px;
            opacity: 0.9;
        }
        .morning-greeting {
            background: linear-gradient(135deg, #56CCF2, #2F80ED);
        }
        .afternoon-greeting {
            background: linear-gradient(135deg, #F2994A, #F2C94C);
        }
        .evening-greeting {
            background: linear-gradient(135deg, #9F7AEA, #614385);
        }
        .night-greeting {
            background: linear-gradient(135deg, #0F2027, #203A43, #2C5364);
        }
        .weather-icon {
            margin-right: 10px;
            font-size: 28px;
        }
        .user-panel .info {
            padding-top: 10px;
        }
        .small-box {
            transition: transform 0.3s;
        }
        .small-box:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="skin-blue">
<header class="header">
    <a href="index.php" class="logo">U-PSN</a>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
        </a>
        <div class="navbar-right">
            <ul class="nav navbar-nav">
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span><?php echo htmlspecialchars($hasiluser['Nama']); ?> <i class="caret"></i></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header bg-light-blue">
                            <img src="<?php echo htmlspecialchars($hasiluser['foto']); ?>" class="img-circle" alt="User Image" />
                            <p>
                                <?php echo htmlspecialchars($hasiluser['Nama']) . " - " . htmlspecialchars($hasiluser['Jabatan']); ?>
                                <small>Member since <?php echo htmlspecialchars($hasiluser['Tanggal_Masuk']); ?></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="profil.php" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="prosesLogout.php" class="btn btn-default btn-flat">Sign out</a>
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
                    <img src="<?php echo htmlspecialchars($hasiluser['foto']); ?>" class="img-circle" alt="User Image" />
                </div>
                <div class="pull-left info">
                    <p>Hello, <?php echo htmlspecialchars($hasiluser['Nama']); ?></p>
                    <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                </div>
            </div>

            <form action="#" method="get" class="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Search..."/>
                    <span class="input-group-btn">
                        <button type='submit' name='search' id='search-btn' class="btn btn-flat">
                            <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
            </form>

            <ul class="sidebar-menu">
                <li class="active"><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li><a href="account.php"><i class="fa fa-users"></i> <span>Account</span></a></li>
                <li><a href="contact.php"><i class="fa fa-envelope"></i> <span>Contact</span></a></li>
                <li><a href="products.php"><i class="fa fa-archive"></i> <span>Product</span></a></li>
                <li><a href="product_request.php"><i class="fa fa-plus-square"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-lightbulb-o"></i> <span>Leads</span></a></li>
                <li><a href="opportunity.php"><i class="fa fa-rocket"></i> <span>Opportunity</span></a></li>
                <li><a href="quotation.php"><i class="fa fa-truck"></i> <span>Quotation</span></a></li>
                <li><a href="purchase_order.php"><i class="fa fa-clipboard"></i> Purchase Order</a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>SALES DASHBOARD</h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>

        <section class="content">
            <!-- Time-based Greeting Card -->
            <div class="row">
                <div class="col-md-12">
                    <div class="greeting-container <?php echo $greetingClass; ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="greeting">
                                    <i class="fa <?php echo $icon; ?> weather-icon"></i>
                                    <?php echo $greeting . ', ' . htmlspecialchars($hasiluser['Nama']); ?>
                                </div>
                                <div class="greeting-time">
                                    <i class="fa fa-clock-o"></i> <?php echo date('l, F j, Y - g:i A'); ?>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <i class="fa <?php echo $icon; ?>" style="font-size: 60px; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php
        // Generate years and months
        $years = [2024, 2025];
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        // Ambil filter dari GET
        $selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $selected_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
        ?>

        <div class="row">
            <div class="col-md-3">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h4 class="box-title">Filter</h4>
                    </div>
                    <div class="box-body">
                        <form method="get" id="filter-form">
                            <div class="form-group">
                                <label for="year">Year</label>
                                <select name="year" id="year" class="form-control">
                                    <?php foreach ($years as $year): ?>
                                        <option value="<?php echo $year; ?>" <?php if ($year == $selected_year) echo 'selected'; ?>>
                                            <?php echo $year; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="month">Month</label>
                                <select name="month" id="month" class="form-control">
                                    <?php foreach ($months as $num => $name): ?>
                                        <option value="<?php echo $num; ?>" <?php if ($num == $selected_month) echo 'selected'; ?>>
                                            <?php echo $name; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Apply Filter</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Contoh penggunaan filter, tampilkan hasil filter di sini -->
             
           <div class="col-lg-3 col-xs-6">
    <div class="small-box bg-red">
        <div class="inner">
             <p>Total Sales</p>
            <h3><?php echo number_format($total_sales, 0, ',', '.'); ?></h3>
        </div>
        <div class="icon">
            <i class="fa fa-money"></i>
        </div>
        <a href="purchase_order.php" class="small-box-footer">
            More info <i class="fa fa-arrow-circle-right"></i>
        </a>
    </div>
    
</div>
        </div>
        <!-- Daily Sales Chart -->
        <div class="col-md-9">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h4 class="box-title">Daily Sales (<?php echo $months[$selected_month] . ' ' . $selected_year; ?>)</h4>
                </div>
                <div class="box-body">
                    <canvas id="dailySalesChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <?php
        // Prepare daily sales data for the selected month
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $selected_month, $selected_year);
        $daily_sales = array_fill(1, $days_in_month, 0);

        // Query daily sales
        $stmt_daily = $mysqli->prepare("
            SELECT DAY(po.order_date) AS day, SUM(poi.total_price) AS total
            FROM purchase_order_item poi
            JOIN purchase_order po ON poi.po_id = po.po_id
            WHERE MONTH(po.order_date) = ? AND YEAR(po.order_date) = ?
            GROUP BY day
            ORDER BY day
        ");
        $stmt_daily->bind_param("ii", $selected_month, $selected_year);
        $stmt_daily->execute();
        $result_daily = $stmt_daily->get_result();
        while ($row = $result_daily->fetch_assoc()) {
            $day = (int)$row['day'];
            $daily_sales[$day] = (float)$row['total'];
        }
        ?>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        var ctx = document.getElementById('dailySalesChart').getContext('2d');
        var dailySalesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [
                    <?php
                    for ($d = 1; $d <= $days_in_month; $d++) {
                        echo "'$d'";
                        if ($d < $days_in_month) echo ',';
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Total Sales',
                    backgroundColor: 'rgba(60,141,188,0.7)',
                    borderColor: 'rgba(60,141,188,1)',
                    borderWidth: 1,
                    data: [
                        <?php
                        for ($d = 1; $d <= $days_in_month; $d++) {
                            echo $daily_sales[$d];
                            if ($d < $days_in_month) echo ',';
                        }
                        ?>
                    ]
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Day'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales'
                        }
                    }
                }
            }
        });
        </script>
        
        <br>
<!-- jQuery -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<!-- AdminLTE App -->
<script src="../js/AdminLTE/app.js" type="text/javascript"></script>

<script>
// Update the greeting in real-time (every minute)
function updateGreeting() {
    var hour = new Date().getHours();
    var greeting = '';
    var greetingClass = '';
    var icon = '';
    
    if (hour >= 5 && hour < 12) {
        greeting = 'Good Morning';
        greetingClass = 'morning-greeting';
        icon = 'fa-sun-o';
    } else if (hour >= 12 && hour < 17) {
        greeting = 'Good Afternoon';
        greetingClass = 'afternoon-greeting';
        icon = 'fa-sun-o';
    } else if (hour >= 17 && hour < 20) {
        greeting = 'Good Evening';
        greetingClass = 'evening-greeting';
        icon = 'fa-moon-o';
    } else {
        greeting = 'Good Night';
        greetingClass = 'night-greeting';
        icon = 'fa-star-o';
    }
    
    $('.greeting-container').removeClass('morning-greeting afternoon-greeting evening-greeting night-greeting')
                           .addClass(greetingClass);
    $('.greeting i.weather-icon').removeClass('fa-sun-o fa-moon-o fa-star-o').addClass(icon);
    $('.greeting').html('<i class="fa ' + icon + ' weather-icon"></i> ' + greeting + ', <?php echo htmlspecialchars($hasiluser["Nama"]); ?>');
    
    // Update time display
    var now = new Date();
    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    $('.greeting-time').html('<i class="fa fa-clock-o"></i> ' + now.toLocaleDateString('en-US', options));
}

// Update every minute
setInterval(updateGreeting, 60000);

// Initial call in case page stays open for a while
$(document).ready(function() {
    updateGreeting();
});
</script>
</body>
</html>