<?php
session_start();
$idpegawai=$_SESSION['idpegawai'];
if(!isset($_SESSION['username'])){
	header("location:../index.php?status=please login first");
	exit();
	}
?>
<?php
include('../konekdb.php');
session_start();

// Check authorization
$username = $_SESSION['username'];
$cekuser = mysqli_query($mysqli, "SELECT count(username) as jmluser FROM authorization WHERE username = '$username' AND modul = 'Adminwarehouse'");
$user = mysqli_fetch_array($cekuser);
if($user['jmluser'] == "0") {
    header("location:../index.php");
    exit();
}
// Check if action and id parameters exist
if(isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']); // Sanitize input
    
    // Validate action
    if($action == 'accept' || $action == 'reject') {
        $status = ($action == 'accept') ? 1 : 2;
        
        // Update the status in database
        $update = mysqli_query($mysqli, "UPDATE pemesanan SET status = $status WHERE id = $id");
        
        if($update) {
            // Log the action
            $log = mysqli_query($mysqli, "INSERT INTO log_aksi 
                (user_id, aksi, tabel, id_recorder_id, waktu) 
                VALUES (
                    '$idpegawai',
                    '$action',
                    'pemesanan',
                    '$id',
                    NOW()
                )");
                
            // Redirect with success message
            header("location:daftarACC.php?success=$action");
        } else {
            // Redirect with error message
            header("location:daftarACC.php?error=db");
        }
    } else {
        // Invalid action
        header("location:daftarACC.php?error=invalid");
    }
} else {
    // Missing parameters
    header("location:daftarACC.php");
}
exit();
?>