<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
}

require_once 'vendor/autoload.php';
$loader = new \Twig\Loader\FilesystemLoader(__DIR__);
$twig = new \Twig\Environment($loader);

$userId = $_SESSION["id"];
$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mysqli = require __DIR__ . "/db_connection.php";
    
    // Getting the user's current and new password from the post request
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['password_confirmation'];

    // Retrieving the hashed password associated with the user from the database
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
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT); //using password_bcrypt to hash passwords as it is a strong algorithm
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $userId);
            $stmt->execute();
            // Password updated successfully
            $successMessage = 'Password changed successfully.';
            $mysqli->close();
        } else {
            // New passwords do not match, show an error
            $errorMessage = 'New passwords do not match.';
        }
    } else {
        // Current password is incorrect, show an error
        $errorMessage = 'Current password is incorrect.';
    }
}

$template = $twig->load('change_password.twig');
echo $template->render([
    'successMessage' => $successMessage
]);
?>
