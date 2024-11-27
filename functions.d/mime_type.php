<?php

	function mime_type( $file ) {
	/*
	 * Mime Type
	 * Uses PHP's FileInfo Functions to determine the mime type of a file.
	 */

	// See if filename was provided
		if ( !trim( $file )) {

			return FALSE;
		}

		$file_info = new finfo( FILEINFO_MIME );

		return $file_info->file( $file );
	}

?>
