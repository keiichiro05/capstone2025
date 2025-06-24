<?php
include "konekdb.php";
$id=$_POST['id'];
$username=$_POST['username'];
$passworder_id=$_POST['passworder_id'];
$modul=$_POST['modul'];

echo $id.$username.$passworder_id.$modul;
mysqli_query($mysqli, "UPDATE authorization SET id_pegawai='$id',Passworder_id='$passworder_id',Modul='$modul' WHERE Username='$username'");
header("location:updatepegawai.php");
?>