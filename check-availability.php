<?php 

$mysqli = require __DIR__ . "/db_connection.php"; //retrieving db file

if (!empty($_POST["email"])) { // if the post request contains an email value
  $email = $_POST["email"]; //storing it inside of a variable
  $sql = "SELECT email FROM users WHERE email = ?"; 
  $query = $mysqli->prepare($sql);
  $query->bind_param("s", $email); // the email replaces the placeholder '?'
  $query->execute();
  $query->store_result(); //result is stored
  $count = $query->num_rows; //counting the number of rows

  if ($count > 0) { // if one or more rows are returned, the email is linked with another user account
      echo "<span style='color: red;'>Email Already Exists.</span>";
      echo "<script>$('#submit').prop('disabled',true);</script>"; // the submit button is disabled so the user is unable to proceed
  } else { // if 0 rows returned
      echo "<span style='color: green;'>Email Available.</span>";
      echo "<script>$('#submit').prop('disabled',false);</script>"; // submit enabled
  }
}

// Check if the "username" field is not empty in the submitted POST data
if (!empty($_POST["username"])) {
  // Retrieve the username from the POST data
  $username = $_POST["username"];

  // Prepare and execute a SELECT query to check if the username already exists in the "users" table
  $sql = "SELECT username FROM users WHERE username = ?";
  $query = $mysqli->prepare($sql);
  $query->bind_param("s", $username);
  $query->execute();
  
  // Store the result and get the number of rows
  $query->store_result();
  $count = $query->num_rows;

  // Check if the username already exists
  if ($count > 0) {
      // If the username exists, display a message in red and disable the submit button using jQuery
      echo "<span style='color: red;'>Username Already Exists.</span>";
      echo "<script>$('#submit').prop('disabled',true);</script>";
  } else {
      // If the username is available, display a message in green and enable the submit button using jQuery
      echo "<span style='color: green;'>Username is available.</span>";
      echo "<script>$('#submit').prop('disabled',false);</script>";
  }
}
