<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

// Get inbound DID that we're editing
	$get_inbound = new jsonrpc\method( 'inbound.get' );
	$get_inbound->param( 'api_token',  $jsonrpc['api_token'] );
	$get_inbound->param( 'hash',       $_SESSION['user']['hash'] );
	$get_inbound->param( 'inbound_id', $_GET['inbound_id'] );
	$get_inbound->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_inbound );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_inbound->id ]['status'] ) {
		$inbound = $result[ $get_inbound->id ]['data']['inbound'][0];
	}

// Get routes set up for the client
	$get_routing = new jsonrpc\method( 'routing.get' );
	$get_routing->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_routing->param( 'hash',      $_SESSION['user']['hash'] );
	$get_routing->param( 'client_id', $inbound['client_id'] );
	$get_routing->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_routing );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_routing->id ]['status'] ) {
		$routing = $result[ $get_routing->id ]['data']['routing'];
	}

	$inbound_form = array(
		array(
			'type'  => 'submit',
			'name'  => 'save',
			'value' => 'Save'
		),
		array(
			'type'  => 'hidden',
			'name'  => 'inbound_id',
			'value' => $inbound['inbound_id']
		),
		array(
			'type'  => 'hidden',
			'name'  => 'client_id',
			'value' => $inbound['client_id']
		)
	);

// Determine status and routing_id
	foreach ( $routing as $route ) {
		if ( $route['routing_id'] == $inbound['routing_id'] ) {
			$status     = $route['status'];
			$routing_id = $route['routing_id'];
		}
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<script type="text/javascript">

	$( document ).ready( function() {
		$( 'input[name=status]' ).on( 'click', function() {
			routing( $( this ).val() );
		} );

		$( 'select[name=routing_id]' ).on( 'change', function() {
			destination( $( this ).val() );
		} );

		routing( $( 'input[name=status]:checked' ).val() );
		destination( $( 'select[name=routing_id]' ).val() );
	} );

	function routing( val ) {
		$( 'span[data-function=forwarding]' ).hide();

		if ( val == 0 ) {
		} else if ( val == 1 ) {
			$( 'span[data-function=forwarding]' ).show();
		}
	}

	function destination( val ) {
		if ( val > 0 ) {
			$( '[data-function=destination]' ).hide();
		} else {
			$( '[data-function=destination]' ).show();
		}
	}

</script>
<form action="/routing/edit-save.php" method="post">
	<h2>Phone Number: <?= $inbound['did']; ?></h2>
	<div class="input radio">
		<span class="label">Routing</span>
		<span class="input">
			<input type="radio" name="status" value="0"<?= ( $status == 0 ? ' checked' : '' ); ?>> Normal Routing<br>
			<input type="radio" name="status" value="1"<?= ( $status == 1 ? ' checked' : '' ); ?>> Forwarding
			<span data-function="forwarding">
				<select name="routing_id">
					<option value="0">-- Add New Destination --</option>
					<?php foreach ( $routing as $route ) : ?>
						<?php if ( $route['status'] == 1 ) : ?>
							<option value="<?= $route['routing_id']; ?>"<?= ( $route['routing_id'] == $inbound['routing_id'] ? ' selected' : '' ); ?>><?= $route['dest']; ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
				<div class="input text" data-function="destination">
					<span class="input">
						<input type="text" name="dest">
					</span>
				</div>
			</span>
		</span>
	</div>
	<?= form_display( $inbound_form, $form_templates['main_form'] ); ?>
</form>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
