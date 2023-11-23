<?php

require_once 'vendor/autoload.php';

session_start();

class Dashboard
{
    private $username;
    private $topRightLinks;
    private $weatherInfo;

    public function __construct($location, $temperature, $temperatureUnitSymbol, $weatherDescription, $weatherData)
    {
        $this->username = $_SESSION["username"];
        $this->topRightLinks = '<a href="logout.php">Log Out</a><a href="edit-profile.php">Edit Profile</a>';
        $this->weatherInfo = $this->renderWeatherInfo($location, $temperature, $temperatureUnitSymbol, $weatherDescription, $weatherData);
    }

    public function render()
    {
        // Define the paths to the Twig templates
        $layoutTemplate = 'views/dashboard_layout.html';

        // Load the Twig environment
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views'); // Adjust the path to your Twig templates
        $twig = new \Twig\Environment($loader);

        // Render the dashboard layout
        echo $twig->render($layoutTemplate, [
            'username' => $this->username,
            'topRightLinks' => $this->topRightLinks,
            'weatherInfo' => $this->weatherInfo,
        ]);
    }

    private function getWeatherInfo()
    {
        $apiKey = "64b1dd546784c2f64d1169be8b09db0b";
        $locations = [
            "London" => ["latitude" => 51.5074, "longitude" => -0.1278],
            "Valletta" => ["latitude" => 35.8989, "longitude" => 14.5146],
            "Belgrade" => ["latitude" => 44.7866, "longitude" => 20.4489],
            "Athens" => ["latitude" => 37.9838, "longitude" => 23.7275],
            "Berlin" => ["latitude" => 52.5200, "longitude" => 13.4050],
            "Rome" => ["latitude" => 41.9028, "longitude" => 12.4964],
            "Amsterdam" => ["latitude" => 52.3676, "longitude" => 4.9041]
        ];

        $location = $_SESSION["location"];

        $locationData = isset($locations[$location]) ? $locations[$location] : null;

        if ($locationData !== null) {
            $latitude = $locationData["latitude"];
            $longitude = $locationData["longitude"];

            $apiUrl = "https://api.openweathermap.org/data/2.5/weather?lat=" . $latitude . "&lon=" . $longitude . "&appid=" . $apiKey;

            $jsonResponse = file_get_contents($apiUrl);

            // Decoding JSON Response
            $weatherData = json_decode($jsonResponse);

            if ($weatherData !== null) {
                return $this->renderWeatherInfo($weatherData);
            } else {
                return "Failed to fetch weather data.";
            }
        } else {
            return "Invalid location selected.";
        }
    }

    private function renderWeatherInfo($weatherData)
    {
        // Define the path to the Twig template
        $weatherTemplate = 'weather_info.html';

        // Load the Twig environment
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views'); // Adjust the path to your Twig templates
        $twig = new \Twig\Environment($loader);

        $temperatureUnitSymbol = $_SESSION["temperature_unit"];

        // Render the weather info template
        return $twig->render($weatherTemplate, [
            'location' => $weatherData->name,
            'temperature' => $weatherData->main->temp,
            'temperatureUnitSymbol' => $temperatureUnitSymbol,
            'weatherDescription' => $weatherData->weather[0]->description,
            'humidity' => $weatherData->main->humidity,
            'pressure' => $weatherData->main->pressure,
            'windSpeed' => $weatherData->wind->speed,
            'cloudiness' => $weatherData->clouds->all,
            'imagePath' => $this->getImagePath($weatherData->weather[0]->description, $weatherData->dt),
        ]);
    }


    private function getImagePath($weatherDescription, $timestamp)
    {
        $imagePath = "images/";

        $localTime = new DateTime("@$timestamp", new DateTimeZone('UTC'));
        $localTime->setTimezone(new DateTimeZone('Europe/Rome'));
        $currentHour = (int) date('H', $localTime->getTimestamp());

        if ($currentHour >= 6 && $currentHour < 18) {
            if (stripos($weatherDescription, 'clear') !== false) {
                $imagePath .= "sun.png";
            } elseif (stripos($weatherDescription, 'cloud') !== false) {
                $imagePath .= "fewclouds.png";
            }
        } elseif ($currentHour >= 18 || $currentHour < 6) {
            if (stripos($weatherDescription, 'clear') !== false) {
                $imagePath .= "moon.png";
            } elseif (stripos($weatherDescription, 'cloud') !== false) {
                $imagePath .= "fewcloudsnight.png";
            }
        } elseif (stripos($weatherDescription, 'rain') !== false) {
            $imagePath .= "rain.png";
        }

        return $imagePath;
    }
}

// Usage in your main file (e.g., dashboard.php)
$is_invalid = false; // Replace with your logic to check for invalid sessions
$dashboard = new Dashboard(
    $_SESSION["username"],
    $location,
    $temperature,
    $temperatureUnitSymbol,
    $weatherDescription,
    $weatherData
);
$dashboard->render();
?>
