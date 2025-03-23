<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

// Instead of using the session user_id directly to query medical_history,
// first retrieve the custom patient ID from the patients table.
$user_id = $_SESSION['user_id'];
$stmt_patient = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
if (!$stmt_patient) {
    die("Prepare failed: " . $conn->error);
}
$stmt_patient->bind_param("i", $user_id);
$stmt_patient->execute();
$result_patient = $stmt_patient->get_result();
if ($result_patient->num_rows > 0) {
    $row = $result_patient->fetch_assoc();
    $custom_patient_id = $row['patient_id'];
} else {
    echo "No patient registration details found. Please register your details first.";
    exit;
}
$stmt_patient->close();

// Use the custom patient ID to query the medical_history table.
$query = "SELECT doctor_name, diagnosis, prescription, visit_date 
          FROM medical_history 
          WHERE patient_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed (medical_history): " . $conn->error);
}
$stmt->bind_param("i", $custom_patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medical History</title>
    <link rel="stylesheet" href="medical.css"> <!-- External CSS -->
</head>
<body>
    <div class="dashboard-container">
        <h2>Medical History</h2>
        <?php if ($result->num_rows > 0) { ?>
            <table class="medical-history-table">
                <thead>
                    <tr>
                        <th>Doctor Name</th>
                        <th>Diagnosis</th>
                        <th>Prescription</th>
                        <th>Visit Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['diagnosis']); ?></td>
                            <td><?php echo htmlspecialchars($row['prescription']); ?></td>
                            <td><?php echo htmlspecialchars($row['visit_date']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No medical history found.</p>
        <?php } ?>
        <!-- Button to Return to Patient Dashboard -->
        <a href="patient_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
