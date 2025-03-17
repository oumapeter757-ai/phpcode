<?php


include 'db_connection.php';


$patients_query = "SELECT id FROM patients";
$patients_result = $conn->query($patients_query);


$query = "SELECT * FROM billing";
$result = $conn->query($query);


if (isset($_GET['delete'])) {
    $bill_id = $_GET['delete'];
    $delete_query = "DELETE FROM billing WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $bill_id);
    $stmt->execute();
    header("Location: billing.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bill'])) {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $service = $_POST['service'];
    $amount = $_POST['amount'];


    $validate_patient_query = "SELECT id FROM patients WHERE id = ?";
    $stmt = $conn->prepare($validate_patient_query);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {

        $insert_query = "INSERT INTO billing (patient_id, doctor_id, service, amount) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iisd", $patient_id, $doctor_id, $service, $amount);
        $stmt->execute();
        header("Location: billing.php");
        exit;
    } else {

        $error_message = "The selected patient does not exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="page-container">
        <h1>Billing</h1>

        <div class="return-dashboard">
            <a href="admin_dashboard.php" class="dashboard-link">Return to Admin Dashboard</a>
        </div>

      
        <?php if (isset($error_message)) { ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php } ?>

        <form action="billing.php" method="POST" class="form-container">
            <h2>Add New Bill</h2>
            <label for="patient_id">Select Patient ID:</label>
            <select id="patient_id" name="patient_id" required>
                <option value="">-- Select Patient ID --</option>
                <?php while ($patient = $patients_result->fetch_assoc()): ?>
                    <option value="<?php echo $patient['id']; ?>"><?php echo $patient['id']; ?></option>
                <?php endwhile; ?>
            </select>

            <label for="doctor_id">Select Doctor ID:</label>
            <select id="doctor_id" name="doctor_id" required>
                <option value="">-- Select Doctor ID --</option>
                <?php
                $doctors_query = "SELECT id, username FROM doctors";
                $doctors_result = $conn->query($doctors_query);
                while ($doctor = $doctors_result->fetch_assoc()): ?>
                    <option value="<?php echo $doctor['id']; ?>"><?php echo $doctor['username']; ?></option>
                <?php endwhile; ?>
            </select>

            <label for="service">Service:</label>
            <input type="text" id="service" name="service" required>

            <label for="amount">Amount:</label>
            <input type="number" id="amount" name="amount" step="0.01" required>

            <button type="submit" name="add_bill" class="add-user-button">Generate Bill</button>
        </form>


        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient ID</th>
                    <th>Service</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['patient_id']; ?></td>
                        <td><?php echo $row['service']; ?></td>
                        <td><?php echo $row['amount']; ?></td>
                        <td>
                            <a href="edit_bill.php?id=<?php echo $row['id']; ?>" class="edit-button">Edit</a>
                            <a href="billing.php?delete=<?php echo $row['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this bill?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>