<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}


include 'db_connection.php';


$user_id = $_SESSION['user_id'];

$query = "SELECT profile_image, username FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES['profile_image']['name']);
    $upload_ok = 1;
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    
    $check = getimagesize($_FILES['profile_image']['tmp_name']);
    if ($check === false) {
        $upload_ok = 0;
    }

    
    if (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
        $upload_ok = 0;
    }

   
    if ($upload_ok && move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
        
        $query = "UPDATE users SET profile_image = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $target_file, $user_id);
        $stmt->execute();

       
        header("Location: admin_dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css"> 
</head>

<body>
    <div class="page-container">
       
        <div class="admin-header">
            <div class="admin-session">
                <img src="<?php echo $admin['profile_image'] ? $admin['profile_image'] : 'uploads/default_avatar.png'; ?>" alt="Admin Profile" class="profile-image">
                <span class="admin-name"><?php echo $admin['username']; ?></span>
            </div>
            <a href="logout.php" class="logout-button">Logout</a>
        </div>

       
        <div class="dashboard-title-container">
            <h1 class="dashboard-title">Welcome to Admin Dashboard</h1>
        </div>

       
        <div class="vertical-navbar">
            <ul>
                <li><a href="manage_users.php">Manage Users</a></li>
                <li><a href="assign_roles.php">Assign Roles</a></li>
                <li><a href="manage_resources.php">Manage Resources</a></li>
                <li><a href="billing.php">Billing</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li><a href="settings.php">Settings</a></li>
            </ul>
        </div>
    </div>
</body>

</html>