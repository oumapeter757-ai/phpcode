<?php
session_start();

include 'db_connection.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $query = "SELECT id, username, role, password FROM users WHERE email = ? AND role = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        
        if (password_verify($password, $user['password'])) {
          
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            
            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($user['role'] == 'doctor') {
                header("Location: doctor_dashboard.php");
            } elseif ($user['role'] == 'patient') {
                header("Location: patient_dashboard.php");

               
            }
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Invalid email or role!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <div class="form-container">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="patient">Patient</option>
                <option value="doctor">Doctor</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit">Login</button>
        </form>
        <div class="link">
            <p><a href="forgot_password.php">Forgot Password?</a></p>
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>
</body>

</html>