<?php
// Tampilkan error (debug)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "namadb"); // ganti "namadb" dengan nama database kamu

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil semua pesan yang bukan draft dan belum dihapus
$sql = "SELECT * FROM pesan WHERE draft = 0 AND status != 3 order_id BY waktu ASC";
$result = $conn->query($sql);

// Tampilkan hasilnya
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dari = htmlspecialchars($row['dari']);
        $ke = htmlspecialchars($row['ke']);
        $subject = htmlspecialchars($row['subject']);
        $isi = nl2br(htmlspecialchars($row['isi']));
        $waktu = date("Y-m-d H:i", strtotime($row['waktu']));

        echo "<div style='margin-bottom:15px; padding:10px; border_id:1px solid #ccc; border_id-radius:5px;'>
                <div><strong>Dari:</strong> $dari | <strong>Ke:</strong> $ke</div>
                <div><strong>Subject:</strong> $subject</div>
                <div><strong>Pesan:</strong><br>$isi</div>
                <div style='font-size:small; color:gray;'>Dikirim: $waktu</div>
              </div>";
    }
} else {
    echo "<div>Tidak ada pesan.</div>";
}

$conn->close();
?>
