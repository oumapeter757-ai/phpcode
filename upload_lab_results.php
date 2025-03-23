<?php
session_start();
include 'db_connection.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo "Access denied. Please log in as a doctor.";
    exit;
}

$doctor_id = $_SESSION['user_id'];
$success_message = "";

// Fetch patients for the dropdown
$patients_query = "SELECT id, username FROM users WHERE role = 'patient'";
$patients_result = $conn->query($patients_query);

if (!$patients_result) {
    die("Error fetching patients: " . $conn->error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'];
    $test_name = $_POST['test_name'];
    $result = $_POST['result'];
    $result_date = $_POST['result_date'];

    // Validate inputs
    if (empty($patient_id) || empty($test_name) || empty($result) || empty($result_date)) {
        $success_message = "All fields are required.";
    } else {
        // Insert the lab result into the database
        $query = "INSERT INTO lab_results (patient_id, doctor_id, test_name, result, result_date) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("iisss", $patient_id, $doctor_id, $test_name, $result, $result_date);
            if ($stmt->execute()) {
                $success_message = "Lab result uploaded successfully!";
            } else {
                $success_message = "Error uploading lab result: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $success_message = "Error preparing statement: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Lab Results</title>
    <link rel="stylesheet" href="style.css"> 
</head>

<body>
    <div class="dashboard-container">
        <h2>Upload Lab Results</h2>

       
        <?php if (!empty($success_message)) { ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php } ?>

      
        <form method="POST" class="form-container">
            <h3>Upload Lab Results</h3>
            <label for="patient_id">Select Patient:</label>
            <select name="patient_id" id="patient_id" required>
                <option value="">-- Select Patient --</option>
                <?php while ($patient = $patients_result->fetch_assoc()) { ?>
                    <option value="<?php echo $patient['id']; ?>"><?php echo htmlspecialchars($patient['username']); ?></option>
                <?php } ?>
            </select>

            <label for="test_name">Test Name:</label>
            <input type="text" name="test_name" id="test_name" required>

            <label for="result">Result:</label>
            <textarea name="result" id="result" rows="5" required></textarea>

            <label for="result_date">Result Date:</label>
            <input type="date" name="result_date" id="result_date" required>

            <button type="submit">Upload Result</button>
        </form>

       
        <a href="doctor_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>

</html>

<?php
$conn->close();
?>