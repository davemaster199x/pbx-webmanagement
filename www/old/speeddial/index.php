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

	$get_speeddial = new jsonrpc\method( 'speeddial.get' );
	$get_speeddial->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_speeddial->param( 'hash',      $_SESSION['user']['hash'] );
	$get_speeddial->param( 'client_id', $client_id );
	$get_speeddial->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_speeddial );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_speeddial->id ]['status'] ) {
		$speeddial = $result[ $get_speeddial->id ]['data']['speeddial'];
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
	<form action="/speeddial/" method="post">
		<select name="client_id">
			<?php foreach ( $_SESSION['user']['clients'] as $client ) : ?>
				<option value="<?= $client['client_id']; ?>"<?= $client_id == $client['client_id'] ? ' selected' : ''; ?>><?= $client['name']; ?></option>
			<?php endforeach; ?>
		</select>
	</form>
<?php endif; ?>
<input type="button" value="Add Speeddial" onclick="location.href='/speeddial/addedit.php?client_id=<?= $client_id; ?>';">
<table>
	<thead>
		<tr>
			<th>Shortcut</th>
			<th>Destination</th>
		</tr>
	</thead>
	<tbody>
		 <?php foreach ( $speeddial as $shortcut ) : ?>
			<tr>
				<td><a href="/speeddial/addedit.php?speeddial_id=<?= $shortcut['speeddial_id']; ?>"><?= $shortcut['shortcut']; ?></a></td>
				<td><?= $shortcut['dest']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
