<?php
// In this file we define general database settings and variables.
define("ROOT", realpath(__DIR__ . "/../"));
$config = parse_ini_file(ROOT."/config.ini");

// Set variables
ini_set("mysql.allow_persistent", "Off");
$hostname_tsr = $config['db_host'];
$database_tsr = $config['db_name'];
$username_tsr = $config['db_user'];
$password_tsr = $config['db_password'];
$tsr = mysql_connect($hostname_tsr, $username_tsr, $password_tsr) or die(mysql_error());

// And connect
$conn = new PDO("mysql:host=$hostname_tsr;dbname=$database_tsr;charset=utf8", $username_tsr, $password_tsr);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>
