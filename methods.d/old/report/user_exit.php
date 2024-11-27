<?php namespace report;

	function user_exit( $params ) {
	/*
	 * User Exit
	 * Generates the user exit report.
	 */

	// Verify authorized API Token
		if ( empty( $params['api_token'] )) {
			return _build_response( ERROR, 'api_token_missing' );
		}

		if ( !\check_api_token( $params['api_token'] )) {
			return _build_response( ERROR, "api_token_failure: {$params['api_token']}" );
		}

		$pdo = \db_connect();

	// Verify params
		if ( empty( $params['company_id'] )) {
			return _build_response( ERROR, 'missing_company_id' );
		}

		if ( empty( $params['date_start'] )) {
			return _build_response( ERROR, 'missing_date_start' );
		}

		if ( empty( $params['date_end'] )) {
			return _build_response( ERROR, 'missing_date_end' );
		}

	// Generate report
		$report_query = <<<SQL
  SELECT company.name AS company,
         user_exit.created, user_exit.plate_file_id,
         CONCAT( user.last_name, ', ', user.first_name ) AS user
    FROM company
           INNER JOIN user
                   ON company.company_id = user.company_id
           INNER JOIN user_exit
                   ON user.user_id = user_exit.user_id
   WHERE company.company_id = :company_id
     AND DATE( user_exit.created ) BETWEEN :date_start AND :date_end
SQL;
		$report_result = $pdo->prepare( $report_query );
		$report_result->execute( array(
			':company_id' => $params['company_id'],
			':date_start' => date_format( date_create( $params['date_start'] ), 'Y-m-d' ),
			':date_end'   => date_format( date_create( $params['date_end'] ), 'Y-m-d' )
		));

		$user_exit_report = $report_result->fetchAll( \PDO::FETCH_ASSOC );
/*
		$user_exit_report = array();

		foreach ( $user_exit_result->fetchAll( \PDO::FETCH_ASSOC ) as $report_row ) {
			$report_row['barcode'] = str_pad( $report_row['barcode_id'], 10, '0', STR_PAD_LEFT ) . '0' . date_format( date_create( $report_row['created'] ), 'U' );

			$barcode_report[] = $report_row;
		}
*/

		return _build_response( COMPLETE, 'user_exit_report', $user_exit_report );
	}

?>
