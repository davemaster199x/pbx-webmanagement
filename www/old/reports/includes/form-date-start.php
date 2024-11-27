<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$date_start_form = array(
		array(
			'type'  => 'text',
			'label' => 'Start Date',
			'name'  => 'date_start',
			'class' => 'width-250px',
			'value' => ''
		)
	);

	echo form_display( $date_start_form, $form_templates['main_form'] );

?>
