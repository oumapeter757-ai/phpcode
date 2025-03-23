<?php
session_start();
include 'db_connection.php';

// Only allow patients to see their billing records.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    echo "Please log in to view your billing information.";
    exit;
}

// Determine the patient's custom ID.
// We assume that when a patient registers their details, their custom patient ID is stored in the 'patients' table
// and ideally saved to the session. If not already in session, fetch it.
if (isset($_SESSION['patient_id']) && !empty($_SESSION['patient_id'])) {
    $custom_patient_id = $_SESSION['patient_id'];
} else {
    $userId = $_SESSION['user_id'];
    $query = "SELECT patient_id FROM patients WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $custom_patient_id = $row['patient_id'];
        // Save to session so we don't have to query next time.
        $_SESSION['patient_id'] = $custom_patient_id;
    } else {
        echo "Patient registration details not found.";
        exit;
    }
    $stmt->close();
}

// Now retrieve billing records for the patient from the billing table.
$query = "SELECT billing_date, amount, service FROM billing WHERE patient_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $custom_patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Information</title>
    <link rel="stylesheet" href="style.css"> <!-- External CSS -->
</head>
<body>
    <div class="dashboard-container">
        <h2>Billing Information</h2>

        <?php if ($result->num_rows > 0) { ?>
            <table class="billing-info-table">
                <thead>
                    <tr>
                        <th>Billing Date</th>
                        <th>Amount</th>
                        <th>Service</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['billing_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['amount']); ?></td>
                            <td><?php echo htmlspecialchars($row['service']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No billing records found.</p>
        <?php } ?>
        
        <a href="patient_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
