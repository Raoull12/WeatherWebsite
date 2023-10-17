<?php
$is_invalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mysqli = require __DIR__ . "/db_connection.php";

    if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $_POST["email"]);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); //fetching the user assosciated with the email

        if ($user) 
        {
            if (password_verify($_POST["password"], $user["password"])) 
            //matching the user's password in the db with the password of the POST request
            {

            session_start();
            $_SESSION["id"] = $user["id"];
            $_SESSION["username"] = $user["username"]; //making the session id = userid
            header("Location: index.php");
            exit;

            } else
            {
                $is_invalid = true; // if pass incorrect display error
            }
        } else
        {
            $is_invalid = true; // if email is incorrect display the error
        }
    } 
else 
{
    $is_invalid = true; // if both incorrect display error
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1>Login</h1>
    <?php if ($is_invalid)://basically if isinvalid = true the below is displayed ?>
        <em>Invalid Email and/or Password</em>
    <?php endif; ?>
    <form method="post">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
        <button>Log in</button>
    </form>
</body>
</html>
