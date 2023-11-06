<?php

session_start(); // Start the session to access session variables
session_destroy(); // Destroying the session so the user logs out.

// Redirect the user to the login page
header("Location: login.php");

exit;
