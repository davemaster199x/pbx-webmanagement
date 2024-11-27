<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$date_end_form = array(
		array(
			'type'  => 'container',
			'id'    => strtolower( str_replace( ' ', '_', $report )) . '-report-container'
		)
	);

	echo form_display( $date_end_form, $form_templates['main_form'] );

?>
