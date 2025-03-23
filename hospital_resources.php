<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'doctor')) {
    echo "Access denied. Please log in as an administrator or doctor.";
    exit;
}

$success_message = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resource_name = $_POST['resource_name'];
    $resource_type = $_POST['resource_type'];
    $quantity = $_POST['quantity'];
    $status = $_POST['status'];

    $query = "INSERT INTO hospital_resources (resource_name, resource_type, quantity, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssis", $resource_name, $resource_type, $quantity, $status);
    $stmt->execute();

    $success_message = "Resource added successfully!";
}


$query = "SELECT * FROM hospital_resources ORDER BY added_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Resources</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="dashboard-container">
        <h2>Hospital Resources</h2>

       
        <?php if (!empty($success_message)) { ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php } ?>

      
        <form method="POST" class="form-container">
            <h3>Add New Resource</h3>
            <label for="resource_name">Resource Name:</label>
            <input type="text" name="resource_name" id="resource_name" required>

            <label for="resource_type">Resource Type:</label>
            <input type="text" name="resource_type" id="resource_type" required>

            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" min="1" required>

            <label for="status">Status:</label>
            <select name="status" id="status" required>
                <option value="Available">Available</option>
                <option value="In Use">In Use</option>
                <option value="Out of Stock">Out of Stock</option>
            </select>

            <button type="submit">Add Resource</button>
        </form>


        <h3>Available Resources</h3>
        <?php if ($result->num_rows > 0) { ?>
            <table class="resources-table">
                <thead>
                    <tr>
                        <th>Resource Name</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Added At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['resource_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['resource_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['added_at']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No resources found.</p>
        <?php } ?>

     
        <a href="doctor_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>
</body>

</html>

<?php
$conn->close();
?>