<?php
session_start();
$mysqli = require __DIR__ . "/db_connection.php"; // Retrieving db file

if (!empty($_POST["email"])) {
    $email = $_POST["email"];
    $userId = isset($_SESSION["id"]) ? $_SESSION["id"] : null;

    // Check if the email already exists for other users (excluding the current user)
    $sql = "SELECT COUNT(*) as count FROM users WHERE email = ? AND id != ?";
    $query = $mysqli->prepare($sql);
    $query->bind_param("si", $email, $userId);
    $query->execute();
    $query->bind_result($count);
    $query->fetch();

    if ($count > 0) {
        echo "<span style='color: red;'>Email Already Exists.</span>";
        echo "<script>$('#submit').prop('disabled', true);</script>"; //disabling the button if an invalid email is inputted
    } else {
        echo "<span style='color: green;'>Email Available.</span>"; 
        echo "<script>$('#submit').prop('disabled', false);</script>"; //enabling the button if an invalid username is inputted
    }
}

if (!empty($_POST["username"])) {
  $username = $_POST["username"];
  $userId = isset($_SESSION["id"]) ? $_SESSION["id"] : null;

  // Check if the username already exists for other users (excluding the current user)
  $sql = "SELECT COUNT(*) as count FROM users WHERE username = ? AND id != ?";
  $query = $mysqli->prepare($sql);
  $query->bind_param("si", $username, $userId);
  $query->execute();
  $query->bind_result($count);
  $query->fetch();

  if ($count > 0) {
      echo "<span style='color: red;'>Username Already Exists.</span>"; //disabling the button if an invalid username is inputted
      echo "<script>$('#submit').prop('disabled', true);</script>";
  } else {
      echo "<span style='color: green;'>Username is available.</span>";
      echo "<script>$('#submit').prop('disabled', false);</script>"; //enabling the button if an invalid username is inputted
  }
}
?>
