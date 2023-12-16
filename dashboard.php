<?php
session_start();

 if (!isset($_SESSION["id"])) 
{
    header("Location: login.php");
} else 
{

    $mysqli = require __DIR__ . "/db_connection.php";
    $user_id = $_SESSION["id"]; // storing the userId and other details for future use
    $username = $_SESSION["username"]; //storing the username from the session variables into a local variable
    $sql = "SELECT * FROM user_preferences WHERE user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute(); 

    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // retrieving the preferences of the user in the current session

    $location = $user["location"];
    $temperature_unit = $user["temperature_unit"]; //storing the retrieved location and temperature unit in local variables

    $mysqli->close(); // closing the database connection

    require_once 'vendor/autoload.php'; //including the composer's autoloader file in the script

    $locations = [
        "London" => ["latitude" => 51.5074, "longitude" => -0.1278],
        "Valletta" => ["latitude" => 35.8989, "longitude" => 14.5146],
        "Belgrade" => ["latitude" => 44.7866, "longitude" => 20.4489],
        "Athens" => ["latitude" => 37.9838, "longitude" => 23.7275],
        "Berlin" => ["latitude" => 52.5200, "longitu
        de" => 13.4050],
        "Rome" => ["latitude" => 41.9028, "longitude" => 12.4964],
        "Amsterdam" => ["latitude" => 52.3676, "longitude" => 4.9041]
    ]; // converting the locations into langitude and longitude values

    if (array_key_exists($location, $locations)) {
        $latitude = $locations[$location]["latitude"];
        $longitude = $locations[$location]["longitude"]; //storing langitude and longitude values depending on the user's stored location

        $apiKey = "64b1dd546784c2f64d1169be8b09db0b"; //storing the API key in a local variable

        $apiUrl = "https://api.openweathermap.org/data/2.5/weather?lat=" . $latitude . "&lon=" . $longitude . "&appid=" . $apiKey; //joining the url with the required data 

        $jsonResponse = file_get_contents($apiUrl); // retrieving the contents of the API response.

        // Decoding JSON Response
        $weatherData = json_decode($jsonResponse);

        if ($weatherData !== null) {

            // Extracting weather description from API response to check the status to retrieve the associated image.
            $weatherDescription = $weatherData->weather[0]->description;

            $temperatureinKelvin = $weatherData->main->temp;

            if ($temperature_unit === "Fahrenheit") {
                $temperature = ($temperatureinKelvin - 273.15) * 9 / 5 + 32; // Convert to Fahrenheit
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

            if (stripos($weatherDescription, 'rain') !== false || stripos($weatherDescription, 'drizzle') !== false) 
            {
                $imagePath .= "rain.png";
            }
            // Daytime logic
            else if ($currentHour >= 6 && $currentHour < 18) {
                if (stripos($weatherDescription, 'clear') !== false) {
                    $imagePath .= "sun.png";
                } else if (stripos($weatherDescription, 'cloud') !== false) {
                    $imagePath .= "fewclouds.png";
                }
            }
            // Nighttime logic
            else  if ($currentHour >= 18 || $currentHour < 6) {
                if (stripos($weatherDescription, 'clear') !== false) {
                    $imagePath .= "moon.png";
                } else if (stripos($weatherDescription, 'cloud') !== false) {
                    $imagePath .= "fewcloudsnight.png";
                }
            }
        } 
        else 
        {
            echo "Failed to fetch weather data.";
        }
    } else {
        echo "Invalid location selected.";
    }

    $loader = new \Twig\Loader\FilesystemLoader(__DIR__);

    // Initializing Twig
    $twig = new \Twig\Environment($loader);

// Rendering the template with variables
echo $twig->render('dashboard.twig', [
    'username' => $username,
    'location' => $location,
    'temperature' => round($temperature, 2),
    'temperatureUnitSymbol' => $temperatureUnitSymbol,
    'weatherDescription' => $weatherDescription,
    'humidity' => $weatherData->main->humidity,
    'windSpeed' => $weatherData->wind->speed,
    'cloudiness' => $weatherData->clouds->all,
    'imagePath' => $imagePath
]);
}
?>

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

        // Iterating through search results and displaying them
        results.forEach(result => {
            const resultContainer = document.createElement('div');
            resultContainer.className = 'search-result';

            const timestamp = new Date(result.dt * 1000); // Converting the UNIX into a date
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
