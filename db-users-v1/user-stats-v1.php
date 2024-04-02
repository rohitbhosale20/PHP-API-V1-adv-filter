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

$userSpecifiedTableName = isset($_GET["dynamic_table"]) ? $_GET["dynamic_table"] : 'mytable';
$tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userSpecifiedTableName);

$checkTableSql = "SHOW TABLES LIKE '$tableName'";
$tableExists = $conn->query($checkTableSql)->num_rows > 0;

if ($tableExists) {
    $timestampQuery = "SELECT timestamp FROM $tableName";
    $result = $conn->query($timestampQuery);

    if ($result->num_rows > 0) {
        $timestampData = array();
        while ($row = $result->fetch_assoc()) {
            $timestampData[] = $row['timestamp'];
        }
        echo json_encode($timestampData);
    } else {
        echo json_encode(array("message" => "No timestamp data found in the table."));
    }
} else {
    echo json_encode(array("message" => "Table does not exist."));
}

$conn->close();
?>
