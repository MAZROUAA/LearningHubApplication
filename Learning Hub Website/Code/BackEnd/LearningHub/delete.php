<?php
// Start the session
session_start();



// Check if the content ID and type are set
if (!isset($_GET['id']) || !isset($_GET['type'])) {
    // Redirect to homepage or show an error message
    header('Location: HomePage.php');
    exit();
}

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "learninghub";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the SQL statement
$id = $_GET['id'];
$type = $_GET['type'];
$stmt = $conn->prepare("DELETE FROM content WHERE ContentID = ?");
$stmt->bind_param("i", $id);

// Execute the SQL statement
if ($stmt->execute()) {
    // Redirect to homepage or show a success message
    header('Location: HomePage.php');
    if (isset($_GET['path'])) {
        $path = $_GET['path'];
        unlink($path);
    }
    exit();
} else {
    // Redirect to homepage or show an error message
    echo "can't be deleted";
    exit();
}
