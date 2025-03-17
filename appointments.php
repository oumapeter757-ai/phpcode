<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    echo "Please log in to view your appointments.";
    exit;
}

$doctor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bill'])) {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $service = $_POST['service'];
    $amount = $_POST['amount'];

    // Validate that the patient exists in the patients table
    $validate_patient_query = "SELECT id FROM patients WHERE id = ?";
    $stmt = $conn->prepare($validate_patient_query);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Patient exists, proceed with inserting the bill
        $insert_query = "INSERT INTO billing (patient_id, doctor_id, service, amount) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iisd", $patient_id, $doctor_id, $service, $amount);

        if ($stmt->execute()) {
            echo "Bill added successfully!";
        } else {
            echo "Error adding bill: " . $stmt->error;
        }
    } else {
        echo "The selected patient does not exist.";
    }
}

// Fetch appointments for the logged-in doctor
$query = "SELECT a.id, u.username AS patient_name, a.appointment_date, a.appointment_time 
          FROM appointments a
          JOIN users u ON a.patient_id = u.id
          WHERE a.doctor_id = ?
          ORDER BY a.appointment_date, a.appointment_time";
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
    <title>Appointments</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Your Appointments</h2>

        <?php if ($result->num_rows > 0) { ?>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Appointment Date</th>
                        <th>Appointment Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                            <td>
                                <a href="view_patient_details.php?id=<?php echo $row['id']; ?>" class="view-button">View</a>
                                <a href="cancel_appointment.php?id=<?php echo $row['id']; ?>" class="cancel-button" onclick="return confirm('Are you sure you want to cancel this appointment?');">Cancel</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No appointments scheduled.</p>
        <?php } ?>

        <!-- Button to Return to Doctor Dashboard -->
        <a href="doctor_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>