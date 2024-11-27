<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	if ( isset( $_GET['speeddial_id'] )) {
	// Get mailbox that we're editing
		$get_speeddial = new jsonrpc\method( 'speeddial.get' );
		$get_speeddial->param( 'api_token',    $jsonrpc['api_token'] );
		$get_speeddial->param( 'hash',         $_SESSION['user']['hash'] );
		$get_speeddial->param( 'speeddial_id', $_GET['speeddial_id'] );
		$get_speeddial->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_speeddial );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_speeddial->id ]['status'] ) {
			$speeddial = $result[ $get_speeddial->id ]['data']['speeddial'][0];
		}
	}

	$speeddial_form = array(
		array(
			'type'  => 'text',
			'label' => 'Shortcut',
			'name'  => 'shortcut',
			'value' => ( !empty( $speeddial['shortcut'] ) ? $speeddial['shortcut'] : '' )
		),
		array(
			'type'  => 'text',
			'label' => 'Destination',
			'name'  => 'dest',
			'value' => ( !empty( $speeddial['dest'] ) ? $speeddial['dest'] : '' )
		),
		array(
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		),
		array(
			'type'  => 'hidden',
			'name'  => 'speeddial_id',
			'value' => ( !empty( $speeddial['speeddial_id'] ) ? $speeddial['speeddial_id'] : '' )
		),
		array(
			'type'  => 'hidden',
			'name'  => 'client_id',
			'value' => ( isset( $speeddial['client_id'] ) ? $speeddial['client_id'] : $_GET['client_id'] )
		)
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<form action="/speeddial/addedit-save.php" method="post">
	<?= form_display( $speeddial_form, $form_templates['main_form'] ); ?>
	<?php if ( isset( $speeddial['client_id'] )) : ?>
		<input type="button" value="Delete" onclick="if ( confirm( 'Are you sure?' )) { location.href = '/speeddial/delete.php?speeddial_id=<?= $speeddial['speeddial_id']; ?>&client_id=<?= $speeddial['client_id']; ?>'; }">
	<?php endif; ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
