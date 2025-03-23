<?php

include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

  
    $role = 'patient';

 
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

   
    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Username or email already exists!";
    } else {

        $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

        if ($stmt->execute()) {
           
            header("Location: login.php");
            exit;
        } else {
            $error = "Error: Could not register user.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="signup.css">
</head>

<body>
    <div class="form-container">
        <h2>Sign Up</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
        <div class="link">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>

</html>