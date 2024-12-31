<?php
session_start(); 
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['csrf_token'])) {
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
        
        // Define the new user_id table name
        $newTableName = $user_id . "_table";
        
        // Check if the user_id table already exists
        $checkTableQuery = "SHOW TABLES LIKE '$newTableName'";
        $tableResult = $conn->query($checkTableQuery);
        
        if ($tableResult && mysqli_num_rows($tableResult) == 0) {
            // Create the new user_id table if it doesn't exist
            $createTableQuery = "CREATE TABLE $newTableName (
                id INT AUTO_INCREMENT PRIMARY KEY,
                recipient CHAR(100),
                Tablename CHAR(100) UNIQUE,
                last_message VARCHAR(255),
                profile_picture VARCHAR(255) DEFAULT '../static/JPG/default-profile-account-unknown-icon-black-silhouette-free-vector.jpg',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            if (!$conn->query($createTableQuery)) {
                die("Error creating table: " . $conn->error);
            }
        }

        // Query to find the chat tables for the current user
        $sql = "SELECT TABLE_NAME 
                FROM information_schema.TABLES 
                WHERE TABLE_SCHEMA = '$dbname' 
                AND (TABLE_NAME LIKE '%_$user_id' OR TABLE_NAME LIKE '$user_id\_%')";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $chatTableName = $row['TABLE_NAME'];
                $other_user_id = $user_id == explode('_', $chatTableName)[0] 
                    ? explode('_', $chatTableName)[1] 
                    : explode('_', $chatTableName)[0];

                // Fetch the recipient's name from the users table
                $recipientQuery = "SELECT name FROM users WHERE id = '$other_user_id'";
                $recipientResult = $conn->query($recipientQuery);
                $recipient = $recipientResult->num_rows > 0 ? $recipientResult->fetch_assoc()['name'] : null;

                // Only insert if the recipient is valid
                if ($recipient !== null) {
                    // Check if the recipient already exists in the user's table
                    $checkInsertQuery = "SELECT * FROM $newTableName WHERE Tablename = '$chatTableName'";
                    $checkInsertResult = $conn->query($checkInsertQuery);

                    // Insert into the user's table only if it does not exist
                    if ($checkInsertResult && $checkInsertResult->num_rows == 0) {
                        $insertQuery = "INSERT INTO $newTableName (recipient, Tablename, profile_picture) 
                                        VALUES ('$recipient', '$chatTableName', '../static/JPG/default-profile-account-unknown-icon-black-silhouette-free-vector.jpg')";
                        
                        if (!$conn->query($insertQuery)) {
                            die("Error inserting data: " . $conn->error);
                        }
                    }
                }
            }
        } else {
            echo "<p>No chat tables found for User ID $user_id.</p>";
        }
    }
}

// Fetch users from the database
$sql = "SELECT recipient, Tablename, last_message, profile_picture FROM  $newTableName";
$result = $conn->query($sql);

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Page</title>
    <!-- Link to external CSS file -->
    <link rel="stylesheet" type="text/css" href="../static/CSS/ChatStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
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
        <!-- Top Blue Header -->
        <div class="chat-box2">
    <h2>Chat Logs</h2>
    <?php
// Check if there are users to display
if ($result->num_rows > 0) {
    // Output each user bubble
    while($row = $result->fetch_assoc()) {
        echo '<a href="ChatFunctions.php?Tablename=' . urlencode($row["Tablename"]) . '">';  // Added link
        echo '<div class="user-chat">';
        echo '<div class="user-avatar">';
        $profilePicture = isset($row["profile_picture"]) && !empty($row["profile_picture"]) 
        ? htmlspecialchars($row["profile_picture"]) 
        : '../static/JPG/HomeIcon.png'; // Default image if none exists
  
        echo '<img src="' . $profilePicture . '" alt="Profile Picture">';
        echo '</div>';
        echo '<div class="user-details">';
        echo '<h3>' . htmlspecialchars($row["recipient"]) . '</h3>';
        echo '</div>';
        echo '</div>';
        echo '</a>';  // Close the link
    }
} else {
    echo "<p>No users found.</p>";
}
?>
</div>
        <!-- Plus Button -->
        <button class="add-button" id="addUserButton">+</button>

        <!-- The Modal -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add User</h2>
                <form id="addUserForm" method="POST" action="addUser.php"> <!-- Replace with your action URL -->
                    <label for="email">User Email:</label>
                    <input type="email" id="email" name="email" required>
                    <button type="submit">Add User</button>
                </form>
            </div>
        </div>

        <script>
            // Get modal element
            var modal = document.getElementById("myModal");
            var btn = document.getElementById("addUserButton");
            var span = document.getElementsByClassName("close")[0];

            // Open the modal when the plus button is clicked
            btn.onclick = function() {
                modal.style.display = "block";
            }

            // Close the modal when the x is clicked
            span.onclick = function() {
                modal.style.display = "none";
            }

            // Close the modal when clicking outside of the modal
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
            </script>
    </div>
</div>
</body>
</html>