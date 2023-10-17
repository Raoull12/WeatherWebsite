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

        
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Index Page</title>
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
    </div>

    <!-- The rest of your content goes here -->

</body>
</html>

