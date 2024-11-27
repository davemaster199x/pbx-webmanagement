<?php namespace extension;

	function status( $params ) {
	/*
	 * Status
	 * Retrieves the status of the given extension(s).
	 */

	require( \env::$paths['methods'] . '/../config.php' );

	\function_init( [ 'build_result', 'check_api_token', 'dbh', 'bind_params', 'verify_hash', 'security_check', 'audit_log', 'get_ext_status' ] );
	
	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return build_result( FALSE, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return build_result( FALSE, "api_token_failure: {$params['api_token']}" );
		}

	// Verify hash
		$user_id = \verify_hash( $params['hash'] );
		if ( !$user_id ) {
			return build_result( FALSE, 'invalid_hash' );
		}

	// Verify extensions were passed
		if ( !isset( $params['extensions'] )) {
			return build_result( FALSE, 'missing_extension' );
		}

		if ( !is_array( $params['extensions'] )) {
			return build_result( FALSE, 'missing_extension' );
		}

		$exts = array();

		foreach ( $params['extensions'] as $extension ) {
			list( $exts[], $context ) = explode( '@', $extension );
		}

//		$status = \get_ext_status( $exts, $context );
		$status = \get_ext_status( $params['extensions'] );

		return build_result( TRUE, 'ext_status', array( 'ext_status' => $status ));
	}

?>
