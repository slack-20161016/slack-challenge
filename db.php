<?php ;

$servername = "localhost";
$username = "root";
$password = "";
$db = 'tictactoe';

// Create connection
$conn = new mysqli($servername, $username, $password, $db);
if ($conn->connect_error) {
  error_log("Connection failed: " . $conn->connect_error);
  exit();
} 

$GLOBALS['db_conn'] = $conn;

?>