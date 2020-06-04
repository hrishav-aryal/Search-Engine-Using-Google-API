<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "sara";
$mysqli = new mysqli($servername, $username, $password, $database);
$result = "";
$query = "SELECT * from page order by lastIndexed desc limit 1000";
$result = $mysqli->query($query);
$data = array();
while ($row = $result->fetch_assoc()){
    $temp = array('url'=>$row['url'], 'title'=>$row['title'], 'description'=>$row['description'], 'lastModified'=>$row['lastModified'], 'lastIndexed'=>$row['lastIndexed'], 'timeToIndex'=>$row['timeToIndex']);
    array_push($data, $temp);
}
$mysqli->close();
echo json_encode($data);
?>
