<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db-conn-v1.php';
$sql = '';
if (!function_exists('handleSearchFilters')) {
    function handleSearchFilters($conn, &$count_sql, &$sql, $filter, $includeKey, $excludeKey, $valueMappings = array()) {
        $includeValues = isset($_GET[$includeKey]) ? explode(',', $_GET[$includeKey]) : array();
        $excludeValues = isset($_GET[$excludeKey]) ? explode(',', $_GET[$excludeKey]) : array();

        $includeValues = array_filter($includeValues);
        $excludeValues = array_filter($excludeValues);

        if (!empty($includeValues)) {
            $mappedIncludeValues = array_map(function ($value) use ($valueMappings) {
                return isset($valueMappings[$value]) ? $valueMappings[$value] : $value;
            }, $includeValues);

            $includeValues = array_merge($includeValues, $mappedIncludeValues);

            $includeValues = array_map('trim', $includeValues);
            $includeValues = array_map([$conn, 'real_escape_string'], $includeValues);

            $likeCondition = implode("%' OR mytable.$filter LIKE '%", $includeValues);
            $count_sql .= " AND (mytable.$filter LIKE '%$likeCondition%')";
            $sql .= " AND mytable.$filter REGEXP '[[:<:]](" . implode('|', $includeValues) . ")[[:>:]]'";
        }
        if (!empty($excludeValues)) {
            $excludeValues = array_map('trim', $excludeValues);
            $excludeValues = array_map([$conn, 'real_escape_string'], $excludeValues);

            $excludeCondition = implode("', '", $excludeValues);
            $count_sql .= " AND $filter NOT IN ('$excludeCondition')";
            $sql .= " AND mytable.$filter NOT IN ('$excludeCondition')";

            $userEmail = isset($_GET["user_email"]) ? $conn->real_escape_string($_GET["user_email"]) : '';
            $tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userEmail);

            $sql .= " AND NOT EXISTS (
                SELECT 1 
                FROM $tableName AS s
                WHERE s.Prospect_Link = mytable.Prospect_Link
            )";
        }
    }
}


function getCachedQueryResult($conn, $query, $cacheFile) {
    if (file_exists($cacheFile) && filemtime($cacheFile) > time() - 3600) {
       
        return json_decode(file_get_contents($cacheFile), true);
    } else {
     
        $result = $conn->query($query);

        if (!$result) {
            die('Error executing query: ' . $conn->error);
        }

        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        file_put_contents($cacheFile, json_encode($data));
        
          return ['test' => 'cached data'];
    }
}

$cacheFile = 'C:\xampp\htdocs\PHP-API\ ';
$query = 'SELECT * FROM mytable WHERE 1';


$hasNonEmptyFilters = false;

foreach ($_GET as $key => $value) {
    if ((strpos($key, 'include_') === 0 || strpos($key, 'exclude_') === 0) && !empty($value)) {
        $hasNonEmptyFilters = true;
        break;
    }
}

if (!$hasNonEmptyFilters) {
  
    $count_sql = "SELECT COUNT(*) as total FROM mytable WHERE 1";

       $count_sql .= " AND NOT EXISTS (
        SELECT 1 
        FROM saved_data 
        WHERE saved_data.Prospect_Link = mytable.Prospect_Link
    )";

    
    $count_result = $conn->query($count_sql);

    if ($count_result) {
        $total_records = $count_result->fetch_assoc()['total'];


        $selectedColumns1 = array(
            'First_Name', 'last_name', 'email_address','company_name', 'job_title',
            'State', 'country', 'Employee_Size', 'Industry', 'Prospect_Link'
        );

        $total_with_all_sql = "SELECT mytable_for_all.* FROM mytable_for_all WHERE 1";

        $total_with_all_result = $conn->query($total_with_all_sql);

        if ($total_with_all_result) {
            $total_with_all_records = $total_with_all_result->fetch_all(MYSQLI_ASSOC);
          
            $total_with_all_records_count = count($total_with_all_records);

            $response['net_new_count'] = $total_records;

            $response['total_count'] = $total_with_all_records_count;

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($response);
            exit();
        } else {
            $response['error'] = "Error fetching total records: " . $conn->error;
            echo json_encode($response);
            exit();
        }
    } else {
        $response['error'] = "Error fetching counts: " . $conn->error;
        echo json_encode($response);
        exit();
    }
}



