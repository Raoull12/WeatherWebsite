<?php
    require_once 'vendor/autoload.php';
class login
{
    private $isInvalid;

    public function __construct($isInvalid)
    {
        $this->isInvalid = $isInvalid;
    }

    public function handleRequest()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            include 'db_connection.php';

            if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $sql = "SELECT * FROM users WHERE email = ?";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param("s", $_POST["email"]);
                $stmt->execute();

                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user && password_verify($_POST["password"], $user["password"])) {
                    $this->startSession($user);
                    $this->redirect('dashboard.php');
                } else {
                    $this->isInvalid = true;
                }
            } else {
                $this->isInvalid = true;
            }
        }
    }

    private function startSession($user)
    {
        session_start();
        $location = "SELECT location FROM user_preferences WHERE user_id = $user[id]";
        $temperature_unit = "SELECT temperature_unit FROM user_preferences WHERE user_id = $user[id]";
        
        $_SESSION["id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["location"] = $location;
        $_SESSION["temperature_unit"] = $temperature_unit;

    }

    private function redirect($location)
    {
        header("Location: $location");
        include 'db_connection.php';
        $mysqli->close();
        exit;
    }

    public function renderView()
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
        $twig = new \Twig\Environment($loader);

        echo $twig->render('login.html', [
            'is_invalid' => $this->isInvalid,
            'email' => htmlspecialchars($_POST["email"] ?? "")
        ]);
    }
}

// Usage in your main file (e.g., index.php)
$is_invalid = false;
$loginController = new login($is_invalid);
$loginController->handleRequest();
$loginController->renderView();
?>
