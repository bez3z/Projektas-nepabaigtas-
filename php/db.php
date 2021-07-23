<?php 

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASSWORD", "");
define("DB_NAME", "forma");

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if($mysqli->connect_error){
    echo "Atsiprašome, nepavyko prisijungti prie duomenų bazės";
    exit();
}

?>