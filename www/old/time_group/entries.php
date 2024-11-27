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

	$get_entries = new jsonrpc\method( 'time_group.get_entries' );
	$get_entries->param( 'api_token',     $jsonrpc['api_token'] );
	$get_entries->param( 'hash',          $_SESSION['user']['hash'] );
	$get_entries->param( 'client_id',     $_GET['client_id'] );
	$get_entries->param( 'time_group_id', $_GET['time_group_id'] );
	$get_entries->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_entries );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_entries->id ]['status'] ) {
		$entries = $result[ $get_entries->id ]['data']['entry'];
	}

	$days = array(
		0 => 'Sunday',
		1 => 'Monday',
		2 => 'Tuesday',
		3 => 'Wednesday',
		4 => 'Thursday',
		5 => 'Friday',
		6 => 'Saturday'
	);

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<input type="button" value="Add Time Condition" onclick="location.href='/time_group/entry-addedit.php?time_group_id=<?= $_GET['time_group_id']; ?>&client_id=<?= $_GET['client_id']; ?>';">
<table>
	<thead>
		<tr>
			<th>Start Day</th>
			<th>End Day</th>
			<th>Start Time</th>
			<th>End Time</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		 <?php foreach ( $entries as $entry ) : ?>
			<tr>
				<td><?= $days[ $entry['day_start'] ]; ?></td>
				<td><?= $days[ $entry['day_end'] ]; ?></td>
				<td><?= $entry['time_start']; ?></td>
				<td><?= $entry['time_end']; ?></td>
				<td>
					<a href="/time_group/entry-addedit.php?time_group_id=<?= $entry['time_group_id']; ?>&time_condition_id=<?= $entry['time_condition_id']; ?>&client_id=<?= $_GET['client_id']; ?>">Edit</a> |
					<a href="/time_group/entry-delete.php?time_group_id=<?= $entry['time_group_id']; ?>&time_condition_id=<?= $entry['time_condition_id']; ?>&client_id=<?= $_GET['client_id']; ?>" onclick="return confirm( 'Are you sure?' );">Delete</a>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
