<?php
/**
 * mobileZorg Home
 * 
 * Home-Screen von mobilezorg mit 1st Level Menü
 * 
 * @author IneX
 * @version 2.0
 * @package mobilezorg
 *
 * @global array $user Globales Array mit allen Uservariablen
 * @global array $db Globales Array mit allen MySQL-Datenbankvariablen
 */
/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/messagesystem.inc.php');

global $user, $db;

if ($user->typ == USER_NICHTEINGELOGGT || !isset($user->typ)) { header('Location: login.php'); }

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

		<div id="iHeader">
			<a href="#" id="waBackButton">Back</a>
			<span id="waHeadTitle">mobile@zorg</span>
		</div>

		<div id="iGroup">

			<div class="iLayer" id="waHome" title="Home">
				<div class="iList" id="async-list">
					<a href="#">Test</a>
   					<p align="center">
						<a class="black button" id="logMeOut" href="logout.php" alt="Logout">Abmelden</a>
					</p>
				</div>
			</div>

		</div>
	</div>
	</body>
</html>