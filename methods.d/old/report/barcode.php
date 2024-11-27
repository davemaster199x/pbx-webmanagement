<?php namespace report;

	function barcode( $params ) {
	/*
	 * Barcode
	 * Generates the barcode report.
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
         barcode.barcode_id, barcode.created, barcode.scanned, barcode.driver_file_id, barcode.plate_file_id,
         CONCAT( user.last_name, ', ', user.first_name ) AS user,
         driver_file.locked AS driver_file_locked,
         plate_file.locked AS plate_file_locked
    FROM barcode
           INNER JOIN user
                   ON barcode.user_id = user.user_id
           INNER JOIN company
                   ON user.company_id = company.company_id
            LEFT JOIN file AS driver_file
                   ON barcode.driver_file_id = driver_file.file_id
            LEFT JOIN file AS plate_file
                   ON barcode.plate_file_id = plate_file.file_id
   WHERE company.company_id = :company_id
     AND DATE( barcode.created ) BETWEEN :date_start AND :date_end
SQL;
		$report_result = $pdo->prepare( $report_query );
		$report_result->execute( array(
			':company_id' => $params['company_id'],
			':date_start' => date_format( date_create( $params['date_start'] ), 'Y-m-d' ),
			':date_end'   => date_format( date_create( $params['date_end'] ), 'Y-m-d' )
		));

		$barcode_report = array();

		foreach ( $report_result->fetchAll( \PDO::FETCH_ASSOC ) as $report_row ) {
			$report_row['barcode'] = str_pad( $report_row['barcode_id'], 10, '0', STR_PAD_LEFT ) . '0' . date_format( date_create( $report_row['created'] ), 'U' );

			$barcode_report[] = $report_row;
		}

		return _build_response( COMPLETE, 'barcode_report', $barcode_report );
	}

?>
