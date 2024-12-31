<?php
header("Access-Control-Allow-Origin: https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/react-welcome-page/build(ProfilePage)/");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

session_start();

$host = 'localhost:3306';
$db = 'cse442_2024_fall_team_q_db';
$user = 'dhuang44';
$pass = '50452905';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Fetch current user data for GET request
    $auth_token = $_COOKIE['auth_token'] ?? null;
    if (!$auth_token) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. No auth token found."]);
        exit;
    }

    $sql = "SELECT name, email, phone, address, photo FROM prof WHERE auth_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $auth_token);
    $stmt->execute();
    $stmt->bind_result($name, $email, $phone, $address, $photo);
    $stmt->fetch();
    $stmt->close();

    if (!$name) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid or expired token"]);
        exit;
    }

    echo json_encode([
        "name" => $name,
        "email" => $email,
        "phone" => $phone,
        "address" => $address,
        "photo" => base64_encode($photo)
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle profile update for POST request
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $photo = null;

    // Handle image upload
    if (!empty($_FILES['photo']['tmp_name'])) {
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid email format."]);
        exit;
    }
    if (!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/", $phone)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid phone number format."]);
        exit;
    }

    $auth_token = $_COOKIE['auth_token'] ?? null;
    if (!$auth_token) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized. No auth token found."]);
        exit;
    }

    $sql = "UPDATE prof SET name = ?, email = ?, phone = ?, address = ?, photo = ? WHERE auth_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssb", $name, $email, $phone, $address, $photo, $auth_token);

    if ($stmt->execute()) {
        echo json_encode(["success" => "Profile updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Error updating profile: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>