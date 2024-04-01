<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$servername = "bom1plzcpnl503931.prod.bom1.secureserver.net";
$username = "DataGateway";
$password = "33zBrmCUqoJ7";
$database = "Data_Gateway";


$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
ini_set('memory_limit', '1256M');
$sql = "SELECT country FROM mytable GROUP BY country"; // Modified SQL to select distinct first names

$result = $conn->query($sql);

if ($result === false) {
    die("Error executing query: " . $conn->error);
}

$num_rows = $result->num_rows;

if ($num_rows > 0) {
    $rows = array();
    while($row = $result->fetch_assoc()) {
        $rows[] = utf8_encode($row['country']); 
    }
    // Encode the array into JSON format
    $json_data = json_encode($rows);
    echo $json_data;
} else {
    echo "0 results";
}

$conn->close();
?>
