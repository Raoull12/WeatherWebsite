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

    $locations = [
        "London" => ["latitude" => 51.5074, "longitude" => -0.1278],
        "Valletta" => ["latitude" => 35.8989, "longitude" => 14.5146],
        "Belgrade" => ["latitude" => 44.7866, "longitude" => 20.4489],
        "Athens" => ["latitude" => 37.9838, "longitude" => 23.7275],
        "Berlin" => ["latitude" => 52.5200, "longitude" => 13.4050],
        "Rome" => ["latitude" => 41.9028, "longitude" => 12.4964],
        "Amsterdam" => ["latitude" => 52.3676, "longitude" => 4.9041]
    ];
    
    if (isset($_POST['start-date']) && isset($_POST['end-date']) && isset($_POST['weather-type'])) {
        $startDate = strtotime($_POST['start-date']);
        $endDate = strtotime($_POST['end-date']);
        $weatherType = $_POST['weather-type'];

        $apiKey = '64b1dd546784c2f64d1169be8b09db0b'; // Replace with your API key

        $lat = $locations[$location]['latitude'];
        $lon = $locations[$location]['longitude'];

        $apiUrl = "https://history.openweathermap.org/data/2.5/history/city?lat=$lat&lon=$lon&type=hour&start=$startDate&end=$endDate&appid=$apiKey";

        $jsonResponse = file_get_contents($apiUrl);

        $weatherData = json_decode($jsonResponse, true);

        $matchingWeatherData = [];

        foreach ($weatherData['list'] as $hourlyData) {
            $weatherDescription = $hourlyData['weather'][0]['description'];

            if (stripos($weatherDescription, $weatherType) !== false) {
                $matchingWeatherData[] = $hourlyData;
            }
        }

        echo json_encode($matchingWeatherData);
        exit(); // Terminate the script
    }
}
?>
