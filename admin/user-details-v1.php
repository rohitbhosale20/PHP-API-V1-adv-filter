<?php
// Database connection parameters
$servername = "bom1plzcpnl503931.prod.bom1.secureserver.net";
$username = "DataGateway";
$password = "33zBrmCUqoJ7";
$database = "Data_Gateway";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch users from the database
$sql = "SELECT * FROM user";
$result = $conn->query($sql);

// Check if any users were found
if ($result->num_rows > 0) {
    // Initialize an empty array to store user data
    $users = array();

    // Fetch user data row by row
    while ($row = $result->fetch_assoc()) {
        // Add each user to the $users array
        $users[] = $row;
    }

    // Convert the $users array to JSON format
    $json_data = json_encode($users);

    // Output the JSON data
    echo $json_data;
} else {
    echo "No users found.";
}

// Close the database connection
$conn->close();
?>
