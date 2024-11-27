<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_location = new jsonrpc\method( 'location.get' );
	$get_location->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_location->param( 'hash',      $_SESSION['user']['hash'] );
	$get_location->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_location );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_location->id ]['status'] ) {

		$locations = $result[ $get_location->id ]['data']['location'];
	} else {

		print_r( $result[ $get_location->id ]['message'] );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<?php if ( isset( $_SESSION['errors'] )) : ?>
	<div class="error">Location Not Saved: <?php print_r( $_SESSION['errors'] ); unset( $_SESSION['errors'] ); ?></div>
<?php endif; ?>
<input type="button" value="Add Location" onclick="location.href='/locations/addedit.php';">
<table>
	<thead>
		<tr>
			<?php if ( client_count() > 1 ) : ?>
				<th>Client</th>
			<?php endif; ?>
			<th>Name</th>
			<th>CallerID</th>
			<th>Address</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $locations as $location ) : ?>
			<tr>
				<?php if ( client_count() > 1 ) : ?>
					<td><?= $location['cname']; ?></td>
				<?php endif; ?>
				<td><a href="/locations/addedit.php?location_id=<?= $location['location_id']; ?>"><?= $location['name']; ?></a></td>
				<td><?= $location['callerid']; ?></td>
				<td><?= $location['address']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
