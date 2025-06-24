<?php
$namahost = "localhost";
$username = "root";
$passworder_id = ""; //passworder_id mysqli anda
$database = "e-pharm"; //database anda
$koneksi=mysqli_connect($namahost,$username,$passworder_id) or die("Failed");
mysqli_select_db($database) or die("Database not exist");
?>