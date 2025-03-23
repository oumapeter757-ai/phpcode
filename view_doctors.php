<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    echo "Please log in to view available doctors.";
    exit;
}

$query = "SELECT username, email FROM users WHERE role = 'doctor'";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Available Doctors</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="dashboard-container">
        <h2>Available Doctors</h2>

        <?php if ($result->num_rows > 0) { ?>
            <table class="doctors-table">
                <thead>
                    <tr>
                        <th>Doctor Name</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No doctors are currently available.</p>
        <?php } ?>

      
        <a href="patient_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>

</html>

<?php
$conn->close();
?>