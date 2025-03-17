<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

// Fetch doctors for the dropdown
$doctors_query = "SELECT id, username FROM users WHERE role = 'doctor'";
$doctors_result = $conn->query($doctors_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_SESSION['user_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $patient_id, $doctor_id, $appointment_date, $appointment_time);
    $stmt->execute();

    $success_message = "Appointment booked successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Book Appointment</h2>

        <!-- Success Message -->
        <?php if (isset($success_message)) { ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php } ?>

        <!-- Appointment Booking Form -->
        <form method="POST">
            <label for="doctor_id">Select Doctor:</label>
            <select name="doctor_id" id="doctor_id" required>
                <option value="">-- Select Doctor --</option>
                <?php while ($doctor = $doctors_result->fetch_assoc()) { ?>
                    <option value="<?php echo $doctor['id']; ?>"><?php echo htmlspecialchars($doctor['username']); ?></option>
                <?php } ?>
            </select>

            <label for="appointment_date">Select Date:</label>
            <input type="date" name="appointment_date" id="appointment_date" required>

            <label for="appointment_time">Select Time:</label>
            <input type="time" name="appointment_time" id="appointment_time" required>

            <button type="submit">Book Appointment</button>
        </form>
    </div>
</body>

</html>