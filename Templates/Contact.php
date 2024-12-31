<?php
session_start();
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "hrlin";  
$password = "50429551";  
$dbname = 'cse442_2024_fall_team_q_db';
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch recipients from the "apartments" table
$query = "SELECT user_id FROM apartments";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact List</title>
    <style>
        .contact-button {
            background-color: darkblue;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Contact List</h1>
    <?php while ($row = $result->fetch_assoc()): ?>
        <form action="addUser.php" method="POST">
            <!-- Dynamically populate recipient_id -->
            <input type="hidden" name="recipient_id" value="<?php echo htmlspecialchars($row['user_id']); ?>">
            <button class="contact-button" type="submit">Add Contact</button>
        </form>
    <?php endwhile; ?>
</body>
</html>