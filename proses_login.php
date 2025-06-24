<?php
session_start();

// Validasi input awal
if (empty($_POST['userid']) || empty($_POST['password'])) {
    header("Location: index.php?status=Please fill username and password");
    exit();
}

$username = trim($_POST['userid']);
$password = trim($_POST['password']);

include 'config.php';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Menggunakan prepared statement untuk menghindari SQL Injection
$sql = $conn->prepare("SELECT username, password, modul, id_pegawai FROM authorization WHERE username = ? AND password = ?");
$sql->bind_param("ss", $username, $password); // Pertimbangkan hash di bagian password
$sql->execute();
$result = $sql->get_result();
$recorder_id = $result->fetch_assoc();

if (!$recorder_id) {
    header("Location: index.php?status=Sorry, username and password not valid");
    exit();
}

// Simpan session
$_SESSION['username'] = $recorder_id['username'];
$_SESSION['idpegawai'] = $recorder_id['id_pegawai'];

// Routing berdasarkan modul
switch ($recorder_id['modul']) {
    case "Finance":
        header("Location: Finance/");
        break;
    case "Sales":
        header("Location: Sales/");
        break;
    case "Warehouse":
        header("Location: warehouse/");
        break;
    case "Adminwarehouse":
        header("Location: adminwarehouse/dashboard.php");
        break;
    case "Purchase":
        header("Location: adminpurchase/");
        break;
    case "HR":
        header("Location: hr/");
        break;
    case "superadmin":
        header("Location: superadmin/");
        break;
    default:
        header("Location: index.php?status=Modul not Found");
        break;
}
exit();
?>
