<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "tutorial";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
function getRecordCount($conn) {
    $result = $conn->query("SELECT COUNT(*) as count FROM mytable");
    $row = $result->fetch_assoc();
    return $row['count'];
}
$beforeUploadCount = getRecordCount($conn);
$duplicateCount = 0;
$rowsRemovedCount = 0;
if (isset($_POST['submit'])) {
    $file = $_FILES['csvFile'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $csvFile = $file['tmp_name'];

        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            $cleanedData = array();

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $cleanedRow = array_map('trim', $data);
                $cleanedData[] = $cleanedRow;
            }

            fclose($handle);

            $uniqueData = array_map("unserialize", array_unique(array_map("serialize", $cleanedData)));
            $duplicateCount = count($cleanedData) - count($uniqueData);

      
            $existingProspectLinks = array();
            $existingRecords = $conn->query("SELECT Prospect_Link FROM mytable");
            if ($existingRecords->num_rows > 0) {
                while ($row = $existingRecords->fetch_assoc()) {
                    $existingProspectLinks[] = $row['Prospect_Link'];
                }
            }

      
            foreach ($uniqueData as $index => $row) {
                if (isset($row[17]) && in_array($row[17], $existingProspectLinks)) {
                    unset($uniqueData[$index]);
                    $rowsRemovedCount++;
                }
            }


            foreach ($uniqueData as $row) {
                $sql = "INSERT INTO mytable (First_Name, last_name, email_address, company_name, company_domain,
                        job_title, job_function, job_level, Company_Address, city, State,
                        Zip_Code, country, Telephone_Number, Employee_Size, Industry,
                        Company_Link, Prospect_Link,pid) 
                        VALUES ('" . implode("','", array_map(array($conn, 'real_escape_string'), $row)) . "')";

                if ($conn->query($sql) !== TRUE) {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }

            $afterUploadCount = getRecordCount($conn);

            echo "CSV data imported successfully.<br>";
            echo "Record count before upload: " . $beforeUploadCount . "<br>";
            echo "Record count after upload: " . $afterUploadCount . "<br>";
            echo "Duplicate rows removed: " . $duplicateCount . "<br>";
            echo "Total rows Updated: " . $rowsRemovedCount;
        } else {
            echo "Error: Unable to open CSV file";
        }
    } else {
        echo "Error uploading file: " . $file['error'];
    }
}

$conn->close();
?>
