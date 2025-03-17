<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    echo "Please log in to view your billing information.";
    exit;
}

$patient_id = $_SESSION['user_id'];

$query = "SELECT bill_date, amount, description, status 
          FROM billing_info 
          WHERE patient_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Information</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Billing Information</h2>

        <?php if ($result->num_rows > 0) { ?>
            <table class="billing-info-table">
                <thead>
                    <tr>
                        <th>Bill Date</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['bill_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['amount']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No billing information found.</p>
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