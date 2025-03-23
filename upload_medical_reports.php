<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo "Please log in to upload medical reports.";
    exit;
}

include 'db_connection.php';

$user_id = $_SESSION['user_id'];
$success_message = "";

// Fetch patients for the dropdown
$patients_query = "SELECT id, username FROM users WHERE role = 'patient'";
$patients_result = $conn->query($patients_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $report_title = $_POST['report_title'];
    $report_file = $_FILES['report_file'];

    // Set your target directory
    $target_dir = "uploads/";
    // Create the uploads directory if it does not exist
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            die("Failed to create directory: " . $target_dir);
        }
    }

    // Generate a unique file name to avoid overwriting existing files
    $unique_filename = md5(uniqid(rand(), true)) . "_" . basename($report_file['name']);
    $target_file = $target_dir . $unique_filename;

    // Attempt to move the uploaded file to the target directory
    if (move_uploaded_file($report_file['tmp_name'], $target_file)) {
        $query = "INSERT INTO medical_reports (patient_id, doctor_id, report_title, report_file) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiss", $patient_id, $user_id, $report_title, $unique_filename);
        if ($stmt->execute()) {
            $success_message = "Medical report uploaded successfully!";
        } else {
            echo "Error adding report to database: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error uploading file.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Medical Reports</title>
    <link rel="stylesheet" href="style.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Upload Medical Reports</h2>

        <!-- Success Message -->
        <?php if (!empty($success_message)) { ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
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
$conn->close();
?>