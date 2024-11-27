<?php

	// function db_connect() {
	// /*
	//  * DB Connect
	//  * Makes a PDO conection to the given database and returns the
	//  * PDO object.
	//  */
	// 	global $_config;

	// 	$pdo = new PDO(
	// 		"{$_config[ $_SERVER['SERVER_NAME'] ]['db']['type']}:" .
	// 		"host={$_config[ $_SERVER['SERVER_NAME'] ]['db']['host']};" .
	// 		"dbname={$_config[ $_SERVER['SERVER_NAME'] ]['db']['name']}",
	// 		$_config[ $_SERVER['SERVER_NAME'] ]['db']['user'],
	// 		$_config[ $_SERVER['SERVER_NAME'] ]['db']['pass']
	// 	);

	// 	return $pdo;
	// }

	function db_connect() {
	/**
	 * Makes a PDO connection to the given database and returns the PDO object.
	 *
	 * @return PDO
	 */

		require( \env::$paths['methods'] . '/../config.php' );

		$pdo = new PDO(
			"{$config_server['db']['type']}:host={$config_server['db']['host']};dbname={$config_server['db']['name']};charset=UTF8",
			$config_server['db']['user'],
			$config_server['db']['pass']
		);

		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); 

		return $pdo;
	}

?>
