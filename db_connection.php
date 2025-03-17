<?php
$host = 'localhost';
$user = 'oumapeter';
$password = 'Petero';
$dbname = 'hospital';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
