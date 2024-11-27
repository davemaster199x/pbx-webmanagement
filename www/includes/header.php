<?php include( "{$_SERVER['DOCUMENT_ROOT']}/includes/css.php" ); ?>
<!DOCTYPE html>
<html>
	<head>
		<title>PBX - by Hyperion Works</title>

		<link rel="stylesheet" type="text/css" media="print, screen" href="/css/pbxweb.css">

		<!-- <link rel="icon" type="image/ico" href="/images/favicon.png"> -->
		<meta name="viewport" content="width=device-width">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

		<script type="text/javascript" src="/js/jquery-3.7.0.min.js"></script>
	</head>

	<body>
		<header>
			<div class="logo">
				<a href="/" title="home"><img src="/images/logo.png" alt="Hyperion Logo"></a>
			</div><!-- /.logo -->

			<nav>
				<ul>
					<li><a href="/dashboard">Dashboard</a></li>
					<li><a href="/endpoints">Endpoints</a></li>
					<li><a href="/voicemails">Voicemails</a></li>
					<li><a href="/contexts">Contexts</a></li>
					<li><a href="/locations">Locations</a></li>
					<li><a href="/extensions">Extensions</a></li>
					<li><a href="/phonebook">Phonebook</a></li>
					<?php if ( $security['global'] ) : ?>
						<li><a href="/clients">Clients</a></li>
						<li><a href="/users">Users</a></li>
					<?php endif; ?>
					<li><a href="/logout.php">Logout</a></li>
				</ul>
			</nav>
		</header>

		<section id="main">
			<div class="frame">
