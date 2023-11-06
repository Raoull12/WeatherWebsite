<?php
session_start();

if (!isset($_SESSION["id"])) {
    header("Location: login.php");
} else {
    $mysqli = require __DIR__ . "/db_connection.php";

    $successMessage = ""; // Initializing the success message

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $newUsername = $_POST['username'];
        $newEmail = $_POST['email'];
        $newLocation = $_POST['location'];
        $newTemperatureUnit = $_POST['temperature_unit'];

        $userId = $_SESSION['id']; //using the sessionId (which is the userId) to find the records in the database

        // the below checks for any modified fields and updates data in the db.
        if (!empty($newUsername)) {
            $sql = "UPDATE users SET username = ? WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $newUsername, $userId);
            $stmt->execute();
        }

        if (!empty($newEmail)) {
            $sql = "UPDATE users SET email = ? WHERE id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $newEmail, $userId);
            $stmt->execute();
        }

        if (!empty($newLocation)) {
            $sql = "UPDATE user_preferences SET location = ? WHERE user_id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $newLocation, $userId);
            $stmt->execute();
        }

        if (!empty($newTemperatureUnit)) {
            $sql = "UPDATE user_preferences SET temperature_unit = ? WHERE user_id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $newTemperatureUnit, $userId);
            $stmt->execute();
        }

        // Set the success message
        $successMessage = "Profile updated successfully!";
    }

    $userId = $_SESSION["id"];

    $sql = "SELECT username, email, location, temperature_unit FROM users 
            LEFT JOIN user_preferences ON users.id = user_preferences.user_id 
            WHERE users.id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // we have fetched the user and their assosciated preferences.
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

        /* Style for container */
        .top-right-links {
            position: absolute;
            top: 10px;
            right: 10px;
        }

        /* Style for individual links */
        .top-right-links a {
            margin-left: 10px;
            /* Adding some spacing between links */
            text-decoration: none;
        }

        /* Style for success message */
        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: center;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function checkEmail() {
            var newEmail = $("#email").val();
            var currentUserEmail = "<?= htmlspecialchars($user['email']) ?>";

            if (newEmail !== currentUserEmail) {
                $("#loaderIcon").show();
                jQuery.ajax({
                    url: "check-availability.php",
                    data: 'email=' + newEmail,
                    type: "POST",
                    success: function(data) {
                        $("#check-email").html(data);
                        $("#loaderIcon").hide();
                    },
                    error: function() {}
                });
            }
        }

        function checkUsername() {
            var newUser = $("#username").val();
            var currentUsername = "<?= htmlspecialchars($user['username']) ?>";

            if (newUser !== currentUsername) {
                $("#loaderIcon").show();
                jQuery.ajax({
                    url: "check-availability.php",
                    data: 'username=' + newUser,
                    type: "POST",
                    success: function(data) {
                        $("#check-username").html(data);
                        $("#loaderIcon").hide();
                    },
                    error: function() {}
                });
            }
        }
    </script>
</head>

<body>
    <div id="error"></div>
    <h1>Edit Profile</h1>

    <?php if (!empty($successMessage)) { ?>
        <div class="success-message"><?= $successMessage //displaying the success message 
                                        ?></div>
    <?php } ?>


    <form id="form" method="post" action="edit-profile.php">
        <div>
            <div class="input-control">
                <span id="check-username"></span>
                <label for="username">Username:</label> <? // onblur calls the checkUsername method upon entering 
                                                        ?>
                <input type="text" name="username" id="username" onblur="checkUsername()" value="<?= htmlspecialchars($user['username']) ?>">
            </div>
        </div>

        <div>
            <div class="input-control">
                <span id="check-email"></span>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" onblur="checkEmail()" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
        </div>

        <div>
            <label for="location">Location:</label>
            <select id="location" name="location">
                <option value="Amsterdam" <?= ($user['location'] === 'Amsterdam') ? 'selected' : '' ?>>Amsterdam (GMT+1)</option>
                <option value="Athens" <?= ($user['location'] === 'Athens') ? 'selected' : '' ?>>Athens (GMT+2)</option>
                <!-- Add similar lines for other location options -->
            </select>
        </div>

        <div>
            <label for="temperature_unit">Temperature Unit:</label>
            <select id="temperature_unit" name="temperature_unit">
                <option value="celsius" <?= ($user['temperature_unit'] === 'celsius') ? 'selected' : '' ?>>Celsius</option>
                <option value="fahrenheit" <?= ($user['temperature_unit'] === 'fahrenheit') ? 'selected' : '' ?>>Fahrenheit</option>
            </select>
        </div>


        <button id="submit" value="submit" type="submit">Save Changes</button>
    </form>

    <div class="top-right-links">
        <a href="dashboard.php">Go to Dashboard</a>
        <a href="logout.php">Log Out</a>
        <a href="change_password.php">Change Password</a>
        <a href="delete.php">Delete Account</a>
    </div>
</body>

</html>