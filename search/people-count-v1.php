<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "bom1plzcpnl503931.prod.bom1.secureserver.net";
$username = "DataGateway";
$password = "33zBrmCUqoJ7";
$database = "Data_Gateway";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


ini_set('memory_limit', '1256M');

function getCountForNetNew($conn) {
    $filters = array(
        'First_Name', 'last_name', 'company_name', 'company_domain', 'job_title',
        'job_function', 'job_level', 'Company_Address', 'city', 'State', 'Zip_Code',
        'country', 'Telephone_Number', 'Employee_Size', 'Industry', 'pid'
    );

    $count_sql = "SELECT COUNT(*) as total FROM mytable WHERE 1";

    foreach ($filters as $filter) {
        if (isset($_GET["include_$filter"])) {
            $value = $conn->real_escape_string($_GET["include_$filter"]);
            $count_sql .= " AND $filter = '$value'";
        }

        if (isset($_GET["exclude_$filter"])) {
            $value = $conn->real_escape_string($_GET["exclude_$filter"]);
            $count_sql .= " AND $filter != '$value'";
        }
    }

    $userEmail = isset($_GET["user_email"]) ? $conn->real_escape_string($_GET["user_email"]) : '';
    $userSpecifiedTableName = isset($_GET["dynamic_table"]) ? $_GET["dynamic_table"] : 'mytable';
    $tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userEmail . $userSpecifiedTableName);

    $count_sql .= " AND NOT EXISTS (
        SELECT 1 
        FROM $tableName AS s
        WHERE s.Prospect_Link = mytable.Prospect_Link
    )";

    $count_result = $conn->query($count_sql);

    if ($count_result) {
        $total_records = $count_result->fetch_assoc()['total'];
        return $total_records;
    } else {
        $response['error'] = "Error fetching total records: " . $conn->error;
        echo json_encode($response);
        exit();
    }
}

$userEmail = isset($_GET["user_email"]) ? $conn->real_escape_string($_GET["user_email"]) : '';
$userSpecifiedTableName = isset($_GET["dynamic_table"]) ? $_GET["dynamic_table"] : 'mytable';
$tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userEmail . $userSpecifiedTableName);
$saved_count_sql = "SELECT COUNT(*) as saved_total FROM $tableName WHERE 1";

$count_sql_all = "SELECT COUNT(*) as total_all FROM mytable";
$count_result_all = $conn->query($count_sql_all);

if ($count_result_all) {
    $total_records_all = $count_result_all->fetch_assoc()['total_all'];
    $response['total_count'] = $total_records_all;
} else {
    $response['error'] = "Error fetching total records for all: " . $conn->error;
}

$totalNetNew = getCountForNetNew($conn);
$response['net_new_count'] = $totalNetNew;

// Add saved_data_count to the response
$saved_count_result = $conn->query($saved_count_sql);
if ($saved_count_result) {
    $saved_total_records = $saved_count_result->fetch_assoc()['saved_total'];
    $response['saved_records_count'] = $saved_total_records;
} else {
    $response['error'] = "Error fetching saved data count: " . $conn->error;
}

echo json_encode($response);

$conn->close();
?>
