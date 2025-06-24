<?php
include('../konekdb.php');
date_default_timezone_set('Asia/Jakarta');
session_start();
$idpegawai=$_SESSION['idpegawai'];
if(!isset($_SESSION['username'])){
	header("location:../index.php?status=please login first");
	exit();
	}
if(!isset($_SESSION['idpegawai'])){
    header("location:../index.php?status=please login first");
    exit();
    }

$p=$_GET['p'];
$a=$_GET['a'];
if($a=="acc"){
$cek=NULL;
while($cek==NULL ){
	$idt=rand(111111, 999999);
	$cek2=mysqli_query("SELECT * FROM transaksi WHERE id_transaksi='$idt'");
	$cekcek=mysqli_fetch_array($cek2);
	if($cekcek==NULL){
		$cek=1;
	$idt=$idt;
	}
}
$show=mysqli_query("insert into transaksi values ('$idt','$tgl','$p','$ids','0')"); 
$show2=mysqli_query("update pemesanan1 set status='1' where order_id='$p'");
if($show && $show2){
header("location:index.php");
}
} else {
$show=mysqli_query("update pemesanan1 set status='2' where order_id='$p'");
if($show){
header("location:index.php");
}
};

?>