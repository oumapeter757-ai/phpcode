<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo "Please log in to view lab results.";
    exit;
}

$doctor_id = $_SESSION['user_id'];

// Fetch lab results uploaded by the logged-in doctor
$query = "SELECT l.id, u.username AS patient_name, l.test_name, l.result, l.result_date 
          FROM lab_results l
          JOIN users u ON l.patient_id = u.id
          WHERE l.doctor_id = ?
          ORDER BY l.result_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Results</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Lab Results</h2>

        <?php if ($result->num_rows > 0) { ?>
            <table class="lab-results-table">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Test Name</th>
                        <th>Result</th>
                        <th>Result Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['test_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['result']); ?></td>
                            <td><?php echo htmlspecialchars($row['result_date']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No lab results found.</p>
        <?php } ?>

        <!-- Button to Return to Dashboard -->
        <a href="doctor_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>