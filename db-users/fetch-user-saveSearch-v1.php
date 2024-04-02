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

// Get user-specified table name from URL parameter or default to 'mytable'
$userSpecifiedTableName = isset($_GET["search_table"]) ? $_GET["search_table"] : 'mytable';

// Create table name from user email
$tableName = 'saved_search__' . str_replace(['@', '.'], ['_', '_'], $userSpecifiedTableName);

// Fetch filter_name provided in the URL
$filterName = isset($_GET["filter_name"]) ? $_GET["filter_name"] : '';

// SQL query to fetch filter_data based on filter_name
$sql = "SELECT filter_data FROM $tableName WHERE filter_name = '$filterName' LIMIT 1";

$result = $conn->query($sql);

if (!$result) {
    // Check for SQL errors
    echo "SQL Error: " . $conn->error;
    exit();
}

if ($result->num_rows > 0) {
    // Fetch the filter_data value
    $row = $result->fetch_assoc();
    $filterData = $row["filter_data"];
    
    // Construct the URL without including "filter_data=" keyword
    $redirectUrl = "search.php?page=1&dynamic_table=$userSpecifiedTableName&$filterData";
    
    // Redirect user to the constructed URL
    header("Location: $redirectUrl");
    exit();
} else {
    echo "No matching record found";
}

$conn->close();
?>
