<?php
$host = 'db.luddy.indiana.edu';
$user = 'i494f25_manadasa';
$pass = 'typos0418hoyle';
$db   = 'i494f25_manadasa';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
