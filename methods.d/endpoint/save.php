<?php namespace endpoint;

	function save( $params ) {
	/*
	 * Save
	 * Save a endpoint.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'dbh', 'verify_hash', 'audit_log', 'pbx_api_endpoint' ] );

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

	// Get client details from rpoint
		$client_query = <<<SQL
  SELECT client.client_id, client.http_username, client.http_password
    FROM client
           INNER JOIN client_rpoint
                   ON client.client_id = client_rpoint.client_id
   WHERE client_rpoint.rpoint_id = :rpoint_id
SQL;
		$client_stmt = dbh()->prepare( $client_query );

		$client_stmt->bindParam( ':rpoint_id', $params['rpoint_id'], \PDO::PARAM_INT );

		$client_stmt->execute();

		$client_row = $client_stmt->fetch( \PDO::FETCH_ASSOC );

	// Get device type
		$type_query = <<<SQL
  SELECT device_type.name
    FROM device_type
   WHERE device_type.type_id = :device_type_id
SQL;
		$type_stmt = dbh()->prepare( $type_query );

		$type_stmt->bindParam( ':device_type_id', $params['device_type_id'], \PDO::PARAM_INT );

		$type_stmt->execute();

		$type_row = $type_stmt->fetch( \PDO::FETCH_ASSOC );

	// Save endpoint
		if ( empty( $params['endpoint_id'] )) {
		// Add new endpoint

			$pdo = dbh();

			$pdo->beginTransaction();

			$endpoint_query = <<<SQL
  INSERT INTO endpoint
     SET rpoint_id      = :rpoint_id,
         device_type_id = :device_type_id,
		 location_id    = :location_id,
         label          = :label,
         name           = :name,
         password       = :password,
         context        = :context,
         transport      = :transport,
         callerid       = :callerid,
         mailboxes      = :mailboxes
SQL;
			$endpoint_stmt = $pdo->prepare( $endpoint_query );

			$endpoint_stmt->bindParam( ':rpoint_id',      $params['rpoint_id'],      \PDO::PARAM_INT );
			$endpoint_stmt->bindParam( ':device_type_id', $params['device_type_id'], \PDO::PARAM_INT );
			$endpoint_stmt->bindParam( ':location_id',    $params['location_id'],    \PDO::PARAM_INT );
			$endpoint_stmt->bindParam( ':label',          $params['label'],          \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':name',           $params['name'],           \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':password',       $params['password'],       \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':context',        $params['context'],        \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':transport',      $params['transport'],      \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':callerid',       $params['callerid'],       \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':mailboxes',      $params['mailboxes'],      \PDO::PARAM_STR );

			if ( $endpoint_stmt->execute() ) {
			// Endpoint saved, make API call

				$endpoint_id = $pdo->lastInsertId();

				$value_query = <<<SQL
  SELECT name, value
    FROM endpoint_value
   WHERE endpoint_id = :endpoint_id
SQL;
				$value_stmt = $pdo->prepare( $value_query );

				$value_stmt->bindParam( ':endpoint_id', $endpoint_id, \PDO::PARAM_INT );

				$value_stmt->execute();

				$values = [
					'P270' => $params['label'],
					'P3'   => $params['label'],
					'P35'  => $params['name'],
					'P34'  => $params['password']
				];

				while ( $value_row = $value_stmt->fetch( \PDO::FETCH_ASSOC )) {

					$values[ $value_row['name'] ] = $value_row['value'];
				}

				$result = \pbx_api_endpoint( $client_row['client_id'], TRUE,
					[
						'mac'           => $params['name'],
						'name'          => $params['label'],
						'device_type'   => $type_row['name'],
						'http_username' => $client_row['http_username'],
						'http_password' => $client_row['http_password'],
						'values'        => $values
					]
				);

				if ( !json_decode( $result, TRUE )['status'] ) {
				// API call failed, rollback

					$pdo->rollBack();

					return build_result( FALSE, 'endpoint_not_saved', [ 'error' => 'API error: ' . $result ] );
				}

				$pdo->commit();

				return build_result( TRUE, 'endpoint_saved', [ 'endpoint_id' => $pdo->lastInsertId() ] );
			} else {

				return build_result( FALSE, 'endpoint_not_saved', [ 'error' => $endpoint_stmt->errorInfo() ] );
			}
		} else {
		// Update existing endpoint
			$pdo = dbh();

			$pdo->beginTransaction();

		// Get current MAC address (name) so we can send that with the API to update
			$mac_query = <<<SQL
  SELECT name
    FROM endpoint
   WHERE endpoint_id = :endpoint_id
SQL;
			$mac_stmt = $pdo->prepare( $mac_query );

			$mac_stmt->bindParam( ':endpoint_id', $params['endpoint_id'], \PDO::PARAM_INT );

			$mac_stmt->execute();

			$mac_row = $mac_stmt->fetch( \PDO::FETCH_ASSOC );

		// Update the endpoint
			$endpoint_query = <<<SQL
  UPDATE endpoint
     SET rpoint_id      = :rpoint_id,
         device_type_id = :device_type_id,
		 location_id    = :location_id,
         label          = :label,
         name           = :name,
		 password       = :password,
         context        = :context,
         transport      = :transport,
         callerid       = :callerid,
         mailboxes      = :mailboxes
   WHERE endpoint_id    = :endpoint_id
SQL;
			$endpoint_stmt = $pdo->prepare( $endpoint_query );

			$endpoint_stmt->bindParam( ':rpoint_id',      $params['rpoint_id'],      \PDO::PARAM_INT );
			$endpoint_stmt->bindParam( ':device_type_id', $params['device_type_id'], \PDO::PARAM_INT );
			$endpoint_stmt->bindParam( ':location_id',    $params['location_id'],    \PDO::PARAM_INT );
			$endpoint_stmt->bindParam( ':label',          $params['label'],          \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':name',           $params['name'],           \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':password',       $params['password'],       \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':context',        $params['context'],        \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':transport',      $params['transport'],      \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':callerid',       $params['callerid'],       \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':mailboxes',      $params['mailboxes'],      \PDO::PARAM_STR );
			$endpoint_stmt->bindParam( ':endpoint_id',    $params['endpoint_id'],    \PDO::PARAM_INT );

			if ( $endpoint_stmt->execute() ) {
			// Endpoint saved, make API call

				$value_query = <<<SQL
  SELECT name, value
    FROM endpoint_value
   WHERE endpoint_id = :endpoint_id
SQL;
				$value_stmt = $pdo->prepare( $value_query );

				$value_stmt->bindParam( ':endpoint_id', $params['endpoint_id'], \PDO::PARAM_INT );

				$value_stmt->execute();

				$values = [
					'P270' => $params['label'],
					'P3'   => $params['label'],
					'P35'  => $params['name'],
					'P34'  => $params['password']
				];

				while ( $value_row = $value_stmt->fetch( \PDO::FETCH_ASSOC )) {

					$values[ $value_row['name'] ] = $value_row['value'];
				}

				$result = \pbx_api_endpoint( $client_row['client_id'], FALSE,
					[
						'mac'           => $params['name'],
						'orig_mac'      => $mac_row['name'],
						'name'          => $params['label'],
						'device_type'   => $type_row['name'],
						'http_username' => $client_row['http_username'],
						'http_password' => $client_row['http_password'],
						'values'        => $values
					]
				);

				if ( !json_decode( $result, TRUE )['status'] ) {
				// API call failed, rollback

					$pdo->rollBack();

					return build_result( FALSE, 'endpoint_not_saved', [ 'error' => 'API error: ' . $result ] );
				}

				$pdo->commit();

				return build_result( TRUE, 'endpoint_saved', [ 'endpoint_id' => $params['endpoint_id'] ] );
			} else {

				return build_result( FALSE, 'endpoint_not_saved', [ 'error' => $endpoint_stmt->errorInfo() ] );
			}
		}
	}

?>
