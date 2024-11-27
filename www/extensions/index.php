<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_extensions = new jsonrpc\method( 'extension.get' );
	$get_extensions->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_extensions->param( 'hash',      $_SESSION['user']['hash'] );
	$get_extensions->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_extensions );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_extensions->id ]['status'] ) {

		$extensions = $result[ $get_extensions->id ]['data']['extension'];
	} else {

		print_r( $result[ $get_extensions->id ]['message'] );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<?php if ( isset( $_SESSION['errors'] )) : ?>
	<div class="error">Extension Not Saved: <?php print_r( $_SESSION['errors'] ); unset( $_SESSION['errors'] ); ?></div>
<?php endif; ?>
<input type="button" value="Add Extension" onclick="location.href='/extensions/addedit.php';">
<table>
	<thead>
		<tr>
			<?php if ( client_count() > 1 ) : ?>
				<th>Client</th>
			<?php endif; ?>
			<th>Extension</th>
			<th>RPoint</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $extensions as $extension ) : ?>
			<tr>
				<?php if ( client_count() > 1 ) : ?>
					<td><?= $extension['client_name']; ?></td>
				<?php endif; ?>
				<td><a href="/extensions/addedit.php?extension_id=<?= $extension['extension_id']; ?>"><?= $extension['ext']; ?></a></td>
				<td><?= $extension['rpoint_name']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
