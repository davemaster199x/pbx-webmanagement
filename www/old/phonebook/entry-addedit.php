<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['phonebook_id'] ) && isset( $_GET['entry_id'] )) {
	// Get mailbox that we're editing
		$get_entries = new jsonrpc\method( 'phonebook.get_entries' );
		$get_entries->param( 'api_token',    $jsonrpc['api_token'] );
		$get_entries->param( 'hash',         $_SESSION['user']['hash'] );
		$get_entries->param( 'phonebook_id', $_GET['phonebook_id'] );
		$get_entries->param( 'entry_id',     $_GET['entry_id'] );
		$get_entries->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_entries );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_entries->id ]['status'] ) {
			$entry = $result[ $get_entries->id ]['data']['entry'][0];
		}
	}

	$entry_form = array(
		array(
			'type'  => 'text',
			'label' => 'First Name',
			'name'  => 'first_name',
			'value' => ( !empty( $entry['first_name'] ) ? $entry['first_name'] : '' )
		),
		array(
			'type'  => 'text',
			'label' => 'Last Name',
			'name'  => 'last_name',
			'value' => ( !empty( $entry['last_name'] ) ? $entry['last_name'] : '' )
		),
		array(
			'type'  => 'text',
			'label' => 'Phone Number',
			'name'  => 'number',
			'value' => ( !empty( $entry['number'] ) ? $entry['number'] : '' )
		),
		array(
			'type'     => 'select',
			'label'    => 'Type',
			'name'     => 'type',
			'selected' => ( !empty( $entry['type'] ) ? $entry['type'] : '' ),
			'options'  => array(
				array(
					'display' => 'Work',
					'value'   => 'Work'
				),
				array(
					'display' => 'Home',
					'value'   => 'Home'
				),
				array(
					'display' => 'Mobile',
					'value'   => 'Mobile'
				)
			)
		),
		array(
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		),
		array(
			'type'  => 'hidden',
			'name'  => 'entry_id',
			'value' => ( !empty( $entry['entry_id'] ) ? $entry['entry_id'] : '' )
		),
		array(
			'type'  => 'hidden',
			'name'  => 'phonebook_id',
			'value' => ( isset( $entry['phonebook_id'] ) ? $entry['phonebook_id'] : $_GET['phonebook_id'] )
		),
		array(
			'type'  => 'hidden',
			'name'  => 'client_id',
			'value' => ( isset( $_GET['client_id'] ) ? $_GET['client_id'] : '' )
		)
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<form action="/phonebook/entry-addedit-save.php" method="post">
	<?= form_display( $entry_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
