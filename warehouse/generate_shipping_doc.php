<?php
include('../konekdb.php');

$doc_number = $_GET['doc'] ?? null;
if (!$doc_number) die('Invalid document number');

// Get document from database
$doc_query = mysqli_query($mysqli, "SELECT * FROM shipping_documents WHERE doc_number = '$doc_number'");
$document = mysqli_fetch_assoc($doc_query);

if ($document) {
    header('Content-Type: text/html');
    echo $document['content'];
} else {
    echo "Document not found";
}
?>