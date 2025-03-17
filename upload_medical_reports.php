<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo "Please log in to upload medical reports.";
    exit;
}

$doctor_id = $_SESSION['user_id'];
$success_message = "";

// Fetch patients for the dropdown
$patients_query = "SELECT id, username FROM users WHERE role = 'patient'";
$patients_result = $conn->query($patients_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $report_title = $_POST['report_title'];
    $report_file = $_FILES['report_file'];

    // Handle file upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($report_file['name']);
    move_uploaded_file($report_file['tmp_name'], $target_file);

    $query = "INSERT INTO medical_reports (patient_id, doctor_id, report_title, report_file) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $patient_id, $doctor_id, $report_title, $report_file['name']);
    $stmt->execute();

    $success_message = "Medical report uploaded successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Medical Reports</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Upload Medical Reports</h2>

        <!-- Success Message -->
        <?php if (!empty($success_message)) { ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php } ?>

        <!-- Medical Reports Upload Form -->
        <form method="POST" enctype="multipart/form-data">
            <label for="patient_id">Select Patient:</label>
            <select name="patient_id" id="patient_id" required>
                <option value="">-- Select Patient --</option>
                <?php while ($patient = $patients_result->fetch_assoc()) { ?>
                    <option value="<?php echo $patient['id']; ?>"><?php echo htmlspecialchars($patient['username']); ?></option>
                <?php } ?>
            </select>

            <label for="report_title">Report Title:</label>
            <input type="text" name="report_title" id="report_title" required>

            <label for="report_file">Upload Report:</label>
            <input type="file" name="report_file" id="report_file" required>

            <button type="submit">Upload Report</button>
        </form>

        <!-- Button to Return to Doctor Dashboard -->
        <a href="doctor_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>