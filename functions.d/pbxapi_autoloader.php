<?php

	function pbxapi_autoloader() {
	/**
	 * Initiate autoloader for PBX API.
	 *
	 * @return void
	 */

		spl_autoload_register( function ( $name ) {

			$class_file = __DIR__ . '/../classes.d/' . str_replace( '\\', '/', $name . '.php' );

			if ( file_exists( $class_file )) {

				require( $class_file );
			}
		} );
	}

?>
