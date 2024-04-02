<?php

include 'db-conn-v1.php';

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
