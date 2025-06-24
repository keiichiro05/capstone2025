<?php 
	include "../config.php";
	$bulan = $_GET['bulan'];

	$total = mysqli_fetch_array(mysqli_query("SELECT sum(total),DATE_FORMAT( tanggal, '%m' ) as tgl
                                            FROM pengeluaran
                                            WHERE DATE_FORMAT( tanggal, '%m' ) = '$bulan'
                                            group by tgl"));
    $hasil = mysqli_query("SELECT a.*, b.nama as pegawai, DATE_FORMAT(tanggal,'%d-%m-%Y') as Tanggal1
                        FROM pengeluaran a, pegawai b
                        WHERE a.id_pegawai = b.id_pegawai AND DATE_FORMAT( tanggal, '%m' ) = '$bulan'
                        order_id BY tanggal");

	/*echo "<tr><td id=\"atas\">Kode Lelang</td><td id=\"atas\">Nama Lelang</td><td id=\"atas\">Jenis</td><td id=\"atas\">Pemilik Lelang</td><td id=\"atas\">HPS</td><td id=\"atas\">Batas Lelang</td></tr>\n";
	while($r = mysqli_fetch_array($hasil)){ 
		echo "<tr><td><a href=\"lelang.php\">KD-00".$r[0]."</a></td><td style=\"text-align:left\"><a href=\"lelang.php\">$r[1]</a></td><td>$r[6]</td><td style=\"text-align:left\">$r[5]</td><td>$r[2]</td><td>$r[4]</td></tr>";
	} 
	*/
	if (mysqli_num_rows($hasil)==0) { 
?>
				<div class="pad margin no-print">
                    <div class="alert alert-info" style="margin-bottom: 0!important;">
                        <i class="fa fa-info"></i>
                        <center><b>No result found.</b></center>
                    </div>
                </div>
<?php
	}else{
?>
                                        <thead>
                                            <tr style=" background-color: #428bca; border_id-color: #428bca; color: #ffffff;">
                                                <th style="text-align : center;">Tanggal</th>
                                                <th style="text-align : center;">Keterangan</th>
                                                <th style="text-align : center;">Kode</th>
                                                <th style="text-align : center;">Oleh</th>
                                                <th style="text-align : center;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php
    $jml = 0;
	while($kas=mysqli_fetch_array($hasil)){
?>
                                        <tr>
                                            <td align="center"><?php echo $kas['Tanggal1']; ?></td>
                                            <td><?php echo $kas['Nama']; ?></td>
                                            <td align="center"><?php echo $kas['Kode'].$kas[1]; ?></td>
                                            <td align="center"><?php echo $kas['pegawai']; ?></td>
                                            <td align="right"><?php echo $kas['Total']; ?></td>
                                        </tr>                                   
                                        
<?php
    }
?>
									</tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4"  style="text-align : right;">Jumlah</th>
                                                <th style="text-align : right;"><?php echo $total[0]; ?></th>
                                            </tr>
                                        </tfoot>
<?php
    }
?>