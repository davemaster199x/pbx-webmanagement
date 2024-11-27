<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	$date_end_form = array(
		array(
			'type'  => 'text',
			'label' => 'End Date',
			'name'  => 'date_end',
			'class' => 'width-250px',
			'value' => ''
		)
	);

	echo form_display( $date_end_form, $form_templates['main_form'] );

?>
