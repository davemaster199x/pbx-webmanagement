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

	$get_phonebook = new jsonrpc\method( 'phonebook.get' );
	$get_phonebook->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_phonebook->param( 'hash',      $_SESSION['user']['hash'] );
	$get_phonebook->param( 'client_id', $client_id );
	$get_phonebook->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_phonebook );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_phonebook->id ]['status'] ) {
		$phonebook = $result[ $get_phonebook->id ]['data']['phonebook'];
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
	<form action="/phonebook/" method="post">
		<select name="client_id">
			<?php foreach ( $_SESSION['user']['clients'] as $client ) : ?>
				<option value="<?= $client['client_id']; ?>"<?= $client_id == $client['client_id'] ? ' selected' : ''; ?>><?= $client['name']; ?></option>
			<?php endforeach; ?>
		</select>
	</form>
<?php endif; ?>
<input type="button" value="Add Phonebook" onclick="location.href='/phonebook/addedit.php?client_id=<?= $client_id; ?>';">
<table>
	<thead>
		<tr>
			<th>Phonebook</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		 <?php foreach ( $phonebook as $shortcut ) : ?>
			<tr>
				<td><a href="/phonebook/addedit.php?phonebook_id=<?= $shortcut['phonebook_id']; ?>"><?= ( !empty( $shortcut['name'] ) ? $shortcut['name'] : '<em>No Name</em>' ); ?></a></td>
				<td><a href="/phonebook/entries.php?phonebook_id=<?= $shortcut['phonebook_id']; ?>&client_id=<?= $client_id; ?>">Entries</a></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
