<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php?status=Silakan login dulu");
    exit();
}
?>
<?php
$server = "localhost";
$user = "root";
$passworder_id = "";
$mysqli = new mysqli($server, $user, $passworder_id, "E-pharm");

if ($mysqli->connect_error) {
	die("Connection failed: " . $mysqli->connect_error);
}

?>