<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$host = 'localhost:3306';
$db = 'cse442_2024_fall_team_q_db';
$user = 'dhuang44';
$pass = '50452905';

// Create a connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));
}

// Check if auth_token cookie exists
if (!isset($_COOKIE['auth_token'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Unauthorized: auth_token is missing."]);
    exit;
}

$auth_token = $_COOKIE['auth_token'];
$stmt = $conn->prepare("SELECT id FROM users WHERE auth_token = ?");
$stmt->bind_param('s', $auth_token);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();

if (!$user_id) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Unauthorized: Invalid auth_token."]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();

// Check if the request is a POST request (for delete operations)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle DELETE functionality
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['id'])) {
        $id = intval($data['id']);  // Sanitize the ID
        
        // Prepare and execute SQL statement to delete the apartment entry for the authenticated user
        $stmt = $conn->prepare("DELETE FROM compare WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => "Apartment removed successfully."]);
        } else {
            echo json_encode(["error" => "Error deleting apartment: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "No apartment ID provided for deletion."]);
    }
} else {
    // Default behavior: retrieve apartment data specific to the authenticated user
    $stmt = $conn->prepare("SELECT * FROM compare WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $apartments = array();

    while ($row = $result->fetch_assoc()) {
        $apartments[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($apartments);
    $stmt->close();
}

// Close the connection
$conn->close();
?>
