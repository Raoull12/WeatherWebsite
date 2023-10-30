<?php
    session_start();

    if(!isset($_SESSION["id"]))
    {
        header("Location: login.php");
    } else
    {
        $mysqli = require __DIR__ . "/db_connection.php";
        $user_id = $_SESSION["id"];
        $sql = "SELECT * FROM user_preferences WHERE user_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $location = $user["location"]; // fetching the location and temperature unit and storing them in local variables.
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

        $latitude = $locations[$location]["latitude"];
        $longitude = $locations[$location]["longitude"];

        $apiKey = "fe0ff6df108ee775692b09002bf17f58";


    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        .top-right-links {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .top-right-links a {
            margin-left: 10px;
            text-decoration: none;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="top-right-links">
        <a href="logout.php">Log Out</a>
        <a href="edit-profile.php">Edit Profile</a>
        <a href="search.php">
    </div>

</body>
</html>

