<?php

// Start session
	session_start();

// Cleanup
	if ( substr( $_SERVER['DOCUMENT_ROOT'], -1 ) != '/' ) {

		$_SERVER['DOCUMENT_ROOT'] .= '/';
	}

// Include functions
	foreach ( glob( "{$_SERVER['DOCUMENT_ROOT']}/includes/functions.d/*.php") as $function ) {

		include( $function );
	}

// JSON-RPC setup
	include( "{$_SERVER['DOCUMENT_ROOT']}/../config.php" );

// Get security
	if ( loggedin() ) {

		if ( security_check( 'global' )) {

			$security['global'] = TRUE;
		} else {

			$security['global'] = FALSE;
		}
	}

// Form Template
	$form_templates['main_form']['autocomplete'] = <<<HTML
<div class="input autocomplete %CLASS%" %DATA%>
	<span class="label">%LABEL%</span>
	<span class="input"><input type="text" name="%NAME%" placeholder="%PLACEHOLDER%" value="%VALUE%" %DATA%></span>
</div>
<script type="text/javascript">
	$( 'input[name=%NAME%]' ).autocomplete( %PARAMS% ).on( 'keydown', function( ev ) { if ( ev.keyCode == 13 ) { ev.preventDefault(); return false; } } );
</script>
HTML;

	$form_templates['main_form']['button'] = <<<HTML
<div class="input button %CLASS%" %DATA%>
	<span class="input">
		<input type="button" name="%NAME%" value="%VALUE%" class="%CLASS%" %DATA%>
	</span>
</div>
HTML;

	$form_templates['main_form']['checkbox'] = <<<HTML
<div class="input checkbox %CLASS%" %DATA%>
	<span class="label">%LABEL%</span>
	<span class="input">
		%%OPTIONS%%
		<label style="display: block;"><span><input type="checkbox" name="%NAME%" value="%VALUE%" %DATA% %CHECKED%></span><span>%DISPLAY%</span></label>
		%%OPTIONS%%
	</span>
</div>
HTML;

	$form_templates['main_form']['container'] = <<<HTML
<div id="%ID%" class="%CLASS%" %DATA%></div>
HTML;

	$form_templates['main_form']['hidden'] = <<<HTML
<input type="hidden" name="%NAME%" value="%VALUE%" %DATA%>
HTML;

	$form_templates['main_form']['hr'] = <<<HTML
<hr>
HTML;

	$form_templates['main_form']['linebreak'] = <<<HTML
<hr class="linebreak">
HTML;

	$form_templates['main_form']['multicheck'] = <<<HTML
<div class="input multicheck %CLASS%" %DATA%>
	<span class="label">%LABEL%</span>
	<span class="input">
		%%OPTIONS%%
		<span><input type="checkbox" name="%NAME%" value="%VALUE%" %DATA% %CHECKED%>%DISPLAY%</span>
		%%OPTIONS%%"
	</span>
</div>
HTML;

	$form_templates['main_form']['parent-container'] = <<<HTML
<div class="%CLASS%" %DATA%>
	%CHILDREN%
</div>
HTML;

	$form_templates['main_form']['password'] = <<<HTML
<div class="input password %CLASS%" %DATA%>
	<span class="label">%LABEL%</span>
	<span class="input"><input type="password" name="%NAME%" placeholder="%PLACEHOLDER%" value="%VALUE%" %DATA%></span>
</div>
HTML;

	$form_templates['main_form']['radio'] = <<<HTML
<div class="input radio %CLASS%" %DATA%>
	<span class="label">%LABEL%</span>
	<span class="input">
		%%OPTIONS%%
		<input type="radio" name="%NAME%" value="%VALUE%" %DATA% %CHECKED%>%DISPLAY%
		%%OPTIONS%%
	</span>
</div>
HTML;

	$form_templates['main_form']['select'] = <<<HTML
<div class="input select %CLASS%" %DATA%>
	<span class="label">%LABEL%</span>
	<span class="input">
		<select name="%NAME%" %DATA%>
			%%OPTIONS%%
			<option value="%VALUE%" %SELECTED%>%DISPLAY%</option>
			%%OPTIONS%%
		</select>
	</span>
</div>
HTML;

	$form_templates['main_form']['submit'] = <<<HTML
<div class="input button %CLASS%" %DATA%>
	<span class="input">
		<input type="submit" name="%NAME%" value="%VALUE%" class="%CLASS%" %DATA%>
	</span>
</div>
HTML;

	$form_templates['main_form']['textarea'] = <<<HTML
<div class="input textarea %CLASS%" %DATA%>
	<span class="label">%LABEL%</span>
	<span class="input"><textarea name="%NAME%" placeholder="%PLACEHOLDER%" %DATA% %WRAP%>%VALUE%</textarea></span>
</div>
HTML;

	$form_templates['main_form']['text'] = <<<HTML
<div class="input text %CLASS%" %DATA%>
	<span class="label">%LABEL%</span>
	<span class="input"><input type="text" name="%NAME%" placeholder="%PLACEHOLDER%" value="%VALUE%" %DATA% %ATTR%></span>
</div>
HTML;

?>
