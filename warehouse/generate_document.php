<?php
include('../konekdb.php');
session_start();

if(!isset($_SESSION['username'])) {
    header("location:../index.php?status=please login first");
    exit();
}

if (isset($_GET['doc'])) {
    $doc_number = mysqli_real_escape_string($mysqli, $_GET['doc']);
    $doc_query = mysqli_query($mysqli, "SELECT * FROM request_documents WHERE doc_number='$doc_number'");
    $document = mysqli_fetch_assoc($doc_query);
    
    if ($document) {
        // Include TCPDF library
        require_once('../tcpdf/tcpdf.php');
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Pasifik Satelit Nusantara');
        $pdf->SetTitle('Purchase Request ' . $doc_number);
        $pdf->SetSubject('Purchase Request');
        $pdf->SetKeywords('Purchase, Request, Document, PSN');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 20, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Handle logo - solution for PNG alpha channel issue
        $logo_path = 'C:\xampp\htdocs\psn\u-psn\img\psn.jpg';
        $logo_html = '';
        
        if (file_exists($logo_path)) {
            $logo_html = '<img src="' . $logo_path . '" width="120" />';
        }
        
        // Get document data with fallback values
        $department = 'Sales'; // Set default department to Sales
        $requested_by = isset($document['requested_by']) ? htmlspecialchars($document['requested_by']) : '-';
        $created_at = isset($document['created_at']) ? date('d F Y', strtotime($document['created_at'])) : date('d F Y');
        
        // Company header
        $company_header = '
        <div style="text-align:center; margin-bottom:20px;">
            ' . $logo_html . '
            <h1 style="color:#003366;">Pasifik Satelit Nusantara</h1>
            <h2 style="color:#0066cc;">PURCHASE REQUEST</h2>
            <hr style="border:1px solid #003366;">
        </div>
        ';
        
        // Document info table
        $doc_info = '
        <table border="0" cellpadding="5">
            <tr>
                <td width="30%"><strong>Document Number</strong></td>
                <td width="70%">: ' . $doc_number . '</td>
            </tr>
            <tr>
                <td><strong>Date Created</strong></td>
                <td>: ' . $created_at . '</td>
            </tr>
            <tr>
                <td><strong>Department</strong></td>
                <td>: ' . $department . '</td>
            </tr>
            <tr>
                <td><strong>Requested By</strong></td>
                <td>: ' . $requested_by . '</td>
            </tr>
        </table>
        ';
        
        // Items table
        $items_html = isset($document['content']) ? $document['content'] : '<p>No items listed</p>';
        
        // Approval section
        $approval_section = '
        <div style="margin-top: 40px;">
            <table border="0" cellpadding="5" width="100%">
                <tr>
                    <td width="50%" style="text-align:center;">
                        <br><br>
                        <hr style="width:200px; border:1px solid #000;">
                        <strong>Requested By</strong><br>
                        ' . $requested_by . '
                    </td>
                    <td width="50%" style="text-align:center;">
                        <br><br>
                        <hr style="width:200px; border:1px solid #000;">
                        <strong>Approved By</strong><br>
                        Abdul<br>
                        <em>Manager Warehouse</em>
                    </td>
                </tr>
            </table>
        </div>
        ';
        
        // Company footer
        $company_footer = '
        <div style="text-align:center; margin-top:30px; font-size:8pt; color:#666;">
            <hr style="border:1px solid #003366;">
            Pasifik Satelit Nusantara<br>
            Kawasan Karyadeka Pancamurni
            Blok A Kav. 3, Cikarang Selatan
            Bekasi 17530
            Indonesia<br>
            Phone: +62 21 1234567 | Email: info@psn.co.id
        </div>
        ';
        
        // Combine all content
        $full_html = $company_header . $doc_info . '<br>' . $items_html . $approval_section . $company_footer;
        
        // Output the HTML content
        $pdf->writeHTML($full_html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output('PSN_Purchase_Request_' . $doc_number . '.pdf', 'I');
        
    } else {
        die('Document not found');
    }
} else {
    die('Invalid request');
}
?>