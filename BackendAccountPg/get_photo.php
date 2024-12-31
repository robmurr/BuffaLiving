<?php
session_start();
// Check if the user is logged in (assuming you store user ID in session)
// Temporary user ID for testing
// $user_id = 1;

$host = 'localhost:3306';
$db = 'cse442_2024_fall_team_q_db';
$user = 'dhuang44';
$pass = '50452905';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$auth_token = $_COOKIE['auth_token'];

$sql = "SELECT photo FROM users WHERE auth_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($photo);
$stmt->fetch();
$stmt->close();

if ($photo) {
    header("Content-Type: image/jpeg"); // Adjust based on your image type
    echo $photo;
} else {
    echo "No profile photo available.";
}

$conn->close();

