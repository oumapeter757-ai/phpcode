<?php
session_start();
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $role = $_POST['role'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if user exists with the provided email and role
        $query = "SELECT id FROM users WHERE email = ? AND role = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found, update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE email = ? AND role = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sss", $hashed_password, $email, $role);
            $update_stmt->execute();

            $success = "Password successfully reset! <a href='login.php'>Login here</a>";
        } else {
            $error = "No account found with that email and role!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="forgot.css">
</head>

<body>
    <div class="form-container">
        <h2>Forgot Password</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <select name="role" required>
                <option value="patient">Patient</option>
                <option value="doctor">Doctor</option>
                <option value="admin">Admin</option>
            </select>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
            <button type="submit">Reset Password</button>
        </form>
        <div class="link">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</body>

</html>