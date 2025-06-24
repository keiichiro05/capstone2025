<?php

$host="localhost"; // Host name 
$username="root"; // mysqli username 
$passworder_id=""; // mysqli passworder_id 
$db_name="E-pharm"; // Database name 
$tbl_name="penggajian"; // Table name 

// Connect to server and select database.
mysqli_connect("$host", "$username", "$passworder_id")or die("cannot connect"); 
mysqli_select_db("$db_name")or die("cannot select DB");

// Get values from form 
$id_pegawai=$_POST['id_pegawai'];
$cuti=$_POST['cuti'];
$aktif=$_POST['aktif'];
$lembur=$_POST['lembur'];
$tgl=date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));


// Insert data into mysqli 
$sql="INSERT INTO $tbl_name(id_pegawai, hari_aktif,cuti, lembur, date)VALUES('$id_pegawai', '$aktif', '$cuti', '$lembur', '$tgl')";
$result=mysqli_query($sql);


$result2 = mysqli_query("SELECT p.id_pegawai, p.gaji, g.hari_aktif, g.cuti, g.lembur, round(p.gaji-(p.gaji/g.hari_aktif*g.cuti)+(p.gaji/g.hari_aktif*g.lembur)) as total
FROM pegawai p, penggajian g
WHERE p.id_pegawai=g.id_pegawai AND g.id_pegawai='$id_pegawai' AND g.date='$tgl'");
$gettotal=mysqli_fetch_array($result2);
echo $gettotal['total'];
$insert=mysqli_query("update penggajian set total='$gettotal[total]' where id_pegawai='$id_pegawai'");
// if successfully insert data into database, displays message "Successful". 
if($result && $insert && $result2){
header("location:penggajian.php");
}

else {
echo "ERROR";
}
?> 

<?php 
// close connection 
mysqli_close();
?>