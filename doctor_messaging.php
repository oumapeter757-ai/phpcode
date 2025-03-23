<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit;
}
include 'db_connection.php';

$doctor_id = $_SESSION['user_id'];

// Retrieve doctor details.
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ? AND role = 'doctor'");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
if (!$doctor) {
    header("Location: logout.php");
    exit;
}
$stmt->close();

// Determine selected conversation if provided via GET.
$selected_patient_id = null;
if (isset($_GET['patient_id'])) {
    $selected_patient_id = intval($_GET['patient_id']);
}

// Get the list of patients for dropdown.
$patients_query = "SELECT id, username FROM users WHERE role = 'patient' ORDER BY username ASC";
$patients_result = $conn->query($patients_query);
if (!$patients_result) {
    die("Error fetching patients: " . $conn->error);
}

// Handle doctor sending or replying a message.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    // If a conversation is active, the receiver_id is provided in a hidden field.
    $receiver_id = $_POST['receiver_id'];
    $message = trim($_POST['message']);
    
    $stmt_send = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    if (!$stmt_send) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_send->bind_param("iis", $doctor_id, $receiver_id, $message);
    if (!$stmt_send->execute()) {
        die("Execution failed: " . $stmt_send->error);
    }
    $stmt_send->close();
    $success_msg = "Message sent successfully!";
    // Refresh conversation by redirecting.
    header("Location: doctor_messaging.php?patient_id=" . $receiver_id);
    exit;
}

// If a patient is selected, retrieve conversation with that patient.
$conversation = [];
if ($selected_patient_id !== null) {
    $queryConversation = "SELECT m.*, u.username AS sender_name
                          FROM messages m
                          JOIN users u ON m.sender_id = u.id
                          WHERE (m.sender_id = ? AND m.receiver_id = ?)
                             OR (m.sender_id = ? AND m.receiver_id = ?)
                          ORDER BY m.sent_at ASC";
    $stmt_conv = $conn->prepare($queryConversation);
    if (!$stmt_conv) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_conv->bind_param("iiii", $doctor_id, $selected_patient_id, $selected_patient_id, $doctor_id);
    $stmt_conv->execute();
    $result_conv = $stmt_conv->get_result();
    while ($row = $result_conv->fetch_assoc()) {
        $conversation[] = $row;
    }
    $stmt_conv->close();
}

// If a patient is selected, retrieve his/her details for display.
$selected_patient_name = "";
if ($selected_patient_id !== null) {
    $stmt_patient = $conn->prepare("SELECT username FROM users WHERE id = ? AND role = 'patient'");
    $stmt_patient->bind_param("i", $selected_patient_id);
    $stmt_patient->execute();
    $result_patient = $stmt_patient->get_result();
    if ($result_patient->num_rows > 0) {
        $patient_data = $result_patient->fetch_assoc();
        $selected_patient_name = $patient_data['username'];
    }
    $stmt_patient->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Doctor Messaging</title>
  <link rel="stylesheet" href="style.css">
  <style>
      /* Simple styling for conversation */
      .conversation { border: 1px solid #ccc; padding: 10px; margin-top: 20px; }
      .message { margin-bottom: 10px; }
      .sent { text-align: right; }
      .received { text-align: left; }
  </style>
</head>
<body>
    <div class="dashboard-container">
      <h2>Secure Messaging</h2>
      <p>Welcome, Dr. <?php echo htmlspecialchars($doctor['username']); ?></p>
      
      <?php if(isset($success_msg)) { echo '<p class="success-message">'.htmlspecialchars($success_msg).'</p>'; } ?>
      
      <!-- Dropdown to select a patient conversation -->
      <form method="GET" style="margin-bottom: 20px;">
          <label for="patient_id">Select Patient to Message:</label>
          <select name="patient_id" id="patient_id" onchange="this.form.submit()">
              <option value="">-- Select Patient --</option>
              <?php 
              // Rewind the result pointer if needed.
              $patients_result->data_seek(0);
              while ($patient = $patients_result->fetch_assoc()) { 
                  $selected = ($selected_patient_id !== null && $selected_patient_id == $patient['id']) ? "selected" : "";
              ?>
                  <option value="<?php echo $patient['id']; ?>" <?php echo $selected; ?>>
                      <?php echo htmlspecialchars($patient['username']); ?>
                  </option>
              <?php } ?>
          </select>
      </form>
      
      <!-- Display conversation if a patient is selected -->
      <?php if ($selected_patient_id !== null): ?>
          <h3>Conversation with <?php echo htmlspecialchars($selected_patient_name); ?></h3>
          <div class="conversation">
              <?php if (count($conversation) > 0): ?>
                  <?php foreach ($conversation as $msg): ?>
                      <div class="message <?php echo ($msg['sender_id'] == $doctor_id) ? 'sent' : 'received'; ?>">
                          <strong><?php echo ($msg['sender_id'] == $doctor_id) ? "You" : htmlspecialchars($msg['sender_name']); ?>:</strong>
                          <?php echo htmlspecialchars($msg['message']); ?>
                          <br>
                          <small><?php echo htmlspecialchars($msg['sent_at']); ?></small>
                      </div>
                  <?php endforeach; ?>
              <?php else: ?>
                  <p>No messages in this conversation yet.</p>
              <?php endif; ?>
          </div>
          
          <!-- Reply/Form to send a new message -->
          <form method="POST" style="margin-top: 20px;">
              <input type="hidden" name="receiver_id" value="<?php echo $selected_patient_id; ?>">
              <label for="message">Your Message:</label>
              <textarea name="message" id="message" rows="4" required></textarea>
              <button type="submit" name="send_message">Send Message</button>
          </form>
      <?php endif; ?>
      
      <a href="doctor_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>
</html>
<?php
$conn->close();
?>
