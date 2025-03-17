<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    echo "Please log in to view your medical reports.";
    exit;
}

$patient_id = $_SESSION['user_id'];

// Fetch medical reports for the logged-in patient
$query = "SELECT r.report_title, r.report_file, r.uploaded_at, u.username AS doctor_name 
          FROM medical_reports r
          JOIN users u ON r.doctor_id = u.id
          WHERE r.patient_id = ?
          ORDER BY r.uploaded_at DESC";
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
    <title>Medical Reports</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Your Medical Reports</h2>

        <?php if ($result->num_rows > 0) { ?>
            <table class="medical-reports-table">
                <thead>
                    <tr>
                        <th>Report Title</th>
                        <th>Doctor Name</th>
                        <th>Uploaded At</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['report_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['uploaded_at']); ?></td>
                            <td><a href="uploads/<?php echo htmlspecialchars($row['report_file']); ?>" download>Download</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No medical reports found.</p>
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