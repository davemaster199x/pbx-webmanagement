<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_entry = new jsonrpc\method( 'time_group.save_entry' );
	$save_entry->param( 'api_token',         $jsonrpc['api_token'] );
	$save_entry->param( 'hash',              $_SESSION['user']['hash'] );
	$save_entry->param( 'time_condition_id', ( isset( $_POST['time_condition_id'] ) ? $_POST['time_condition_id'] : '' ));
	$save_entry->param( 'time_group_id',     $_POST['time_group_id'] );
	$save_entry->param( 'day_start',         $_POST['day_start'] );
	$save_entry->param( 'day_end',           $_POST['day_end'] );
	$save_entry->param( 'time_start',        $_POST['time_start'] );
	$save_entry->param( 'time_end',          $_POST['time_end'] );
	$save_entry->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_entry );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $save_entry->id ]['response'] != 'complete' ) {
		$_SESSION['errors'] = $result[ $save_entry->id ]['message'];
	}

	header( "Location: /time_group/entries.php?time_group_id={$_POST['time_group_id']}&client_id={$_POST['client_id']}" );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
