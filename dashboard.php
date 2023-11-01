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

            // Extracting weather description from API response to check the status to retrieve the associated image.
            $weatherDescription = $weatherData->weather[0]->description;

            $temperatureinKelvin = $weatherData->main->temp;

            if ($temperature_unit === "Fahrenheit") {
                $temperature = ($temperatureinKelvin - 273.15) * 9/5 + 32; // Convert to Fahrenheit
                $temperatureUnitSymbol = "°F";
            } else {
                $temperature = $temperatureinKelvin - 273.15; // Default is Celsius
                $temperatureUnitSymbol = "°C";
            }

            $imagePath = "images/"; // Define the default image path
            // Format and display the local time
            $timestamp = $weatherData->dt;
            $localTime = new DateTime("@$timestamp", new DateTimeZone('UTC'));
            $localTime->setTimezone(new DateTimeZone('Europe/Rome'));

            // Getting the current time in 24-hour format
            $currentHour = (int)date('H', $localTime->getTimestamp());

            echo $currentHour;

            // Daytime logic
            if ($currentHour >= 6 && $currentHour < 18) {
                if (stripos($weatherDescription, 'clear') !== false) {
                    $imagePath .= "sun.png";
                } else if (stripos($weatherDescription, 'cloud') !== false)
                {
                    $imagePath .= "fewclouds.png";
                }
            }            // Nighttime logic
            else  if($currentHour >= 18 || $currentHour < 6){
                if (stripos($weatherDescription, 'clear') !== false) {
                    $imagePath .= "moon.png";
                }   else if (stripos($weatherDescription, 'cloud') !== false)
                {
                    $imagePath .= "fewcloudsnight.png";
                }
            }
             elseif (stripos($weatherDescription, 'rain') !== false) 
             {
                $imagePath .= "rain.png";
            }
            
            echo "<h2>Weather Information for $location</h2>";

                echo "<div style='display: flex;'>";

                echo "<div class='info-item' style='flex: 1; padding-right: 10px;'>";
                echo "Temperature: " . round($temperature, 2) . " " . $temperatureUnitSymbol;
                echo "</div>";

                echo "<div class='info-item' style='flex: 1; padding-right: 10px;'>";
                echo "Description: " . $weatherDescription;
                echo "</div>";

                echo "<div class='info-item' style='flex: 1; padding-right: 10px;'>";
                echo "Humidity: " . $weatherData->main->humidity . "%";
                echo "</div>";

                echo "<div class='info-item' style='flex: 1; padding-right: 10px;'>";
                echo "Pressure: " . $weatherData->main->pressure . " hPa";
                echo "</div>";

                echo "<div class='info-item' style='flex: 1; padding-right: 10px;'>";
                echo "Wind Speed: " . $weatherData->wind->speed . " m/s";
                echo "</div>";

                echo "<div class='info-item' style='flex: 1; padding-right: 10px;'>";
                echo "Cloudiness: " . $weatherData->clouds->all . "%";
                echo "</div>";

                echo "</div>";

                echo "<div class='info-item'><img src='$imagePath' alt='Weather Image' width='100' height='100'></div>";


            echo "<div><br><br><br><br></div>";

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

        #weather-info {
            display: flex;
            flex-wrap: wrap;
        }

        .info-item {
            flex: 1;
            padding: 10px;
            font-size: 24px;
        }
    </style>
</head>
<body>
<form id="search-form">
    <label for="start-date">Start Date:</label>
    <input type="date" id="start-date" name="start-date">

    <label for="end-date">End Date:</label>
    <input type="date" id="end-date" name="end-date">

    <label for="weather-type">Weather Type:</label>
    <select id="weather-type" name="weather-type">
        <option value="">Select Weather Type (optional)</option>
        <option value="clear">Clear</option>
        <option value="clouds">Clouds</option>
        <option value="rain">Rain</option>
    </select>

    <button type="button" id="search-button">Search</button>
</form>
<div class="top-right-links">
    <a href="logout.php">Log Out</a>
    <a href="edit-profile.php">Edit Profile</a>
</div>

<div id="search-results">
    <h2>Search Results</h2>
    <div id="search-results-content"></div>
</div>
</body>
</html>

<script>
// Adding an event listener for the search button
document.getElementById('search-button').addEventListener('click', function() {
    // Getting user input
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    const weatherType = document.getElementById('weather-type').value;

    // configuring ajax request before sending
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'search.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Defining the data to send
    const data = `start-date=${startDate}&end-date=${endDate}&weather-type=${weatherType}`;

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Handle the server's response (display search results)
            const searchResults = JSON.parse(xhr.responseText);
            displaySearchResults(searchResults);
        }
    };

    // sending the AJAX request
    xhr.send(data);
});

function displaySearchResults(results) {
    const searchResultsContent = document.getElementById('search-results-content');
    searchResultsContent.innerHTML = ''; // Clearing any previous results

    if (results.length === 0) {
        searchResultsContent.innerHTML = '<p>No results found.</p>';
        return;
    }

    // Iterate through the search results and display them
    results.forEach(result => {
        const resultContainer = document.createElement('div');
        resultContainer.className = 'search-result';

        const timestamp = new Date(result.dt * 1000); // Convert UNIX timestamp to a date
        const temperature = result.main.temp;
        const description = result.main.description;

        // Creating HTML elements to display the result
        const dateElement = document.createElement('p');
        dateElement.textContent = 'Date: ' + timestamp.toLocaleDateString() + ' ' + timestamp.toLocaleTimeString();

        const temperatureElement = document.createElement('p');
        temperatureElement.textContent = 'Temperature: ' + temperature + ' °K';

        // Append elements to the result container
        resultContainer.appendChild(dateElement);
        resultContainer.appendChild(temperatureElement);

        // Append the result container to the search results content
        searchResultsContent.appendChild(resultContainer);
    });
}
</script>