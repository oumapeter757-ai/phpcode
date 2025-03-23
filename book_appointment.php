<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

// First, get the custom patient ID from the patients table using the logged‐in user’s ID.
$patient_custom_id = null;
$stmt_patient = $conn->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
if (!$stmt_patient) {
    die("Prepare failed: " . $conn->error);
}
$stmt_patient->bind_param("i", $_SESSION['user_id']);
$stmt_patient->execute();
$result_patient = $stmt_patient->get_result();
if ($result_patient->num_rows > 0) {
    $row = $result_patient->fetch_assoc();
    $patient_custom_id = $row['patient_id'];
} else {
    echo "You must register your patient details first to book appointments.";
    exit;
}
$stmt_patient->close();

// Retrieve the list of doctors.
$doctors_query = "SELECT id, username FROM users WHERE role = 'doctor'";
$doctors_result = $conn->query($doctors_query);
if (!$doctors_result) {
    die("Error fetching doctors: " . $conn->error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Insert into appointments using the custom patient id.
    $query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    // Bind parameters; here we assume that the custom patient id is an integer.
    $stmt->bind_param("iiss", $patient_custom_id, $doctor_id, $appointment_date, $appointment_time);
    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error);
    }
    $success_message = "Appointment booked successfully!";
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="style.css"> <!-- External CSS -->
</head>
<body>
    <div class="dashboard-container">
        <h2>Book Appointment</h2>

        <?php if (isset($success_message)) { ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php } ?>

        <form method="POST">
            <label for="doctor_id">Select Doctor:</label>
            <select name="doctor_id" id="doctor_id" required>
                <option value="">-- Select Doctor --</option>
                <?php while ($doctor = $doctors_result->fetch_assoc()) { ?>
                    <option value="<?php echo htmlspecialchars($doctor['id']); ?>">
                        <?php echo htmlspecialchars($doctor['username']); ?>
                    </option>
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
<?php
$conn->close();
?>
