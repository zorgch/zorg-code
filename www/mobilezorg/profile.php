<?php
/**
 * File Includes
 */
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/main.inc.php');

?>

<!-- SETTINGS -->
	<div id="profil" title="Mein Profil" class="panel">
		<h2>Benutzer</h2>
		<fieldset>
			<div class="row">
				<label>Username</label>
				<input type="text" name="userName" value="<?php echo $user->username ?>"/>
			</div>
			<?php /* the following data is not yet implemented in the User-DB...
			<div class="row">
				<label>Vorname</label>
				<input type="text" name="firstName" value="<?php echo $user->firstname ?>"/>
			</div>
			<div class="row">
				<label>Nachname</label>
				<input type="text" name="lastName" value="<?php echo $user->lastname ?>"/>
			</div>
			<div class="row">
				<label>Strasse</label>
				<input type="text" name="street" value="<?php echo $user->street ?>"/>
			</div>
			<div class="row">
				<label>PLZ</label>
				<input type="text" name="zip" value="<?php echo $user->zip ?>"/>
			</div>
			<div class="row">
				<label>Ort</label>
				<input type="text" name="city" value="<?php echo $user->city ?>"/>
			</div>*/ ?>
			<div class="row">
				<label>E-Mail</label>
				<input type="text" name="eMail" value="<?php echo $user->email ?>"/>
			</div>
			<div class="row">
				<label>ICQ</label>
				<input type="text" name="ICQ" value="<?php echo $user->icq ?>"/>
			</div>
		</fieldset>
		
		<h2>Spiele</h2>
		<fieldset>
			<div class="row">
				<label>Addle spielen?</label>
				<div class="toggle" id="addle" name="addle" onclick="" <?php echo ($user->addle == 1) ? 'toggled="true"' : '' ; ?>><span class="thumb"></span><span class="toggleOn">Ja</span><span class="toggleOff">Nein</span></div>
			</div>
		</fieldset>
		
		<!-- h2>Passwort</h2>
		<fieldset>
			<div class="row">
				<label>Aktuell</label>
				<input type="password" name="passwordOld" value=""/>
			</div>
			<div class="row">
				<label>Neu</label>
				<input type="password" name="password" value=""/>
			</div>
			<div class="row">
				<label>Best&auml;tigen</label>
				<input type="password" name="password" value=""/>
			</div>
		</fieldset -->
		<a class="whiteButton" name="saveSettings" type="SUBMIT" href="index.php" target="_self">Speichern</a><br />
		<a class="grayButton" name="userLogout" type="SUBMIT" href="logout.php" target="_self" onclick="return confirm('Wirklich abmelden?');">Logout</a>
	</div>