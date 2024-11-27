<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() || ( !$security['global'] && !$security[''] )) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_users = new jsonrpc\method( 'user.get' );
	$get_users->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_users->param( 'hash',      $_SESSION['user']['hash'] );
	$get_users->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_users );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_users->id ]['status'] ) {

		$users = $result[ $get_users->id ]['data']['users'];
	} else {

		$users = [];

		print_r( $result[ $get_users->id ]['message'] );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<?php if ( isset( $_SESSION['errors'] )) : ?>
	<div class="error">User Not Saved: <?php print_r( $_SESSION['errors'] ); unset( $_SESSION['errors'] ); ?></div>
<?php endif; ?>
<input type="button" value="Add User" onclick="location.href='/users/addedit.php';">
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Active</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $users as $user ) : ?>
			<tr>
				<td><a href="/users/addedit.php?user_id=<?= $user['user_id']; ?>"><?= $user['last_name']; ?>, <?= $user['first_name']; ?></a></td>
				<td><?= ( $user['active'] ? 'Yes' : 'No' ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
