<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$delete_endpoint = new jsonrpc\method( 'endpoint.delete' );
	$delete_endpoint->param( 'api_token',   $config_client['jsonrpc']['api_token'] );
	$delete_endpoint->param( 'hash',        $_SESSION['user']['hash'] );
	$delete_endpoint->param( 'endpoint_id', $_GET['endpoint_id'] );

	$delete_endpoint->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $delete_endpoint );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( !$result[ $delete_endpoint->id ]['status'] ) {

		$_SESSION['errors'] = $result[ $delete_endpoint->id ]['message'] . ' - ' . implode( ', ', $result[ $delete_endpoint->id ]['data'] );
	}

	header( 'Location: /endpoints' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
