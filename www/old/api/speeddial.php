<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !isset( $_GET['shortcut'] )) {
		echo 'Invalid call.';

		exit();
	}

	if ( !isset( $_GET['client_id'] )) {
		echo 'Invalid call.';

		exit();
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

// Get routes set up for the client
	$get_speeddial = new jsonrpc\method( 'speeddial.speeddial' );
	$get_speeddial->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_speeddial->param( 'shortcut',  $_GET['shortcut'] );
	$get_speeddial->param( 'client_id', $_GET['client_id'] );
	$get_speeddial->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_speeddial );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_speeddial->id ]['status'] ) {
		$speeddial = $result[ $get_speeddial->id ]['data']['speeddial'];
	}

	echo preg_replace( '/[^0-9]/', '', $speeddial['dest'] );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
