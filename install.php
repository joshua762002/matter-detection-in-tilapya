<?php
// install.php
$host = 'localhost';
$username = 'root';
$password = '';

// Create connection without database
$conn = new mysqli($host, $username, $password);

// Read SQL file
$sql = file_get_contents('organic_tilapia.sql');

// Execute multi query
if ($conn->multi_query($sql)) {
    echo "Database installed successfully!";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>