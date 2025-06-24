<?php
include('../konekdb.php');
include('../koneksi.php');
date_default_timezone_set('Asia/Jakarta');
// Check if the session is already started
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
// Check if the session variable 'idpegawai' is set
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
$user=$_SESSION['username'];
$getuser=mysqli_query($mysqli, "select id_pegawai from authorization where Username='$user'");
$getiduser=mysqli_fetch_array($getuser);
$iduser=$getiduser['id_pegawai'];
$tgl=date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y')));

if(!isset($_GET['a'])){
$a=$_POST['a'];
$p=$_POST['p'];
} else {
$a=$_GET['a'];
$p=$_GET['p'];
$ids=$_GET['ids'];
}
$harga=$_GET['harga'];
if($a=="acc"){
$ambil=mysqli_query($mysqli, "select id_transaksi from transaksi where id_supplier='$ids' and tanggal='$tgl'");
$ambil2=mysqli_fetch_array($ambil);
if($ambil2==NULL){
$cek=NULL;
while($cek==NULL ){
	$idt=rand(111111, 999999);
	$cek2=mysqli_query($mysqli,"SELECT * FROM transaksi WHERE id_transaksi='$idt'");
	$cekcek=mysqli_fetch_array($cek2);
	if($cekcek==NULL){
		$cek=1;
	$idt=$idt;
	}
}} else{
$idt=$ambil2['id_transaksi'];
}
$show=mysqli_query($mysqli, "insert into transaksi values ('$idt','$tgl','$p','$ids','0')"); 
$show2=mysqli_query($mysqli, "update pemesanan1 set harga='$harga', status='1' where order_id='$p'");
if($show && $show2){
header("location:index.php");
}
} else if($a=="dc"){
$show=mysqli_query($mysqli, "update pemesanan1 set status='2' where order_id='$p'");
if($show){
header("location:index.php");
}
} else if($a=="siap"){
$show3=mysqli_query($mysqli, "update transaksi set status='3' where id_transaksi='$p'"); 
$show=mysqli_query($mysqli, "select * from pemesanan1 where order_id in (select order_id from transaksi where id_transaksi='$p')");
$ket=$_POST['keterangan'];
while($get=mysqli_fetch_array($show)){
$nama=$get['namabarang'];
$tot=$get['harga'];
$out=mysqli_query($mysqli, "insert into pengeluaran (id_pegawai, Nama, Tanggal, Keterangan, Total) values ('$iduser','$nama','$tgl','$ket','$tot')");
}

if($show3 && $show && $out){
header("location:transaksi.php");
}
} else if($a=="fin"){
$show=mysqli_query("select * from pemesanan1 where order_id in (select order_id from transaksi where id_transaksi='$p')");

while($get=mysqli_fetch_array($show)){
$nama=$get['namabarang'];
$cek=mysqli_query($mysqli, "select count(Nama) as tot from warehouse where Nama='$nama'");
$cek2=mysqli_fetch_array($cek);
if($cek2['tot']!="0"){
$show2=mysqli_query("select * from warehouse where Nama='$nama'");
$ids=$get['order_id'];
$get2=mysqli_fetch_array($show2);
$tot1=$get['jumlah'];
$tot2=$get2['Stok'];
$tot=$tot1+$tot2;
$harga1=$get['harga'];
$harga2=$get2['Harga'];
$harga=$harga1+$harga2;
$satuan=$get['satuan'];
$id=$get2['id_barang'];
$new=mysqli_query($mysqli, "update warehouse set Stok=$tot, Harga='$harga' where id_barang='$id'");
} else {
$harga=$get['harga'];
$tot=$get['jumlah'];
$satuan=$get['satuan'];
$jenis=$get['jenis'];
$new=mysqli_query($mysqli, "insert into warehouse (Nama, Stok, Jenis, Harga, Satuan) values ('$nama','$tot', '$jenis','$harga','$satuan')");
}
}
$show4=mysqli_query($mysqli, "update transaksi set status='6' where id_transaksi='$p'"); 
if($show && $show4 && $new){
header("location:laporan.php");
}
};

?>