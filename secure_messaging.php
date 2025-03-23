<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

$patient_id = $_SESSION['user_id'];
$success_message = "";

// Fetch doctors for the dropdown (for sending new messages)
$doctors_query = "SELECT id, username FROM users WHERE role = 'doctor'";
$doctors_result = $conn->query($doctors_query);
if (!$doctors_result) {
    die("Error fetching doctors: " . $conn->error);
}

// Handle form submission for sending a new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt_send = $conn->prepare($query);
    if (!$stmt_send) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_send->bind_param("iis", $patient_id, $receiver_id, $message);
    if (!$stmt_send->execute()) {
        die("Execution failed: " . $stmt_send->error);
    }
    $stmt_send->close();
    $success_message = "Message sent successfully!";
}

// Retrieve all messages involving this patient (both sent and received),
// ordered by the timestamp (oldest to newest)
$query = "SELECT m.message, m.sent_at, m.sender_id, u.username AS sender_name 
          FROM messages m 
          JOIN users u ON m.sender_id = u.id 
          WHERE m.sender_id = ? OR m.receiver_id = ? 
          ORDER BY m.sent_at ASC";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ii", $patient_id, $patient_id);
$stmt->execute();
$messages_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Messaging</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <h2>Secure Messaging</h2>

        <!-- Success Message -->
        <?php if (!empty($success_message)) { ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php } ?>

        <!-- Messaging Form -->
        <form method="POST">
            <label for="receiver_id">Select Doctor:</label>
            <select name="receiver_id" id="receiver_id" required>
                <option value="">-- Select Doctor --</option>
                <?php while ($doctor = $doctors_result->fetch_assoc()) { ?>
                    <option value="<?php echo $doctor['id']; ?>">
                        <?php echo htmlspecialchars($doctor['username']); ?>
                    </option>
                <?php } ?>
            </select>

            <label for="message">Message:</label>
            <textarea name="message" id="message" rows="5" required></textarea>
            <button type="submit" name="send_message">Send Message</button>
        </form>

        <!-- Conversation History -->
        <h3>Conversation History</h3>
        <?php if ($messages_result->num_rows > 0) { ?>
            <table class="messages-table">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Message</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $messages_result->fetch_assoc()) {
                        // If the sender is the patient, show "You" instead of the username.
                        $sender = ($row['sender_id'] == $patient_id) ? "You" : htmlspecialchars($row['sender_name']);
                    ?>
                        <tr>
                            <td><?php echo $sender; ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo htmlspecialchars($row['sent_at']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No messages found.</p>
        <?php } ?>

        <!-- Button to Return to Patient Dashboard -->
        <a href="patient_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
