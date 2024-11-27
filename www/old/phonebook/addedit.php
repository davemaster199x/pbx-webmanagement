<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['phonebook_id'] )) {
	// Get mailbox that we're editing
		$get_phonebook = new jsonrpc\method( 'phonebook.get' );
		$get_phonebook->param( 'api_token',    $jsonrpc['api_token'] );
		$get_phonebook->param( 'hash',         $_SESSION['user']['hash'] );
		$get_phonebook->param( 'phonebook_id', $_GET['phonebook_id'] );
		$get_phonebook->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_phonebook );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_phonebook->id ]['status'] ) {
			$phonebook = $result[ $get_phonebook->id ]['data']['phonebook'][0];
		}
	}

	$phonebook_form = array(
		array(
			'type'  => 'text',
			'label' => 'Name',
			'name'  => 'name',
			'value' => ( !empty( $phonebook['name'] ) ? $phonebook['name'] : '' )
		),
		array(
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		),
		array(
			'type'  => 'hidden',
			'name'  => 'phonebook_id',
			'value' => ( !empty( $phonebook['phonebook_id'] ) ? $phonebook['phonebook_id'] : '' )
		),
		array(
			'type'  => 'hidden',
			'name'  => 'client_id',
			'value' => ( isset( $phonebook['client_id'] ) ? $phonebook['client_id'] : $_GET['client_id'] )
		)
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<form action="/phonebook/addedit-save.php" method="post">
	<?= form_display( $phonebook_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
