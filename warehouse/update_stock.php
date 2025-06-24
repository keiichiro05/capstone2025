<?php
include('../konekdb.php');
session_start();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['username']) || !isset($_SESSION['idpegawai'])) {
    $response['message'] = 'Unauthorized access - please login first';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['action'])) {
    $orderId = mysqli_real_escape_string($mysqli, $_POST['order_id']);
    $action = mysqli_real_escape_string($mysqli, $_POST['action']);
    $pic = $_SESSION['idpegawai'];
    
    // Verify this user has warehouse authorization
    $authCheck = mysqli_query($mysqli, "SELECT COUNT(*) as auth_count FROM authorization 
                                      WHERE username = '{$_SESSION['username']}' AND modul = 'Warehouse'");
    $authResult = mysqli_fetch_assoc($authCheck);
    
    if ($authResult['auth_count'] == 0) {
        $response['message'] = 'You are not authorized to perform warehouse operations';
        echo json_encode($response);
        exit;
    }
    
    // Get order details with JOIN to product information
    $orderQuery = "SELECT po.*, p.Code as product_code, p.Stok as current_stock
                  FROM pemesanan1 po
                  LEFT JOIN warehouse_inventory p ON po.item_name = p.Nama
                  WHERE po.order_id = '$orderId'";
    $orderResult = mysqli_query($mysqli, $orderQuery);
    
    if (mysqli_num_rows($orderResult) == 0) {
        $response['message'] = 'Order not found';
        echo json_encode($response);
        exit;
    }
    
    $order = mysqli_fetch_assoc($orderResult);
    
    // Validate order status
    if ($order['status'] != 'approved' || empty($order['delivery_date'])) {
        $response['message'] = 'Order is not in a deliverable state';
        echo json_encode($response);
        exit;
    }
    
    if (!empty($order['received_date'])) {
        $response['message'] = 'This order has already been received';
        echo json_encode($response);
        exit;
    }
    
    // Validate product exists in warehouse
    if (empty($order['product_code'])) {
        $response['message'] = "Product '{$order['item_name']}' not found in warehouse inventory";
        echo json_encode($response);
        exit;
    }
    
    $quantity = (int)$order['quantity'];
    $currentStock = (int)$order['current_stock'];
    $newStock = $currentStock + $quantity;
    $productCode = $order['product_code'];
    $warehouse = 'main_warehouse'; // Adjust as needed
    
    // Start transaction
    mysqli_begin_transaction($mysqli);
    
    try {
        // 1. Update warehouse inventory
        $updateQuery = "UPDATE warehouse_inventory 
                       SET Stok = '$newStock' 
                       WHERE Code = '$productCode'";
        
        if (!mysqli_query($mysqli, $updateQuery)) {
            throw new Exception('Failed to update warehouse inventory: ' . mysqli_error($mysqli));
        }
        
        // 2. Record inventory movement
        $movementQuery = "INSERT INTO inventory_movement 
                         (product_code, movement_type, quantity, previous_stock, new_stock, movement_date, pic, warehouse, notes)
                         VALUES 
                         ('$productCode', 'inbound', '$quantity', '$currentStock', '$newStock', NOW(), '$pic', '$warehouse', 'Received from PO #$orderId')";
        
        if (!mysqli_query($mysqli, $movementQuery)) {
            throw new Exception('Failed to record inventory movement: ' . mysqli_error($mysqli));
        }
        
        // 3. Update order status to mark as received
        $updateOrderQuery = "UPDATE pemesanan1 
                            SET received_date = NOW(), 
                                received_by = '$pic',
                                status = 'completed'
                            WHERE order_id = '$orderId'";
        
        if (!mysqli_query($mysqli, $updateOrderQuery)) {
            throw new Exception('Failed to update order status: ' . mysqli_error($mysqli));
        }
        
        // Commit transaction
        mysqli_commit($mysqli);
        
        $response['success'] = true;
        $response['message'] = "Successfully received $quantity units of {$order['item_name']}";
        $response['data'] = [
            'product' => $order['item_name'],
            'quantity' => $quantity,
            'previous_stock' => $currentStock,
            'new_stock' => $newStock
        ];
    } catch (Exception $e) {
        mysqli_rollback($mysqli);
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

$response['message'] = 'Invalid request method or missing parameters';
echo json_encode($response);
?>