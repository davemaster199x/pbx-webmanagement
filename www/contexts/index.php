<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {

		header( 'Location: /' );
	}

	$jsonrpc_client = new jsonrpc\client();
	$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

	$get_context = new jsonrpc\method( 'context.get' );
	$get_context->param( 'api_token', $config_client['jsonrpc']['api_token'] );
	$get_context->param( 'hash',      $_SESSION['user']['hash'] );
	$get_context->id = $jsonrpc_client->generate_unique_id();

	$jsonrpc_client->method( $get_context );
	$jsonrpc_client->send();

	$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

	if ( $result[ $get_context->id ]['status'] ) {

		$contexts = $result[ $get_context->id ]['data']['context'];
	} else {

		print_r( $result[ $get_context->id ]['message'] );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<?php if ( isset( $_SESSION['errors'] )) : ?>
	<div class="error">Context Not Saved: <?php print_r( $_SESSION['errors'] ); unset( $_SESSION['errors'] ); ?></div>
<?php endif; ?>
<input type="button" value="Add Context" onclick="location.href='/contexts/addedit.php';">
<table>
	<thead>
		<tr>
			<?php if ( client_count() > 1 ) : ?>
				<th>Client</th>
			<?php endif; ?>
			<th>RPoint</th>
			<th>Context</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $contexts as $context ) : ?>
			<tr>
				<?php if ( client_count() > 1 ) : ?>
					<td><?= $context['cname']; ?></td>
				<?php endif; ?>
				<td><?= $context['clname']; ?></td>
				<td><a href="/contexts/addedit.php?context_id=<?= $context['context_id']; ?>"><?= $context['context']; ?></a></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
