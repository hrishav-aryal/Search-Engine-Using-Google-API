<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "sara";
$mysqli = new mysqli($servername, $username, $password, $database);

$query = "SELECT * from search order by searchDate desc";
$result = $mysqli->query($query);
$data = array();
while ($row = $result->fetch_assoc()){
    $temp = array('terms'=>$row['terms'], 'count'=>$row['count'], 'search_date'=>$row['searchDate'], 'timeToSearch' => $row['timeToSearch']);
    array_push($data, $temp);
}
$mysqli->close();
echo json_encode($data);
?>
