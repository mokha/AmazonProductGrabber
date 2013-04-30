<?php

ignore_user_abort(TRUE); // run script in background 
set_time_limit(0); // run script forever 
ini_set('memory_limit', '512M');
error_reporting(0);


$host = 'localhost';
$user = 'root';
$pass = '';
$database = 'amazon';

$connection = mysql_connect($host, $user, $pass);
mysql_select_db($database, $connection);

?>
