<?php

	function bind_params( &$statement, $params ) {
	/**
	 * Binds parameters to a prepared statement
	 *
	 * @param string statement - Prepared query statement
	 * @param array  params    - The parameters to be bind into the statement
	 *
	 * @return bool
	 */

		if ( is_array( $params ) && count( $params ) > 0 ) {

			foreach( $params as $key => $param ) {

				$statement->bindParam( $key, $param['value'], $param['type'] );
			}
		}
	}
	
?>
