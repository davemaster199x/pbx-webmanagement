<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_phonebook = new jsonrpc\method( 'phonebook.get' );
	$get_phonebook->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_phonebook->param( 'hash',      $_SESSION['user']['hash'] );
	$get_phonebook->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_phonebook );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_phonebook->id ]['status'] ) {

		$phonebooks = $result[ $get_phonebook->id ]['data']['phonebook'];
	} else {

		print_r( $result[ $get_phonebook->id ]['message'] );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<?php if ( isset( $_SESSION['errors'] )) : ?>
	<div class="error">Extension Not Saved: <?php print_r( $_SESSION['errors'] ); unset( $_SESSION['errors'] ); ?></div>
<?php endif; ?>
<input type="button" value="Add Phonebook" onclick="location.href='/phonebook/addedit.php';">
<table>
	<thead>
		<tr>
			<th>Phonebook</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $phonebooks as $phonebook ) : ?>
			<tr>
                <td><a href="/phonebook/addedit.php?phonebook_id=<?= $phonebook['phonebook_id']; ?>"><?= $phonebook['name']; ?></a></td>
                <td><a href="/phonebook/entries.php?phonebook_id=<?= $phonebook['phonebook_id']; ?>">Entries</a></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
