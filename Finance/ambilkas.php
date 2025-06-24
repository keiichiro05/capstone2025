<?php 
	include "../config.php";
	$bulan = $_GET['bulan'];

	$qsaldo = mysqli_fetch_array(mysqli_query("select *, DATE_FORMAT(tanggal,'%d-%m-%Y') as tanggal1 from saldo WHERE DATE_FORMAT( tanggal, '%m' ) = '$bulan'"));
	$hasil = mysqli_query("SELECT *, DATE_FORMAT(tanggal,'%d-%m-%Y') as tanggal1 FROM `pengeluaran` 
                        WHERE DATE_FORMAT( tanggal, '%m' ) = '$bulan'
                        UNION 
                        SELECT *,DATE_FORMAT(tanggal,'%d-%m-%Y') as tanggal1 FROM `pemasukan` 
                        WHERE DATE_FORMAT( tanggal, '%m' ) = '$bulan'
                        order_id BY tanggal");

	/*echo "<tr><td id=\"atas\">Kode Lelang</td><td id=\"atas\">Nama Lelang</td><td id=\"atas\">Jenis</td><td id=\"atas\">Pemilik Lelang</td><td id=\"atas\">HPS</td><td id=\"atas\">Batas Lelang</td></tr>\n";
	while($r = mysqli_fetch_array($hasil)){ 
		echo "<tr><td><a href=\"lelang.php\">KD-00".$r[0]."</a></td><td style=\"text-align:left\"><a href=\"lelang.php\">$r[1]</a></td><td>$r[6]</td><td style=\"text-align:left\">$r[5]</td><td>$r[2]</td><td>$r[4]</td></tr>";
	} 
	*/
	if ($qsaldo[0]==0) { 
        if (mysqli_num_rows($hasil)==0) { 
?>
				<div class="pad margin no-print">
                    <div class="alert alert-info" style="margin-bottom: 0!important;">
                        <i class="fa fa-info"></i>
                        <center><b>No result found.</b></center>
                    </div>
                </div>
<?php
        }
	}else{
?>

										<thead>
                                            <tr style=" background-color: #428bca; border_id-color: #428bca; color: #ffffff;">
                                                <th style="text-align : center;">Tanggal</th>
                                                <th style="text-align : center;">Keterangan</th>
                                                <th style="text-align : center;">Kode</th>
                                                <th style="text-align : center;">Debet</th>
                                                <th style="text-align : center;">Kredit</th>
                                                <th style="text-align : center;">Saldo</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        <tr>
                                            <td align="center"><?php echo $qsaldo['tanggal1']; ?></td>
                                            <td>Saldo Awal</td>
                                            <td align="center"><?php echo "SD-".$qsaldo[0]; ?></td>
                                            <td align="right"><?php echo $qsaldo['Jumlah']; ?></td>
                                            <td align="right">-</td>
                                            <td align="right"><?php echo $qsaldo['Jumlah']; ?></td>
                                        </tr>

<?php
	$saldo = $qsaldo['Jumlah'];
	$total_debet = $saldo;
    $total_kredit = 0;

    while($kas=mysqli_fetch_array($hasil)){
        if($kas['Kode']=="DB-"){
            $debet = $kas['Total'];
            $kredit = "-";
            $saldo = $saldo + $debet;
        }else{
             $kredit = $kas['Total'];
             $debet = "-";
             $saldo = $saldo - $kredit;                                   
        }

             $total_debet = $total_debet + $debet;
             $total_kredit = $total_kredit + $kredit;
?>
        <tr>
            <td align="center"><?php echo $kas['tanggal1']; ?></td>
            <td><?php echo $kas['Nama']; ?></td>
            <td align="center"><?php echo $kas['Kode'].$kas[1]; ?></td>
            <td align="right"><?php echo $debet; ?></td>
            <td align="right"><?php echo $kredit; ?></td>
            <td align="right"><?php echo $saldo; ?></td>
        </tr>                                    
                                        
<?php
    }
?>
									</tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3"  style="text-align : right;">Jumlah</th>
                                                <th style="text-align : right;"><?php echo $total_debet; ?></th>
                                                <th style="text-align : right;"><?php echo $total_kredit; ?></th>
                                                <th style="text-align : right;"><?php echo $saldo; ?></th>
                                            </tr>
                                        </tfoot>

<?php
    }
?>