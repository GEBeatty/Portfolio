<?php
$conn = mysqli_connect($host,$user,$pass,$dbname);
if(mysqli_connect_error()){
    die("Error connecting to database");
}
?>