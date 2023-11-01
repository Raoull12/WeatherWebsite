<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") 
{
    include 'db_connection.php';
    // Check if the user confirmed the account deletion
    if (isset($_POST["confirm"]) && $_POST["confirm"] === "yes") 
    {
        // Get the user's ID
        $userId = $_SESSION["id"];
        
        // Delete the user's account, first the from the user preferences table due to the tables being linked.
        $sqlDeletePreferences = "DELETE FROM user_preferences WHERE user_id = ?";
        $stmtDeletePreferences = $mysqli->prepare($sqlDeletePreferences);
        $stmtDeletePreferences->bind_param("i", $userId);
        $stmtDeletePreferences->execute();

        // Delete the user's record from the users table
        $sqlDeleteUser = "DELETE FROM users WHERE id = ?";
        $stmtDeleteUser = $mysqli->prepare($sqlDeleteUser);
        $stmtDeleteUser->bind_param("i", $userId);
        $stmtDeleteUser->execute();
        
        // Logout the user and destroy the session
        session_destroy();

        header("Location: deleted_confirmation.html"); // You can create this confirmation page
        $mysqli->close();
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Account</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1>Delete Account</h1>
    <div id="error"></div>

    <form method="post">
        <p>Are you sure you want to delete your account?</p>
        <p>This action cannot be undone.</p>
        <label for="confirm">Type "yes" to confirm:</label>
        <input type="text" name="confirm" id="confirm" required>
        <button type="submit" value="submit">Delete Account</button>
    </form>

    <div class="top-right-links">
        <a href="index.php">Go to Index</a>
        <a href="logout.php">Log Out</a>
        <a href="edit-profile.php">Edit Profile</a>
    </div>
</body>
</html>
