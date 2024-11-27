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

	$get_entries = new jsonrpc\method( 'phonebook.get_entries' );
	$get_entries->param( 'api_token',    $jsonrpc['api_token'] );
	$get_entries->param( 'hash',         $_SESSION['user']['hash'] );
	$get_entries->param( 'client_id',    $_GET['client_id'] );
	$get_entries->param( 'phonebook_id', $_GET['phonebook_id'] );
	$get_entries->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_entries );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_entries->id ]['status'] ) {
		$entries = $result[ $get_entries->id ]['data']['entry'];
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<input type="button" value="Add Entry" onclick="location.href='/phonebook/entry-addedit.php?phonebook_id=<?= $_GET['phonebook_id']; ?>&client_id=<?= $_GET['client_id']; ?>';">
<table>
	<thead>
		<tr>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Phone Number</th>
			<th>Type</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		 <?php foreach ( $entries as $entry ) : ?>
			<tr>
				<td><?= $entry['first_name']; ?></td>
				<td><?= $entry['last_name']; ?></td>
				<td><?= $entry['number']; ?></td>
				<td><?= $entry['type']; ?></td>
				<td>
					<a href="/phonebook/entry-addedit.php?phonebook_id=<?= $entry['phonebook_id']; ?>&entry_id=<?= $entry['entry_id']; ?>&client_id=<?= $_GET['client_id']; ?>">Edit</a> |
					<a href="/phonebook/entry-delete.php?phonebook_id=<?= $entry['phonebook_id']; ?>&entry_id=<?= $entry['entry_id']; ?>&client_id=<?= $_GET['client_id']; ?>" onclick="return confirm( 'Are you sure?' );">Delete</a>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
