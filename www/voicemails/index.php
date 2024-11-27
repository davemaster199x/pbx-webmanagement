<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_voicemails = new jsonrpc\method( 'voicemail.get' );
	$get_voicemails->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_voicemails->param( 'hash',      $_SESSION['user']['hash'] );
	$get_voicemails->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_voicemails );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_voicemails->id ]['status'] ) {

		$voicemails = $result[ $get_voicemails->id ]['data']['voicemail'];
	} else {

		print_r( $result[ $get_voicemails->id ]['message'] );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<?php if ( isset( $_SESSION['errors'] )) : ?>
	<div class="error">Voicemail Not Saved: <?php print_r( $_SESSION['errors'] ); unset( $_SESSION['errors'] ); ?></div>
<?php endif; ?>
<input type="button" value="Add Voicemail" onclick="location.href='/voicemails/addedit.php';">
<table>
	<thead>
		<tr>
			<?php if ( client_count() > 1 ) : ?>
				<th>Client</th>
			<?php endif; ?>
			<th>Mailbox</th>
			<th>Name</th>
			<th>Email</th>
			<th>Options</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $voicemails as $voicemail ) : ?>
			<tr>
				<?php if ( client_count() > 1 ) : ?>
					<td><?= $voicemail['cname']; ?></td>
				<?php endif; ?>
				<td><a href="/voicemails/addedit.php?voicemail_id=<?= $voicemail['voicemail_id']; ?>"><?= $voicemail['mailbox']; ?></a></td>
				<td><?= $voicemail['vname']; ?></td>
				<td><?= $voicemail['email']; ?></td>
				<td><?= $voicemail['options']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
