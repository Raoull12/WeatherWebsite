<?php
session_start(); //starting session to retrieve variables.

// Check if the user is not logged in
if (!isset($_SESSION["id"])) {
    header("Location: login.php"); // Redirect to the login page if not logged in
    exit;
} else {
    // Include the database connection file and retrieve user preferences
    include 'db_connection.php';
    $user_id = $_SESSION["id"];
    $username = $_SESSION["username"];
    
    // Prepare and execute a SELECT query to get user preferences
    $sql = "SELECT * FROM user_preferences WHERE user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Get user location and temperature unit
    $location = $user["location"];
    $temperature_unit = $user["temperature_unit"];

    $mysqli->close(); // Close the database connection

    // Define an array of predefined locations with their coordinates
    $locations = [
        "London" => ["latitude" => 51.5074, "longitude" => -0.1278],
        "Valletta" => ["latitude" => 35.8989, "longitude" => 14.5146],
        "Belgrade" => ["latitude" => 44.7866, "longitude" => 20.4489],
        "Athens" => ["latitude" => 37.9838, "longitude" => 23.7275],
        "Berlin" => ["latitude" => 52.5200, "longitude" => 13.4050],
        "Rome" => ["latitude" => 41.9028, "longitude" => 12.4964],
        "Amsterdam" => ["latitude" => 52.3676, "longitude" => 4.9041]
    ];

    // Check if a POST request was sent with the minimal details
    if (isset($_POST['start-date']) && isset($_POST['end-date'])) {
        // Retrieve user input
        $startDate = strtotime($_POST['start-date']);
        $endDate = strtotime($_POST['end-date']);
        $weatherType = isset($_POST['weather-type']) ? $_POST['weather-type'] : ''; // Check if weather-type is set

        $apiKey = '64b1dd546784c2f64d1169be8b09db0b';

        // Get latitude and longitude based on user's location
        $lat = $locations[$location]['latitude'];
        $lon = $locations[$location]['longitude'];

        // Construct the API URL for weather data
        $apiUrl = "https://history.openweathermap.org/data/2.5/history/city?lat=$lat&lon=$lon&type=hour&start=$startDate&end=$endDate&appid=$apiKey";

        // Get weather data from the OpenWeatherMap API
        $jsonResponse = file_get_contents($apiUrl);

        // Decode the JSON response
        $weatherData = json_decode($jsonResponse, true);

        // Filter weather data based on user's specified weather type
        $matchingWeatherData = [];

        foreach ($weatherData['list'] as $hourlyData) {
            $weatherDescription = $hourlyData['weather'][0]['description'];

            // Check if weather-type is empty or if it matches the user's specified type
            if (empty($weatherType) || stripos($weatherDescription, $weatherType) !== false) {
                $matchingWeatherData[] = $hourlyData;
            }
        }

        // Send the filtered weather data back to the dashboard
        echo json_encode($matchingWeatherData);

        exit(); // Exit after sending the data
    }
}
?>
