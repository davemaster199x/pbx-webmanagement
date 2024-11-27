<?php namespace file;

	function file_get( $params ) {
	/*
	 * Get
	 * Get a file from the file manager.
	 */

		global $_config;

		$pdo = \db_connect();

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return _build_response( ERROR, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return _build_response( ERROR, "api_token_failure: {$params['api_token']}" );
		}


	// Verify hash
		$user_id = \verify_hash( $params['hash'] );
		if ( !$user_id ) {
			return _build_response( ERROR, 'invalid_hash' );
		}

		if ( empty( $params['file_id'] )) {
			return _build_response( ERROR, 'missing_file_id' );
		}

		$file_query = <<<SQL
  SELECT *
    FROM file
   WHERE file_id = :file_id
SQL;
		$file_result = $pdo->prepare( $file_query );
		$file_result->execute( array(
			':file_id' => $params['file_id']
		));

		if( !$file_result->rowCount() ) {
			return _build_response( ERROR, 'file_not_found' );
		}

		$file_row = $file_result->fetch( \PDO::FETCH_ASSOC );

		if ( empty( $params['no_data'] )) {
			$file_path = $_config[ $_SERVER['SERVER_NAME'] ]['paths']['files'];
			$file_data = base64_encode( file_get_contents( $file_path . '/' . substr( $file_row['hash'], 0, 2 ) . '/' . $file_row['hash'] ));

			$file_row['file_data'] = $file_data;
		}

		return _build_response( COMPLETE, 'file_data', $file_row );
	}

?>
