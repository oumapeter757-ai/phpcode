<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    echo "Please log in to provide feedback.";
    exit;
}

$patient_id = $_SESSION['user_id'];
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $feedback_text = $_POST['feedback_text'];

    $query = "INSERT INTO feedback (patient_id, feedback_text) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $patient_id, $feedback_text);
    $stmt->execute();

    $success_message = "Thank you for your feedback!";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provide Feedback</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Provide Feedback</h2>

        <!-- Success Message -->
        <?php if (!empty($success_message)) { ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php } ?>

        <!-- Feedback Form -->
        <form method="POST">
            <label for="feedback_text">Your Feedback:</label>
            <textarea name="feedback_text" id="feedback_text" rows="5" required></textarea>
            <button type="submit">Submit Feedback</button>
        </form>

        <!-- Button to Return to Patient Dashboard -->
        <a href="patient_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>