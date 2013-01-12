<?php
/**
 * Login
 * 
 * Baut das Loginformular und verwaltet die Anmeldung eines Benutzers an mobilezorg
 * 
 * @author IneX
 * @version 2.0
 * @package mobilezorg
 * @subpackage usersystem
 *
 * @global array $user Globales Array mit allen Uservariablen
 * @global array $login_erre Globales Array welches allenfalls eine Fehlermeldung enthält
 */
/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

global $user, $login_error;

($user->typ != USER_NICHTEINGELOGGT && isset($user->typ)) ? header('Location: index.php') : $error_msg = is_string($login_error) ? $login_error : ''; ;


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>mobile@zorg</title>
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
		<link rel="stylesheet" href="Design/Render.css" />
		<script type="text/javascript" src="Action/Logic.js"></script>
	</head>

	<body>
	<div id="WebApp">
		
		<div class="iLayer" id="waForm" title="Login">
			<a href="http://www.zorg.ch/" rel="action" class="iButton iBAction">Web View</a>
			<form id="loginForm" action="login.php" onsubmit="return WA.Submit(this)">
	
			<div class="iPanel">
				<div id="form-res"></div>
				
				<!-- Error Message -->
				<?php if ($error_msg) { ?>
					<div class="error">
						<h1>Fehler beim Login</h1>
					</div>
				<?php } ?>
				
				<fieldset>
					<ul>
						<li><input type="text" name="username" placeholder="Benutzername" maxlength="35" /></li>
						<li><input style="-border:1px solid black;" type="text" name="password" placeholder="Passwort" maxlength="35" /></li>
					</ul>
				</fieldset>
				<fieldset>
					<ul>
						<li><input type="checkbox" name="cookie" id="cbSaveLogin" class="iToggle" title="Ja|Nein" checked="checked" value="true" /> <label for="cbSaveLogin">Login speichern</label></li>
					</ul>
					<p align="center">
						<a class="white button" type="submit" id="logMeIn" href="#" alt="Login" onclick="return WA.Submit('loginForm')">Anmelden</a>
					</p>
				</fieldset>
			</div>
	
			</form>
		</div>
		
	</div>
</body>
</html>
