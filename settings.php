<?php


include 'db_connection.php';


$admin_query = "SELECT * FROM admins WHERE id = 1";
$admin_result = $conn->query($admin_query);
$admin = $admin_result->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];

    $update_query = "UPDATE admins SET name = ?, email = ? WHERE id = 1";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();
    header("Location: settings.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

   
    if (password_verify($current_password, $admin['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_query = "UPDATE admins SET password = ? WHERE id = 1";
            $stmt = $conn->prepare($update_password_query);
            $stmt->bind_param("s", $hashed_password);
            $stmt->execute();
            $password_message = "Password updated successfully!";
        } else {
            $password_message = "New passwords do not match!";
        }
    } else {
        $password_message = "Current password is incorrect!";
    }
}
?>

<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="styles.css"> 
</head>

<body>
    <div class="page-container">
        <h1>Settings</h1>

        
        <form action="settings.php" method="POST" class="form-container">
            <h2>Update Profile</h2>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo $admin['name']; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $admin['email']; ?>" required>

            <button type="submit" name="update_profile" class="add-user-button">Update Profile</button>
        </form>

   
        <form action="settings.php" method="POST" class="form-container">
            <h2>Change Password</h2>
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" name="change_password" class="add-user-button">Change Password</button>
        </form>

        <?php if (isset($password_message)): ?>
            <p class="password-message"><?php echo $password_message; ?></p>
        <?php endif; ?>

        
        <div class="return-dashboard">
            <a href="admin_dashboard.php" class="dashboard-link">Return to Admin Dashboard</a>
        </div>
    </div>
</body>

</html>