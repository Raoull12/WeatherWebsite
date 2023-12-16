<?php
// Checking if the "username" field is empty
if (empty($_POST["username"])) {
    die("Username is required"); // If empty, terminate with an error message
}

// Checking if the "username" contains any special symbols
if (preg_match("/[!@#$%^&*()_+{}\[\]:;<>,.?~\\-]/", $_POST["username"])) {
    die("Username cannot contain symbols"); // If symbols are found, terminate with an error message
}

// Checking if the "email" field contains a valid email address
if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
    die("Valid email is required"); // If not a valid email, terminate with an error message
}

// Checking if the "password" field is less than 8 characters
if (strlen($_POST["password"]) < 8) {
    die("Password must be at least 8 characters"); // If less than 8 characters, terminate with an error message
}

// Checking if the "password" contains at least one letter
if (!preg_match("/[a-z]/i", $_POST["password"])) {
    die("Password must contain at least one letter"); // If no letter found, terminate with an error message
}

// Checking if the "password" contains at least one number
if (!preg_match("/[0-9]/", $_POST["password"])) {
    die("Password must contain at least one number"); // If no number found, terminate with an error message
}

// Checking if the "password" contains at least one special symbol
if (!preg_match("/[!@#$%^&*()_+{}\[\]:;<>,.?~\\-]/", $_POST["password"])) {
    die("Password must contain at least one symbol"); // If no symbol found, terminate with an error message
}


include 'db_connection.php';

// Check if the username is already taken
$sql = "SELECT * FROM users WHERE username = ?";
$userstmt = $mysqli->prepare($sql);
$userstmt->bind_param("s", $_POST["username"]);
$userstmt->execute();
$result = $userstmt->get_result();

if ($result->num_rows > 0) {
    die("Username already taken"); // if the result is anything other than 0
}

// Check if the email is already taken
$sql = "SELECT * FROM users WHERE email = ?";
$userstmt = $mysqli->prepare($sql);
$userstmt->bind_param("s", $_POST["email"]);
$userstmt->execute();
$result = $userstmt->get_result();

if ($result->num_rows > 0) {
    die("Email already taken"); // if the result is anything other than 0
}

$location = $_POST["location"];

// If the location is empty, seting a default value (Amsterdam)
if (empty($location)) {
    $location = "Amsterdam";
}

$temperature_unit = $_POST["temperature_unit"];

// Proceeding with insertion. (If validations pass)

$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT); //hashing the password

$registration_date = date("Y-m-d H:i:s");

$sql = "INSERT INTO users (username, email, password, registration_date)
        VALUES (?, ?, ?, ?)";
$userstmt = $mysqli->prepare($sql);
$userstmt->bind_param("ssss", $_POST["username"], $_POST["email"], $password_hash, $registration_date); //using a prepare statement to insert into the users table as it is the best option to prevent SQL injection etc.

if ($userstmt->execute()) {
    $user_id = $mysqli->insert_id; // Getting the user id

    $sql2 = "INSERT INTO user_preferences (user_id, location, temperature_unit) VALUES (?, ?, ?)";
    $prefstmt = $mysqli->prepare($sql2);
    $prefstmt->bind_param("iss", $user_id, $location, $temperature_unit); //inserting into user preferences table

    if ($prefstmt->execute()) {
        header("Location: registration_success.html"); // if successful redirect.
        exit;
    }
} else {
    if ($mysqli->errno === 1062) {
        die("Email or username already taken");
    } else {
        die($mysqli->error . " " . $mysqli->errno);
    }
}

$mysqli->close(); //closing the db connection
