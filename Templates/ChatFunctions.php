<?php
session_start(); 
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection setup
$servername = "localhost";
$username = "hrlin";  
$password = "50429551";  
$dbname = 'cse442_2024_fall_team_q_db';
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_COOKIE['auth_token'])) {
    $auth_token = mysqli_real_escape_string($conn, $_COOKIE['auth_token']);
    
    // Find the user_id associated with the auth_token
    $userQuery = "SELECT id FROM users WHERE auth_token = '$auth_token'";
    $userResult = $conn->query($userQuery);
    if ($userResult && $userResult->num_rows > 0) {
        $userRow = $userResult->fetch_assoc();
        $user_id = $userRow['id'];
        $userQuery2 = "SELECT name, photo FROM users WHERE id = '$user_id'";
        $userResult2 = $conn->query($userQuery2);
        if ($userResult2 && $userResult2->num_rows > 0) {
            $user = $userResult2->fetch_assoc();
        }
    }
}

// Handle message submission via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
        exit();
    }

    if (isset($_POST['message']) && !empty(trim($_POST['message'])) && isset($_POST['tablename'])) {
        $message = mysqli_real_escape_string($conn, trim($_POST['message']));
        $tablename = mysqli_real_escape_string($conn, $_POST['tablename']); // Get the table name
        $auth_token = isset($_COOKIE['auth_token']) ? $_COOKIE['auth_token'] : '';
        $user_id = null;
        if (!empty($auth_token)) {
            // Prepare a query to find the user_id based on the auth_token
            $stmt = $conn->prepare("SELECT id FROM users WHERE auth_token = ?");
            $stmt->bind_param("s", $auth_token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // User found, get user_id
                $user_data = $result->fetch_assoc();
                $user_id = $user_data['id'];
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid auth token.']);
                exit(); // Stop processing if the token is invalid
            }
        }
        // Insert message into the specific table
        $query = "INSERT INTO $tablename (message, user_id) VALUES ('$message', '$user_id')";
        if (mysqli_query($conn, $query)) {
            header("Refresh:0");
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . mysqli_error($conn)]);
        }
        exit(); // Stop further processing since this is an AJAX request
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Page</title>
    <link rel="stylesheet" type="text/css" href="../static/CSS/ChatStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Top Blue Header -->
    <div class="header">
        <div className='buffaliving'>
                <p>BuffaLiving</p>
                    </div>
            <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(interactivemap)/"><img src="../static/JPG/Mainicon.png" alt="House Icon">
            <!-- Dropdown Menu -->
            <div class="menu">
                <button class="menu-button">☰</button>
                <div class="menu-content">
                <!-- Profile Greeting -->
                <div class="user-info">
                    <?php if (isset($user['photo']) && !empty($user['photo'])): ?>
                        <img 
                            src="<?= htmlspecialchars($user['photo']) ?>" 
                            alt="Profile" 
                            class="profile-photo" 
                        />
                    <?php else: ?>
                        <div class="profile-photo-placeholder">?</div>
                    <?php endif; ?>
                    <p>Hello, <?= htmlspecialchars($user['name'] ?? 'Guest') ?></p>
                </div>
                <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(ProfilePage)/"><i class="fas fa-user"></i> Account</a>
                <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(Listing)/"><i class="fas fa-home"></i> Properties</a>
                <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(CompPg)/"><i class="fas fa-balance-scale"></i> Compare</a>
                <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/fa24-semesterproject-noproblems-1/Templates/ChatUser.php">
    <i class="fas fa-comments"></i> Chat
</a>
                <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(Saved)/"><i class="fas fa-heart"></i> Saved</a>
                <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/build(createAListing)/"><i class="fas fa-list"></i> List</a>
                <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442q/react-welcome-page/logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="chat-wrapper">
        <div class="chatheader">
                <?php
                    // Extract the 'Tablename' query parameter from the URL
                    $tablename = isset($_GET['Tablename']) ? $_GET['Tablename'] : '';

                    // Check if the tablename is in the expected format
                    if ($tablename) {
                        // Split the tablename by '_'
                        $parts = explode('_', $tablename);
                        
                        // Assuming the first ID is the user ID, and the second is the other ID
                        if (count($parts) == 2) {
                            $other_id = $parts[1]; // Second ID (the other ID)
                            $userQuery3 = "SELECT name FROM users WHERE id = '$other_id'";
                            $userResult3 = $conn->query($userQuery3);
                            if ($userResult3 && $userResult3->num_rows > 0) {
                                $userRow3 = $userResult3->fetch_assoc();
                                $other_name = $userRow3['name'];
                        }
                    }
                }
                ?>
                <p><?= htmlspecialchars($other_name) ?></p>
        </div>
    <!-- Chat Box Area -->
    <div class="chat-box" id="chat-box">
            <!-- Existing chat messages will appear here -->
            <?php
