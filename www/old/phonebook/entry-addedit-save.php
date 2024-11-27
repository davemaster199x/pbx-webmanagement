<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$save_entry = new jsonrpc\method( 'phonebook.save_entry' );
	$save_entry->param( 'api_token',    $jsonrpc['api_token'] );
	$save_entry->param( 'hash',         $_SESSION['user']['hash'] );
	$save_entry->param( 'entry_id',     ( isset( $_POST['entry_id'] ) ? $_POST['entry_id'] : '' ));
	$save_entry->param( 'phonebook_id', $_POST['phonebook_id'] );
	$save_entry->param( 'first_name',   $_POST['first_name'] );
	$save_entry->param( 'last_name',    $_POST['last_name'] );
	$save_entry->param( 'number',       $_POST['number'] );
	$save_entry->param( 'type',         $_POST['type'] );
	$save_entry->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $save_entry );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $save_entry->id ]['response'] != 'complete' ) {
		$_SESSION['errors'] = $result[ $save_entry->id ]['message'];
	}

	header( "Location: /phonebook/entries.php?phonebook_id={$_POST['phonebook_id']}&client_id={$_POST['client_id']}" );

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
