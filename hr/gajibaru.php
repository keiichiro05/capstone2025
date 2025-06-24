<?php

$host="localhost"; // Host name 
$username="root"; // mysqli username 
$passworder_id=""; // mysqli passworder_id 
$db_name="E-pharm"; // Database name 
$tbl_name="pegawai"; // Table name 

// Connect to server and select database.
mysqli_connect("$host", "$username", "$passworder_id")or die("cannot connect"); 
mysqli_select_db("$db_name")or die("cannot select DB");

// Get values from form 
$gaji=$_GET['gaji'];


// Insert data into mysqli 
$sql="INSERT INTO $tbl_name(gaji)VALUES('$gaji')";
$result=mysqli_query($sql);

// if successfully insert data into database, displays message "Successful". 
if($result){
echo "<a href='index.php'>GO</a>";
}

else {
echo "ERROR";
}
?> 

<?php 
// close connection 
mysqli_close();
?>