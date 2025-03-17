<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    echo "Please log in to access secure messaging.";
    exit;
}

$patient_id = $_SESSION['user_id'];
$success_message = "";

// Fetch doctors for the dropdown
$doctors_query = "SELECT id, username FROM users WHERE role = 'doctor'";
$doctors_result = $conn->query($doctors_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $patient_id, $receiver_id, $message);
    $stmt->execute();

    $success_message = "Message sent successfully!";
}

// Fetch messages sent by the patient
$messages_query = "SELECT m.message, m.sent_at, u.username AS doctor_name 
                   FROM messages m 
                   JOIN users u ON m.receiver_id = u.id 
                   WHERE m.sender_id = ?";
$stmt = $conn->prepare($messages_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$messages_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Messaging</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Secure Messaging</h2>

        <!-- Success Message -->
        <?php if (!empty($success_message)) { ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php } ?>

        <!-- Messaging Form -->
        <form method="POST">
            <label for="receiver_id">Select Doctor:</label>
            <select name="receiver_id" id="receiver_id" required>
                <option value="">-- Select Doctor --</option>
                <?php while ($doctor = $doctors_result->fetch_assoc()) { ?>
                    <option value="<?php echo $doctor['id']; ?>"><?php echo htmlspecialchars($doctor['username']); ?></option>
                <?php } ?>
            </select>

            <label for="message">Message:</label>
            <textarea name="message" id="message" rows="5" required></textarea>
            <button type="submit">Send Message</button>
        </form>

        <!-- Sent Messages -->
        <h3>Sent Messages</h3>
        <?php if ($messages_result->num_rows > 0) { ?>
            <table class="messages-table">
                <thead>
                    <tr>
                        <th>Doctor Name</th>
                        <th>Message</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $messages_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php echo htmlspecialchars($row['sent_at']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No messages sent yet.</p>
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