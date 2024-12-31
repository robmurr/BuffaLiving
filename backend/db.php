<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost:3306"; // e.g., localhost or se-prod.cse.buffalo.edu
$db_name = "tauhidur_db"; // Replace with your database name
$username = "tauhidur"; // Replace with your MySQL username
$password = "50432803"; // Replace with your MySQL password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>
