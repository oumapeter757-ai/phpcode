<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo "Please log in to view medical reports.";
    exit;
}

$doctor_id = $_SESSION['user_id'];

// Fetch medical reports uploaded by the logged-in doctor
$query = "SELECT r.id, u.username AS patient_name, r.report_title, r.report_file, r.uploaded_at 
          FROM medical_reports r
          JOIN users u ON r.patient_id = u.id
          WHERE r.doctor_id = ?
          ORDER BY r.uploaded_at DESC";
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
    <title>Medical Reports</title>
    <link rel="stylesheet" href="style.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Medical Reports</h2>

        <?php if ($result->num_rows > 0) { ?>
            <table class="medical-reports-table">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Report Title</th>
                        <th>Uploaded At</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['report_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['uploaded_at']); ?></td>
                            <td><a href="uploads/<?php echo htmlspecialchars($row['report_file']); ?>" download>Download</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No medical reports found.</p>
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