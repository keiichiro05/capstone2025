<?php
include('../konekdb.php');

if (isset($_GET['id'])) {
    $order_idId = mysqli_real_escape_string($mysqli, $_GET['id']);
    $query = mysqli_query($mysqli, "SELECT * FROM dariwarehouse WHERE no = '$order_idId'");
    
    if ($order_id = mysqli_fetch_assoc($query)) {
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h4>Product Information</h4>';
        echo '<table class="table table-border_ided">';
        echo '<tr><th>Product Code</th><td>'.$order_id['code'].'</td></tr>';
        echo '<tr><th>Product Name</th><td>'.$order_id['nama'].'</td></tr>';
        echo '<tr><th>Category</th><td>'.$order_id['kategori'].'</td></tr>';
        echo '<tr><th>Quantity</th><td>'.$order_id['jumlah'].'</td></tr>';
        echo '<tr><th>Unit</th><td>'.$order_id['satuan'].'</td></tr>';
        echo '</table>';
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h4>order_id Information</h4>';
        echo '<table class="table table-border_ided">';
        echo '<tr><th>Reorder_id Level</th><td>'.$order_id['reorder_id'].'</td></tr>';
        echo '<tr><th>Supplier</th><td>'.$order_id['supplier'].'</td></tr>';
        echo '<tr><th>Branch</th><td>'.$order_id['cabang'].'</td></tr>';
        echo '<tr><th>Status</th><td>';
        
        if ($order_id['status'] === "0") {
            echo '<span class="status-badge status-pending">Pending</span>';
        } elseif ($order_id['status'] === "1") {
            echo '<span class="status-badge status-accepted">Accepted</span>';
        } elseif ($order_id['status'] === "2") {
            echo '<span class="status-badge status-rejected">Rejected</span>';
        }
        
        echo '</td></tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="row">';
        echo '<div class="col-md-12 text-center">';
        echo '<img src="https://barcode.tec-it.com/barcode.ashx?data='.$order_id['code'].'&code=Code128&dpi=96" style="height:80px;">';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger">order_id not found</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request</div>';
}
?>