if (!function_exists('generateExportQuery')) {
    function generateExportQuery($conn) {
        $sql = "SELECT mytable.* FROM mytable 
                WHERE mytable.Prospect_Link = (
                    SELECT MAX(t.Prospect_Link) 
                    FROM mytable AS t
                    WHERE t.Prospect_Link = mytable.Prospect_Link
                )
                AND NOT EXISTS (
                    SELECT 1 
                    FROM saved_data 
                    WHERE saved_data.Prospect_Link = mytable.Prospect_Link
                )";

        return $sql;
    }
}

if (!function_exists('handleSearchFilters1')) {
 function handleSearchFilters1($conn, &$count_sql, &$sql, $filter, $includeKey, $excludeKey, $valueMappings = array()) {
        $includeValues = isset($_GET[$includeKey]) ? explode(',', $_GET[$includeKey]) : array();
        $excludeValues = isset($_GET[$excludeKey]) ? explode(',', $_GET[$excludeKey]) : array();

        $includeValues = array_filter($includeValues);
        $excludeValues = array_filter($excludeValues);

        if (!empty($includeValues)) {
            $mappedIncludeValues = array_map(function ($value) use ($valueMappings) {
                return isset($valueMappings[$value]) ? $valueMappings[$value] : $value;
            }, $includeValues);

            $includeValues = array_merge($includeValues, $mappedIncludeValues);

            $includeValues = array_map('trim', $includeValues);
            $includeValues = array_map([$conn, 'real_escape_string'], $includeValues);

            $likeCondition = implode("%' OR mytable_for_all.$filter LIKE '%", $includeValues);
            $count_sql .= " AND (mytable_for_all.$filter LIKE '%$likeCondition%')";
            $sql .= " AND mytable_for_all.$filter REGEXP '[[:<:]](" . implode('|', $includeValues) . ")[[:>:]]'";
        }

        if (!empty($excludeValues)) {
            $excludeValues = array_map('trim', $excludeValues);
            $excludeValues = array_map([$conn, 'real_escape_string'], $excludeValues);

            $excludeCondition = implode("', '", $excludeValues);
            $count_sql .= " AND $filter NOT IN ('$excludeCondition')";
            $sql .= " AND mytable_for_all.$filter NOT IN ('$excludeCondition')";

            $userEmail = isset($_GET["user_email"]) ? $conn->real_escape_string($_GET["user_email"]) : '';
            $tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userEmail);

            // $sql .= " AND EXISTS (
            //     SELECT 1 
            //     FROM $tableName AS s
            //     WHERE s.Prospect_Link = mytable_for_all.Prospect_Link
            // )";
        }
    }
}


if (!function_exists('saveDataToUserAccount')) {
    function saveDataToUserAccount($conn, $email, $selectedRowIds) {
        $email = isset($email) ? $conn->real_escape_string($email) : '';
        $selectedRowIds = isset($selectedRowIds) ? implode("', '", array_map([$conn, 'real_escape_string'], $selectedRowIds)) : '';

        $userSpecifiedTableName = isset($_GET["dynamic_table"]) ? $_GET["dynamic_table"] : 'mytable';
        $tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userSpecifiedTableName);
        $checkTableSql = "SHOW TABLES LIKE '$tableName'";
        echo "Dynamic Table: " . $dynamicTable . "<br>";
echo "Selected IDs: " . $selectedIds;

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
        $insertSql = "INSERT INTO $tableName ($columnsToInsertString) 
                      SELECT '$email', $columnsToSelectString
                      FROM mytable 
                      WHERE Prospect_Link IN ('$selectedRowIds')";

        $response = array();

        if ($conn->query($insertSql) === TRUE) {
            $saved_count_sql = "SELECT COUNT(*) as saved_total FROM $tableName WHERE user_id = '$email'";
            $saved_count_result = $conn->query($saved_count_sql);

            if ($saved_count_result) {
                $saved_total_records = $saved_count_result->fetch_assoc()['saved_total'];

                $response['saved_records_count'] = $saved_total_records;
                $response['message'] = "Data saved successfully";
            } else {
                $response['error'] = "Error getting saved records count: " . $conn->error;
            }
        } else {
            $response['error'] = "Error saving data: " . $conn->error;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}

if (!function_exists('exportDataToCSV')) {
    function exportDataToCSV($conn, $sql, $tableName) {
            $result = $conn->query($sql);

            if (!$result) {
                $response['error'] = "Query execution failed during export: " . $conn->error;
                echo json_encode($response);
                exit();
            }

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="exported_data.csv"');
            $output = fopen('php://output', 'w');
            $firstRow = $result->fetch_assoc();

            if ($firstRow) {
                $header = array_keys($firstRow);
                fputcsv($output, $header);
            }

            $result->data_seek(0);

            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }

            fclose($output);
            exit();

    }
}

