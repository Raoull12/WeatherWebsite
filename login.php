<?php
session_start();
require_once 'vendor/autoload.php'; //including the composer's autoloader file in the script

$isInvalid = false;

// Check if the form is submitted using the POST method
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Include the database connection file
    include 'db_connection.php';

    // Validate the email using a filter
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        // Prepare and execute a SELECT query to check if the user with the given email exists
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $_POST["email"]);
        $stmt->execute();

        // Get the result and fetch the user details
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Check if the user exists and the password is correct
        if ($user && password_verify($_POST["password"], $user["password"])) {

            // Retrieve user preferences (location and temperature unit)
            $locationSql = "SELECT location FROM user_preferences WHERE user_id = ?";
            $locationStmt = $mysqli->prepare($locationSql);
            $locationStmt->bind_param("i", $user['id']);
            $locationStmt->execute();
            $locationResult = $locationStmt->get_result();
            $location = $locationResult->fetch_assoc()['location'];

            $temperatureUnitSql = "SELECT temperature_unit FROM user_preferences WHERE user_id = ?";
            $temperatureUnitStmt = $mysqli->prepare($temperatureUnitSql);
            $temperatureUnitStmt->bind_param("i", $user['id']);
            $temperatureUnitStmt->execute();
            $temperatureUnitResult = $temperatureUnitStmt->get_result();
            $temperature_unit = $temperatureUnitResult->fetch_assoc()['temperature_unit'];

            // Set session variables with user details
            $_SESSION["id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["location"] = $location;
            $_SESSION["temperature_unit"] = $temperature_unit;

            $mysqli->close(); //closing db connection
            // Redirect to the dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            //indicating invalid login credentials
            $isInvalid = true;
        }
    } else {
        //indicating invalid email format
        $isInvalid = true;
    }
}

// Load Twig template engine and render the login.twig template
$loader = new \Twig\Loader\FilesystemLoader(__DIR__);
$twig = new \Twig\Environment($loader);

// Pass data to the template for rendering
echo $twig->render('login.twig', [
    'is_invalid' => $isInvalid,
    'email' => htmlspecialchars($_POST["email"] ?? "")
]);
