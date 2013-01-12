<?php



?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>mobile@zorg</title>
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<style type="text/css" media="screen">@import "iui/iuix.css";</style>
<script type="application/x-javascript" src="iui/iuix.js"></script>
<!--
<script type="application/x-javascript" src="http://10.0.1.2:1840/ibug.js"></script>
-->
</head>

<body onclick="console.log('Hello', event.target);">
	<div class="toolbar">
		<h1 id="pageTitle"></h1>
		<a id="forceBackButton" class="button" href="index.php" target="_self">Zorg</a>
		<a class="button" href="#newThread">Schreiben</a>
	</div>
	
	<!-- FORUM -->
	<ul id="forum" title="Forum" selected="true">
		<li><a class="threadTitle" href="#thread1">Thread 1</a>
			<a class="newItemIndicator">6</a></li>
		<li><a class="threadTitle" href="#thread2">Es war einmal im Wun...</a>
			<a class="newItemIndicator">332</a></li>
		<li><a class="threadTitle" href="#thread2">Nur ein Test</a>
			</li>
		<li><a href="forum/seite1.html" target="_replace">Mehr...</a></li>
	</ul>
		
		<!-- Comment -->
		<form id="newComment" class="dialog" action="#newComment">
			<fieldset>
				<h1>Kommentar</h1>
				<a class="button leftButton" type="cancel">Cancel</a>
				<a class="button blueButton" type="submit">Senden</a>
				
				<input type="text" name="comment"/>
			</fieldset>
		</form>
		
		<!-- Thread -->
		<form id="newThread" class="dialog" action="#newThread">
			<fieldset>
				<h1>Neuer Thread</h1>
				<a class="button leftButton" type="cancel">Cancel</a>
				<a class="button blueButton" type="submit">Senden</a>
				
				<input type="text" name="threadText"/>
			</fieldset>
		</form>
</body>
</html>
