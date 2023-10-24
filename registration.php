<?php
if (empty($_POST["username"])) {
    die("Username is required");
}

if (preg_match("/[!@#$%^&*()_+{}\[\]:;<>,.?~\\-]/", $_POST["username"])) {
    die("Username cannot contain symbols");
}

if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Valid email is required");
}

if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters");
}

if (!preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter");
}

if (!preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain at least one number");
}

if (!preg_match("/[!@#$%^&*()_+{}\[\]:;<>,.?~\\-]/", $_POST["password"])) {
    die("Password must contain at least one symbol");
}

$mysqli = require __DIR__ . "/db_connection.php";

// Check if the username is already taken
$sql = "SELECT * FROM users WHERE username = ?";
$userstmt = $mysqli->prepare($sql);
$userstmt->bind_param("s", $_POST["username"]);
$userstmt->execute();
$result = $userstmt->get_result();

if ($result->num_rows > 0) 
{
    die("Username already taken");
}

// Check if the email is already taken
$sql = "SELECT * FROM users WHERE email = ?";
$userstmt = $mysqli->prepare($sql);
$userstmt->bind_param("s", $_POST["email"]);
$userstmt->execute();
$result = $userstmt->get_result();

if ($result->num_rows > 0) 
{
    die("Email already taken");
}

$location = $_POST["location"];
$temperature_unit = $_POST["temperature_unit"];

if (empty($location) || empty($temperature_unit)) 
{
    die("Please select a time zone and temperature unit");
}

// Proceeding with insertion. (If validations pass)

$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$registration_date = date("Y-m-d H:i:s");

$sql = "INSERT INTO users (username, email, password, registration_date)
        VALUES (?, ?, ?, ?)";
$userstmt = $mysqli->prepare($sql);
$userstmt->bind_param("ssss", $_POST["username"], $_POST["email"], $password_hash, $registration_date);

if ($userstmt->execute()) 
{
    $user_id = $mysqli->insert_id; // Getting the user id

    $sql2 = "INSERT INTO user_preferences (user_id, location, temperature_unit) VALUES (?, ?, ?)";
    $prefstmt = $mysqli->prepare($sql2);
    $prefstmt->bind_param("iss", $user_id, $timezone, $temperature_unit);

    if ($prefstmt->execute()) {
        header("Location: registration_success.html");
        exit;
    }
} else 
{
    if ($mysqli->errno === 1062) 
    {
        die("Email or username already taken");
    } else 
    {
        die($mysqli->error . " " . $mysqli->errno);
    }
}
?>
