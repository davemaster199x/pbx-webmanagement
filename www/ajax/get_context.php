<?php

	include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_context = new jsonrpc\method( 'context.get' );
	$get_context->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_context->param( 'hash',      $_SESSION['user']['hash'] );
	$get_context->param( 'rpoint_id', $_GET['rpoint_id'] );
	$get_context->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_context );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_context->id ]['status'] ) {

		$context_typelist = [];

		$context_typelist = [
			[
				'value'   => '',
				'display' => '[Select]'
			]
		];

		foreach ( $result[ $get_context->id ]['data']['context'] as $rpoint ) {

			$context_typelist[] = [
				'value'   => $rpoint['context_id'],
				'display' => $rpoint['context']
			];
		}
	}

	header( 'Content-type: application/json' );

	echo json_encode( $context_typelist );

?>
