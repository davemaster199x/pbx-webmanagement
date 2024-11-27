<?php

	if ( !loggedin() ) {
		header( 'Location: /' );
	}

	if ( $security['global'] ) {
		$jsonrpc_client = new jsonrpc\client();
		$jsonrpc_client->server( $config_client['jsonrpc']['url'] );

		$get_companies = new jsonrpc\method( 'company.get' );
		$get_companies->param( 'api_token', $config_client['jsonrpc']['api_token'] );
		$get_companies->param( 'hash',      $_SESSION['user']['hash'] );
		$get_companies->id = $jsonrpc_client->generate_unique_id();

		$jsonrpc_client->method( $get_companies );
		$jsonrpc_client->send();

		$result = jsonrpc\client::parse_result( $jsonrpc_client->result );

		if ( $result[ $get_companies->id ]['status'] ) {
			$companies = $result[ $get_companies->id ]['data']['companies'];
		}

		$select_options['companies'] = array(
			array(
				'display' => '[Select]',
				'value'   => ''
			)
		);

		if ( $result[ $get_companies->id ]['status'] ) {
			$companies = $result[ $get_companies->id ]['data']['companies'];

			foreach ( $companies as $company ) {
				$select_options['companies'][] = array(
					'display' => $company['name'],
					'value'   => $company['company_id']
				);
			}
		}

		$company_form = array(
			array(
				'type'     => 'select',
				'label'    => 'Company',
				'name'     => 'company_id',
				'class'    => 'width-200px',
				'selected' => ( isset( $site['company_id'] ) ? $site['company_id'] : '' ),
				'options'  => $select_options['companies']
			)
		);
	} else {
		$company_form = array(
			array(
				'type'  => 'hidden',
				'name'  => 'company_id',
				'value' => $_SESSION['user']['company_id']
			)
		);
	}

	echo form_display( $company_form, $form_templates['main_form'] );

?>
