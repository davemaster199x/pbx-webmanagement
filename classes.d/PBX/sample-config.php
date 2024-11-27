<?php

// Import config
	include( __DIR__ . '/../../config.php' );

// Import autoloader
	include( __DIR__ . '/../../functions.d/pbxapi_autoloader.php' );

	pbxapi_autoloader();

// Create the new API object
	$api = new PBX\API();

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
	$method = $api->method( 'DeviceCreate' );

// Set the parameters
	$method->set( 'mac',           '1234567890ab' );
	$method->set( 'name',          'test device' );
	$method->set( 'device_type',   'Grandstream GXP2130' );
	$method->set( 'http_user',     'username' );
	$method->set( 'http_password', 'password' );

// Submit the request
	print_r( $api->send() );

?>
