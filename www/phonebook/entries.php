<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_entry = new jsonrpc\method( 'phonebook.get_entry' );
	$get_entry->param( 'api_token',    $config_client['jsonrpc']['api_token'] );
	$get_entry->param( 'hash',         $_SESSION['user']['hash'] );
	$get_entry->param( 'phonebook_id', $_GET['phonebook_id'] );
	$get_entry->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_entry );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_entry->id ]['status'] ) {

		$entries = $result[ $get_entry->id ]['data']['entries'];
	} else {

		print_r( $result[ $get_entry->id ]['message'] );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<?php if ( isset( $_SESSION['errors'] )) : ?>
	<div class="error">Extension Not Saved: <?php print_r( $_SESSION['errors'] ); unset( $_SESSION['errors'] ); ?></div>
<?php endif; ?>
<input type="button" value="Add Phonebook" onclick="location.href='/phonebook/addedit_entry.php?phonebook_id=<?= $_GET['phonebook_id'] ?>'">
<table>
	<thead>
		<tr>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Phone Number</th>
			<th>Type</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $entries as $entry ) : ?>
			<tr>
                <td><a href="/phonebook/addedit_entry.php?phonebook_id=<?= $entry['phonebook_id']; ?>&entry_id=<?= $entry['entry_id']; ?>"><?= $entry['first_name']; ?></a></td>
                <td><?= $entry['last_name']; ?></td>
                <td><?= $entry['number']; ?></td>
                <td><?= $entry['type']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
