<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !isset( $_GET['did'] )) {
		echo 'Invalid call.';

		exit();
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

// Get routes set up for the client
	$get_routing = new jsonrpc\method( 'routing.routing' );
	$get_routing->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_routing->param( 'did',       $_GET['did'] );
	$get_routing->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_routing );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_routing->id ]['status'] ) {
		$routing = $result[ $get_routing->id ]['data']['routing'];
	}

	echo $routing['status'] . '|' . preg_replace( '/[^0-9]/', '', $routing['dest'] );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
