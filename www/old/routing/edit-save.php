<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_inbound = new jsonrpc\method( 'inbound.save' );
	$save_inbound->param( 'api_token',  $jsonrpc['api_token'] );
	$save_inbound->param( 'hash',       $_SESSION['user']['hash'] );
	$save_inbound->param( 'inbound_id', $_POST['inbound_id'] );
	$save_inbound->param( 'status',     $_POST['status'] );
	$save_inbound->param( 'client_id',  $_POST['client_id'] );
	$save_inbound->param( 'routing_id', $_POST['routing_id'] );
	$save_inbound->param( 'dest',       $_POST['dest'] );
	$save_inbound->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_inbound );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $save_inbound->id ]['response'] != 'complete' ) {
		$_SESSION['errors'] = $result[ $save_inbound->id ]['message'];
	}

	header( 'Location: /routing' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
