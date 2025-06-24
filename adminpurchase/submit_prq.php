<?php
include('../konekdb.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    $_SESSION['error'] = "You must be logged in to perform this action.";
    header("location:../index.php");
    exit();
}

// Check if the request is POST and contains PRQ data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_pemesanan1'])) {
    // Initialize variables
    $errors = [];
    
    // Sanitize and validate input data
    $order_id_code = !empty($_POST['order_id_code']) ? mysqli_real_escape_string($mysqli, trim($_POST['order_id_code'])) : NULL;
    $item_name = isset($_POST['item_name']) ? mysqli_real_escape_string($mysqli, trim($_POST['item_name'])) : '';
    $category = isset($_POST['category']) ? mysqli_real_escape_string($mysqli, trim($_POST['category'])) : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
    $unit = isset($_POST['unit']) ? mysqli_real_escape_string($mysqli, trim($_POST['unit'])) : '';
    $id_supplier = isset($_POST['id_supplier']) ? (int)$_POST['id_supplier'] : 0;
    $requested_by = mysqli_real_escape_string($mysqli, $_SESSION['username']);
    $branch = isset($_POST['branch']) ? mysqli_real_escape_string($mysqli, trim($_POST['branch'])) : NULL;
    
    // Validate required fields
    if (empty($item_name)) {
        $errors[] = "Item name is required";
    }
    if (empty($category)) {
        $errors[] = "Category is required";
    }
    if ($quantity <= 0) {
        $errors[] = "Quantity must be greater than 0";
    }
    if ($price <= 0) {
        $errors[] = "Price must be greater than 0";
    }
    if (empty($unit)) {
        $errors[] = "Unit is required";
    }
    if ($id_supplier <= 0) {
        $errors[] = "Supplier must be selected";
    }

    // If no validation errors, proceed with database operation
    if (empty($errors)) {
        // Calculate total price
        $total_price = $quantity * $price;

        // Get supplier name
        $supplier_name = NULL;
        $get_supplier_name_query = mysqli_query($mysqli, "SELECT nama_perusahaan FROM supplier WHERE id_supplier = '$id_supplier'");
        if (mysqli_num_rows($get_supplier_name_query) > 0) {
            $supplier_data = mysqli_fetch_array($get_supplier_name_query);
            $supplier_name = $supplier_data['nama_perusahaan'];
        }

        // Start transaction for data integrity
        mysqli_begin_transaction($mysqli);
        
        try {
            // Prepare and execute the INSERT query for PRQ
            $insert_query = "INSERT INTO pemesanan1 
                            (order_id_code, item_name, category, quantity, price, unit, 
                            supplier_id, supplier_name, requested_by, order_date, status, branch) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 1, ?)";

            $stmt = mysqli_prepare($mysqli, $insert_query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . mysqli_error($mysqli));
            }
            
            mysqli_stmt_bind_param($stmt, 'sssidsssss', 
                $order_id_code, $item_name, $category, $quantity, $price, 
                $unit, $id_supplier, $supplier_name, $requested_by, $branch);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
            }
            
            // Get the inserted PRQ ID
            $prq_id = mysqli_insert_id($mysqli);
            
            // Recorder_id action in history table
            $history_query = "INSERT INTO history_pemesanan1 
                              (order_id, aksi, dilakukan_oleh, tanggal, keterangan) 
                              VALUES (?, 'created', ?, NOW(), ?)";
            
            $history_stmt = mysqli_prepare($mysqli, $history_query);
            $action_description = "PRQ submitted for " . $item_name;
            mysqli_stmt_bind_param($history_stmt, 'iss', $prq_id, $requested_by, $action_description);
            
            if (!mysqli_stmt_execute($history_stmt)) {
                throw new Exception("History recorder_id failed: " . mysqli_stmt_error($history_stmt));
            }
            
            // Commit transaction if everything is successful
            mysqli_commit($mysqli);
            
            $_SESSION['message'] = "PRQ #" . $prq_id . " for " . htmlspecialchars($item_name) . " has been submitted successfully and is pending approval.";
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($mysqli);
            $_SESSION['error'] = "Error submitting PRQ: " . $e->getMessage();
        } finally {
            if (isset($stmt)) {
                mysqli_stmt_close($stmt);
            }
            if (isset($history_stmt)) {
                mysqli_stmt_close($history_stmt);
            }
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
        $_SESSION['form_data'] = $_POST; // Store form data to repopulate the form
    }
} else {
    $_SESSION['error'] = "Invalid request to submit PRQ.";
}

// Redirect back to the PRQ management page
header("location:pemesanan.php");
exit();
?>