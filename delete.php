<?php
session_start();

require_once 'vendor/autoload.php';

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit;
} else {
    $isConfirmed = isset($_POST["confirm"]) && $_POST["confirm"] === "yes";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        include 'db_connection.php';

        // Check if the user confirmed the account deletion
        if (isset($_POST["confirm"]) && $_POST["confirm"] === "yes") {
            // Get the user's ID
            $userId = $_SESSION["id"];

            // Delete the user's account, first from the user preferences table due to the tables being linked.
            $sqlDeletePreferences = "DELETE FROM user_preferences WHERE user_id = ?";
            $stmtDeletePreferences = $mysqli->prepare($sqlDeletePreferences);
            $stmtDeletePreferences->bind_param("i", $userId);
            $stmtDeletePreferences->execute();

            // Delete the user's record from the users table
            $sqlDeleteUser = "DELETE FROM users WHERE id = ?";
            $stmtDeleteUser = $mysqli->prepare($sqlDeleteUser);
            $stmtDeleteUser->bind_param("i", $userId);
            $stmtDeleteUser->execute();

            // Close the database connection
            $mysqli->close();

            // Logout the user and destroy the session
            session_destroy();

            // Redirect to the confirmation page
            header("Location: deleted_confirmation.html");
            exit;
        }
    }

    // Render the delete.twig template
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__);
    $twig = new \Twig\Environment($loader);

    echo $twig->render('delete.twig', [
        'confirmForm' => !$isConfirmed,
    ]);
}
?>