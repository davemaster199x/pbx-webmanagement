<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_location = new jsonrpc\method( 'location.save' );
	$save_location->param( 'api_token',   $config_client['jsonrpc']['api_token'] );
	$save_location->param( 'hash',        $_SESSION['user']['hash'] );
	$save_location->param( 'client_id',   $_POST['client_id'] );
	$save_location->param( 'name',        $_POST['name'] );
	$save_location->param( 'callerid',    $_POST['callerid'] );
	$save_location->param( 'address',     $_POST['address'] );

	if ( !empty( $_POST['location_id'] )) {

		$save_location->param( 'location_id', $_POST['location_id'] );
	}

	$save_location->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_location );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( !$result[ $save_location->id ]['status'] ) {

		$_SESSION['errors'] = $result[ $save_location->id ]['message'];
	}

	header( 'Location: /locations' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
