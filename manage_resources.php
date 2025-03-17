<?php



include 'db_connection.php';


$query = "SELECT * FROM resources";
$result = $conn->query($query);

if (isset($_GET['delete'])) {
    $resource_id = $_GET['delete'];
    $delete_query = "DELETE FROM resources WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $resource_id);
    $stmt->execute();
    header("Location: manage_resources.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resource'])) {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $quantity = $_POST['quantity'];

    $insert_query = "INSERT INTO resources (name, type, quantity) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssi", $name, $type, $quantity);
    $stmt->execute();
    header("Location: manage_resources.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Resources</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="page-container">
        <h1>Manage Resources</h1>

   
        <form action="manage_resources.php" method="POST" class="form-container">
            <h2>Add New Resource</h2>
            <label for="name">Resource Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="type">Resource Type:</label>
            <input type="text" id="type" name="type" required>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required>

            <button type="submit" name="add_resource" class="add-user-button">Add Resource</button>
        </form>

       
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Resource Name</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['type']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>
                            <a href="edit_resource.php?id=<?php echo $row['id']; ?>" class="edit-button">Edit</a>
                            <a href="manage_resources.php?delete=<?php echo $row['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this resource?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>