<?php
session_start();
include 'db_connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "Access denied. Please log in as an admin.";
    exit;
}


if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit;
}

$bill_id = $_GET['id'];

// Fetch the bill details
$query = "SELECT * FROM billing WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $bill_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Bill not found.";
    exit;
}

$bill = $result->fetch_assoc();

// Handle form submission to update the bill
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $service = $_POST['service'];
    $amount = $_POST['amount'];

    // Update the bill in the database
    $update_query = "UPDATE billing SET patient_id = ?, doctor_id = ?, service = ?, amount = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iisdi", $patient_id, $doctor_id, $service, $amount, $bill_id);

    if ($stmt->execute()) {
        header("Location: billing.php");
        exit;
    } else {
        $error_message = "Error updating bill: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bill</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="page-container">
        <h1>Edit Bill</h1>

        <div class="return-dashboard">
            <a href="billing.php" class="dashboard-link">Return to Billing</a>
        </div>

        <!-- Display error message if any -->
        <?php if (isset($error_message)) { ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php } ?>

        <form action="edit_bill.php?id=<?php echo $bill_id; ?>" method="POST" class="form-container">
            <label for="patient_id">Patient ID:</label>
            <input type="number" id="patient_id" name="patient_id" value="<?php echo $bill['patient_id']; ?>" required>

            <label for="doctor_id">Doctor ID:</label>
            <input type="number" id="doctor_id" name="doctor_id" value="<?php echo $bill['doctor_id']; ?>" required>

            <label for="service">Service:</label>
            <input type="text" id="service" name="service" value="<?php echo $bill['service']; ?>" required>

            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" step="0.01" value="<?php echo $bill['amount']; ?>" required>

            <button type="submit" class="update-button">Update Bill</button>
        </form>
    </div>
</body>

</html>