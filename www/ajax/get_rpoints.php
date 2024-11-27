<?php

	include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_rpoint = new jsonrpc\method( 'client_rpoint.get' );
	$get_rpoint->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_rpoint->param( 'hash',      $_SESSION['user']['hash'] );
	$get_rpoint->param( 'client_id', $_GET['client_id'] );
	$get_rpoint->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_rpoint );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_rpoint->id ]['status'] ) {

		$rpoint_typelist = [];

		$rpoint_typelist = [
			[
				'value'   => '',
				'display' => '[Select]'
			]
		];

		foreach ( $result[ $get_rpoint->id ]['data']['client_rpoint'] as $rpoint ) {

			$rpoint_typelist[] = [
				'value'   => $rpoint['rpoint_id'],
				'display' => $rpoint['name']
			];
		}
	}

	header( 'Content-type: application/json' );

	echo json_encode( $rpoint_typelist );

?>