$email = '';
$response = array();
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $data = json_decode(file_get_contents("php://input"), true);
        $userEmail = $conn->real_escape_string($data["email"]);

        saveDataToUserAccount($conn, $userEmail, $data["dataToSave"]);
    } else {
        $records_per_page = 50;
        $page = isset($_GET['page']) ? $_GET['page'] : 1;

     $filters = array(
                'First_Name', 'last_name', 'email_address', 'company_name', 'company_domain',
            'job_title', 'job_function', 'job_level', 'Company_Address', 'city', 'State',
            'Zip_Code', 'country', 'Telephone_Number', 'Employee_Size', 'Industry',
            'Company_Link', 'Prospect_Link', 'pid'
);

        $filterMappings = array(
            'IT' => 'Information Technology', 'IT',
        );

        $count_sql = "SELECT COUNT(*) as total FROM mytable WHERE 1";

        $filtersApplied = false;

        foreach ($filters as $filter) {
            handleSearchFilters($conn, $count_sql, $sql, $filter, "include_$filter", "exclude_$filter", $filterMappings);

            if (isset($_GET["include_$filter"]) || isset($_GET["exclude_$filter"])) {
                $filtersApplied = true;
            }
        }
      
        $userEmail = isset($_GET["user_email"]) ? $conn->real_escape_string($_GET["user_email"]) : '';
        $otherSearchParams = array_diff_key($_GET, array('user_email' => ''));

        $userSpecifiedTableName = isset($_GET["dynamic_table"]) ? $_GET["dynamic_table"] : 'mytable';
        $tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userSpecifiedTableName);

        if ($filtersApplied && !empty($userSpecifiedTableName)) {
            $sqlCondition = !empty($sql) ? " $sql" : '';

            $selectedColumns = array(
            'First_Name', 'last_name', 'email_address', 'company_name', 'job_title',
            'State', 'country', 'Employee_Size', 'Industry', 'Prospect_Link'
);

     $sql = "SELECT " . implode(', ', array_map(function($col) {
             return "mytable.$col";
          }, $selectedColumns)) . " 
        FROM mytable 
        LEFT JOIN $tableName AS s 
            ON mytable.Prospect_Link = s.Prospect_Link
        WHERE mytable.Prospect_Link = (
            SELECT MAX(t.Prospect_Link) 
            FROM mytable AS t
            WHERE t.Prospect_Link = mytable.Prospect_Link
        )
        AND s.Prospect_Link IS NULL
        $sqlCondition";

        }

        if ($filtersApplied) {
            $filtered_count_sql = $count_sql;

            $filtered_count_sql .= " AND NOT EXISTS (
                SELECT 1 
                FROM $tableName AS s
                WHERE s.Prospect_Link = mytable.Prospect_Link
            )";

            $count_result = $conn->query($filtered_count_sql);
        } else {
            $count_result = $conn->query($count_sql);
        }

        if ($count_result) {
            $total_records = $count_result->fetch_assoc()['total'];

            $userSpecifiedTableName = isset($_GET["dynamic_table"]) ? $_GET["dynamic_table"] : 'mytable';
            $tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userSpecifiedTableName);
            $saved_count_sql = "SELECT COUNT(*) as saved_total FROM $tableName WHERE 1";

            $selectedColumns1 = array(
           'First_Name', 'last_name', 'email_address', 'company_name', 'job_title',
            'State', 'country', 'Employee_Size', 'Industry', 'Prospect_Link'
        );

            $total_with_all_sql = "SELECT " . implode(', ', array_map(function($col) {
                return "mytable_for_all.$col";
            }, $selectedColumns1)) . " FROM mytable_for_all WHERE 1";

              foreach ($filters as $filter) {
    handleSearchFilters1($conn, $total_with_all_sql, $total_with_all_sql, $filter, "include_$filter", "exclude_$filter", $filterMappings);

    if (isset($_GET["include_$filter"]) || isset($_GET["exclude_$filter"])) {
        $filtersApplied = true;
    }
}
                            $total_with_all_result = $conn->query($total_with_all_sql);

            if ($total_with_all_result) {
                $total_with_all_records = $total_with_all_result->fetch_all(MYSQLI_ASSOC);
                $total_with_all_records_count = count($total_with_all_records);

                $response['total_data'] = array_slice($total_with_all_records, ($page - 1) * $records_per_page, $records_per_page);

                $saved_count_result = $conn->query($saved_count_sql);

                if ($saved_count_result) {
                    $saved_total_records = $saved_count_result->fetch_assoc()['saved_total'];

                    $display_messages = array(
                        'net_new_count' => $total_records,
                        // net_new = net_new_count
                        'saved_data_count' => $saved_total_records,
                        // saved_data = user Account saved data
                        'total_count' => $total_with_all_records_count,
                        // total_count = net_new_count + saved_data_count
                    );

                    $response = array_merge($response, $display_messages);
                    $response['net_new_data'] = array();
                    // data which is excluded from saved table 
                    $response['total_data'] = array_slice($total_with_all_records, ($page - 1) * $records_per_page, $records_per_page);
                    // data which is included saved table 
                        $response['pagination_new'] = array(
                            'current_page_new' => $page,
                            'total_pages_new' => ceil($total_records / $records_per_page),
                            'records_per_page_new' => $records_per_page,
                        );
                    // pagination of net_new_data

                    $response['pagination_total'] = array(
                        'current_page_total' => $page,
                        'total_pages_total' => ceil($total_with_all_records_count / $records_per_page),
                        'records_per_page_total' => $records_per_page,
                    );

                    // pagination of total_data 

                    $offset = ($page - 1) * $records_per_page;

                    $sql .= " LIMIT $offset, $records_per_page";

                    $result = $conn->query($sql);

                    if (!$result) {
                        $response['error'] = "Query execution failed: " . $conn->error;
                        echo json_encode($response);
                        exit();
                    } else {
                        $response['net_new_data'] = array();

                        while ($row = $result->fetch_assoc()) {
                            $response['net_new_data'][] = $row;
                        }

                        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
                            $selectedIds = isset($_GET['selectedIds']) ? json_decode($_GET['selectedIds']) : [];

                            if (!empty($selectedIds)) {
                                $selectedIds = array_map([$conn, 'real_escape_string'], $selectedIds);
                                $selected_condition = implode("', '", $selectedIds);

                                $csvSql = generateExportQuery($conn) . " AND Prospect_Link IN ('$selected_condition')";

                                exportDataToCSV($conn, $csvSql, $tableName);
                            }
                        }

                        header('Content-Type: application/json; charset=utf-8');

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
                    }
                } else {
                    throw new Exception("Error fetching saved records count: " . $conn->error);
                }
            } else {
                throw new Exception("Error fetching total records: " . $conn->error);
            }
        }
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    echo json_encode($response);
}



if (!function_exists('getSavedDataForUser')) {
    function getSavedDataForUser($conn, $email) {
        $email = $conn->real_escape_string($email);

        $userSpecifiedTableName = isset($_GET["dynamic_table"]) ? $_GET["dynamic_table"] : 'mytable';
        $tableName = 'saved_data__' . str_replace(['@', '.'], ['_', '_'], $userSpecifiedTableName);
        $checkTableSql = "SHOW TABLES LIKE '$tableName'";
        $result = $conn->query($checkTableSql);

        if ($result->num_rows == 0) {
            return array();
        }

        $selectSql = "SELECT * FROM $tableName WHERE user_id = '$email' ORDER BY timestamp DESC";
        $result = $conn->query($selectSql);

        if (!$result) {
            die('Error executing select query: ' . $conn->error);
        }

        $savedData = array();
        while ($row = $result->fetch_assoc()) {
            $savedData[] = $row;
        }

        return $savedData;
    }
}

$conn->close();
?>





