<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_time_group = new jsonrpc\method( 'time_group.save' );
	$save_time_group->param( 'api_token',     $jsonrpc['api_token'] );
	$save_time_group->param( 'hash',          $_SESSION['user']['hash'] );
	$save_time_group->param( 'client_id',     ( isset( $_POST['client_id'] ) ? $_POST['client_id'] : '' ));
	$save_time_group->param( 'time_group_id', $_POST['time_group_id'] );
	$save_time_group->param( 'name',          $_POST['name'] );
	$save_time_group->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_time_group );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $save_time_group->id ]['response'] != 'complete' ) {
		$_SESSION['errors'] = $result[ $save_time_group->id ]['message'];
	}

	header( 'Location: /time_group' );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
