<?php

	function build_result( $status, $message, $data = [] ) {
	/**
	 * Builds a result array with status, message, and data for the front-end to
	 * interpret.
	 *
	 * @param bool   status  - Either TRUE (indicating success) or FALSE (indicating failure).  
	 *        This refers to an application-level success or failure.
	 * @param string message - A short message to return to the front-end.
	 * @param array  data    - An option array of data to return to the front-end.
	 *
	 * @return bool
	 */

		require( \env::$paths['methods'] . '/../config.php' );

		return [
			'status'  => $status,
			'message' => $message,
			'data'    => $data
		];
	}

?>