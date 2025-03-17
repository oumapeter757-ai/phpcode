<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';


$user_id = $_SESSION['user_id'];
$query = "SELECT username FROM users WHERE id = ? AND role = 'doctor'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if (!$doctor) {
    header("Location: logout.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <!-- Add the heading here -->
       
        <div class="doctor-session-bar">
            <p>Welcome, Dr. <?php echo htmlspecialchars($doctor['username']); ?></p>
            <a href="logout.php" class="logout-button">Logout</a>
        </div>


        <div class="doctor-dashboard-bar">
            <h2>Doctor Dashboard</h2>
        </div>


        <div class="left-navbar">
            <h3>Navigation</h3>
            <ul>
                <li><a href="view_patient_records.php">Patient Records</a></li>
                <li><a href="add_diagnosis.php">Diagnosis & Prescriptions</a></li>
                <li><a href="appointments.php">Appointments</a></li>
                <li><a href="lab_results.php">Lab Results</a></li>
                <li><a href="generate_reports.php">Medical Reports</a></li>
                <li><a href="hospital_resources.php">Hospital Resources</a></li>
                <li><a href="upload_medical_reports.php">Upload Medical Reports</a></li>
                <li><a href="upload_lab_results.php">Upload Lab Results</a></li>
            </ul>
        </div>



    </div>
</body>

</html>