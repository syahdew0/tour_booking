<?php
include 'db.php';

if (isset($_GET['id'])) {
    $destination_id = $_GET['id'];

    $stmt = $conn->prepare("SELECT price FROM destinations WHERE id = ?");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $stmt->bind_result($price);
    $stmt->fetch();
    $stmt->close();

    echo json_encode(['price' => $price]);
}
?>
