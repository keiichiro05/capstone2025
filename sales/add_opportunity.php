<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

$iduser = $_SESSION['idpegawai'];
$usersql = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai='$iduser'");
$hasiluser = mysqli_fetch_array($usersql);

// Get accounts for dropdown
$accountsql = mysqli_query($mysqli, "SELECT account_id, account_name FROM account ORDER BY account_name");
$accounts = [];
while ($row = mysqli_fetch_assoc($accountsql)) {
    $accounts[] = $row;
}

// Get contacts for dropdown
$contactsql = mysqli_query($mysqli, "SELECT id, first_name FROM contact ORDER BY first_name");
$contacts = [];
while ($row = mysqli_fetch_assoc($contactsql)) {
    $contacts[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Generate unique opportunity ID
    $opp_id = 'OPP-' . uniqid();
    
    $opp_name = mysqli_real_escape_string($mysqli, $_POST['opp_name']);
    $account_id = mysqli_real_escape_string($mysqli, $_POST['account_id']);
    $contact_id = mysqli_real_escape_string($mysqli, $_POST['contact_id']);
    $start_date = date('Y-m-d'); // Automatically set to current date
    $close_date = mysqli_real_escape_string($mysqli, $_POST['close_date']);
    $expected_value = mysqli_real_escape_string($mysqli, $_POST['expected_value']);
    $chance_of = mysqli_real_escape_string($mysqli, $_POST['chance_of']);
    $status = mysqli_real_escape_string($mysqli, $_POST['status']);
    $sales_phase = mysqli_real_escape_string($mysqli, $_POST['sales_phase']);
    $business_line = mysqli_real_escape_string($mysqli, $_POST['business_line']);
    $source = mysqli_real_escape_string($mysqli, $_POST['source']);
    
    $insert_sql = "INSERT INTO opportunity (
        opp_id, opp_name, account_id, contact_id, start_date, close_date, 
        expected_value, chance_of, status, sales_phase, business_line, source
    ) VALUES (
        '$opp_id', '$opp_name', '$account_id', '$contact_id', '$start_date', '$close_date', 
        '$expected_value', '$chance_of', '$status', '$sales_phase', '$business_line', '$source'
    )";


    
    if (mysqli_query($mysqli, $insert_sql)) {
        header("location:opportunity.php?msg=Opportunity+added+successfully");
        exit();
    } else {
        $error = "Error: " . mysqli_error($mysqli);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>E-pharm | Add Opportunity</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <link href="../css/modern-3d.css" rel="stylesheet" type="text/css" />
</head>
<body class="skin-blue">
<header class="header">
    <a href="index.php" class="logo">E-pharm</a>
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

            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                <li><a href="account.php"><i class="fa fa-users"></i> <span>Account</span></a></li>
                <li><a href="contact.php"><i class="fa fa-envelope"></i> <span>Contact</span></a></li>
                <li><a href="products.php"><i class="fa fa-archive"></i> <span>Product</span></a></li>
                <li><a href="product_request.php"><i class="fa fa-plus-square"></i> <span>Product Request</span></a></li>
                <li><a href="leads.php"><i class="fa fa-shopping-cart"></i> <span>Leads</span></a></li>
                <li class="active"><a href="opportunity.php"><i class="fa fa-lightbulb-o"></i> <span>Opportunity</span></a></li>
                <li><a href="quotation.php"><i class="fa fa-truck"></i> <span>Quotation</span></a></li>
                <li><a href="purchase_order.php"><i class="fa fa-clipboard"></i> Purchase Order</a></li>
            </ul>
        </section>
    </aside>

    <aside class="right-side">
        <section class="content-header">
            <h1>Add Opportunity <small>Create new opportunity</small></h1>
            <ol class="breadcrumb">
                <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="opportunity.php">Opportunity</a></li>
                <li class="active">Add Opportunity</li>
            </ol>
        </section>

        <section class="content">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Opportunity Information</h3>
                </div>
                
                <form role="form" method="post" action="add_opportunity.php">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="opp_name">Opportunity Name*</label>
                                    <?php
                                    // Ambil lead name jika ada parameter lead_id di URL
                                    $lead_name = '';
                                    if (isset($_GET['lead_id'])) {
                                        $lead_id = mysqli_real_escape_string($mysqli, $_GET['lead_id']);
                                        $lead_query = mysqli_query  ($mysqli, "SELECT lead_name FROM leads WHERE lead_id='$lead_id' LIMIT 1");
                                        if ($lead_row = mysqli_fetch_assoc($lead_query)) {
                                            $lead_name = $lead_row['lead_name'];
                                        }
                                    }
                                    ?>
                                    <input type="text" class="form-control" id="opp_name" name="opp_name" value="<?php echo htmlspecialchars($lead_name); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="account_id">Account*</label>
                                    <?php
                                    // Ambil account_id dari leads jika ada parameter lead_id di URL
                                    $selected_account_id = '';
                                    if (isset($_GET['lead_id'])) {
                                        $lead_id = mysqli_real_escape_string($mysqli, $_GET['lead_id']);
                                        $lead_query = mysqli_query($mysqli, "SELECT account_id FROM leads WHERE lead_id='$lead_id' LIMIT 1");
                                        if ($lead_row = mysqli_fetch_assoc($lead_query)) {
                                            $selected_account_id = $lead_row['account_id'];
                                        }
                                    }
                                    ?>
                                    <select class="form-control" id="account_id" name="account_id" required <?php echo $selected_account_id ? 'readonly disabled' : ''; ?>>
                                        <option value="">-- Select Account --</option>
                                        <?php foreach ($accounts as $account): ?>
                                            <option value="<?php echo $account['account_id']; ?>"
                                                <?php if ($selected_account_id && $account['account_id'] == $selected_account_id) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($account['account_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($selected_account_id): ?>
                                        <input type="hidden" name="account_id" value="<?php echo htmlspecialchars($selected_account_id); ?>">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_id">Contact*</label>
                                    <?php
                                    // Ambil contact_id dari leads jika ada parameter lead_id di URL
                                    $selected_contact_id = '';
                                    if (isset($_GET['lead_id'])) {
                                        $lead_id = mysqli_real_escape_string($mysqli, $_GET['lead_id']);
                                        $lead_query = mysqli_query($mysqli, "SELECT contact_id FROM leads WHERE lead_id='$lead_id' LIMIT 1");
                                        if ($lead_row = mysqli_fetch_assoc($lead_query)) {
                                            $selected_contact_id = $lead_row['contact_id'];
                                        }
                                    }
                                    ?>
                                    <select class="form-control" id="contact_id" name="contact_id" required <?php echo $selected_contact_id ? 'readonly disabled' : ''; ?>>
                                      
                                        <?php foreach ($contacts as $contact): ?>
                                            <option value="<?php echo $contact['id']; ?>"
                                                <?php if ($selected_contact_id && $contact['id'] == $selected_contact_id) echo 'selected'; ?>>
                                                <?php echo htmlspecialchars($contact['first_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($selected_contact_id): ?>
                                        <input type="hidden" name="contact_id" value="<?php echo htmlspecialchars($selected_contact_id); ?>">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="start_date">Start Date*</label>
                                    <input type="text" class="form-control" id="start_date" name="start_date" 
                                           value="<?php echo date('Y-m-d'); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="close_date">Close Date*</label>
                                    <input type="date" class="form-control" id="close_date" name="close_date" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expected_value">Expected Value*</label>
                                    <input type="number" step="0.01" class="form-control" id="expected_value" name="expected_value" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="chance_of">Chance Of*</label>
                                    <select class="form-control" id="chance_of" name="chance_of" required>
                                        <option value="">-- Select Chance --</option>
                                        <option value="0%">0%</option>
                                        <option value="20%">20%</option>
                                        <option value="40%">40%</option>
                                        <option value="60%">60%</option>
                                        <option value="100%">100%</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Status*</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="">-- Select Status --</option>
                                        <option value="lost">Lost</option>
                                        <option value="stopped">Stopped</option>
                                        <option value="won">Won</option>
                                        <option value="in progress">In Progress</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="sales_phase">Sales Phase*</label>
                                    <select class="form-control" id="sales_phase" name="sales_phase" required>
                                        <option value="">-- Select Phase --</option>
                                        <option value="qualification">Qualification</option>
                                        <option value="proposal">Proposal</option>
                                        <option value="negotiation">Negotiation</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="business_line">Business Line*</label>
                                    <select class="form-control" id="business_line" name="business_line" required>
                                        <option value="">-- Select Business Line --</option>
                                        <option value="cho">CHO</option>
                                        <option value="sho">SHO</option>
                                        <option value="qho">QHO</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="source">Source</label>
                                    <input type="text" class="form-control" id="source" name="source">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Save Opportunity</button>
                        <a href="opportunity.php" class="btn btn-default">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </aside>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js" type="text/javascript"></script>
<script src="../js/AdminLTE/app.js" type="text/javascript"></script>
</body>
</html>