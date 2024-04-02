<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$servername = "bom1plzcpnl503931.prod.bom1.secureserver.net";
$username = "DataGateway";
$password = "33zBrmCUqoJ7";
$dbname = "Data_Gateway";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT 
            country, 
            COUNT(*) AS count, 
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM mytable), 2) AS percentage
          FROM 
            mytable 
          GROUP BY 
            country 
          ORDER BY 
            count DESC 
          LIMIT 10";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $topCountries = array();
    while ($row = $result->fetch_assoc()) {
        $topCountries[] = array(
            "country" => $row["country"], 
            "count" => (int)$row["count"], 
            "percentage" => (float)$row["percentage"] 
        );
    }
    echo json_encode($topCountries);
} else {
    echo json_encode(array("message" => "No data found."));
}

$conn->close();
?>
