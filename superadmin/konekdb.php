<?php
$server = "localhost";
$user = "root";
$passworder_id = "";
$mysqli = mysqli_connect($server, $user, $passworder_id, "E-pharm");

if (mysqli_connect_errno()) {
    echo "Gagal koneksi ke mysqli: " . mysqli_connect_error();
    exit();
}

?>