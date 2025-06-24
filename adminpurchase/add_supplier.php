<?php
session_start();
require_once('../konekdb.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve supplier data from the form
    $supplierName = $_POST['supplierName'];
    $supplierContact = $_POST['supplierContact'];
    $supplierEmail = $_POST['supplierEmail'];
    $supplierAddress = $_POST['supplierAddress'];

    // Check if an ID is provided for updating an existing supplier
    if (isset($_POST['supplierId']) && !empty($_POST['supplierId'])) {
        $supplierId = $_POST['supplierId'];

        // Prepare and bind for update
        $stmt = $mysqli->prepare("UPDATE suppliers SET name=?, contact=?, email=?, address=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("ssssi", $supplierName, $supplierContact, $supplierEmail, $supplierAddress, $supplierId);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Supplier updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating supplier: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Error preparing statement: " . $mysqli->error;
        }
    } else {
        // Prepare and bind for insert
        $stmt = $mysqli->prepare("INSERT INTO suppliers (name, contact, email, address) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $supplierName, $supplierContact, $supplierEmail, $supplierAddress);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Supplier added successfully!";
            } else {
                $_SESSION['error'] = "Error adding supplier: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Error preparing statement: " . $mysqli->error;
        }
    }
    header("location:supplier.php");
    exit();
}
?>
