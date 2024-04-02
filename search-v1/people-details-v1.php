<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$servername = "bom1plzcpnl503931.prod.bom1.secureserver.net";
$username = "DataGateway";
$password = "33zBrmCUqoJ7";
$dbname = "Data_Gateway";

ini_set('memory_limit', '1256M');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    $response['error'] = "Connection failed: " . $conn->connect_error;
    echo json_encode($response);
    exit();
}
ini_set('memory_limit', '1256M');
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["Prospect_Link"])) {
    $prospectLink = $conn->real_escape_string($_GET["Prospect_Link"]);

    $sql = "SELECT * FROM mytable WHERE Prospect_Link = '$prospectLink'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['userDetails'] = $row; 
        echo json_encode($response);
    } else {
        $response['error'] = "User not found";
        echo json_encode($response);
    }
}

$conn->close();
?>
