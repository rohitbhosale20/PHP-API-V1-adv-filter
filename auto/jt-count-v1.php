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


$query = "SELECT job_title, COUNT(*) AS count FROM mytable GROUP BY job_title ORDER BY count DESC LIMIT 10";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $totalCount = 0;
    $topJobTitles = array();


    while ($row = $result->fetch_assoc()) {
        $totalCount += $row["count"];
    }


    $result->data_seek(0);
    while ($row = $result->fetch_assoc()) {
        $count = $row["count"];
        $percentage = ($count / $totalCount) * 100;
        $percentage = intval($percentage); 

        $topJobTitles[] = array(
            "job_title" => $row["job_title"],
            "count" => $count,
            "percentage" => $percentage
        );
    }

    echo json_encode($topJobTitles);
} else {
    echo json_encode(array("message" => "No data found."));
}

$conn->close();
?>
