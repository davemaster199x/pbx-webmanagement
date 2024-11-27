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

	$get_time_group = new jsonrpc\method( 'time_group.get' );
	$get_time_group->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_time_group->param( 'hash',      $_SESSION['user']['hash'] );
	$get_time_group->param( 'client_id', $client_id );
	$get_time_group->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_time_group );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_time_group->id ]['status'] ) {
		$time_group = $result[ $get_time_group->id ]['data']['time_group'];
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
	<form action="/time_group/" method="post">
		<select name="client_id">
			<?php foreach ( $_SESSION['user']['clients'] as $client ) : ?>
				<option value="<?= $client['client_id']; ?>"<?= $client_id == $client['client_id'] ? ' selected' : ''; ?>><?= $client['name']; ?></option>
			<?php endforeach; ?>
		</select>
	</form>
<?php endif; ?>
<input type="button" value="Add Time Group" onclick="location.href='/time_group/addedit.php?client_id=<?= $client_id; ?>';">
<table>
	<thead>
		<tr>
			<th>Time Group</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		 <?php foreach ( $time_group as $shortcut ) : ?>
			<tr>
				<td><a href="/time_group/addedit.php?time_group_id=<?= $shortcut['time_group_id']; ?>"><?= ( !empty( $shortcut['name'] ) ? $shortcut['name'] : '<em>No Name</em>' ); ?></a></td>
				<td><a href="/time_group/entries.php?time_group_id=<?= $shortcut['time_group_id']; ?>&client_id=<?= $client_id; ?>">Entries</a></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
