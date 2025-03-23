<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
  header("Location: login.php");
  exit;
}

include 'db_connection.php';

$user_id = $_SESSION['user_id'];


$query = "SELECT profile_image, username FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();


$patient_update_error = "";
$patient_update_success = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_patient_details'])) {
  $edit_user_id = intval($_POST['user_id']);
  $patient_input_id = trim($_POST['patient_id']);
  $name = trim($_POST['name']);
  $address = trim($_POST['address']);
  $age = intval($_POST['age']);
  $phone = trim($_POST['phone']);
  $gender = trim($_POST['gender']);


  $check_query = "SELECT user_id FROM patients WHERE user_id = ?";
  $stmt_check = $conn->prepare($check_query);
  $stmt_check->bind_param("i", $edit_user_id);
  $stmt_check->execute();
  $result_check = $stmt_check->get_result();

  if ($result_check->num_rows > 0) {
    // Update record.
    $update_query = "UPDATE patients SET patient_id = ?, name = ?, address = ?, age = ?, phone = ? , gender = ?  WHERE user_id = ?";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param("sssisis", $patient_input_id, $name, $address, $age, $phone, $edit_user_id);
    if ($stmt_update->execute()) {
      $patient_update_success = "Patient details updated successfully.";
    } else {
      $patient_update_error = "Failed to update patient details: " . $stmt_update->error;
    }
  } else {
    // Insert new record.
    $insert_query = "INSERT INTO patients (user_id, patient_id, name, address, age, phone,gender) VALUES (?, ?, ?, ?, ?, ?,?)";
    $stmt_insert = $conn->prepare($insert_query);
    $stmt_insert->bind_param("isssiss", $edit_user_id, $patient_input_id, $name, $address, $age, $phone, $gender);
    if ($stmt_insert->execute()) {
      $patient_update_success = "Patient details inserted successfully.";
    } else {
      $patient_update_error = "Failed to insert patient details: " . $stmt_insert->error;
    }
  }
  header("Location: admin_dashboard.php");
  exit;
}

// If an admin clicked "Edit" for a patient, fetch that patient's details.
$editPatient = null;
if (isset($_GET['edit_patient'])) {
  $edit_patient_id = intval($_GET['edit_patient']);
  $patient_query = "SELECT patient_id, name, address, age, phone,gender FROM patients WHERE user_id = ?";
  $stmt_patient = $conn->prepare($patient_query);
  $stmt_patient->bind_param("i", $edit_patient_id);
  $stmt_patient->execute();
  $result_patient = $stmt_patient->get_result();
  if ($result_patient->num_rows > 0) {
    $editPatient = $result_patient->fetch_assoc();
  } else {
    // If not exists, prepare empty values for insertion.
    $editPatient = ['patient_id' => '', 'name' => '', 'address' => '', 'age' => '', 'phone' => '', 'gender' => ''];
  }
  // Fetch the corresponding basic user info.
  $user_query = "SELECT username, email FROM users WHERE id = ?";
  $stmt_user = $conn->prepare($user_query);
  $stmt_user->bind_param("i", $edit_patient_id);
  $stmt_user->execute();
  $result_user = $stmt_user->get_result();
  $userData = $result_user->fetch_assoc();
  $editPatient['user_id'] = $edit_patient_id;
  $editPatient['username'] = $userData['username'];
  $editPatient['email'] = $userData['email'];
}

// Fetch all registered patients (using a LEFT JOIN so every patient appears).
$patients_query = "SELECT 
                        u.id AS user_id, 
                        u.username, 
                        u.email, 
                        p.patient_id, 
                        p.name, 
                        p.address, 
                        p.age, 
                        p.phone,
                         p.gender 
                   FROM users u
                   LEFT JOIN patients p ON u.id = p.user_id
                   WHERE u.role = 'patient'
                   ORDER BY u.id ASC";
