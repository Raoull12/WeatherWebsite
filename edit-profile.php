<?php 
    session_start(); //starting the session to retrieve superglobals

    if(!isset($_SESSION["id"]))
    {
        header("Location: login.php"); // if the user is not logged in, they will be redirected to login.php
    }
    else 
    {
        $mysqli = require __DIR__ . "/db_connection.php";

        if($_SERVER["REQUEST_METHOD"] === "POST")
        {
                $newUsername = $_POST['username'];
                $newEmail = $_POST['email'];
                $newLocation = $_POST['location'];
                $newTemperatureUnit = $_POST['temperature_unit'];

                    $userId = $_SESSION['id'];

                    if(!empty($newUsername))
                    {
                        $sql = "UPDATE users SET username = ? WHERE id = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("si", $newUsername, $userId);
                        $stmt->execute();
                    }

                    if(!empty($newEmail))
                    {
                        $sql = "UPDATE users SET email = ? WHERE id = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("si", $newEmail, $userId);
                        $stmt->execute();
                    }

                    if(!empty($newLocation))
                    {
                        $sql = "UPDATE user_preferences SET location = ? WHERE user_id = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("si", $newLocation, $userId);
                        $stmt->execute();
                    }

                    if(!empty($newTemperatureUnit))
                    {
                        $sql = "UPDATE user_preferences SET temperature_unit = ? WHERE user_id = ?";
                        $stmt = $mysqli->prepare($sql);
                        $stmt->bind_param("si", $newTemperatureUnit, $userId);
                        $stmt->execute();
                    }
            }

            $userId = $_SESSION["id"];

            $sql = "SELECT username, email FROM users WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
        /* Adding some spacing */
        div {
            margin-bottom: 10px;
        }

        /* Editing container */
        .top-right-links {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        /* Style for individual links */
        .top-right-links a {
            margin-left: 10px; /*adding some spacing between links */
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Edit Profile</h1>

    <form method="post">
        <div>
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) //so the user can see current data ?>">
        </div>

        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>">
        </div>

        <div>
            <label for="location">Location:</label>
            <select id="Location" name="location">
                <option value="Amsterdam">Amsterdam (GMT+1)</option>
                <option value="Athens">Athens (GMT+2)</option>
                <option value="Belgrade">Belgrade (GMT+1)</option>
                <option value="Berlin">Berlin (GMT+1)</option>
                <option value="London">London (GMT+1)</option>
                <option value="Rome">Rome (GMT+1)</option>
                <option value="Valletta">Valletta (GMT+1)</option>
            </select>
        </div>

        <div>
            <label for="temperature_unit">Temperature Unit:</label>
            <select id="temperature_unit" name="temperature_unit">
                <option value="celsius">Celsius</option>
                <option value="fahrenheit">Fahrenheit</option>
            </select>
        </div>

        <button>Save Changes</button>
    </form>

    <!-- Add the links to the top-right corner -->
    <div class="top-right-links">
        <a href="index.php">Go to Index</a>
        <a href="logout.php">Log Out</a>
        <a href="change_password.php">Change Password</a>
    </div>
</body>
</html>
