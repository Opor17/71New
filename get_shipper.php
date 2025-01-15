<?php

include 'config/db.php';

$id = $_GET['id'];

$sql = "SELECT * FROM shipper WHERE id = :id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

$shipper = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$shipper) {
    echo "Shipper not found.";
    exit;
}

echo json_encode($shipper);

?>