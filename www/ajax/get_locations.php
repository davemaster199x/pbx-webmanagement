<?php

	include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_location = new jsonrpc\method( 'client_location.get' );
	$get_location->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_location->param( 'hash',      $_SESSION['user']['hash'] );
	$get_location->param( 'client_id', $_GET['client_id'] );
	$get_location->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_location );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_location->id ]['status'] ) {

		$location_typelist = [];

		foreach ( $result[ $get_location->id ]['data']['client_location'] as $location ) {

			$location_typelist[] = [
				'value'   => $location['location_id'],
				'display' => $location['name']
			];
		}
	}

	header( 'Content-type: application/json' );

	echo json_encode( $location_typelist );

?>
