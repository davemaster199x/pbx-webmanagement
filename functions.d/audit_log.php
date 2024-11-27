<?php

	function audit_log( $user_id, $methods, $params ) {
	/**
	 * Creates a log entry to log RPC method access.
	 *
	 * @param int    user_id - The ID of the user making the request.
	 * @param string methods - The method name being requested.
	 * @param string params  - A JSON-encoded string of the submitted parameters.
	 *
	 * @return void
	 */

		require( \env::$paths['methods'] . '/../config.php' );

		$file_path = $config_server['paths']['audit_log'];

		$file_name = date('Y-m-d').'.log';

		$file_text = date( 'Y-m-d H:i:s' ) . ' - ' . $user_id . ' ' . implode( '\\', array_unique( explode( '\\', $methods )))  . ' ' . $params . "\n";

		$path = $file_path . '/' . $file_name;

		$content = file_put_contents( $path, $file_text . PHP_EOL, FILE_APPEND );
	}

?>
