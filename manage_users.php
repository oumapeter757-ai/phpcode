<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'db_connection.php';

$error = "";
$success = "";

// Process deletion if "delete" is set in GET parameters.
if (isset($_GET['delete'])) {
    $delete_user_id = intval($_GET['delete']);
    // Prevent admin from deleting their own account.
    if ($delete_user_id == $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $delete_query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $delete_user_id);
        if ($stmt->execute()) {
            $success = "User deleted successfully.";
        } else {
            $error = "Failed to delete user: " . $stmt->error;
        }
    }
}

// Process the update (edit) form submission.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $update_user_id = intval($_POST['user_id']);
    $username_update = trim($_POST['username']);
    $email_update    = trim($_POST['email']);
    $role_update     = trim($_POST['role']);
    $password_update = trim($_POST['password']);

    if (empty($username_update) || empty($email_update) || empty($role_update)) {
        $error = "Username, email, and role are required.";
    } else {
        // If the password field is filled in, update it.
        if (!empty($password_update)) {
            $hashed_password = password_hash($password_update, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssi", $username_update, $email_update, $hashed_password, $role_update, $update_user_id);
        } else {
            $update_query = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sssi", $username_update, $email_update, $role_update, $update_user_id);
        }

        if ($stmt->execute()) {
            $success = "User updated successfully.";
            // Remove edit GET parameter after update.
            header("Location: manage_users.php");
            exit;
        } else {
            $error = "Failed to update user: " . $stmt->error;
        }
    }
}

// Process the add user form.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $new_user_id = trim($_POST['user_id']);
    $username    = trim($_POST['username']);
    $email       = trim($_POST['email']);
    $password    = trim($_POST['password']);
    $role        = trim($_POST['role']);

    if (empty($new_user_id) || empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // Check if a user with this ID already exists.
        $check_query = "SELECT id FROM users WHERE id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $new_user_id);
        $stmt->execute();
        $result_check = $stmt->get_result();

        if ($result_check->num_rows > 0) {
            $error = "User with this ID already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (id, username, email, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issss", $new_user_id, $username, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $success = "User added successfully.";
            } else {
                $error = "Failed to add user: " . $stmt->error;
            }
        }
    }
}

// If "edit" is set in GET, load the user credentials for editing.
$editUser = null;
if (isset($_GET['edit'])) {
    $edit_user_id = intval($_GET['edit']);
    $edit_query = "SELECT id, username, email, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($edit_query);
    $stmt->bind_param("i", $edit_user_id);
    $stmt->execute();
    $result_edit = $stmt->get_result();
    if ($result_edit->num_rows > 0) {
        $editUser = $result_edit->fetch_assoc();
    } else {
        $error = "User not found for editing.";
    }
}

// Fetch all users (for the table listing)
$users_query = "SELECT id, username, email, role FROM users ORDER BY id ASC";
$users_result = $conn->query($users_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="manage.css">
</head>

<body>
    <div class="page-container">
        <div class="admin-header">
            <a href="admin_dashboard.php" class="back-link">Back to Dashboard</a>
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
        <div class="dashboard-title-container">
            <h1>Manage Users</h1>
        </div>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <!-- EDIT USER FORM (shows only if "edit" is set) -->
        <?php if ($editUser): ?>
            <div class="form-container">
                <h2>Edit User</h2>
                <form action="manage_users.php" method="post">
                    <label for="user_id_edit">User ID:</label>
                    <input type="number" id="user_id_edit" name="user_id" value="<?php echo htmlspecialchars($editUser['id']); ?>" readonly>

                    <label for="username_edit">Username:</label>
                    <input type="text" id="username_edit" name="username" required value="<?php echo htmlspecialchars($editUser['username']); ?>">

                    <label for="email_edit">Email:</label>
                    <input type="email" id="email_edit" name="email" required value="<?php echo htmlspecialchars($editUser['email']); ?>">

                    <label for="role_edit">Role:</label>
                    <select name="role" id="role_edit" required>
                        <option value="">-- Select Role --</option>
                        <option value="admin" <?php if ($editUser['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                        <option value="doctor" <?php if ($editUser['role'] == 'doctor') echo 'selected'; ?>>Doctor</option>
                        <option value="patient" <?php if ($editUser['role'] == 'patient') echo 'selected'; ?>>Patient</option>
                    </select>

                    <label for="password_edit">Password: (Leave blank to keep current)</label>
                    <input type="password" id="password_edit" name="password">

                    <button type="submit" name="update_user">Update User</button>
                    <a href="manage_users.php" class="cancel-button">Cancel</a>
                </form>
            </div>
        <?php endif; ?>

        <!-- ADD NEW USER FORM -->
        <div class="form-container">
            <h2>Add New User</h2>
            <form action="manage_users.php" method="post">
                <label for="user_id">User ID:</label>
                <input type="number" id="user_id" name="user_id" required>

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="role">Role:</label>
                <select name="role" id="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="patient">Patient</option>
                </select>

                <button type="submit" name="add_user">Add User</button>
            </form>
        </div>

        <!-- USERS TABLE -->
        <div class="table-container">
            <h2>Users List</h2>
            <?php if ($users_result && $users_result->num_rows > 0): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <a href="manage_users.php?edit=<?php echo $user['id']; ?>" class="edit-link">Edit</a> |
                                    <a href="manage_users.php?delete=<?php echo $user['id']; ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                </td>

                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
<?php
$conn->close();
?>