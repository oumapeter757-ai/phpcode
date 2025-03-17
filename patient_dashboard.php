<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

// Fetch patient details
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

if (!$patient) {
    header("Location: logout.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <!-- Upper Bar -->
        <div class="upper-bar">
            <p class="session-info">Welcome, <?php echo htmlspecialchars($patient['username']); ?></p>
            <a href="logout.php" class="logout-button">Logout</a>
        </div>

        <!-- Page Heading -->
        <div class="page-heading">
            <h1>PATIENT DASHBOARD</h1>
        </div>

        <!-- Left Navigation Sidebar -->
        <div class="left-sidebar">
            <ul>
                <li><a href="book_appointment.php">Book Appointment</a></li>
                <li><a href="view_medical_history.php">View Medical History</a></li>
                <li><a href="billing_info.php">Access Billing Information</a></li>
                <li><a href="feedback.php">Provide Feedback</a></li>
                <li><a href="view_doctors.php">View Available Doctors</a></li>
                <li><a href="secure_messaging.php">Secure Messaging</a></li>
            </ul>
        </div>

      
    </div>
</body>

</html>