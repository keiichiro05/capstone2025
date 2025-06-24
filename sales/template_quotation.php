<?php
include "konekdb.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("location:../index.php");
    exit();
}

if (!isset($_GET['opp_id'])) {
    die("Opportunity ID is missing.");
}

$opp_id = $_GET['opp_id'];

// Fetch opportunity
$stmt = $mysqli->prepare("SELECT * FROM opportunity WHERE opp_id = ?");
$stmt->bind_param("s", $opp_id);
$stmt->execute();
$opp_result = $stmt->get_result();
$opp = $opp_result->fetch_assoc();
$stmt->close();
if (!$opp) {
    die("Opportunity not found.");
}

// Fetch account
$account_id = $opp['account_id'];
$stmt = $mysqli->prepare("SELECT * FROM account WHERE account_id = ?");
$stmt->bind_param("s", $account_id);
$stmt->execute();
$account_result = $stmt->get_result();
$account = $account_result->fetch_assoc();
$stmt->close();

// Fetch contact
$contact_id = $opp['contact_id'];
$stmt = $mysqli->prepare("SELECT * FROM contact WHERE id = ?");
$stmt->bind_param("s", $contact_id);
$stmt->execute();
$contact_result = $stmt->get_result();
$contact = $contact_result->fetch_assoc();
$stmt->close();

// Get company info (fallback to first account)
$company_q = mysqli_query($mysqli, "SELECT * FROM account ORDER BY account_id ASC LIMIT 1");
$company = mysqli_fetch_assoc($company_q);

// Prepare data for display
$opp['nama_akun'] = $account['account_name'] ?? '';
$opp['alamat_akun'] = $account['address'] ?? '';
$opp['kota_akun'] = $account['city'] ?? '';
$opp['provinsi_akun'] = $account['state'] ?? '';
$opp['negara_akun'] = $account['country'] ?? '';
$opp['kode_pos_akun'] = '';
$opp['tanggal_akun_dibuat'] = $account['created_at'] ?? '';

$opp['nama_kontak'] = $contact['name'] ?? '';
$opp['email_kontak'] = $contact['email'] ?? '';
$opp['telepon_kontak'] = $contact['phone'] ?? '';

$company['nama_perusahaan'] = $company['account_name'] ?? '';
$company['alamat'] = $company['address'] ?? '';
$company['kota'] = $company['city'] ?? '';
$company['provinsi'] = $company['state'] ?? '';
$company['negara'] = $company['country'] ?? '';
$company['kode_pos'] = '';
$company['email'] = $company['email'] ?? '';
$company['telepon'] = $company['telepon'] ?? '';

// Get user
$iduser = isset($_SESSION['idpegawai']) ? $_SESSION['idpegawai'] : null;
$user = null;
if ($iduser) {
    $user_q = mysqli_query($mysqli, "SELECT * FROM pegawai WHERE id_pegawai = '$iduser'");
    $user = mysqli_fetch_assoc($user_q);
}
if (!$user) {
    $user = ['nama' => ''];
}

// Get quotation
$quote_q = mysqli_query($mysqli, "SELECT * FROM quotation WHERE opp_id = '$opp_id' LIMIT 1");
$quotation = mysqli_fetch_assoc($quote_q);
if (!$quotation) {
    die("Quotation not found for this Opportunity.");
}
$quotation_id = $quotation['quotation_id'];

// Cek apakah quotation_date belum diisi
if (empty($quotation['quotation_date']) || $quotation['quotation_date'] == '0000-00-00') {
    $quotation_no = $quotation['quotation_no']; // atau bisa kamu generate juga
    $quotation_date = date('Y-m-d');
    $due_date = date('Y-m-d', strtotime('+3 months'));

    // Update database
    $stmt = $mysqli->prepare("UPDATE quotation SET quotation_date = ?, due_date = ? WHERE quotation_id = ?");
    $stmt->bind_param("ssi", $quotation_date, $due_date, $quotation_id);
    $stmt->execute();
    $stmt->close();

    // Refresh quotation dari database agar tampil yang baru
    $quote_q = mysqli_query($mysqli, "SELECT * FROM quotation WHERE quotation_id = '$quotation_id' LIMIT 1");
    $quotation = mysqli_fetch_assoc($quote_q);
}

