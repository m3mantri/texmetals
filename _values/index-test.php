<?php
//echo "hello";
$file = '../app/etc/env.php';
$env_data = require $file;

//print_r($env_data);
//echo "      -spacing-----       ";
//print_r($env_data['db']['connection']['default']['host']);

$table_prefix = $env_data['db']['table_prefix'];
//echo $table_prefix;
//echo "      -spacing-----       ";

$mysql_config = array
(
	'hostname'	=>	$env_data['db']['connection']['default']['host'],
	'username'	=>	$env_data['db']['connection']['default']['username'],
	'password'	=>	$env_data['db']['connection']['default']['password'],
	'database'	=>	$env_data['db']['connection']['default']['dbname'],
);

print_r($mysql_config);
?>
