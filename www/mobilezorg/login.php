<?php
/**
 * Login
 * 
 * Baut das Loginformular und verwaltet die Anmeldung eines Benutzers an mobilezorg
 * 
 * @author IneX
 * @version 1.0
 * @package mobilezorg
 * @subpackage usersystem
 */

include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $login_error;

($user->typ != USER_NICHTEINGELOGGT && isset($user->typ)) ? header('Location: index.php') : $error_msg = is_string($login_error) ? $login_error : ''; ;


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>mobile@zorg</title>
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<style type="text/css" media="screen">@import "iui/iui.css";</style>
<script type="application/x-javascript" src="iui/iui.js"></script>
<!--
<script type="application/x-javascript" src="http://10.0.1.2:1840/ibug.js"></script>
-->
</head>

<body onclick="console.log('Hello', event.target);">
	<div class="toolbar">
		<h1 id="pageTitle"></h1>
		<a id="backButton" class="button" href="#"></a>
		<a class="button" href="http://zorg.ch/" target="_self">Web View</a>
	</div>
	
	
<!-- LOGIN SCREEN -->
	<form id="login" title="Login" class="panel" selected="true" action="login.php" method="post" redirect="true" target="_self">
		
	<!-- Error Message -->
	<?php if ($error_msg) { ?>
		<div class="error">
			<h1>Fehler beim Login</h1>
		</div>
	<?php } ?>
		
		
		<h2>Anmelden</h2>
		<fieldset>
		<div class="row">
			<label>Benutzer:</label>
			<input type="text" name="username" maxlength="35" />
		</div>
	
		<div class="row">
			<label>Passwort:</label>
			<input type="password" name="password" maxlength="35" />
		</div>
		
		<div class="row">
			<label>Login speichern?</label>
			<div class="toggle" onclick="if (document.getElementByName['cookie'].value='true') document.getElementByName['cookie'].value='false' else document.getElementByName['cookie'].value='true'";><span class="thumb"></span><span class="toggleOn">Ja</span><span class="toggleOff">Nein</span></div>
			<input type="hidden" name="cookie" value="false"/>
		</div>
		</fieldset>
		<a class="whiteButton" type="submit" href="#" target="_self">Login</a>
	</form>
</body>
</html>
