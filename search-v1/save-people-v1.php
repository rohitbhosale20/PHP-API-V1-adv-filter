<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

// Database connection details
$servername = "bom1plzcpnl503931.prod.bom1.secureserver.net";
$username = "DataGateway";
$password = "33zBrmCUqoJ7";
$dbname = "Data_Gateway";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check database connection
if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}

// Function to save data to user account
function saveDataToUserAccount($conn, $email, $selectedRowIds) {
    $email = isset($email) ? $conn->real_escape_string($email) : '';

    // Now $selectedRowIds array contains the unique identifiers extracted from the LinkedIn profile URLs

    $userSpecifiedTableName = isset($_GET["dynamic_table"]) ? $_GET["dynamic_table"] : 'mytable';
    $tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userSpecifiedTableName);

    $columnsToInsert = array(
        'user_id', 'First_Name', 'last_name', 'email_address', 'company_name', 'company_domain',
        'job_title', 'job_function', 'job_level', 'Company_Address', 'city', 'State',
        'Zip_Code', 'country', 'Telephone_Number', 'Employee_Size', 'Industry',
        'Company_Link', 'Prospect_Link', 'pid'
    );

    $columnsToInsertString = implode(', ', $columnsToInsert);

    $columnsToSelect = array(
        'First_Name', 'last_name', 'email_address', 'company_name', 'company_domain',
        'job_title', 'job_function', 'job_level', 'Company_Address', 'city', 'State',
        'Zip_Code', 'country', 'Telephone_Number', 'Employee_Size', 'Industry',
        'Company_Link', 'Prospect_Link', 'pid'
    );

    $columnsToSelectString = implode(', ', $columnsToSelect);

    $email = $conn->real_escape_string($email);
    // Escape and concatenate selectedRowIds
    $selectedRowIdsEscaped = array_map(array($conn, 'real_escape_string'), $selectedRowIds);
    $selectedRowIdsImploded = "'" . implode("', '", $selectedRowIdsEscaped) . "'";

    $insertSql = "INSERT INTO $tableName ($columnsToInsertString) 
                  SELECT '$email', $columnsToSelectString
                  FROM mytable 
                  WHERE Prospect_Link IN ($selectedRowIdsImploded)";

    $response = array();

    if ($conn->query($insertSql) === TRUE) {
        $saved_count_sql = "SELECT COUNT(*) as saved_total FROM $tableName WHERE user_id = '$email'";
        $saved_count_result = $conn->query($saved_count_sql);

        if ($saved_count_result) {
            $saved_total_records = $saved_count_result->fetch_assoc()['saved_total'];

            $response['saved_records_count'] = $saved_total_records;
            $response['message'] = "Data saved successfully";
            http_response_code(200);
        } else {
            $response['error'] = "Error getting saved records count: " . $conn->error;
            http_response_code(500);
        }
    } else {
        // Check if the error is due to a duplicate entry
        if ($conn->errno == 1062) {
            $response['error'] = "Duplicate entry found. Data already saved.";
            http_response_code(409); // Conflict
        } else {
            $response['error'] = "Error saving data: " . $conn->error;
            http_response_code(500);
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$email = isset($_GET["dynamic_table"]) ? $_GET["dynamic_table"] : '';
$selectedRowIds = isset($_GET["selectedRowIds"]) ? $_GET["selectedRowIds"] : array();

saveDataToUserAccount($conn, $email, $selectedRowIds);

$conn->close();
?>
