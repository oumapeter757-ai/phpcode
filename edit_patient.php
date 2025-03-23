<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $contact = $_POST['contact'];

    $query = "UPDATE patients SET name = ?, age = ?, gender = ?, contact = ? WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sissi", $name, $age, $gender, $contact, $id);
    $stmt->execute();

    header("Location: view_patient_records.php");
    exit;
}

$id = $_GET['id'];
$query = "SELECT * FROM patients WHERE patient_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h2>Edit Patient</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $patient['patient_id']; ?>">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($patient['name']); ?>" required>
        <label>Age:</label>
        <input type="number" name="age" value="<?php echo htmlspecialchars($patient['age']); ?>" required>
        <label>Gender:</label>
        <select name="gender" required>
            <option value="Male" <?php if ($patient['gender'] == 'Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if ($patient['gender'] == 'Female') echo 'selected'; ?>>Female</option>
        </select>
        <label>Contact:</label>
        <input type="text" name="contact" value="<?php echo htmlspecialchars($patient['contact']); ?>" required>
        <button type="submit">Update</button>
    </form>
</body>

</html>