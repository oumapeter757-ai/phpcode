<?php

include 'db_connection.php';


$query = "SELECT * FROM users";
$result = $conn->query($query);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $role = $_POST['role'];

    $update_query = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $role, $user_id);
    $stmt->execute();

    header("Location: assign_roles.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Roles</title>
    <link rel="stylesheet" href="assign.css"> 
</head>

<body>
    <div class="page-container">
        <h1>Assign Roles</h1>
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Current Role</th>
                    <th>Assign New Role</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['role']; ?></td>
                        <td>
                            <form action="assign_roles.php" method="POST" class="assign-role-form">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <select name="role" required>
                                    <option value="Admin" <?php echo $row['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="Doctor" <?php echo $row['role'] === 'Doctor' ? 'selected' : ''; ?>>Doctor</option>
                                    <option value="Nurse" <?php echo $row['role'] === 'Nurse' ? 'selected' : ''; ?>>Nurse</option>
                                    <option value="Receptionist" <?php echo $row['role'] === 'Receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                                </select>
                                <button type="submit" class="assign-role-button">Assign</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
       
        <div class="return-dashboard">
            <a href="admin_dashboard.php" class="dashboard-link">Return to Admin Dashboard</a>
        </div>
    </div>
</body>

</html>