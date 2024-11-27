<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_clients = new jsonrpc\method( 'client.get' );
	$get_clients->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_clients->param( 'hash',      $_SESSION['user']['hash'] );
	$get_clients->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_clients );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_clients->id ]['status'] ) {

		$clients = $result[ $get_clients->id ]['data']['clients'];
	} else {

		$clients = [];

		print_r( $result[ $get_clients->id ]['message'] );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<input type="button" value="Add Client" onclick="location.href='/clients/addedit.php';">
<table>
	<thead>
		<tr>
			<th>Client</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $clients as $client ) : ?>
			<tr>
				<td><a href="/clients/addedit.php?client_id=<?= $client['client_id']; ?>"><?= ( !empty( $client['name'] ) ? $client['name'] : '(Unnamed)' ); ?></a></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
