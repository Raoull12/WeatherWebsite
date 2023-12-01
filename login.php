<?php
session_start();
require_once 'vendor/autoload.php';

$isInvalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include 'db_connection.php';

    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $_POST["email"]);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($_POST["password"], $user["password"])) {

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

            $_SESSION["id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["location"] = $location;
            $_SESSION["temperature_unit"] = $temperature_unit;

            header("Location: dashboard.php");
            exit; // Make sure to exit after redirection
        } else {
            $isInvalid = true;
        }
    } else {
        $isInvalid = true;
    }
}

$loader = new \Twig\Loader\FilesystemLoader(__DIR__);
$twig = new \Twig\Environment($loader);

echo $twig->render('login.twig', [
    'is_invalid' => $isInvalid,
    'email' => htmlspecialchars($_POST["email"] ?? "")
]);
?>
