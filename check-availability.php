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
      echo "<span style='color:#fff'>Email Already Exists.</span>";
      echo "<script>$('#submit').prop('disabled',true);</script>"; // the submit button is disabled so the user is unable to proceed
  } else { // if 0 rows returned
      echo "<span style='color:#fff'>Email Available.</span>";
      echo "<script>$('#submit').prop('disabled',false);</script>"; // submit enabled
  }
}

if (!empty($_POST["username"])) {
  $username = $_POST["username"];
  $sql = "SELECT username FROM users WHERE username = ?";
  $query = $mysqli->prepare($sql);
  $query->bind_param("s", $username);
  $query->execute();
  $query->store_result();
  $count = $query->num_rows;

  if ($count > 0) {
      echo "<span style='color:#fff'>Username Already Exists.</span>";
      echo "<script>$('#submit').prop('disabled',true);</script>";
  } else {
      echo "<span style='color:#fff'>Username is available.</span>";
      echo "<script>$('#submit').prop('disabled',false);</script>";
  }
}

?>