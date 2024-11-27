<?php

	include( "{$_SERVER['DOCUMENT_ROOT']}/includes/lesscss.php" );

	$less = new lessc( "{$_SERVER['DOCUMENT_ROOT']}/css/pbxweb.less" );

	$output_css = str_replace( '//', '/', "{$_SERVER['DOCUMENT_ROOT']}/css/pbxweb.css" );

	if ( is_writable( $output_css )) {

		file_put_contents( $output_css, $less->parse() );
	} else {

		print "<span class=\"error\">$output_css is not writable.</span>\n";
	}

?>
