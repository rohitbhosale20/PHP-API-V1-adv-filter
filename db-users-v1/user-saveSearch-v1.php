<?php

$servername = "bom1plzcpnl503931.prod.bom1.secureserver.net";
$username = "DataGateway";
$password = "33zBrmCUqoJ7";
$dbname = "Data_Gateway";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user email and convert it into table name
// $userEmail = isset($_GET["user_email"]) ? $_GET["user_email"] : '';
$userSpecifiedTableName = isset($_GET["search_table"]) ? $_GET["search_table"] : 'mytable';
$tableName = 'saved_search__' . str_replace(['@', '.'], ['_', '_'], $userSpecifiedTableName);

// Get search filters and user-provided name
$searchFilters = isset($_GET["filter_data"]) ? $_GET["filter_data"] : '';
$name = isset($_GET["filter_name"]) ? $_GET["filter_name"] : '';

// Prepare SQL statement to insert search filters
$sql = "INSERT INTO $tableName (filter_name, filter_data) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
   
    $stmt->bind_param("ss", $name, $searchFilters);
    $stmt->execute();


    if ($stmt->affected_rows > 0) {
        echo "Search filters saved successfully.";
    } else {
        echo "Error: Unable to save search filters.";
    }

    $stmt->close();
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
