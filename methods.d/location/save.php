<?php namespace location;

	function save( $params ) {
	/*
	 * Save
	 * Save a location.
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'dbh', 'verify_hash', 'audit_log' ] );

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

		if ( empty( $params['location_id'] )) {
		// Save context

			$pdo = dbh();

			$location_query = <<<SQL
  INSERT INTO client_location
     SET client_id = :client_id,
         name      = :name,
         callerid  = :callerid,
         address   = :address
SQL;
			$location_stmt = $pdo->prepare( $location_query );

			$location_stmt->bindParam( ':client_id',   $params['client_id'],   \PDO::PARAM_INT );
			$location_stmt->bindParam( ':name',        $params['name'],        \PDO::PARAM_STR );
			$location_stmt->bindParam( ':callerid',    $params['callerid'],    \PDO::PARAM_STR );
			$location_stmt->bindParam( ':address',     $params['address'],     \PDO::PARAM_STR );

			$location_stmt->execute();

			$location_id = $pdo->lastInsertId();

			return build_result( TRUE, 'location_saved', [ 'location_id' => $location_id ] );
		} else {
		// Update existing location

			$pdo = dbh();

			$location_query = <<<SQL
  UPDATE client_location
     SET client_id   = :client_id,
         name        = :name,
         callerid    = :callerid,
         address     = :address
   WHERE location_id = :location_id
SQL;
			$location_stmt = $pdo->prepare( $location_query );

			$location_stmt->bindParam( ':client_id',   $params['client_id'],   \PDO::PARAM_INT );
			$location_stmt->bindParam( ':name',        $params['name'],        \PDO::PARAM_STR );
			$location_stmt->bindParam( ':callerid',    $params['callerid'],    \PDO::PARAM_STR );
			$location_stmt->bindParam( ':address',     $params['address'],     \PDO::PARAM_STR );
			$location_stmt->bindParam( ':location_id', $params['location_id'],  \PDO::PARAM_INT );

			$location_stmt->execute();

			return build_result( TRUE, 'location_saved', [ 'location_id' => $params['location_id'] ] );
		}
	}

?>
