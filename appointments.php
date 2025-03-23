<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

$user_id = $_SESSION['user_id'];

// Retrieve doctor details.
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

// Retrieve appointments booked by patients for this doctor.
// We join the appointments table with patients using the custom patient_id
// then join patients with users to get the patient’s name.
// Here, appointments.patient_id references patients.patient_id.
$appointments_query = "SELECT 
    a.id, 
    a.appointment_date, 
    a.appointment_time, 
    u.username AS patient_name, 
    p.patient_id AS custom_patient_id
FROM appointments a
JOIN patients p ON a.patient_id = p.patient_id
JOIN users u ON p.user_id = u.id
WHERE a.doctor_id = ?
ORDER BY a.appointment_date, a.appointment_time";
$stmt_app = $conn->prepare($appointments_query);
$stmt_app->bind_param("i", $user_id);
$stmt_app->execute();
$result_app = $stmt_app->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appointments - Doctor Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="dashboard-container">
    <h2>Your Appointments</h2>
    <?php if ($result_app->num_rows > 0) { ?>
      <table class="appointments-table">
        <thead>
          <tr>
            <th>Patient Name</th>
            <th>Patient Custom ID</th>
            <th>Appointment Date</th>
            <th>Appointment Time</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result_app->fetch_assoc()) { ?>
            <tr>
              <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
              <td><?php echo htmlspecialchars($row['custom_patient_id']); ?></td>
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
    <a href="doctor_dashboard.php" class="return-button">Return to Dashboard</a>
  </div>
</body>
</html>
<?php
$stmt_app->close();
$stmt->close();
$conn->close();
?>
