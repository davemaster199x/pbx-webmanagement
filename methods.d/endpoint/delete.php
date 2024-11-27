<?php namespace endpoint;

	function delete( $params ) {
	/*
	 * Delete
	 * Deletes an endpoint.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'client_access', 'dbh', 'verify_hash', 'audit_log' ] );

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {

			return build_result( FALSE, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {

			return build_result( FALSE, "api_token_failure: {$params['api_token']}" );
		}

        audit_log( 0, __NAMESPACE__ . '\\' . __FUNCTION__, json_encode( $params ) );

	// Verify hash
		$user_id = \verify_hash( $params['hash'] );

		if ( !$user_id ) {

			return build_result( FALSE, 'invalid_hash' );
		}

	// Get client ID access
		$client_ids = implode( ',', client_access( $user_id ));

	// Get name of endpoint we're about to delete
		$endpoint_query = <<<SQL
  SELECT name
    FROM endpoint
   WHERE endpoint_id = :endpoint_id
SQL;
		$endpoint_stmt = dbh()->prepare( $endpoint_query );

		$endpoint_stmt->bindParam( ':endpoint_id', $params['endpoint_id'], \PDO::PARAM_INT );

		$endpoint_stmt->execute();

		$endpoint_row = $endpoint_stmt->fetch( \PDO::FETCH_ASSOC );

	// Delete endpoint
		$pdo = dbh();

		$pdo->beginTransaction();

		$endpoint_query = <<<SQL
  DELETE endpoint
    FROM endpoint
           INNER JOIN client_rpoint
                   ON endpoint.rpoint_id = client_rpoint.rpoint_id
            LEFT JOIN client
                   ON client_rpoint.client_id = client.client_id
   WHERE client.client_id IN ( $client_ids )
     AND endpoint.endpoint_id = :endpoint_id
SQL;

		$endpoint_stmt = $pdo->prepare( $endpoint_query );

		$endpoint_stmt->bindParam( ':endpoint_id', $params['endpoint_id'], \PDO::PARAM_INT );

		$endpoint_stmt->execute();

		if ( $endpoint_stmt->execute() ) {
		// Endpoint saved, make API call

		// Import autoloader
			include_once( __DIR__ . '/../../functions.d/pbxapi_autoloader.php' );

			pbxapi_autoloader();

		// Create the new API object
			$api = new \PBX\API();

		// Set the endpoint
			$api->endpoint(
				$config_server['api']['config']['protocol'],
				$config_server['api']['config']['server'],
				$config_server['api']['config']['port'],
				$config_server['api']['config']['version']
			);

		// Set the credentials
			$api->credentials( $config_server['api']['config']['username'], $config_server['api']['config']['password'] );

		// Create the new Method object
			$method = $api->method( 'DeviceDelete' );

		// Set the parameters
			$method->set( 'mac', $endpoint_row['name'] );

		// Submit the request
			$result = $api->send();

			if ( !$result['status'] ) {
			// API call failed, rollback

				$pdo->rollBack();

				return build_result( FALSE, 'endpoint_not_saved', [ 'error' => 'API error: ' . $result['data'] ] );
			}

		// Get rpoints for client, then loop through, submitting all Endpoints
			$rpoint_query = <<<SQL
  SELECT client_rpoint.rpoint_id, client_rpoint.api_endpoint, client_rpoint.api_user, client_rpoint.api_password
    FROM client_rpoint
   WHERE client_rpoint.client_id = :client_id
SQL;
			$rpoint_stmt = dbh()->prepare( $rpoint_query );

			$rpoint_stmt->bindParam( ':client_id', $client_row['client_id'], \PDO::PARAM_INT );

			$rpoint_stmt->execute();

			$endpoint_query = <<<SQL
  SELECT endpoint.name, endpoint.password, endpoint.context, endpoint.transport, endpoint.callerid, endpoint.mailboxes
    FROM endpoint
   WHERE endpoint.rpoint_id = :rpoint_id
SQL;
			$endpoint_stmt = dbh()->prepare( $endpoint_query );

			while ( $rpoint_row = $rpoint_stmt->fetch( \PDO::FETCH_ASSOC )) {

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

				$result = \pbx_api(
					'SIPEndpoints',
					[
						$config_server['api']['config']['protocol'],
						$rpoint_row['api_endpoint'],
						$config_server['api']['config']['port'],
						$config_server['api']['config']['version']
					],
					$rpoint_row['api_user'],
					$rpoint_row['api_password'],
					$endpoints
				);

				if ( !$result['status'] ) {
				// API call failed, rollback

					$pdo->rollBack();

					return build_result( FALSE, 'endpoint_not_saved', [ 'error' => 'API error: ' . $result['data'] ] );
				}
			}

			$pdo->commit();

			return build_result( TRUE, 'endpoint_saved', [ 'endpoint_id' => $params['endpoint_id'] ] );
		} else {

			return build_result( TRUE, 'endpoint', [ 'endpoint' => $endpoint ] );
		}
	}

?>
