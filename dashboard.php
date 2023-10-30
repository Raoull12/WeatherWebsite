<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
} else {

    $mysqli = require __DIR__ . "/db_connection.php";
    $user_id = $_SESSION["id"];
    $username = $_SESSION["username"];
    $sql = "SELECT * FROM user_preferences WHERE user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $location = $user["location"];
    $temperature_unit = $user["temperature_unit"];

    echo "Hello " . $username . " :)";

    $locations = [
        "London" => ["latitude" => 51.5074, "longitude" => -0.1278],
        "Valletta" => ["latitude" => 35.8989, "longitude" => 14.5146],
        "Belgrade" => ["latitude" => 44.7866, "longitude" => 20.4489],
        "Athens" => ["latitude" => 37.9838, "longitude" => 23.7275],
        "Berlin" => ["latitude" => 52.5200, "longitude" => 13.4050],
        "Rome" => ["latitude" => 41.9028, "longitude" => 12.4964],
        "Amsterdam" => ["latitude" => 52.3676, "longitude" => 4.9041]
    ];

    if (array_key_exists($location, $locations)) {
        $latitude = $locations[$location]["latitude"];
        $longitude = $locations[$location]["longitude"];

        $apiKey = "64b1dd546784c2f64d1169be8b09db0b";

        $apiUrl = "https://api.openweathermap.org/data/2.5/weather?lat=" . $latitude . "&lon=" . $longitude . "&appid=" . $apiKey;

        $jsonResponse = file_get_contents($apiUrl);

        // Decoding JSON Response
        $weatherData = json_decode($jsonResponse);

        if ($weatherData !== null) {

            // Extracting weather description from API response to check the status in order to retrieve the associated image.
            $weatherDescription = $weatherData->weather[0]->description;

            $temperatureinKelvin = $weatherData->main->temp;
            $Celsius = $temperatureinKelvin - 273.15;

            echo "<h1>Weather Information for $location</h1>";
            echo "Temperature: " . round($Celsius, 2) . " °C<br>"; // Rounding to 2 decimal places
            echo "Description: " . $weatherDescription . "<br>";

            // Additional weather details
            echo "Humidity: " . $weatherData->main->humidity . "%<br>";
            echo "Pressure: " . $weatherData->main->pressure . " hPa<br>";
            echo "Wind Speed: " . $weatherData->wind->speed . " m/s<br>";
            echo "Cloudiness: " . $weatherData->clouds->all . "%<br>";

            $imagePath = "images/";

            if (stripos($weatherDescription, 'clear') !== false) {
                $imagePath .= "clear.png";
            } elseif (stripos($weatherDescription, 'cloud') !== false) {
                $imagePath .= "fewclouds.png";
            } elseif (stripos($weatherDescription, 'rain') !== false) {
                $imagePath .= "rain.png";
            }

            
            // Displaying Weather Image.
            echo "<img src='$imagePath' alt='Weather Image' width='100' height='100'><br>";

            $timezoneOffset = $weatherData->timezone;

            // Calculate the local time in the selected location.
            $currentTime = time() + $timezoneOffset;

            // Format and display the local time.
            $localTime = date("Y-m-d H:i:s", $currentTime);
            echo "Local Date and Time: " . $localTime . "<br>";

            echo "<br><br><br> Have a nice Day!";

        } else {
            echo "Failed to fetch weather data.";
        }
    } else {
        echo "Invalid location selected.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
        .top-right-links {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .top-right-links a {
            margin-left: 10px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="top-right-links">
        <a href="logout.php">Log Out</a>
        <a href="edit-profile.php">Edit Profile</a>
        <a href="search.php">Search</a>
    </div>
