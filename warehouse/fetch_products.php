<?php
include('../konekdb.php');
header('Content-Type: application/json');

if (isset($_POST['warehouse'])) {
    $warehouse = mysqli_real_escape_string($mysqli, $_POST['warehouse']);
    
    // Query to get products from the selected warehouse
    $query = "SELECT Code, Nama FROM warehouse WHERE cabang = '$warehouse' order_id BY Nama";
    $result = mysqli_query($mysqli, $query);
    
    if (!$result) {
        die(json_encode(array('error' => mysqli_error($mysqli))));
    }
    
    $products = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    echo json_encode($products);
} else {
    echo json_encode(array('error' => 'No warehouse specified'));
}
?>