$patients_result = $conn->query($patients_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Patient Details</title>
  <link rel="stylesheet" href="admin.css">
</head>

<body>
  <div class="page-container">
    <!-- Admin Header -->
    <div class="admin-header">
      <div class="admin-session">
        <img src="<?php echo $admin['profile_image'] ? $admin['profile_image'] : 'uploads/default_avatar.png'; ?>" alt="Profile" class="profile-image">
        <span class="admin-name"><?php echo htmlspecialchars($admin['username']); ?></span>
      </div>
      <a href="logout.php" class="logout-button">Logout</a>
    </div>

    <!-- Dashboard Title -->
    <div class="dashboard-title-container">
      <h1>Welcome to Admin Dashboard</h1>
    </div>

    <!-- Navigation Sidebar -->
    <div class="vertical-navbar">
      <ul>
        <li><a href="manage_users.php">Manage Users</a></li>
        <li><a href="assign_roles.php">Assign Roles</a></li>
        <li><a href="manage_resources.php">Manage Resources</a></li>
        <li><a href="billing.php">Billing</a></li>
        <li><a href="reports.php">Reports</a></li>
        <li><a href="settings.php">Settings</a></li>
      </ul>
    </div>

    <!-- Section: Edit/Receive Patient Details -->
    <div class="main-content">
      <h2>Registered Patient Details</h2>

      <?php
      if (!empty($patient_update_error)) {
        echo "<p class='error'>" . htmlspecialchars($patient_update_error) . "</p>";
      }
      if (!empty($patient_update_success)) {
        echo "<p class='success'>" . htmlspecialchars($patient_update_success) . "</p>";
      }
      ?>

      <?php if (isset($_GET['edit_patient'])): ?>
        <!-- Edit Patient Details Form -->
        <div class="form-container">
          <h3>Edit Patient Details for User ID: <?php echo htmlspecialchars($editPatient['user_id']); ?></h3>
          <form action="admin_dashboard.php?edit_patient=<?php echo htmlspecialchars($editPatient['user_id']); ?>" method="post">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($editPatient['user_id']); ?>">

            <label for="patient_id">Custom Patient ID:</label>
            <input type="text" id="patient_id" name="patient_id" required value="<?php echo htmlspecialchars($editPatient['patient_id']); ?>">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($editPatient['name']); ?>">

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required value="<?php echo htmlspecialchars($editPatient['address']); ?>">

            <label for="age">Age:</label>
            <input type="number" id="age" name="age" required value="<?php echo htmlspecialchars($editPatient['age']); ?>">

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required value="<?php echo htmlspecialchars($editPatient['phone']); ?>">

            <label for="phone">Gender:</label>
            <input type="text" id="gender" name="gender" required value="<?php echo htmlspecialchars($editPatient['gender']); ?>">

            <button type="submit" name="update_patient_details">Update Patient Details</button>
            <a href="admin_dashboard.php" class="cancel-button">Cancel</a>
          </form>
        </div>
      <?php endif; ?>

      <!-- Patients Table -->
      <table class="patients-table">
        <thead>
          <tr>
            <th>User ID</th>
            <th>Custom Patient ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Name</th>
            <th>Address</th>
            <th>Age</th>
            <th>Phone</th>
            <th>Gender</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($patients_result && $patients_result->num_rows > 0): ?>
            <?php while ($patient = $patients_result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($patient['user_id']); ?></td>
                <td><?php echo htmlspecialchars($patient['patient_id'] ?? "Not Provided"); ?></td>
                <td><?php echo htmlspecialchars($patient['username']); ?></td>
                <td><?php echo htmlspecialchars($patient['email']); ?></td>
                <td><?php echo htmlspecialchars($patient['name'] ?? "Not Registered"); ?></td>
                <td><?php echo htmlspecialchars($patient['address'] ?? "Not Registered"); ?></td>
                <td><?php echo htmlspecialchars($patient['age'] ?? "Not Registered"); ?></td>
                <td><?php echo htmlspecialchars($patient['phone'] ?? "Not Registered"); ?></td>

                <td><?php echo htmlspecialchars($patient['gender'] ?? "Not Registered"); ?></td>
                <td>
                  <a href="admin_dashboard.php?edit_patient=<?php echo $patient['user_id']; ?>">Edit</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="10">No patient records found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>
<?php
$conn->close();
?>