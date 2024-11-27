<?php

// Include config
	include_once( __DIR__ . '/../config.php' );

// Include API function
	include_once( __DIR__ . '/../functions.d/pbx_api.php' );
	include_once( __DIR__ . '/../functions.d/dbh.php' );

// Set client
	$client_id = 2;

// Setup environment
	class env {
	/**
	 * Store values that can be easily accessed by user methods.
	 */

		public static $paths;
		public static $server;

	}

	\env::$paths['methods'] = __DIR__ . '/../methods.d/';

// Get all client endpoint details
	$endpoint_query = <<<SQL
  SELECT endpoint.endpoint_id, endpoint.name, endpoint.label, endpoint.name, endpoint.password,
         device_type.name AS device_type,
         client.http_username, client.http_password
    FROM endpoint
           INNER JOIN device_type
                   ON endpoint.device_type_id = device_type.type_id
           INNER JOIN client_rpoint
                   ON endpoint.rpoint_id = client_rpoint.rpoint_id
           INNER JOIN client
                   ON client_rpoint.client_id = client.client_id
   WHERE client.client_id = :client_id
SQL;
	$endpoint_stmt = dbh()->prepare( $endpoint_query );

	$endpoint_stmt->bindParam( ':client_id', $client_id, PDO::PARAM_INT );

	$endpoint_stmt->execute();

	$value_query = <<<SQL
  SELECT name, value
    FROM endpoint_value
   WHERE endpoint_id = :endpoint_id
SQL;
	$value_stmt = dbh()->prepare( $value_query );

	while ( $endpoint_row = $endpoint_stmt->fetch( PDO::FETCH_ASSOC )) {

		$value_stmt->bindParam( ':endpoint_id', $endpoint_row['endpoint_id'], \PDO::PARAM_INT );

		$value_stmt->execute();

		$values = [
			'P270' => $endpoint_row['label'],
			'P3'   => $endpoint_row['label'],
			'P35'  => $endpoint_row['name'],
			'P34'  => $endpoint_row['password']
		];

		while ( $value_row = $value_stmt->fetch( \PDO::FETCH_ASSOC )) {

			$values[ $value_row['name'] ] = $value_row['value'];
		}

		echo "Submitting device {$endpoint_row['name']} to server using DeviceCreate...\n";

		$result = \pbx_api(
			'DeviceCreate',
			[
				'protocol' => $config_server['api']['config']['protocol'],
				'server'   => $config_server['api']['config']['server'],
				'port'     => $config_server['api']['config']['port'],
				'version'  => $config_server['api']['config']['version']
			],
			$config_server['api']['config']['username'],
			$config_server['api']['config']['password'],
			[
				'mac'           => $endpoint_row['name'],
				'name'          => $endpoint_row['label'],
				'device_type'   => $endpoint_row['device_type'],
				'http_username' => $endpoint_row['http_username'],
				'http_password' => $endpoint_row['http_password'],
				'values'        => $values
			]
		);

		echo "  Result: {$result['data']}\n";

		if ( $result['data'] == "MAC '{$endpoint_row['name']}' is not unique." ) {
		// The device already exists, let's update it

			echo "  Submitting device {$endpoint_row['name']} to server using DeviceUpdate...\n";

			$result = \pbx_api(
				'DeviceUpdate',
				[
					'protocol' => $config_server['api']['config']['protocol'],
					'server'   => $config_server['api']['config']['server'],
					'port'     => $config_server['api']['config']['port'],
					'version'  => $config_server['api']['config']['version']
				],
				$config_server['api']['config']['username'],
				$config_server['api']['config']['password'],
				[
					'mac'           => $endpoint_row['name'],
					'name'          => $endpoint_row['label'],
					'device_type'   => $endpoint_row['device_type'],
					'http_username' => $endpoint_row['http_username'],
					'http_password' => $endpoint_row['http_password'],
					'values'        => $values
				]
			);

			echo "    Result: {$result['data']}\n";
		}
	}

?>
