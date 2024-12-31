<?php
session_start();
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection setup
$servername = "localhost";
$username = "hrlin";
$password = "50429551";
$dbname = 'cse442_2024_fall_team_q_db';
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Function to add recipient and create table
function addRecipient($conn, $user_id, $recipient_id, $recipient_name, $newTableName) {
    // Check if the recipient is already in the user's table
    $checkRecipientQuery = "SELECT * FROM $newTableName WHERE recipient = '$recipient_name'";
    $recipientCheckResult = $conn->query($checkRecipientQuery);
    $recipientTableName = $user_id . "_" . $recipient_id;
    if ($recipientCheckResult && $recipientCheckResult->num_rows == 0) {
        // Define the recipient table name
        // Insert recipient into the user's table with Tablename
        $insertRecipientQuery = "INSERT INTO $newTableName (recipient, Tablename, last_message, profile_picture) 
                                 VALUES ('$recipient_name', '$recipientTableName', 'none', '../static/JPG/default-profile-account-unknown-icon-black-silhouette-free-vector.jpg')";

        if ($conn->query($insertRecipientQuery) === TRUE) {
            echo "Recipient '$recipient_name' added successfully to '$newTableName'.";

            // Create the new table for the recipient if it doesn't exist
            $checkRecipientTableQuery = "SHOW TABLES LIKE '$recipientTableName'";
            $recipientTableResult = $conn->query($checkRecipientTableQuery);

            if ($recipientTableResult && $recipientTableResult->num_rows == 0) {
                // Create the new recipient table
                $createRecipientTableQuery = "CREATE TABLE $recipientTableName (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    message TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    user_id VARCHAR(255)
                )";

                if ($conn->query($createRecipientTableQuery) === TRUE) {
                    echo "Table '$recipientTableName' created successfully.";
                    return $recipientTableName;
                } else {
                    echo "Error creating recipient table: " . $conn->error;
                    return $recipientTableName;
                }
            } else {
                echo "Table '$recipientTableName' already exists.";
                return $recipientTableName;
            }
        } else {
            echo "Error adding recipient: " . $conn->error;
            return $recipientTableName;
        }
        return $recipientTableName;
    } else {
        echo "Recipient '$recipient_name' already exists in your chat list.";
        return $recipientTableName;
    }
}

// Check for auth_token in cookies
if (isset($_COOKIE['auth_token'])) {
    $auth_token = mysqli_real_escape_string($conn, $_COOKIE['auth_token']);
    
    // Find the user_id associated with the auth_token
    $userQuery = "SELECT id FROM users WHERE auth_token = '$auth_token'";
    $userResult = $conn->query($userQuery);
    if ($userResult && $userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $user_id = $userRow['id'];
        $newTableName = $user_id . "_table";

        // Case 1: Handle when recipient_id is provided
        if (isset($_POST['recipient_id'])) {
            $recipient_id = mysqli_real_escape_string($conn, $_POST['recipient_id']);

            // Fetch recipient's name
            $recipientQuery = "SELECT name FROM users WHERE id = '$recipient_id'";
            $recipientResult = $conn->query($recipientQuery);
            if ($recipientResult && $recipientResult->num_rows > 0) {
                $recipientRow = $recipientResult->fetch_assoc();
                $recipient_name = $recipientRow['name'];

                // Call function to add recipient and create table
                $recipTableName = addRecipient($conn, $user_id, $recipient_id, $recipient_name, $newTableName);
                header('Location: ChatFunctions.php?Tablename=' . urlencode($recipTableName));
            } else {
                echo "No user found with the provided recipient_id.";
            }
        }
        // Case 2: Handle when email is provided
        elseif (isset($_POST['email'])) {
            $email = mysqli_real_escape_string($conn, $_POST['email']);

            // Fetch recipient's ID and name by email
            $recipientQuery = "SELECT id, name FROM users WHERE email = '$email'";
            $recipientResult = $conn->query($recipientQuery);
            if ($recipientResult && $recipientResult->num_rows > 0) {
                $recipientRow = $recipientResult->fetch_assoc();
                $recipient_id = $recipientRow['id'];
                $recipient_name = $recipientRow['name'];

                // Call function to add recipient and create table
                $recipTableName = addRecipient($conn, $user_id, $recipient_id, $recipient_name, $newTableName);
                header('Location: ChatFunctions.php?Tablename=' . urlencode($recipTableName));
            } else {
                echo "No user found with the provided email.";
            }
        } else {
            echo "No recipient information provided.";
        }
    } else {
        echo "User not found with the provided auth_token.";
    }
} else {
    echo "Auth token not found.";
}


// Close the connection
$conn->close();
exit();
?>