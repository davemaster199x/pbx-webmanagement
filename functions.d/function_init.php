<?php

	function function_init( $functions = [] ) {
	/**
	 * Includes functions so that they're defined.
	 *
	 * @param array $functions - An array containing a list of functions to
	 *        include.
	 *
	 * @return void
	 */

        require( \env::$paths['methods'] . '/../config.php' );
		
		foreach ( $functions as $function ) {

			if ( !function_exists( $function ) ) {

				if ( file_exists( $config_server['paths']['functions'] . "/$function.php" )) {

					require_once( $config_server['paths']['functions'] . "/$function.php" );
				} else {

					throw new \Exception( "Function file {$config_server['paths']['functions']}/$function.php not found." );
				}
			}
		}
	}

?>
