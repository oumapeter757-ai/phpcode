<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

$id = $_GET['id'];
$query = "DELETE FROM diagnoses WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: add_diagnosis.php");
exit;
