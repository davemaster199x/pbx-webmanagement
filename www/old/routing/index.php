<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	if ( isset( $_POST['client_id'] )) {
		$client_id = $_POST['client_id'];
	} else {
		$client_id = $_SESSION['user']['clients'][0]['client_id'];
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_inbound = new jsonrpc\method( 'inbound.get' );
	$get_inbound->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_inbound->param( 'hash',      $_SESSION['user']['hash'] );
	$get_inbound->param( 'client_id', $client_id );
	$get_inbound->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_inbound );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_inbound->id ]['status'] ) {
		$inbound = $result[ $get_inbound->id ]['data']['inbound'];
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<script type="text/javascript">

	$( document ).ready( function() {
		$( 'select[name=client_id]' ).on( 'change', function() {
			$( this ).parent().submit();
		} );
	} );

</script>
<?php if ( count( $_SESSION['user']['clients'] ) > 1 ) : ?>
	<form action="/routing/" method="post">
		<select name="client_id">
			<?php foreach ( $_SESSION['user']['clients'] as $client ) : ?>
				<option value="<?= $client['client_id']; ?>"><?= $client['name']; ?></option>
			<?php endforeach; ?>
		</select>
	</form>
<?php endif; ?>
<table>
	<thead>
		<tr>
			<th>Inbound Number</th>
			<th>Destination</th>
		</tr>
	</thead>
	<tbody>
		 <?php foreach ( $inbound as $did ) : ?>
			<tr>
				<td><a href="/routing/edit.php?inbound_id=<?= $did['inbound_id']; ?>"><?= $did['did']; ?></td>
				<td><?= $did['dest']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
