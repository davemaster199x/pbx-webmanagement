<?php

	function pbx_api_endpoint( $client_id, $create, $payload ) {
	/**
	 * A wrapper around the pbx_api function specifically for managing endpoints.
	 *
	 * @param client_id int   - The Client ID that owns the endpoint.
	 * @param create    bool  - TRUE if creating, FALSE if updating.
	 * @param payload   array - The payload to send.
	 *
	 * @return array - The result of the API call.
	 */

	// Import config
		require( \env::$paths['methods'] . '/../config.php' );

	// Import depending pbx_api function
		\function_init( [ 'pbx_api', 'dbh' ] );

	// Import autoloader
		include_once( __DIR__ . '/pbxapi_autoloader.php' );

	// Create result array
		$result = [];

	// Check if we're creating or updating a device
		if ( $create ) {

		// Send DeviceCreate to config server
			$result['DeviceCreate'] = \pbx_api(
				'DeviceCreate',
				[
					'protocol' => $config_server['api']['config']['protocol'],
					'server'   => $config_server['api']['config']['server'],
					'port'     => $config_server['api']['config']['port'],
					'version'  => $config_server['api']['config']['version']
				],
				$config_server['api']['config']['username'],
				$config_server['api']['config']['password'],
				$payload
			);

			if ( !isset( $result['DeviceCreate']['status'] ) || !$result['DeviceCreate']['status'] ) {
			// API call failed

				return json_encode( [
					'status' => FALSE,
					'result' => $result
				] );
			}
		} else {

		// Send DeviceUpdate to config server
			$result['DeviceUpdate'] = \pbx_api(
				'DeviceUpdate',
				[
					'protocol' => $config_server['api']['config']['protocol'],
					'server'   => $config_server['api']['config']['server'],
					'port'     => $config_server['api']['config']['port'],
					'version'  => $config_server['api']['config']['version']
				],
				$config_server['api']['config']['username'],
				$config_server['api']['config']['password'],
				$payload
			);

			if ( !isset( $result['DeviceUpdate']['status'] ) || !$result['DeviceUpdate']['status'] ) {
			// API call failed

				return json_encode( [
					'status' => FALSE,
					'result' => $result
				] );
			}
		}

	// Get rpoint details
		$rpoint_query = <<<SQL
  SELECT client_rpoint.rpoint_id, client_rpoint.api_endpoint, client_rpoint.api_user, client_rpoint.api_password
    FROM client_rpoint
   WHERE client_rpoint.client_id = :client_id
SQL;
		$rpoint_stmt = dbh()->prepare( $rpoint_query );

		$rpoint_stmt->bindParam( ':client_id', $client_id, \PDO::PARAM_INT );

		$rpoint_stmt->execute();

	// Query for retrieving client endpoints
		$endpoint_query = <<<SQL
  SELECT endpoint.name, endpoint.password, endpoint.context, endpoint.transport, endpoint.callerid, endpoint.mailboxes
    FROM endpoint
   WHERE endpoint.rpoint_id = :rpoint_id
SQL;
		$endpoint_stmt = dbh()->prepare( $endpoint_query );

		while ( $rpoint_row = $rpoint_stmt->fetch( \PDO::FETCH_ASSOC )) {

			if ( !empty( $rpoint_row['api_endpoint'] )) {

				$endpoint_stmt->bindParam( ':rpoint_id', $rpoint_row['rpoint_id'], \PDO::PARAM_INT );

				$endpoint_stmt->execute();

				$endpoints = [];

				while ( $endpoint_row = $endpoint_stmt->fetch( \PDO::FETCH_ASSOC )) {

					$endpoints[] = [
						'name'      => $endpoint_row['name'],
						'password'  => $endpoint_row['password'],
						'transport' => $endpoint_row['transport'],
						'context'   => $endpoint_row['context'],
						'callerid'  => $endpoint_row['callerid'],
						'mailboxes' => $endpoint_row['mailboxes']
					];
				}

				$url_parts = parse_url( $rpoint_row['api_endpoint'] );

				if ( isset( $url_parts['port'] )) {

					$port = $url_parts['port'];
				} else {

					$port = $config_server['api']['config']['port'];
				}

				$result['SIPEndpoints'][ $rpoint_row['rpoint_id'] ] = \pbx_api(
					'SIPEndpoints',
					[
						'protocol' => $url_parts['scheme'],
						'server'   => $url_parts['host'],
						'port'     => $port,
						'version'  => $config_server['api']['config']['version']
					],
					$rpoint_row['api_user'],
					$rpoint_row['api_password'],
					$endpoints
				);

				if ( !isset( $result['SIPEndpoints'][ $rpoint_row['rpoint_id'] ]['status'] ) || !$result['SIPEndpoints'][ $rpoint_row['rpoint_id'] ]['status'] ) {
				// API call failed

					return json_encode( [
						'status' => FALSE,
						'result' => $result
					] );
				}
			}
		}

		return json_encode( [
			'status' => TRUE,
			'result' => $result
		] );
	}

?>
