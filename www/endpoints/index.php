<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_endpoints = new jsonrpc\method( 'endpoint.get' );
	$get_endpoints->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_endpoints->param( 'hash',      $_SESSION['user']['hash'] );
	$get_endpoints->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_endpoints );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_endpoints->id ]['status'] ) {

		$endpoints = $result[ $get_endpoints->id ]['data']['endpoint'];
	} else {

		print_r( $result[ $get_endpoints->id ]['message'] );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<?php if ( isset( $_SESSION['errors'] )) : ?>
	<div class="error">Endpoint Not Saved: <?php print_r( $_SESSION['errors'] ); unset( $_SESSION['errors'] ); ?></div>
<?php endif; ?>
<input type="button" value="Add Endpoint" onclick="location.href='/endpoints/addedit.php';">
<table>
	<thead>
		<tr>
			<?php if ( client_count() > 1 ) : ?>
				<th>Client</th>
			<?php endif; ?>
			<th>Label</th>
			<th>Name</th>
			<th>Location</th>
			<th>RPoint</th>
			<th>Device Type</th>
			<th>Context</th>
			<th>Transport</th>
			<th>Mailboxes</th>
			<th>Functions</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $endpoints as $endpoint ) : ?>
			<tr>
				<?php if ( client_count() > 1 ) : ?>
					<td><?= $endpoint['client_name']; ?></td>
				<?php endif; ?>
				<td><a href="/endpoints/addedit.php?endpoint_id=<?= $endpoint['endpoint_id']; ?>"><?= ( !empty( $endpoint['label'] ) ? $endpoint['label'] : '<em>(No Label)</em>' ); ?></a></td>
				<td><?= $endpoint['endpoint_name']; ?></td>
				<td><?= $endpoint['client_location']; ?></td>
				<td><?= $endpoint['client_rpoint_name']; ?></td>
				<td><?= $endpoint['device_type_name']; ?></td>
				<td><?= $endpoint['context']; ?></td>
				<td><?= $endpoint['transport']; ?></td>
				<td><?= $endpoint['mailboxes']; ?></td>
				<td><a href="/endpoints/delete.php?endpoint_id=<?= $endpoint['endpoint_id']; ?>" onclick="return confirm( 'Are you sure?' );">Delete</a></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
