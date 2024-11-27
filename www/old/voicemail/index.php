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

	$get_voicemail = new jsonrpc\method( 'voicemail.get' );
	$get_voicemail->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_voicemail->param( 'hash',      $_SESSION['user']['hash'] );
	$get_voicemail->param( 'client_id', $client_id );
	$get_voicemail->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_voicemail );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_voicemail->id ]['status'] ) {
		$voicemail = $result[ $get_voicemail->id ]['data']['voicemail'];
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
	<form action="/voicemail/" method="post">
		<select name="client_id">
			<?php foreach ( $_SESSION['user']['clients'] as $client ) : ?>
				<option value="<?= $client['client_id']; ?>"><?= $client['name']; ?></option>
			<?php endforeach; ?>
		</select>
	</form>
<?php endif; ?>
<table>
	<thead>
		<tr>
			<th>Mailbox</th>
			<th>SMS Notify</th>
		</tr>
	</thead>
	<tbody>
		 <?php foreach ( $voicemail as $mailbox ) : ?>
			<tr>
				<td><a href="/voicemail/edit.php?voicemail_id=<?= $mailbox['voicemail_id']; ?>"><?= $mailbox['mailbox']; ?></td>
				<td><?= $mailbox['sms_notify']; ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
