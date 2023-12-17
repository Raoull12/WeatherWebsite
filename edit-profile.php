<?php
session_start(); //starting the session to retrieve session variables

require_once 'vendor/autoload.php'; //including the composer's autoloader file in the script

if (!isset($_SESSION["id"])) {
    header("Location: login.php"); //redirecting the user to the login page if not logged in (session id variable not set)
    exit;
} else {

    $mysqli = require __DIR__ . "/db_connection.php"; //opening db connection through the db_connection.php file

    $successMessage = ""; // Initializing the success message

    if ($_SERVER["REQUEST_METHOD"] === "POST") { //if a post request is sent to the server
        $newUsername = $_POST['username'];
        $newEmail = $_POST['email'];
        $newLocation = $_POST['location'];
        $newTemperatureUnit = $_POST['temperature_unit']; //storing variables sent with the post request into local variables.

        $userId = $_SESSION['id']; //using the sessionId (which is the userId) to find the records in the database

        // the below checks for any modified fields and updates data in the db.
        if (!empty($newUsername)) {
            $sql = "UPDATE users SET username = ? WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $newUsername, $userId);
            $stmt->execute();
        }

        if (!empty($newEmail)) {
            $sql = "UPDATE users SET email = ? WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $newEmail, $userId);
            $stmt->execute();
        }

        if (!empty($newLocation)) {
            $sql = "UPDATE user_preferences SET location = ? WHERE user_id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $newLocation, $userId);
            $stmt->execute();
        }

        if (!empty($newTemperatureUnit)) {
            $sql = "UPDATE user_preferences SET temperature_unit = ? WHERE user_id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $newTemperatureUnit, $userId);
            $stmt->execute();
        }

        // Setting success message
        $successMessage = "Profile updated successfully!";
    }

    $userId = $_SESSION["id"]; //storing the userId in a local variable.

    $sql = "SELECT username, email, location, temperature_unit FROM users 
            LEFT JOIN user_preferences ON users.id = user_preferences.user_id 
            WHERE users.id = ?"; //joining the user and user preferences table where the userid matches
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // we have fetched the user and their associated preferences.
    
    $mysqli->close(); //closing db connection

    $loader = new \Twig\Loader\FilesystemLoader(__DIR__);
    $twig = new \Twig\Environment($loader);

    //rendering the twig template
    echo $twig->render('edit-profile.twig', [
        'successMessage' => $successMessage,
        'user' => $user,
    ]);
}
?>
