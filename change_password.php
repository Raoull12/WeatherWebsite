<?php
session_start(); //starting session to retrieve superglobals.

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mysqli = require __DIR__ . "/db_connection.php";
    // Get the user's entered current password and new password
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['password_confirmation'];

    // Retrieve the hashed password associated with the user from the database
    $userId = $_SESSION["id"];
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify that the current password matches the hashed password from the database
    if (password_verify($currentPassword, $user['password'])) {
        // Passwords match, proceed to update the password
        if ($newPassword === $confirmPassword) {
            // Hash the new password before storing it
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $userId);
            $stmt->execute();
            // Password updated successfully

            header("Location: edit-profile.php");
        } else {
            // New passwords do not match, show an error
            echo "New passwords do not match.";
        }
    } else {
        // Current password is incorrect, show an error
        echo "Current password is incorrect.";
    }
}


$userId = $_SESSION["id"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js" defer></script>
    <script src="validation.js" defer></script>
</head>
<body>
    <h1>Change Password</h1>
    <div id="error"></div>

    <form id="form" method="post">
        <div>
            <label for="current_password">Current Password:</label>
            <input type="password" name="current_password" id="current_password" required>
        </div>

        <div>
            <label for="password">New Password:</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div>
            <label for="password_confirmation">Confirm New Password:</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required>
        </div>

        <button id="submit" type="submit" value="submit">Change Password</button>
    </form>

    <div class="top-right-links">
        <a href="dashboard.php">Go to Dashboard</a>
        <a href="logout.php">Log Out</a>
        <a href="edit-profile.php">Edit Profile</a>
    </div>
</body>
</html>
