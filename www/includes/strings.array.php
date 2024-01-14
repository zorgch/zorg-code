<?php
/**
 * Strings die im zorg Code benutzt werden
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
 * @TODO JSON anstatt PHP-Array?
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
			,'error-game-invalid' => 'Invalid Game ID "%s".'
			,'error-game-already-joined' => 'You have already joined this game.'
			,'error-game-player-unknown' => 'The selected player does not exist.'
			,'error-game-finish-message' => 'Finish-Message ohne Inhalt bei Game "%d", ausgelöst durch user "%s".<br>Winner: %s<br>Receiver: %s'
			,'error-game-notyourturn' => 'Du bisch nöd dra, yarak!'
			,'error-file-notfound' => 'File not found or not linked in database.'
			,'game-join' => 'join!'
			,'game-your-game' => 'Du spielst hier mit!'
			,'game-waiting-for-players' => 'Warten auf weitere Mitspieler...'
			,'game-your-turn' => '!!! Du bist am Zug !!!'
			,'datetime-today' => 'heute'
			,'datetime-yesterday' => 'gestern'
			,'datetime-recently' => 'jetzt'
			,'datetime-second' => '%d Sekunde'
			,'datetime-seconds' => '%d Sekunden'
			,'datetime-minute' => '%d Minute'
			,'datetime-minutes' => '%d Minuten'
			,'datetime-hour' => '%d Stunde'
			,'datetime-hours' => '%d Stunden'
			,'datetime-day' => '%d Tag'
			,'datetime-days' => '%d Tage'
			,'datetime-week' => '%d Woche'
			,'datetime-weeks' => '%d Wochen'
			,'datetime-month' => '%d Monat'
			,'datetime-months' => '%d Monaten'
			,'datetime-year' => '%d Jahr'
			,'datetime-years' => '%d Jahre'
			,'text-abbreviation' => '&hellip;'
		]
	,'user' =>
		[
			 'error-userprofile-nochange' => 'Fehler beim Aktualisieren des Userprofil. Oder es gab gar keine Änderungen am Profil.'
			,'error-userprofile-update' => 'Userprofil update FAILED. Sie sorry, aber äääh...'
			,'error-lockout-date' => 'Ungültiges aussperren-Datum: %s'
			,'error-lockout-status' => 'Leider hat es mit dem aussperren nicht geklappt. So eifach chunsch üs nöd devo...'
			,'error-userpw-update' => 'Passwort konnte nicht aktualisiert werden. Probiers nomel bitte.'
			,'error-userpic-name' => 'Der "name"-Wert des übertragenen Bildes fehlt oder konnte nicht gelesen werden.'
			,'error-userpic-upload' => 'Das Bild konnte nicht übertragen werden. Probiers nomel, bitte.'
			,'error-userpic-permissions' => 'Bild konnte nicht bearbeitet werden.'
			,'error-userpic-archive' => 'Original Bild konnte nicht archiviert werden.'
			,'error-userpictn-archive' => 'Thumbnail Bild konnte nicht archiviert werden.'
			,'account-inactive' => 'Dein Account wurde noch nicht aktiviert'
			,'account-is-active' => 'Dein Account ist bereits aktiviert!'
			,'account-activated' => 'Dein Account wurde erfolgreich aktiviert!'
			,'account-activated-text' => 'Herzlich willkommen - Schön, dass du da bist :)<br>Du kannst dich jetzt mit dem gewählten Username + PW einloggen.'
			,'authentication-failed' => 'Benutzer/Passwort Kombination falsch!'
			,'authentication-empty' => 'Benutzer+Passwort muss ausgefüllt sein!'
			,'invalid-cookie' => 'Dein Browser-Cookie für den zorg Login wurde kompromittiert! Bitte Browser-Cookies löschen.'
			,'invalid-id' => '<h1>ID is not valid!</h1><p><strong>Please tell us about this via the <a href="bugtracker.php" title="Bugtracker - zorg.ch">Bugtracker</a>.</strong><br>You will contribute making zorg more secure and stable :) Thanks!</p>'
			,'invalid-email' => 'E-Mailadresse ist ungültig! (wotsch wieder cheatä ?)'
			,'invalid-username' => 'Username ist ung&uuml;ltig!'
			,'invalid-regcode' => 'Ungültiger Aktivierungscode!'
			,'invalid-userpw-match' => 'Du hast dich vertippt, bitte wiederholen! (tippä chasch au nö?)'
			,'invalid-userpw-old' => 'Das alte Passwort ist falsch! (bisch du däne, hai echt)'
			,'invalid-userpw-missing' => 'Alle Passwort-Felder müssen zum ändern ausgefüllt werden! (ähä)'
			,'invalid-userpic-format' => 'Dies ist kein JPEG Bild! (muäsch nöd meinä!)'
			,'lockout-notice' => 'Du bist ausgesperrt! (bis %s)'
			,'user-wird-geaechtet' => '%s wird zur Zeit geächtet - weil er sich selber <strong>ausgesperrt</strong> hat. Tz-tz-tz...'
			,'newpass-confirmation' => 'Ein neues Passwort wurde generiert und Dir zugestellt!'
			,'newpass-confirmation-text' => 'Falls Du innerhalb der nächsten 15 Minuten keine E-Mail mit dem neuen Passwort erhälst, melde dich bitte <a href="/page/vereinsvorstand">bei uns</a>'
			,'account-confirmation' => 'Dein Account wurde erfolgreich erstellt, Du wirst in k&uuml;rze eine E-Mail mit weiteren Informationen bekommen!'
			,'new-userpw-confirmation' => 'Dein Passwort wurde erfolgreich geändert!'
			,'userprofile-change-ok' => 'Änderungen wurden erfolgreich gespeichert!'
			,'userpic-change-ok' => 'Änderungen wurden erfolgreich gespeichert!'
			,'message-newaccount-subject' => SITE_HOSTNAME . ' Benutzerdaten'
			,'message-newaccount' =>  "Willkommen auf zorg!\n
			 							Du hast erfolgreich einen Account erstellt mit folgendem Benutzernamen: %s\n\n
										Wir bitten Dich deinen Account noch <b>freizuschalten</b>, bevor Du dich das erste mal anmelden kannst. Dazu musst du lediglich folgendem Link aufrufen:\n
										%s/profil.php?regcode=%s\n\n
										Vielen Dank & viel Spass auf zorg!"
			,'message-newpass-subject' => 'Neues Passwort'
			,'message-newpass' => "Neues Passwort für den Benutzer: %s\n
									Passwort: %s\n\n
									Dieses Passwort kannst du auf unserer Website unter mein Profil wieder ändern.\n
									Wir wünschen Dir weiterhin viel Spass auf zorg!"
			,'activity-newuser' => ', willkommen auf zorg!'
		]
	,'messagesystem' =>
		[
			 'invalid-permissions' => 'Sorry du darfst diese Message nicht lesen.'
			,'invalid-message' => 'Notification Text is empty or otherwise invalid'
			,'invalid-userid' => 'Empty or invalid User-ID(s)'
			,'invalid-telegram-chatid' => 'Empty or invalid Telegram Chat-ID(s)'
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
			 'invalid-parent_id' => 'Die Parent ID existiert nicht oder ist ungültig.'
			,'invalid-thread_id' => 'Thread ID fehlt oder ist ungültig.'
			,'invalid-comment_id' => 'Comment ID ist ungültig.'
			,'invalid-permissions' => 'Permission denied for posting on thread %s / %d.'
			,'invalid-permissions-search' => 'Um das Forum zu durchsuchen musst du eingeloggt sein.'
			,'invalid-comment-no-parentid' => 'Du darfst per Edit keine neuen Threads erstellen'
			,'invalid-comment-edit-permissions' => 'Das ist nicht dein Kommentar, den darfst du nicht bearbeiten!'
			,'invalid-comment-empty' => 'Leere Posts sind nicht erlaubt!'
			,'error-missing-board' => 'Board nicht angegeben!'
			,'error-search-noresult' => 'Für die Suche nach "%s" gibt es leider keine passenden Einträge'
			,'activity-newthread' =>  'hat einen neuen Thread <a href="%s%s">"%s..."</a> gestartet'
			,'activity-newcomment' =>  'hat <a href="%1$s%2$s">einen %3$s Comment</a> geschrieben:<br>
										<p><small><a href="%1$s%2$s">"%4$s..."</a></small></p>'
			,'message-newcomment-subject' => 'Du wurdest von %s in einem Comment erwähnt'
			,'message-newcomment' => '%s hat Dich in einem Comment erwähnt:<br>
									  <blockquote><p><i>%s</i></p></blockquote>
									  <a href="%s">→ Comment lesen</a>'
			,'message-newcomment-subscribed-subject' => 'Neuer Reply von %s zum Post #%d'
			,'message-newcomment-subscribed' => '%s hat einen Post commented welchen Du abonniert hast:
												<p><a href="%s">%s &raquo;</a></p>'
			,'message-commentupdate-subject' => 'Du wurdest von %s bei einem Comment-Edit erwähnt'
			,'message-commentupdate' => '%s hat Dich in einem bearbeiteten Comment erwähnt:<br>
										 <p><i>%s</i></p>
										 <a href="%s">→ Comment lesen</a>'
			,'forum-new-thread' => '<h3 style="text-align:left;">Neuen Thread erstellen</h3>'
			,'forum-favorite-thread-action' => '[fav]'
			,'forum-unfavorite-thread-action' => '[unfav]'
			,'forum-ignore-thread-action' => '[ignore]'
			,'forum-unignore-thread-action' => '[follow]'
			,'forum-rss-thread-action' => '[rss]'
		]
	,'tpl' =>
		[
			 'created' =>  'Neue Seite wurde erstellt. ID: %d'
			,'updated' => 'Seite "%d" erfolgreich aktualisiert'
			,'deleted' => 'Seite "%d" wurde <strong>gelöscht</strong>'
			,'invalid-permissions-read' => 'Ungültiges Lese-Recht.<br>'
			,'invalid-permissions-write' => 'Ungültiges Schreib-Recht.<br>'
			,'invalid-border' => 'Ungültiger Rahmen-Typ.'
			,'error-create' => 'Template konnte nicht erstellt werden'
			,'error-empty' => 'Bitte keine leeren Seiten.'
			,'error-word-toolong' => 'Word "%s" ist zu lang. Max. 30 Zeichen!'
			,'error-word-validation' => 'Ungültige Zeichen im Word "%s". Erlaubt sind nur: a-z, A-Z, 0-9, _, -'
			,'error-package-missing' => 'Package "<strong>%s</strong>" existiert nicht.'
			,'error-package-loading' => 'Error loading packages for template #%d'
			,'activity-newpage' =>  'hat die Seite <a href="/tpl/%d">%s</a> erstellt.'
			,'change-notification-owner' =>  '%s hat dein Template <a href="/tpl/%d">%s</a> bearbeitet.'
			,'change-notification-owner-subject' => 'Dein Template wurde von %s bearbeitet'
			,'favorite-page-action' => '[fav]'
			,'unfavorite-page-action' => '[unfav]'
		]
	,'gallery' =>
		[
			 'error-not-logged-in' =>  '<h3>Gallery ist nur f&uuml;r eingeloggte Mitglieder sichtbar!</h3>
			 							<p>Bitte logge Dich ein oder <a href="%s/profil.php?do=anmeldung">erstelle einen neuen Benutzer</a></p>'
			,'error-invalid-album' => 'Ung&uuml;ltiges Album!'
			,'error-no-member' =>  '<h3>Gallery ist gem&auml;ss <a href="https://github.com/zorgch/zorg-verein-docs/blob/master/GV/GV%202018/2018-12-23%20zorg%20GV%202018%20Protokoll.md">GV 2018-Beschluss</a> nur f&uuml;r Vereinsmitglieder sichtbar!</h3>
			 						<p>Du findest das doof? &Auml;ndere es mit deiner Stimme!<br>
			 						Eine Nachricht <a href="/page/vereinsvorstand">an den Vorstand</a> gen&uuml;gt um Mitglied zu werden - und dadurch von weiteren Vorteilen zu profitieren!</p>'
			,'permissions-insufficient' => 'Permission denied for <code>%s</code>'
			,'telegram-dailypic-notification' => 'Daily Pic: %s [%s]'
		]
	,'bugtracker' =>
		[
			 'buglist-headline' =>  '<h1>Bugs und Features Liste</h1>'
			,'newbug-headline' =>  '<h2>Neuen Bug/Request eintragen</h2>'
			,'newcategory-headline' =>  '<h2>Neue Kategorie adden</h2>'
			,'activity-newbug' =>  'hat den Bug <a href="%s/bug/%d">%s</a> gemeldet.'
			,'message-subject-newbug' => '%s hat den Bug #%d neu erstellt'
			,'message-subject-reopenbug' => '%s hat den Bug #%d reopened'
			,'message-subject-resolvedbug' => '%s hat den Bug #%d gelöst'
			,'message-subject-deniedbug' => '%s hat den Bug #%d denied'
			,'message-newbug' => 'Bug Details:<br><blockquote><i>%s</i></blockquote><br><a href="%s/bug/%d">&raquo; %s</a>'
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
			,'next' => '<b>%s</b> ist am Zug'
			,'message-subject' => '-- Addle Game -- (autom. Nachricht)'
			,'message-game-finish' => '<a href="%s/addle.php?show=play&id=%d">Du hast unser Addle-Game %s.</a>'
			,'message-game-unentschieden' => '<a href="%s/addle.php?show=play&id=%d">Unser Addle-Game ging unentschieden aus.</a>'
			,'message-game-forceclosed' => '<a href="%s/addle.php?show=play&id=%d">Du hast unser Addle-Game %s, weil %s nicht mehr weiter gespielt %s.</a>'
			,'message-your-turn' => 'Ich habe meinen Addle-Zug gemacht, du bist wieder dran in <a href="%s/addle.php?show=play&amp;id=%d">unserem Addle Spiel</a>'
			,'unentschieden' => 'unentschieden'
			,'gewinner' => '<b>%s</b> hat gewonnen.'
			,'gewinner-dwz' => '<b>%s</b> hat %s DWZ-Punkte gewonnen.'
		]
	,'hz' =>
		[
			 'activity-newgame' => 'hat ein neues Hunting z Spiel auf der Karte %s er&ouml;ffnet.<br/><br/><a href="%s/?tpl=103&amp;game=%d">Am Spiel als Inspector teilnehmen</a>'
			,'activity-won-mrz' => 'konnt als Mr. z in <a href="%s/?tpl=103&game=%d">diesem Hunting z Spiel</a> erfolgreich vor den Inspectors in die Bahamas fl&uuml;chten!'
			,'activity-won-inspectors-me' => 'wir haben als Inspectors <a href="%s/?tpl=103&game=%d">in diesem Game</a> Mr. z erfolgreich festgenommen!'
			,'activity-won-inspectors-them' => ' & seine Kumpels haben als Inspectors <a href="%s/?tpl=103&game=%d">in diesem Game</a> Mr. z erfolgreich festgenommen!'
			,'activity-joingame' => 'ist <a href="%s?tpl=103&game=%d">diesem Hunting z Spiel</a> als Inspector beigetreten.'
			,'unknown-map' => 'Invalid map "%s"'
			,'unknown-start' => 'Cannot assign start station in game "%d"'
			,'invalid-turn' => 'invalid turn type "%s"'
			,'invalid-ticket' => 'invalid ticket type "%s"'
			,'message-subject' => '-- Hunting z -- (autom. Nachricht)'
			,'message-your-turn-mrz' => 'Du bist wieder an der Reihe um als Mr. z im <a href="%1$s/?tpl=103&game=%2$d">Hz Spiel #%2$d</a> die Inspectors auszutricksen'
			,'message-your-turn-inspectors' => 'Du bist wieder an der Reihe um Mr. z im <a href="%1$s/?tpl=103&game=%2$d">Hunting z Spiel #%2$d</a> zu jagen'
			,'message-game-won-mrz' => 'Du hast <a href="%s/?tpl=103&game=%d">dieses Hunting z Spiel</a> als Mister z <b>gewonnen</b>.'
			,'message-game-lost-mrz' => 'Du hast <a href="%s/?tpl=103&game=%d">dieses Hunting z Spiel</a> als Mister z <b>verloren</b>.'
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
	,'peter' =>
		[
			 'waiting-for-num-players' => 'Warten auf %d weitere Mitspieler...'
			,'activity-newgame' => 'mischelt die Karten für einen <a href="%s/peter.php?game=%d">neuen «Peter» Jass</a>.'
			,'activity-won' => 'hat <a href="%s/peter.php?game=%d">diese Runde</a> «Peter» für sich entschieden.'
		]
	,'poll' =>
		[
			 'invalid-poll_id' => 'Invalid Poll id "%s"'
			,'activity-new-poll' => 'm&ouml;chte gerne wissen, ob...<br><br>{poll id=%d}'
		]
	,'util' =>
		[
			 'array2d_sort-invalid-parameter' => 'Invalid parameter "%s" for array2d_sort.'
			,'htmlcolor2array-invalid-parameter' => 'Invalid color "%s" for htmlcolor2array.'
			,'smarty_brackets_ok-invalid-brackets' => 'ERROR: Ungültige Klammernsetzung &lbrace; oder &rbrace; in der Nähe von: <br><pre>%s</pre><br>Template wurde NICHT gespeichert!'
			,'invalid-array' => 'Invalid array type for <pre>%s</pre>'
		]
	,'event' =>
		[
			 'telegram-event-notification' => '🔜 %s'
			,'error-invalid-hours' => '[WARN] <%s:%d> Starts in "%s" hours is no valid integer value!'
			,'error-upcoming-event' => '[NOTICE] <%s:%d> No upcoming Event found within %d hours'
			,'error-googlemapsapi-geocode' => '[NOTICE] <%s:%d> $googleMapsApi->geocode(): no result'
		]
	,'apod' =>
		[
			 'apod-pic-comment' => '<h4><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></h4><blockquote>%s</blockquote><a href="%s" target="_blank" rel="noopener noreferrer">© %s</a>'
		]
	,'stl' =>
		[
			 'activity-newgame' => 'hat ein neues Shoot the Lamber-Spiel &laquo;%1$s&raquo; er&ouml;ffnet für %2$d vs. %2$d. <br/><a href="%3$s/stl.php?do=game&amp;game_id=%4$d">Jetzt joinen!</a>'
			,'activity-won-gelb' => 'hat Team Gelb im STL-Spiel <a href="%s/stl.php?do=game&game_id=%d">%s</a> zum Sieg gef&uuml;hrt!'
			,'activity-won-gruen' => 'hat Team Gr&uuml;n im STL-Spiel <a href="%s/stl.php?do=game&game_id=%d">%s</a> zum Sieg gef&uuml;hrt!'
			,'activity-joingame' => 'ist dem STL-Spiel <a href="%s/stl.php?do=game&game_id=%d">%s</a> gejoined.'
		]
	,'activity' =>
		[
			 'test' => 'hat eine Test Activity im Bereich %s generiert.'
			,'telegram-notification' => '<b>%s</b> %s'
		]
	,'stockbroker' =>
		[
			 'message-subject' => '-- Stockbroker -- (autom. Nachricht)'
			,'message-stock-warning' => '<p>Stockbroker Warning</p><br><a href="%1$s/?tpl=173&symbol=%2$s">Stock Information für %2$s</a><br>%2$s ist %3$g %4$g (aktueller Kurs: %5$g)'
		]
	,'verein_mailer' =>
		[
			 'webview-link' => "Diese E-Mail in all it's glory anschauen dooooo:\n%s/verein_mailer.php?mail=%s&user=%s&hash=%s"
		]
	,'wetten' =>
		[
			 'activity-neuewette' => '<a href="%s/wetten.php?id=%d">behauptet</a> dass «%s» - stimmst du zu, oder wettest du dagegen?'
			,'activity-wette-done' => 'hat <a href="%s/wetten.php?id=%d">seine Wette</a> als entschieden markiert. Zeit den Wetteinsatz einzulösen!'
		]
	,'rezepte' =>
		[
			 'activity-new' => 'hat ein <a href="%s/tpl/129?rezept_id=%d">neues Rezept verraten</a> - lönds eu schmeckä!'
		]
	,'books' =>
		[
			 'activity-new' => 'hat das <a href="%s/books.php?do=show&book_id=%d">Buch «%s»</a> neu im Regal 👀'
		]
];
