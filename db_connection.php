<?php
$user = 'root';
$pass = '';
$db = 'weather_website_db';

$mysqli = new mysqli('localhost', $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Unable to connect: " . $mysqli->connect_error);
}

return $mysqli;
?>
