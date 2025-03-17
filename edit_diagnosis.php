<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $diagnosis = $_POST['diagnosis'];
    $prescription = $_POST['prescription'];

    $query = "UPDATE diagnoses SET diagnosis = ?, prescription = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $diagnosis, $prescription, $id);
    $stmt->execute();

    header("Location: add_diagnosis.php");
    exit;
}

$id = $_GET['id'];
$query = "SELECT * FROM diagnoses WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$diagnosis = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Diagnosis</title>
</head>

<body>
    <h2>Edit Diagnosis & Prescription</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $diagnosis['id']; ?>">
        <label for="diagnosis">Diagnosis:</label>
        <textarea name="diagnosis" id="diagnosis" rows="4" required><?php echo htmlspecialchars($diagnosis['diagnosis']); ?></textarea>

        <label for="prescription">Prescription:</label>
        <textarea name="prescription" id="prescription" rows="4" required><?php echo htmlspecialchars($diagnosis['prescription']); ?></textarea>

        <button type="submit">Update</button>
    </form>
</body>

</html>