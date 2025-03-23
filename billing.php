<?php
session_start();
include 'db_connection.php';

// Function to check role-based access
function user_has_access($roles)
{
  return isset($_SESSION['user_id']) && in_array($_SESSION['role'], $roles);
}

if (!user_has_access(['admin', 'doctor', 'patient'])) {
  echo "Access denied. Please log in with the appropriate account.";
  exit;
}

// Handle form submission to add a new bill (only for admin and doctor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bill']) && user_has_access(['admin', 'doctor'])) {
  // Expecting the dropdown to return the custom registered patient id.
  $patient_custom_id = $_POST['patient_id'];
  $doctor_id  = $_POST['doctor_id'];
  $service    = $_POST['service'];
  $amount     = $_POST['amount'];

  // Insert new bill record. The billing_date is set to the current timestamp using NOW().
  $insert_query = "INSERT INTO billing (patient_id, doctor_id, service, amount, billing_date) 
                     VALUES (?, ?, ?, ?, NOW())";
  $stmt = $conn->prepare($insert_query);
  if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
  }
  // Assuming the custom patient_id is an integer. If it's alphanumeric, change the type string accordingly.
  $stmt->bind_param("iisd", $patient_custom_id, $doctor_id, $service, $amount);
  if (!$stmt->execute()) {
    die("Execution failed: " . $stmt->error);
  }

  // Set the success message so that it can be displayed on the billing page.
  $_SESSION['success'] = "Bill added successfully!";
  header("Location: billing.php");
  exit;
}

// Build the main query retrieving billing records with explicit table qualifiers.
// We join the patients table on b.patient_id = p.patient_id (as billing.patient_id references patients.patient_id).
$query = "SELECT 
            b.id, 
            b.patient_id, 
            b.doctor_id, 
            b.service, 
            b.amount, 
            b.billing_date, 
            p.name AS patient_name, 
            u.email AS patient_email,
            d.username AS doctor_name, 
            d.email AS doctor_email
          FROM billing AS b
          JOIN patients AS p ON b.patient_id = p.patient_id
          JOIN users AS u ON p.user_id = u.id
          JOIN users AS d ON b.doctor_id = d.id";

if ($_SESSION['role'] == 'patient') {
  // For patients, you must have the custom id stored in the session (e.g., $_SESSION['patient_id']).
  $query .= " WHERE b.patient_id = ?";
}

$query .= " ORDER BY b.billing_date DESC";

// Prepare the statement.
$stmt = $conn->prepare($query);
if ($_SESSION['role'] == 'patient') {
  $stmt->bind_param("i", $_SESSION['patient_id']);
}
$stmt->execute();
$result = $stmt->get_result();
if (!$result) {
  die("Query failed: " . $conn->error);
}

// For the dropdowns (only if user is admin or doctor)
if (user_has_access(['admin', 'doctor'])) {
  // Retrieve only the custom registered patient id from the patients table.
  $patients_query = "SELECT patient_id FROM patients";
  $patients_result = $conn->query($patients_query);
  if (!$patients_result) {
    die("Error fetching patients: " . $conn->error);
  }

  $doctors_query = "SELECT id, username FROM users WHERE role = 'doctor'";
  $doctors_result = $conn->query($doctors_query);
  if (!$doctors_result) {
    die("Error fetching doctors: " . $conn->error);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Billing</title>
  <link rel="stylesheet" href="billing.css">
</head>

<body>
  <div class="page-container">
    <h1>Billing</h1>

    <!-- Display success message if present -->
    <?php
    if (isset($_SESSION['success'])) {
      echo '<p class="success">' . htmlspecialchars($_SESSION['success']) . '</p>';
      unset($_SESSION['success']);
    }
    ?>

    <?php if (user_has_access(['admin', 'doctor'])) { ?>
      <!-- Form for adding a new bill -->
      <form action="billing.php" method="POST" class="form-container">
        <h2>Add New Bill</h2>

        <label for="patient_id">Patient Custom ID:</label>
        <select name="patient_id" id="patient_id" required>
          <option value="">-- Select Patient Custom ID --</option>
          <?php while ($p = $patients_result->fetch_assoc()) { ?>
            <option value="<?php echo $p['patient_id']; ?>">
              <?php echo htmlspecialchars($p['patient_id']); ?>
            </option>
          <?php } ?>
        </select>

        <label for="doctor_id">Doctor:</label>
        <select name="doctor_id" id="doctor_id" required>
          <option value="">-- Select Doctor --</option>
          <?php while ($d = $doctors_result->fetch_assoc()) { ?>
            <option value="<?php echo $d['id']; ?>">
              <?php echo htmlspecialchars($d['username']); ?>
            </option>
          <?php } ?>
        </select>

        <label for="service">Service:</label>
        <input type="text" name="service" id="service" required>

        <label for="amount">Amount ($):</label>
        <input type="number" name="amount" id="amount" step="0.01" required>

        <button type="submit" name="add_bill" class="add-user-button">Add Bill</button>
      </form>
    <?php } ?>

    <!-- Table for displaying billing records -->
    <h2>Billing Records</h2>
    <?php if ($result->num_rows > 0) { ?>
      <table class="billing-table">
        <thead>
          <tr>
            <th>Bill ID</th>
            <th>Patient Name</th>
            <th>Patient Email</th>
            <th>Doctor Name</th>
            <th>Doctor Email</th>
            <th>Service</th>
            <th>Amount</th>
            <th>Billing Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($bill = $result->fetch_assoc()) { ?>
            <tr>
              <td><?php echo htmlspecialchars($bill['id']); ?></td>
              <td><?php echo htmlspecialchars($bill['patient_name']); ?></td>
              <td><?php echo htmlspecialchars($bill['patient_email']); ?></td>
              <td><?php echo htmlspecialchars($bill['doctor_name']); ?></td>
              <td><?php echo htmlspecialchars($bill['doctor_email']); ?></td>
              <td><?php echo htmlspecialchars($bill['service']); ?></td>
              <td>$<?php echo number_format($bill['amount'], 2); ?></td>
              <td><?php echo htmlspecialchars($bill['billing_date']); ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } else { ?>
      <p>No billing records found.</p>
    <?php } ?>

    <!-- Link to return to the dashboard -->
    <a href="admin_dashboard.php" class="return-button">Return to Dashboard</a>
  </div>
</body>

</html>
<?php
$conn->close();
?>