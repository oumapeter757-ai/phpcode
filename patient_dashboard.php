<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
  header("Location: login.php");
  exit;
}

include 'db_connection.php';

$user_id = $_SESSION['user_id'];

// Process form submission for patient registration details.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_details'])) {
  // Retrieve and sanitize form data.
  $patient_input_id = trim($_POST['patient_id']); // Custom patient ID input by the patient
  $name    = trim($_POST['name']);
  $address = trim($_POST['address']);
  $age     = intval($_POST['age']);
  $phone   = trim($_POST['phone']);
  $gender  = trim($_POST['gender']);

  // Check if registration details already exist for this user.
  $check_query = "SELECT user_id FROM patients WHERE user_id = ?";
  $stmt = $conn->prepare($check_query);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result_check = $stmt->get_result();

  if ($result_check->num_rows > 0) {
    // Update existing record.
    $update_query = "UPDATE patients SET patient_id = ?, name = ?, address = ?, age = ?, phone = ?, gender = ? WHERE user_id = ?";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param("sssisis", $patient_input_id, $name, $address, $age, $phone, $gender, $user_id);
    $stmt_update->execute();
  } else {
    // Insert a new record.
    $insert_query = "INSERT INTO patients (user_id, patient_id, name, address, age, phone,gender) VALUES (?, ?, ?, ?, ?, ?,?)";
    $stmt_insert = $conn->prepare($insert_query);
    $stmt_insert->bind_param("isssiss", $user_id, $patient_input_id, $name, $address, $age, $phone, $gender);
    $stmt_insert->execute();
  }
  header("Location: patient_dashboard.php");
  exit;
}

// Fetch basic patient user data from the users table.
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

// Retrieve additional registration details (if available) from the patients table.
$patient_details = [];
$details_query = "SELECT patient_id, name, address, age, phone,gender FROM patients WHERE user_id = ?";
$stmt_details = $conn->prepare($details_query);
$stmt_details->bind_param("i", $user_id);
$stmt_details->execute();
$result_details = $stmt_details->get_result();
if ($result_details->num_rows > 0) {
  $patient_details = $result_details->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Patient Dashboard</title>
  <link rel="stylesheet" href="patient.css">
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

    <!-- Main Content: Patient Registration/Details Form -->
    <div class="main-content">
      <h2>Patient Registration Details</h2>
      <form action="patient_dashboard.php" method="post" class="registration-form">
        <!-- Allow the patient to input their custom ID -->
        <label for="patient_id">Patient ID:</label>
        <input type="text" id="patient_id" name="patient_id" required value="<?php echo isset($patient_details['patient_id']) ? htmlspecialchars($patient_details['patient_id']) : ''; ?>">

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required value="<?php echo isset($patient_details['name']) ? htmlspecialchars($patient_details['name']) : ''; ?>">

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" required value="<?php echo isset($patient_details['address']) ? htmlspecialchars($patient_details['address']) : ''; ?>">

        <label for="age">Age:</label>
        <input type="number" id="age" name="age" required value="<?php echo isset($patient_details['age']) ? htmlspecialchars($patient_details['age']) : ''; ?>">

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" required value="<?php echo isset($patient_details['phone']) ? htmlspecialchars($patient_details['phone']) : ''; ?>">

        <label for="gender">Gender:</label>
        <select name="gender" id="gender" required>
          <option value="">-- Select Gender --</option>
          <option value="Male" <?php if (isset($patient_details['gender']) && $patient_details['gender'] === 'Male') {
                                  echo 'selected';
                                } ?>>Male</option>
          <option value="Female" <?php if (isset($patient_details['gender']) && $patient_details['gender'] === 'Female') {
                                    echo 'selected';
                                  } ?>>Female</option>
          <option value="Other" <?php if (isset($patient_details['gender']) && $patient_details['gender'] === 'Other') {
                                  echo 'selected';
                                } ?>>Other</option>
        </select>

        <button type="submit" name="save_details">Save Details</button>
      </form>
    </div>
  </div>
</body>

</html>
<?php
$conn->close();
?>