<?php

	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://fruityvice.com/api/fruit/all',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
	));

	$response = curl_exec($curl);

	curl_close($curl);

	$response = json_decode($response);
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "fruit";
	$tablename1 = "fruits";
	$tablename2 = "nutritions";
	$conn = mysqli_connect($servername, $username, $password);
	$delete_fruit_db = "DROP DATABASE IF EXISTS `{$dbname}`;";
	$create_fruit_db = "CREATE DATABASE `{$dbname}`;";
	$delete_fruits_table = "DROP TABLE IF EXISTS `{$tablename1}`;";
	$create_fruits_table = "CREATE TABLE `{$tablename1}` (
	  	`id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	  	`genus` VARCHAR(255) NOT NULL,
	  	`name` VARCHAR(255) NOT NULL,
	  	`family` VARCHAR(255) NOT NULL,
	  	`order` VARCHAR(255) NOT NULL,
	  	`nutrition_id` INT(11) NOT NULL,
	  	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	  	`udpated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
	);";
	$delete_nutritions_table = "DROP TABLE IF EXISTS `{$tablename2}`;";
	$create_nutritions_table = "CREATE TABLE `{$tablename2}` (
	  	`id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	  	`carbohydrates` FLOAT(11) NOT NULL,
	  	`protein` FLOAT(11) NOT NULL,
	  	`fat` FLOAT(11) NOT NULL,
	  	`calories` FLOAT(11) NOT NULL,
	  	`sugar` FLOAT(11) NOT NULL,
	  	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	  	`udpated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
	);";

	console_connecting($conn);

	run_sql_and_console_result($conn, $delete_fruit_db, 'Database', $dbname, 'delete');
	run_sql_and_console_result($conn, $create_fruit_db, 'Database', $dbname, 'create');

	mysqli_close($conn);
	$conn = mysqli_connect($servername, $username, $password, $dbname);

	console_connecting($conn);

	run_sql_and_console_result($conn, $delete_fruits_table, 'Table', $tablename1, 'delete');
	run_sql_and_console_result($conn, $create_fruits_table, 'Table', $tablename1, 'create');

	run_sql_and_console_result($conn, $delete_nutritions_table, 'Table', $tablename2, 'delete');
	run_sql_and_console_result($conn, $create_nutritions_table, 'Table', $tablename2, 'create');

	foreach ($response as $fruit) {
		$nutrition = $fruit->nutritions;
		$sql = "INSERT INTO nutritions (carbohydrates, protein, fat, calories, sugar) VALUES (".$nutrition->carbohydrates.", ".$nutrition->protein.", ".$nutrition->fat.", ".$nutrition->calories.", ".$nutrition->sugar.");";
		run_sql_and_console_result($conn, $sql, 'Row', 'nutritions of' . $fruit->name, 'create' );

		$last_id = mysqli_insert_id($conn);

		$sql = "INSERT INTO fruits (`id`, `genus`, `name`, `family`, `order`, `nutrition_id`) VALUES (".$fruit->id.", '".$fruit->genus."', '".$fruit->name."', '".$fruit->family."', '".$fruit->order."', ".$last_id.");";
		run_sql_and_console_result($conn, $sql, 'Row', $fruit->name, 'create' );
	}

	mysqli_close($conn);

	function console_connecting($connect){
		if(! $connect ) {
      		die("Could not connect: " . mysql_error() . "\n");
   		}
   		echo "Connected successfully\n";
	}

	function run_sql_and_console_result($connect, $sql, $type, $name, $action){
		if (mysqli_query($connect, $sql)) {
			echo "{$type} '{$name}' {$action}d successfully\n";
		} else {
			if($type == 'Database' || $type == 'Table'){
				echo "Error {$action} {$type}: " . mysqli_error($connect). "\n";
			}else{
				echo "Error: " . $sql . "\n" . $conn->error . "\n";
			}
		}
	}
