<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/start.php" ); ?>
<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/header.php" ); ?>
<script type="text/javascript">

	function show_report( report ) {
		$( '.report-container' ).css( 'display', 'none' ).html( '' );

		$( '#container-' + report ).load( '/reports/enabled.d/' + report + '.php',
			{
				'task' : 'load'
			}, function() {
				$( '#container-' + report ).show();
			}
		);
	}

</script>
<?php

    $dh = opendir( './enabled.d' );

    while (( $contents = readdir( $dh )) !== FALSE ) {
        if ( $contents != '.' && $contents != '..' ) {
            $reports[] = $contents;
        }
    }

    closedir( $dh );

    sort( $reports );

    foreach ( $reports as $report ) {
        include( "./enabled.d/$report" );
		echo '<hr>';
    }

?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/footer.php" ); ?>
<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/finish.php" ); ?>
