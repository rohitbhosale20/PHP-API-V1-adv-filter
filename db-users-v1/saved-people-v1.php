<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "bom1plzcpnl503931.prod.bom1.secureserver.net";
$username = "DataGateway";
$password = "33zBrmCUqoJ7";
$dbname = "Data_Gateway";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userSpecifiedTableName = isset($_GET["dynamic_table"]) ? $_GET["dynamic_table"] : 'mytable';

$tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userSpecifiedTableName);

$pageNumber = isset($_GET["page"]) ? intval($_GET["page"]) : 1;
$rowsPerPage = 50;
$startRow = ($pageNumber - 1) * $rowsPerPage;

$sql = "SELECT First_Name, last_name, company_name, job_title, State, country, Employee_Size, Industry, Prospect_Link 
        FROM $tableName 
        LIMIT $startRow, $rowsPerPage";

$result = $conn->query($sql);

$response = array();

if ($result->num_rows > 0) {
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $totalRecordsSql = "SELECT COUNT(*) as total_records FROM $tableName";
    $totalResult = $conn->query($totalRecordsSql);
    $totalRow = $totalResult->fetch_assoc();
    $totalRecords = $totalRow['total_records'];

    $pagination = array(
        'current_page_saved' => $pageNumber,
        'total_pages_saved' => ceil($totalRecords / $rowsPerPage),
        'records_per_page_saved' => $rowsPerPage,
        'total_records' => $totalRecords,
    );

    $response = array(
        'saved_data' => $data,
        'pagination_saved' => $pagination,
        
    );
} else {
    $response['message'] = 'No results found';
}

if (empty($response)) {
    $response['error'] = "Empty response array";
} else {
    array_walk_recursive($response, function (&$value) {
        $value = preg_replace('/[^\x20-\x7E]/', '', $value);
    });

    echo json_encode($response);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['error'] = "JSON encoding error: " . json_last_error_msg();
        echo json_encode($response);
    }
}

$conn->close();

?>
