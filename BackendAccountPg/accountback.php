<?php
header("Access-Control-Allow-Origin: https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/react-welcome-page/");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$host = 'localhost:3306';
$db = 'cse442_2024_fall_team_q_db';
$user = 'dhuang44';
$pass = '50452905';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

if (!isset($_COOKIE['auth_token'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: auth_token is missing."]);
    exit;
}

$auth_token = $_COOKIE['auth_token'];

// Retrieve user ID based on auth_token
$stmt = $conn->prepare("SELECT id, name, email, phone, address, photo FROM users WHERE auth_token = ?");
$stmt->bind_param('s', $auth_token);
$stmt->execute();
$stmt->bind_result($user_id, $current_name, $current_email, $current_phone, $current_address, $current_photo);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: Invalid auth_token."]);
    $conn->close();
    exit;
}

// Fetch current user data for pre-filling
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        "name" => $current_name,
        "email" => $current_email,
        "phone" => $current_phone,
        "address" => $current_address,
        "photo" => $current_photo ? base64_encode($current_photo) : null
    ]);
}

// Update user data with JSON payload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $name = $data['name'] ?? $current_name;
    $email = $data['email'] ?? $current_email;
    $phone = $data['phone'] ?? $current_phone;
    $address = $data['address'] ?? $current_address;
    $photo = isset($data['photo']) ? base64_decode($data['photo']) : $current_photo;

    if ($email !== $current_email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["error" => "Invalid email format."]);
        exit;
    }
    if ($phone !== $current_phone && !preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", $phone)) {
        echo json_encode(["error" => "Invalid phone number format. Use XXX-XXX-XXXX."]);
        exit;
    }

    // Prepare the SQL for updating
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, photo = ? WHERE id = ?");
    if (!$stmt) {
        echo json_encode(["error" => "Statement preparation failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssssbi", $name, $email, $phone, $address, $photo, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Profile updated successfully."]);
    } else {
        echo json_encode(["error" => "Error updating profile: " . $stmt->error]);
    }

    $stmt->close();
}


$conn->close();
?>