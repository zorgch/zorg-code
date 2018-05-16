<?php
/**
 * Strings die im Zorg Code benutzt werden
 * • Format: 'platzhalter' => 'string durch den der platzhalter ersetzt wird'
 * • Struktur:
 * - Array
 * -- Kontext (z.B. "forum", "gallery", usw.)
 * --- Strings
 *
 * WICHTIG:
 * - keine Sonderzeichen (ä, ö, ü, usw.) als Platzhalter!
 * - %-Zeichen müssen mit % escaped werden. Beispiel: 5% = 5%%
 *
 * @todo JSON anstatt PHP-Array?
 *
 * @link sprintf() https://secure.php.net/manual/function.sprintf.php
 */
return
[ 
	'global' =>
		[
			 'error-newgame-not-logged-in' => 'You must be logged in to start a new game.'
			,'error-newgame' => 'Cannot create new game.'
			,'error-play-not-logged-in' => 'You have to log in before you can play.'
			,'error-game-max-limit-reached' => 'No more games possible.'
			,'error-game-invalid' => 'Invalid Game ID "%d".'
			,'error-game-already-joined' => 'You have already joined this game.'
			,'error-game-player-unknown' => 'The selected player does not exist.'
			,'error-game-finish-message' => 'Finish-Message ohne Inhalt bei Game "%d", ausgelöst durch user "%s".<br>Winner: %s<br>Receiver: %s'
			,'error-game-notyourturn' => 'Du bisch nöd dra, yarak!'
			,'error-file-notfound' => 'File not found or not linked in database.'
		]
	,'user' =>
		[
			 'lockout-notice' => 'Du bist ausgesperrt! (bis %s)'
			,'account-inactive' => 'Dein Account wurde noch nicht aktiviert'
			,'account-activated' => 'Dein Account wurde erfolgreich aktiviert!'
			,'authentication-failed' => 'Benutzer/Passwort Kombination falsch!'
			,'invalid-email' => 'E-Mail Adresse ist ung&uuml;ltig!'
			,'invalid-username' => 'Username ist ung&uuml;ltig!'
			,'invalid-regcode' => 'Ungültiger Aktivierungscode!'
			,'newpass-confirmation' => 'Ein neues Passwort wurde generiert und Dir zugestellt!'
			,'account-confirmation' => 'Dein Account wurde erfolgreich erstellt, Du wirst in k&uuml;rze eine E-Mail mit weiteren Informationen bekommen!'
			,'message-newaccount-subject' => SITE_HOSTNAME . ' Benutzerdaten'
			,'message-newaccount' =>  "Willkommen auf Zorg!\n
			 							Du hast erfolgreich einen Account erstellt mit folgendem Benutzernamen: %s\n\n
										Wir bitten Dich deinen Account noch <b>freizuschalten</b>, bevor Du dich das erste mal anmelden kannst. Dazu musst du lediglich folgendem Link aufrufen:\n
										%s/profil.php?menu_id=13&regcode=%s\n\n
										Vielen Dank & viel Spass auf Zorg!"
			,'message-newpass-subject' => 'Neues Passwort'
			,'message-newpass' => "Neues Passwort für den Benutzer: %s\n
									Passwort: %s\n\n
									Dieses Passwort kannst du auf unserer Website unter mein Profil wieder ändern.\n
									Wir wünschen Dir weiterhin viel Spass auf Zorg!"
			,'activity-newuser' => ', willkommen auf zorg!'
		]
	,'messagesystem' =>
		[
			 'invalid-permissions' => 'Sorry du darfst diese Message nicht lesen.'
			,'invalid-message' => 'Notification Text is empty or otherwise invalid'
			,'invalid-image-data' => 'Image Data or URL is empty, not reachable or otherwise invalid'
			,'message-empty-text' => '--- i bi z fuul gsii zum en text schriibe ---'
			,'telegram-newmessage-notification' => 'Neue <a href="%s/user/%d">Nachricht</a> von <b>%s</b> auf %s: %s'
			,'email-notification-subject' => 'Neue Nachricht von %s auf %s'
			,'email-notification-header' =>  'MIME-Version: 1.0'."\n"
											.'Content-type: text/plain; charset="utf-8"'."\n"
											.'From: %1$s <%2$s>'."\n"
										    .'Reply-To: %2$s'."\n"
										    .'X-Mailer: PHP/%3$s'
			,'email-notification-body' =>    'Du hast eine neue Nachricht in deinem Posteingang auf %1$s'."\n"
											."\r\n"
											.'Titel: %2$s'."\n"
											.'Von: %3$s'."\n"
											.'Auszug:'."\n"
											.'%4$s'."\r\n"
											.'Nachricht lesen » %1$s/user/%5$d'
		]
	,'commenting' =>
		[
			 'invalid-parent_id' => 'Parent ID ist ungültig.'
			,'invalid-thread_id' => 'Thread ID ist ungültig.'
			,'invalid-comment_id' => 'Comment ID ist ungültig.'
			,'invalid-permissions' => 'Permission denied for posting on thread %s / %d.'
			,'activity-newcomment' =>  'hat <a href="%1$s%2$s">einen %3$s Comment</a> geschrieben:<br>
										<p><small><a href="%1$s%2$s">"%4$s..."</a></small></p>'
			,'message-newcomment-subject' => 'Du wurdest von %s in einem Comment erwähnt'
			,'message-newcomment' => '%s hat Dich in einem Comment erwähnt:<br>
									  <p><i>%s</i></p>
									  <a href="%s">→ Comment lesen</a>'
			,'message-newcomment-subscribed-subject' => 'Neuer Reply von %s zum Post #%d'
			,'message-newcomment-subscribed' => '%s hat einen Post commented welchen Du abonniert hast:
												<p><a href="%s">%s &raquo;</a></p>'
		]
	,'gallery' =>
		[
			 'error-not-logged-in' =>  '<h3>Gallery ist nur f&uuml;r eingeloggte User sichtbar!</h3>
			 							<p>Bitte logge Dich ein oder <a href="%s/profil.php?do=anmeldung&menu_id=13">erstelle einen neuen Benutzer</a></p>'
			,'permissions-insufficient' => 'Permission denied for <code>%s</code>'
		]
	,'bugtracker' =>
		[
			 'buglist-headline' =>  '<h2 style="text-align:left;">Bugs und Features Liste:</h2>'
			,'newbug-headline' =>  '<h2>Neuen Bug/Request eintragen:</h2>'
			,'newcategory-headline' =>  '<h2>Neue Kategorie adden:</h2>'
		]
	,'addle' =>
		[
			'howto' => 'Ziel des Spiels Addle ist es, möglichst viele Punkte zu erzielen.<br>
					Um Punkte zu bekommen, wähle ein Feld aus deiner markierten Linie aus. Du erhältst die entsprechende Punktzahl. Anschliessend
					darf dein Gegner von seiner markierten Linie auswählen. Die Linie wechselt jeweils von der Vertikalen in die Horizontalen
					deines gewählten Feldes, und umgekehrt. Der erste Spiele wählt immer von aus einer horizontalen Linie aus, der zweite immer
					aus einer vertikalen Linie. Das Spiel ist fertig, wenn ein Spieler kein Feld mehr aus seiner Linie auswählen kann.<br>
					Die Spielerin Barabara Harris ist eine KI, ihr spielt dabei also gegen den Computer.'
			,'neue-herausforderung' => 'Ich habe Dich zu <a href="%s/addle.php?show=play&id=%d">einem neuen Addle-Game</a> herausgefordert!'
			,'next' => '%s ist am Zug'
			,'message-subject' => '-- Addle Game -- (autom. Nachricht)'
			,'message-game-finish' => '<a href="%s/addle.php?show=play&id=%d">Du hast unser Addle-Game %s.</a>'
			,'message-game-unentschieden' => '<a href="%s/addle.php?show=play&id=%d">Unser Addle-Game ging unentschieden aus.</a>'
			,'message-game-forceclosed' => '<a href="%s/addle.php?show=play&id=%d">Du hast unser Addle-Game %s, weil %s nicht mehr weiter gespielt %s.</a>'
			,'unentschieden' => 'unentschieden'
			,'gewinner' => '%s hat gewonnen.'
			,'gewinner-dwz' => '%s hat %s DWZ-Punkte gewonnen.'
		]
	,'hz' =>
		[
			 'activity-newgame' => 'hat ein neues Hunting z Spiel auf der Karte %s er&ouml;ffnet.<br/><br/><a href="%s/?tpl=103&amp;game=%d">Am Spiel als Inspector teilnehmen</a>'
			,'activity-won-mrz' => 'konnt als Mr. Z in <a href="%s/?tpl=103&game=%d">diesem Hunting z Spiel</a> erfolgreich vor den Inspectors in die Bahamas fl&uuml;chten!'
			,'activity-won-inspectors' => 'wir haben als Inspectors <a href="%s/?tpl=103&game=%d">in diesem Game</a> Mr. Z erfolgreich festgenommen!'
			,'activity-joingame' => 'ist <a href="%s?tpl=103&game=%d">diesem Hunting z Spiel</a> als Inspector beigetreten.'
			,'unknown-map' => 'Invalid map "%s"'
			,'unknown-start' => 'Cannot assign start station in game "%d"'
			,'invalid-turn' => 'invalid turn type "%s"'
			,'invalid-ticket' => 'invalid ticket type "%s"'
			,'message-subject' => '-- Hunting z -- (autom. Nachricht)'
			,'message-your-turn' => 'Du bist wieder an der Reihe in <a href="%s/?tpl=103&game=%d">unserem Hunting z Spiel</a>.'
			,'message-game-won-mrz' => 'Du hast <a href="%s/?tpl=103&game=%d">dieses Hunting z Spiel</a> als Mister z <b>gewonnen</b>.'
			,'message-game-lost-mrz' => 'Du hast <a href="%s/?tpl=103&game=%d">dieses Hunting z Spiel</a> als Mister z <b>gewonnen</b>.'
			,'message-game-won-inspectors' => 'Ihr habt <a href="%s/?tpl=103&game=%d">dieses Hunting z Spiel</a> als Inspectors <b>gewonnen</b>.'
			,'message-game-lost-inspectors' => 'Ihr habt <a href="%s/?tpl=103&game=%d">dieses Hunting z Spiel</a> als Inspectors <b>verloren</b>.'
		]
	,'go' =>
		[
			 'invalid-size' => 'The selected size is invalid!'
			,'invalid-gamestate' => 'Game is not in %s state!'
			,'invalid-datastate' => 'Invalid data state %s!'
			,'invalid-coordinates' => 'Koordinaten ausserhalb des Spielfeldes: %d (x=%d, y=%d)'
			,'invalid-field' => 'Feld %d (x=%d, y=%d) ist leer!'
			,'suicide-prevention' => 'Spinnsch enart, chasch di jo noed selber umloh! (Aber probiere chammers jo, gell)'
			,'ko-situation' => 'KO-Situation! Du kannst diesen Stein in dieser Runde nicht schlagen!'
			,'gebietssteine-freiheiten' => 'Gebietssteine: %d, Freiheiten: %d'
			,'ko-warning' => 'KO Situation. Du kannst diesen Stein in dieser Runde nicht schlagen!'
			,'hit-check' => 'Jawoll, friss de Chaib!'
			,'maaachs' => 'Tu in daaaaa rein!'
			,'activity-newgame' => 'hat %s zu einem <a href="%s/?tpl=699&amp;game=%d">GO Spiel</a> herausgefordert.'
			,'message-subject' => '-- GO -- (autom. Nachricht)'
			,'message-your-turn' => 'Ich habe meinen GO-Zug gemacht, du bist jetzt dran in <a href="%s/?tpl=699&amp;game=%d">unserem GO Spiel</a>'
		]
	,'poll' =>
		[
			 'invalid-poll_id' => 'Invalid Poll id "%d"'
		]
	,'util' =>
		[
			 'array2d_sort-invalid-parameter' => 'Invalid parameter "%s" for array2d_sort.'
			,'htmlcolor2array-invalid-parameter' => 'Invalid color "%s" for htmlcolor2array.'
			,'smarty_brackets_ok-invalid-brackets' => 'Ungültige Klammernsetzung &lbrace; oder &rbrace; in der Nähe von: <br><pre>%s</pre><br>'
			,'smarty_brackets_ok-invalid-argument' => 'Invalid argument type for <pre>%s</pre>'
		]
];
