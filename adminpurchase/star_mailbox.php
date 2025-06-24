<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

include "../config.php";
if (!$conn) {
    header("HTTP/1.1 500 Internal Server Error");
    exit();
}

$message_id = (int)$_POST['id'];
$action = $_POST['action'] === 'star' ? 1 : 0;

$stmt = $conn->prepare("UPDATE pesan SET starred = ? WHERE id_pesan = ?");
$stmt->bind_param("ii", $action, $message_id);

$response = ['success' => false];

if ($stmt->execute()) {
    $response['success'] = true;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>s