// Make sure the 'tablename' parameter is passed
if (isset($_GET['Tablename'])) {
    $tablename = urldecode($_GET['Tablename']); // The table that stores this user's chat logs

    $tablename = htmlspecialchars($tablename, ENT_QUOTES, 'UTF-8');

    // Verify the table exists
    $tableCheckQuery = "SHOW TABLES LIKE '$tablename'";
    $result = $conn->query($tableCheckQuery);
    $stmt = $conn->prepare($tableCheckQuery);
    $stmt->execute();
    $tableCheckResult = $stmt->get_result();

    if ($tableCheckResult->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Table does not exist.']);
        exit();
    }

    // Retrieve the current user ID based on the auth token
    $auth_token = isset($_COOKIE['auth_token']) ? $_COOKIE['auth_token'] : '';
    $user_id = null;

    if (!empty($auth_token)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE auth_token = ?");
        $stmt->bind_param("s", $auth_token);
        $stmt->execute();
        $user_result = $stmt->get_result();
        
        if ($user_result->num_rows > 0) {
            $user_data = $user_result->fetch_assoc();
            $user_id = $user_data['id'];
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid auth token.']);
            exit();
        }
    }

    // Fetch the chat logs from the validated table
    $query = "SELECT * FROM `$tablename` ORDER BY id ASC"; // Use backticks around $tablename
    $chat_result = $conn->query($query);

    if ($chat_result && $chat_result->num_rows > 0) {
        while ($chat_row = $chat_result->fetch_assoc()) {
            $isCurrentUser = $chat_row['user_id'] == $user_id;
            $bubbleClass = $isCurrentUser ? 'current-user-bubble' : 'other-user-bubble';
            echo '<div class="chat-bubble ' . $bubbleClass . '"><p>' . htmlspecialchars($chat_row["message"]) . '</p></div>';
        }
    } else {
        echo '<p>No chat logs found.</p>';
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Tablename parameter missing.']);
}
            ?>
        </div>

        <!-- Message Input Field -->
        <form id="chat-form" method="POST" onsubmit="sendMessage(event)">
    <div class="message-input">
        <input type="text" id="message" name="message" placeholder="Type your message...">
        <input type="hidden" id="tablename" name="tablename" value="<?php echo $tablename; ?>"> <!-- Send the tablename -->
        <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button type="submit">Send</button>
    </div>
        </form>
    </div>
</div>
<!-- <script src="../static/JS/ChatFunctions.js"></script> -->
</body>
</html>
<script>
function sendMessage(event) {
    event.preventDefault(); // Prevent traditional form submission

    var messageInput = document.getElementById('message');
    var message = messageInput.value;
    var tablename = document.getElementById('tablename').value;
    var csrfToken = document.getElementById('csrf_token').value;

    if (message.trim() === '') {
        return; // Don't send empty messages
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.href, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var response = JSON.parse(xhr.responseText); // Parse the JSON response
                if (response.status === 'success') {
                    // Successfully sent message, update the chat box
                    var chatBox = document.getElementById('chat-box');
                    var bubbleClass = "current-user-bubble";
                    chatBox.innerHTML += '<div class="' + bubbleClass + '"><p>' + message + '</p></div>';
                    messageInput.value = ''; // Clear input field
                    chatBox.scrollTop = 0; // Scroll to bottom
                } else {
                    console.error('Error:', response.message); // Handle errors
                }
            } catch (e) {
                console.error("Error parsing JSON:", e);
            }
        } else {
            console.error('Error sending message:', xhr.status);
        }
    };

    xhr.send('message=' + encodeURIComponent(message) + '&tablename=' + encodeURIComponent(tablename) + '&csrf_token=' + encodeURIComponent(csrfToken));
}
function updateChatBubbles() {
            document.querySelectorAll('.chat-bubble').forEach(function(bubble) {
                var isCurrentUser = /* logic to determine if this message is from the current user */;
                var newClass = isCurrentUser ? 'current-user-bubble' : 'other-user-bubble';
                bubble.classList.add(newClass); // Add the new class
            });
        }

window.onload = function() {
    var chatBox = document.getElementById('chat-box');
    chatBox.scrollTop = chatBox.scrollHeight; // Set scroll position to the bottom
    updateChatBubbles();
};
</script>