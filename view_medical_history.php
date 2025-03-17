<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    echo "Please log in to view your medical history.";
    exit;
}

$patient_id = $_SESSION['user_id'];

$query = "SELECT doctor_name, diagnosis, prescription, visit_date 
          FROM medical_history 
          WHERE patient_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medical History</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
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