// Get quotation items
$items_q = mysqli_query($mysqli, "
    SELECT qi.*, w.Nama AS product_name 
    FROM quotation_item qi
    JOIN warehouse w ON qi.product_code = w.Code
    WHERE qi.quotation_id = '$quotation_id'
");

$items = [];
$grand_total = 0;
$total_discount = 0;
while ($item = mysqli_fetch_assoc($items_q)) {
    $grand_total += $item['total'];
    $total_discount += ($item['price'] * $item['quantity']) - $item['total'];
    $items[] = $item;
}
?>

<!-- HTML START -->
<!DOCTYPE html>
<html>
<head>
    <title>Quotation - <?php echo htmlspecialchars($opp['opp_name']); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fa;
            color: #222;
            margin: 0;
            padding: 0;
        }
        .quotation-container {
            background: #fff;
            max-width: 800px;
            margin: 40px auto;
            padding: 32px 40px 40px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            letter-spacing: 2px;
            color: #2d3e50;
        }
        .header h2 {
            margin: 8px 0 0 0;
            font-size: 20px;
            color: #888;
            font-weight: 400;
            letter-spacing: 1px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .info-block {
            width: 48%;
            background: #f2f4f8;
            padding: 16px 18px;
            border-radius: 6px;
            font-size: 15px;
        }
        .info-block strong {
            font-size: 16px;
            color: #2d3e50;
        }
        hr {
            border: 0;
            border-top: 2px solid #e0e0e0;
            margin: 28px 0 18px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 10px 8px;
            text-align: left;
        }
        th {
            background: #f7fafc;
            font-weight: 600;
            color: #2d3e50;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 12px;
            width: 100%;
            max-width: 350px;
            float: right;
        }
        .totals td {
            border: none;
            padding: 6px 8px;
        }
        .totals .label {
            color: #666;
        }
        .totals .value {
            font-weight: 600;
        }
        .section-title {
            font-weight: bold;
            margin: 22px 0 10px 0;
            color: #2d3e50;
            font-size: 17px;
        }
        .signature {
            margin-top: 60px;
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 13px;
            color: #888;
        }
        .no-print {
            margin-top: 30px;
            text-align: center;
        }
        .no-print button, .no-print a {
            background: #2d3e50;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 5px;
            margin: 0 8px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }
        .no-print button:hover, .no-print a:hover {
            background: #1a2533;
        }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .quotation-container {
                box-shadow: none;
                border-radius: 0;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    

<div class="quotation-container">
    <div class="header">
        <h1>U - PSN</h1>
        <h2>QUOTATION</h2>
    </div>
    <div class="info-row" style="margin-bottom:0;">
        <div class="info-block" style="width:32%;">
            <strong>From:</strong><br>
            KANTOR PUSAT<br>
            Kawasan Karyadeka Pancamurni<br>
            Blok A Kav. 3, Cikarang Selatan<br>
            Bekasi 17530<br>
            Indonesia<br>
            Telephone: +62 21 8990 8111<br>
        </div>
        <div class="info-block" style="width:32%;">
            <strong>To:</strong><br>
            <?php echo htmlspecialchars($opp['nama_akun']); ?><br>
            <?php echo htmlspecialchars($opp['alamat_akun']); ?><br>
            <?php echo htmlspecialchars($opp['kota_akun']); ?>, 
            <?php echo htmlspecialchars($opp['provinsi_akun']); ?>, 
            <?php echo htmlspecialchars($opp['negara_akun']); ?><br>
            <strong>Contact Person:</strong>
            <?php echo htmlspecialchars($opp['nama_kontak']); ?><br>
            Email: <?php echo htmlspecialchars($opp['email_kontak']); ?><br>
            Phone: <?php echo htmlspecialchars($opp['telepon_kontak']); ?>
        </div>
        
        <div class="info-block" style="width:32%;">
            <table style="width:100%;border:none;font-size:14px;">
                <tr>
                    <td style="border:none;padding:2px 0;"><strong>Quotation No:</strong></td>
                    <td style="border:none;padding:2px 0;">
                        <?php
                            // Format: Q -001
                            $q_number = isset($quotation['quotation_no']) && $quotation['quotation_no'] !== '' 
                                ? $quotation['quotation_no'] 
                                : $quotation['quotation_id'];
                            echo 'Q -' . str_pad($q_number, 3, '0', STR_PAD_LEFT);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td style="border:none;padding:2px 0;"><strong>Date:</strong></td>
                   <td style="border:none;padding:2px 0;"><?php echo date('M d, Y', strtotime($quotation['quotation_date'])); ?></td>
                </tr>
                <tr>
                    <td style="border:none;padding:2px 0;"><strong>Due Date:</strong></td>
                    <td style="border:none;padding:2px 0;"><?php echo date('M d, Y', strtotime($quotation['due_date'])); ?></td>
                </tr>
                <tr>
                    <td style="border:none;padding:2px 0;"><strong>Country Of Supply:</strong></td>
                    <td style="border:none;padding:2px 0;"><?php echo htmlspecialchars($opp['negara_akun']); ?></td>
                </tr>
                <tr>
                    <td style="border:none;padding:2px 0;"><strong>Place Of Supply:</strong></td>
                    <td style="border:none;padding:2px 0;"><?php echo htmlspecialchars($opp['provinsi_akun']); ?></td>
                </tr>
            </table>
        </div>
    </div>
    

    <hr>

    <table>
        <thead>
            <tr>
                <th>Item Description</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td class="text-right"><?php echo $item['quantity']; ?></td>
                <td class="text-right">Rp <?php echo number_format($item['price'], 2); ?></td>
                <td class="text-right"><?php echo $item['discount']; ?>%</td>
                <td class="text-right">Rp <?php echo number_format($item['total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="label">Subtotal</td><td class="value text-right">Rp <?php echo number_format($grand_total + $total_discount, 2); ?></td></tr>
        <tr><td class="label">Total Discount</td><td class="value text-right">Rp <?php echo number_format($total_discount, 2); ?></td></tr>
        <tr><td class="label"><strong>Grand Total</strong></td><td class="value text-right"><strong>Rp <?php echo number_format($grand_total, 2); ?></strong></td></tr>
    </table>
    <div style="clear:both"></div>

    <div class="section-title">Terms and Conditions</div>
    <ol>
        <li>Please pay within 15 days from the date of invoice, overdue interest @ 14% will be charged on delayed payments.</li>
        <li>Please quote invoice number when remitting funds.</li>
    </ol>

    <div class="section-title">Additional Notes</div>
    <p>Thank you for your business. If you have any questions about this quotation, please contact us.</p>

    <div class="signature">
        <br>
        <p style="margin-top:30px;">_________________________</p>
        <p style="margin-top:10px;font-weight:bold;">Elvin Natalia Sidabutar</p>
        <p style="margin-top:5px;">Sales Team</p>
    </div>

        <div class="footer">
        For any enquiries, email us at <b>help@psn.co.id</b> or call <b>+62 21 8990 8111</b>
        </div>

        <div class="no-print">
        <form method="post" action="export_quotation_pdf.php" target="_blank" style="display:inline;">
            <input type="hidden" name="quotation_id" value="<?php echo htmlspecialchars($quotation_id); ?>">
            <button type="submit">Download PDF</button>
        </form>
        <a href="opportunity.php?opp_id=<?php echo $opp_id; ?>">Back to Opportunity</a>
        </div>
</div>
</body>
</html>