<?php
session_start();
$loggedOutPageURL = "https://example.com/logged-out";  // Replace this with the actual URL of your logged-out page

// Database connection
$host = 'localhost';
$db = 'cse442_2024_fall_team_q_db';
$user = 'tauhidur';
$pass = '50432803';
$port = 3306;
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Database connection failed.");
}

// If the user submits the "Yes" form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_COOKIE['auth_token'])) {
        $authToken = $_COOKIE['auth_token'];

        // Reset the auth token in the database
        $stmt = $conn->prepare("UPDATE users SET auth_token = NULL WHERE auth_token = ?");
        $stmt->bind_param("s", $authToken);
        $stmt->execute();

        // Clear the auth_token cookie
        setcookie('auth_token', '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, true);

        // Redirect the user to the logged-out page
        header("Location: https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/react-welcome-page/loggedout.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
</head>
<body>
    <h1>Would you like to log out?</h1>
    <form method="POST">
        <button type="submit">Yes, log me out</button>
    </form>
</body>
</html>