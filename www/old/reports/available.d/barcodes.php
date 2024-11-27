<?php

	$report = 'Barcodes';

	if ( !isset( $_POST['task'] )) {
		include( "{$_SERVER['DOCUMENT_ROOT']}/reports/includes/report-header.php" );
	} elseif ( $_POST['task'] == 'load' ) {
		include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" );
		include( "{$_SERVER['DOCUMENT_ROOT']}/reports/includes/form-company-select.php" );
		include( "{$_SERVER['DOCUMENT_ROOT']}/reports/includes/form-date-start.php" );
		include( "{$_SERVER['DOCUMENT_ROOT']}/reports/includes/form-date-end.php" );

		echo <<<HTML
<div class="input button">
	<span class="input">
		<input type="button" value="Generate Report" id="barcodes-report-generate">
	</span>
</div>
<script type="text/javascript">

	$( document ).ready( function() {
		$( '#barcodes-report-generate' ).on( 'click', function() {
			var values = {
				'company_id' : $( '[name=company_id]' ).val(),
				'date_start' : $( '[name=date_start]' ).val(),
				'date_end'   : $( '[name=date_end]' ).val(),
				'task'       : 'execute'
			};

			$.post( '/reports/enabled.d/barcodes.php', values, function( result ) {
				$( '#barcodes-report-container' ).html( result );
			} );
		} );
	} );

	function show_image( file_id, width, height ) {
		create_popup( 'edit-option',
			{
				'close'   : true,
				'content' : '<div><img src="/scripts/get-file.php?file_id=' + file_id + '&disposition=inline" width="' + width + '" height="' + height + '" alt="Photo"></div><div><a href="/scripts/get-file.php?file_id=' + file_id + '&disposition=download">Download Photo</a>',
				'height'  : '540px',
				'title'   : 'Photo',
				'width'   : '700px'
			}
		);

	}

</script>
HTML;
		include( "{$_SERVER['DOCUMENT_ROOT']}/reports/includes/form-container.php" );
		include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" );
	} elseif ( $_POST['task'] == 'execute' ) {

?>
<?php ob_start(); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<style type="text/css">

	table {
		border-collapse: collapse;
		font-family: arial;
		font-size: 12px;
		width: 1000px;
	}

	th, td {
		border: 1px solid #777;
		padding: 5px;
	}

	td.right {
		text-align: right;
	}

</style>
<table>
	<tr>
		<th>Company</th>
		<th>Date/Time</th>
		<th>User</th>
		<th>Scanned</th>
		<th>Photo(s)</th>
	</tr>
<?php

		$jsonrpc_client = new jsonrpc\client();
		$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

		$report = new jsonrpc\method( 'report.barcode' );
		$report->param( 'api_token',  $jsonrpc['api_token'] );
		$report->param( 'hash',       $_SESSION['user']['hash'] );
		$report->param( 'company_id', $_POST['company_id'] );
		$report->param( 'date_start', $_POST['date_start'] );
		$report->param( 'date_end',   $_POST['date_end'] );
		$report->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $report );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $report->id ]['status'] ) {
			$barcode_report = $result[ $report->id ]['data'];
		} else {
			print_r( $result[ $report->id ]['message'] );
		}

?>
	<?php foreach ( $barcode_report as $barcode ) : ?>
		<tr>
			<td><?= $barcode['company']; ?></td>
			<td><?= date_format( date_create( $barcode['created'] ), 'n/d/Y g:ia' ); ?></td>
			<td><?= $barcode['user']; ?></td>
			<td><?= ( $barcode['scanned'] == '0000-00-00 00:00:00' ? 'Not Scanned' : date_format( date_create( $barcode['scanned'] ), 'n/d/Y g:ia' )); ?></td>
			<td>
				<?php if ( $barcode['driver_file_id'] != 0 ) : ?>
					<a href="javascript:void(0);" onclick="show_image( <?= $barcode['driver_file_id']; ?>, 640, 480 );">Driver Photo</a> |
				<?php endif; ?>
				<?php if ( $barcode['plate_file_id'] != 0 ) : ?>
					<a href="javascript:void(0);" onclick="show_image( <?= $barcode['plate_file_id']; ?>, 640, 480 );">Plate Photo</a>
				<?php endif ?>
<?php /*
				<a href="/scripts/get-file.php?file_id=<?= $barcode['driver_file_id']; ?>">Driver Photo</a> |
				<a href="/scripts/get-file.php?file_id=<?= $barcode['plate_file_id']; ?>">Plate Photo</a>
*/ ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php

		include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" );
	}

?>
