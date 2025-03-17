<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

// Fetch all patient records
$query = "SELECT * FROM patients";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error); // Debugging: Check for query errors
}

if ($result->num_rows == 0) {
    echo "<p>No patient records found.</p>"; // Debugging: Check if the table is empty
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patient Records</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
</head>

<body>
    <div class="dashboard-container">
        <h2>Manage Patient Records</h2>
        <table>
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($patient = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($patient['id']); ?></td>
                            <td><?php echo htmlspecialchars($patient['name']); ?></td>
                            <td><?php echo htmlspecialchars($patient['age']); ?></td>
                            <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                            <td><?php echo htmlspecialchars($patient['contact']); ?></td>
                            <td>
                                <a href="edit_patient.php?id=<?php echo $patient['id']; ?>">Edit</a> |
                                <a href="delete_patient.php?id=<?php echo $patient['id']; ?>" onclick="return confirm('Are you sure you want to delete this patient?');">Delete</a>
                            </td>
                        </tr>
                <?php }
                } else {
                    echo "<tr><td colspan='6'>No patient records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>