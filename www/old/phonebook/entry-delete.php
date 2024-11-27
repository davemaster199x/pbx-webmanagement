<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$delete_entry = new jsonrpc\method( 'phonebook.delete_entry' );
	$delete_entry->param( 'api_token',    $jsonrpc['api_token'] );
	$delete_entry->param( 'hash',         $_SESSION['user']['hash'] );
	$delete_entry->param( 'entry_id',     $_GET['entry_id'] );
	$delete_entry->param( 'phonebook_id', $_GET['phonebook_id'] );
	$delete_entry->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $delete_entry );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $delete_entry->id ]['response'] != 'complete' ) {
		$_SESSION['errors'] = $result[ $delete_entry->id ]['message'];
	}

	header( "Location: /phonebook/entries.php?phonebook_id={$_GET['phonebook_id']}&client_id={$_GET['client_id']}" );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
