<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$callerid = $_POST['callerid_name'] .' <'.  $_POST['callerid_number'] . '>';

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );
	
	$save_endpoint = new jsonrpc\method( 'endpoint.save' );
	$save_endpoint->param( 'api_token',      $config_client['jsonrpc']['api_token'] );
	$save_endpoint->param( 'hash',           $_SESSION['user']['hash'] );
	$save_endpoint->param( 'rpoint_id',      $_POST['rpoint_id'] );
	$save_endpoint->param( 'location_id',    $_POST['location_id'] );
	$save_endpoint->param( 'device_type_id', $_POST['device_type_id'] );
	$save_endpoint->param( 'label',          $_POST['label'] );
	$save_endpoint->param( 'name',           $_POST['name'] );
	$save_endpoint->param( 'password',       $_POST['password'] );
	$save_endpoint->param( 'context',        $_POST['context'] );
	$save_endpoint->param( 'transport',      $_POST['transport'] );
	$save_endpoint->param( 'callerid',       $callerid );
	$save_endpoint->param( 'mailboxes',      $_POST['mailboxes'] ?? '' );
	$save_endpoint->param( 'context',        $_POST['context'] );

	if ( !empty( $_POST['endpoint_id'] )) {

		$save_endpoint->param( 'endpoint_id', $_POST['endpoint_id'] );
	}

	$save_endpoint->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_endpoint );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( !$result[ $save_endpoint->id ]['status'] ) {

		$_SESSION['errors'] = $result[ $save_endpoint->id ]['message'] . ' - ' . implode( ', ', $result[ $save_endpoint->id ]['data'] );
	}

	header( 'Location: /endpoints' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
