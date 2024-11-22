{if $smarty.get.viewas == ''}<form method="post" enctype="multipart/form-data" action="{$form_action}">{/if}
	<input type="hidden" name="do" value="update">
	<div class="row">
		<div class="col-sm-6">
			<h2>Sichtbare Profildaten</h2>
			<div class="input-field">
				<label for="username">Username</label>
				<input type="text" name="username" id="username" maxlength="200" autocomplete="off" value="{$user->username}">
				<span class="input-hint hide-sm-down">⚠️ Achtung: den Username benötigst Du auch für den Login</span>
			</div>
			<div class="input-field">
				<label for="clan_tag">Clantag</label>
				<input type="text" name="clan_tag" id="clan_tag" maxlength="75" autocomplete="off" value="{$user->clantag}">
			</div>
			<div class="input-field">
				<h3>Activities</h3>
				<span class="switch">
					<input type="hidden" id="activities_allow" name="checkbox[activities_allow]" value="{if $user->activities_allow}1{else}0{/if}">
					<input type="checkbox" id="switch-activities_allow" onclick="$(getElementById('activities_allow')).attr('value', this.checked ? '1' : '0')" {if $user->activities_allow}checked{/if}>
					<label for="switch-activities_allow">Aktivitäten von mir anzeigen</label>
				</span>
				<span class="input-hint hide-sm-down"><span class="badge badge-secondary">?</span> Comments, Game Joins, Pic-Votes, etc. von Dir werden im Acitivies-Stream für alle sichtbar angezeigt. Activities werden auch in die zorg Telegram-Gruppe geteilt.</span>
			</div>
		</div>
		<div class="col-sm-6">
			<h3>Userpic</h3>
			{assign var='check_userimage' value=$user->id|@check_userimage}
			<fieldset>
				<table>
					<thead><tr>
						<th class="text-center"><strong>zorg Pic</strong></th>
						{if $check_userimage.type === 'gravatar'}<th class="text-center"><strong>Gravatar Pic</strong></th>{/if}
					</tr></thead>
					<tbody style="vertical-align:top;{if $sun == "down" || $user->zorger}filter: invert(100%);{/if}">
						<tr>
							<td>
								<img src="/data/userimages/{$user->id}.jpg?{$smarty.now}"></td>
								{* userpic.jpg?$smarty.now = holt immer das neuste Userpic (ohne es aus dem Cache auszuliefern) *}
							{if $check_userimage.type === 'gravatar'}<td><img src="/data/userimages/{$user->id}_gravatar.jpg{*$user->image*}">
								<span class="input-hint hide-sm-down"><span class="badge badge-secondary">?</span> Gravatar-Bild zu der hinterlegten E-mail bei <a href="https://gravatar.com" target="_blank">gravatar.com</a></span>
							</td>{/if}
						</tr>
					</tbody>
				</table>
				{if $smarty.get.viewas == ''}<span class="file-button">
					<input type="file" name="image" id="image">
					<label class="button button-dark button-block" for="image">Neues Bild auswählen</label>
					<input type="submit" id="send" name="send" class="button button-block" value="Hochladen" formaction="{$form_action_image_upload}">
				</span>{/if}
			</fieldset>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
			<h2>Persönliche Daten</h2>
			<div class="input-field">
				<label for="email">E-Mail</label>
				<input type="email" name="email" id="email" maxlength="255" autocomplete="off" value="{$user->email}">
			</div>
			<label for="irc_username">IRC Nick</label>
			<div class="input-group">
				<span class="input-addon">#</span>
				<input type="text" name="irc_username" id="irc_username" maxlength="9" autocomplete="off" value="{$user->irc}">
			</div>
			<label for="telegram_chat_id">Telegram Chat-ID</label>
			<div class="input-group">
				<span class="input-addon">@</span>
				<input type="text" name="telegram_chat_id" id="telegram_chat_id" maxlength="35" autocomplete="off" value="{$user->telegram}">
			</div>
			<div class="tabs">
				<span class="input-hint">Anleitung um Deine Telegram Chat-ID zu ermitteln &amp; Benachrichtigungen zu empfangen:</span>
				<nav class="tabs-nav">
					<a href="#step-1">1.</a>
					<a href="#step-2">2.</a>
					<a href="#step-3">3.</a>
					<a href="#step-4">4.</a>
				</nav>
				<div class="tabs-pane" style="min-height:205px;" id="step-1">
					<p>Den <a href="https://web.telegram.org/#/im?p=@userinfobot" target="_blank">@userinfobot</a> in Telegram suchen & anklicken</p>
					<img {if $sun == "down" || $user->zorger}style="filter: invert(100%);"{/if} src="{$smarty.const.IMAGES_DIR}/profile/telegram-chatid-step1.png" alt="Den @userinfobot in Telegram suchen & anklicken" />
				</div>
				<div class="tabs-pane" style="min-height:205px;" id="step-2">
					<p><code>/start</code>-Nachricht dem Bot schicken, oder via Button starten</p>
					<img {if $sun == "down" || $user->zorger}style="filter: invert(100%);"{/if} src="{$smarty.const.IMAGES_DIR}/profile/telegram-chatid-step2.png" alt="/start-Nachricht dem Bot schicken, oder via Button starten" />
				</div>
				<div class="tabs-pane" style="min-height:205px;" id="step-3">
					<p>Den Wert von <code>id: <mark>xxxxxxx</mark></code> verwenden als Chat-ID</p>
					<img {if $sun == "down" || $user->zorger}style="filter: invert(100%);"{/if} src="{$smarty.const.IMAGES_DIR}/profile/telegram-chatid-step3.png" alt="Den Wert vom id: Feld verwenden als Chat-ID" />
				</div>
				<div class="tabs-pane" style="min-height:205px;" id="step-4">
					<p>Jetzt mit der Bärbel <a href="https://web.telegram.org/#/im?p=@zBarbaraHarris_bot" target="_blank">@zBarbaraHarris_bot</a> auf Telegram connecten, damit sie dir Messages schicken darf. Aktiviere sie via <code>/start</code>.</p>
					<img {if $sun == "down" || $user->zorger}style="filter: invert(100%);"{/if} src="{$smarty.const.IMAGES_DIR}/profile/telegram-chatid-step4.png" alt="Mit @zBarbaraHarris_bot connecten und sie via /start-Nachricht aktivieren" />
				</div>
			</div>
		</div>
		<div class="col-sm-6 pad-b-md">
			<h3>Userinformationen</h3>
			{assign_array var='usertypes' value="array(0 => array('group' => 'Nicht eingeloggt', 'badge' => 'badge-secondary'), 1 => array('group' => 'Normaler User', 'badge' => 'badge-primary'), 2 => array('group' => 'Schöne', 'badge' => 'badge-success'))"}
			{assign var='usertype' value=$user->typ}
			<span class="badge badge-info">ID: {$user->id}</span>
			<span class="badge {$usertypes[$usertype].badge}">{$usertypes[$usertype].group}</span>
			{if $user->z_gremium}<span class="badge badge-dark">[z] Gremium</span>{/if}
			{if $user->vereinsmitglied}<span class="badge badge-primary">zorg Verein {$user->vereinsmitglied}</span>{else}<a href="/page/verein" target="_blank" class="badge badge-secondary">kein zorg Vereinsmitglied</a>{/if}
			{if $user->typ >= 2}<a href="/page/sql-query-tracker" target="_blank" class="badge {if $user->sql_tracker}badge-info">SQL-Query Tracker ON{else}badge-secondary">SQL-Query Tracker OFF{/if}</a>{/if}
			{if $user->vereinsmitglied neq ''}
				<h4 style="margin-top: 1.4em;">Personenbezogene Daten</h4>
				<div class="input-field">
					<label for="firstname">Vorname</label>
					<input type="text" maxlength="75" autocomplete="off" value="{$user->firstname}" disabled>
				</div>
				<div class="input-field">
					<label for="firstname">Nachname</label>
					<input type="text" maxlength="75" autocomplete="off" value="{$user->lastname}" disabled>
				</div>
			{/if}
		</div>
	</div>
	<div class="row pad-b-xl">
		<div class="col">
			{if $smarty.get.viewas == ''}<input type="submit" id="send" name="send" class="button" value="Änderungen speichern" formaction="{$form_action}">{/if}
		</div>
	</div>

	<div class="row">
		<div class="col">
			<h3>Benachrichtigungen</h3>
			<p>Hier kannst Du einstellen bei welchen Aktionen Du wie benachrichtigt werden möchtest.<br>
				Für Telegram-Messages musst Du deinen Telegram Username speichern!</p>
		</div>
	</div>
	<div class="row hide-sm-down">
		<div class="col-sm-3"><h5>&nbsp;</h5></div>
		<div class="col-sm-3 pad-l-xl">
			<span class="switch switch-xs">
				<input type="checkbox" id="switch-message-all">
				<label for="switch-message-all">alle</label>
			</span>
		</div>
		<div class="col-sm-3 pad-l-xl">
			<span class="switch switch-xs">
				<input type="checkbox" id="switch-telegram-all">
				<label for="switch-telegram-all">alle</label>
			</span>
		</div>
		<div class="col-sm-3 pad-l-xl">
			<span class="switch">
				<span class="switch switch-xs">
				<input type="checkbox" id="switch-email-all">
				<label for="switch-email-all">alle</label>
			</span>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-3"><h5>Abonnierte Forum-Threads</h5></div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-message-subscriptions" name="notifications[subscriptions][message]" value="true" {if $user->notifications.subscriptions.message}checked{/if}>
				<label for="switch-message-subscriptions">Message</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-telegram-subscriptions" name="notifications[subscriptions][telegram]" value="true" {if $user->notifications.subscriptions.telegram}checked{/if}>
				<label for="switch-telegram-subscriptions">Telegram</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-email-subscriptions" name="notifications[subscriptions][email]" value="true"  {if $user->notifications.subscriptions.email}checked{/if}>
				<label for="switch-email-subscriptions">E-Mail</label>
			</span>
		</div>
	</div>
	<div class="row pad-t-sm">
		<div class="col-sm-3"><h5>Commenting <a>@mentions</a></h5></div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-message-mentions" name="notifications[mentions][message]" value="true" {if $user->notifications.mentions.message}checked{/if}>
				<label for="switch-message-mentions">Message</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-telegram-mentions" name="notifications[mentions][telegram]" value="true" {if $user->notifications.mentions.telegram}checked{/if}>
				<label for="switch-telegram-mentions">Telegram</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-email-mentions" name="notifications[mentions][email]" value="true"  {if $user->notifications.mentions.email}checked{/if}>
				<label for="switch-email-mentions">E-Mail</label>
			</span>
		</div>
	</div>
	<div class="row pad-t-sm">
		<div class="col-sm-3 text-muted"><h5>Events Erinnerungen</h5></div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-message-events" name="notifications[events][message]" value="" {if $user->notifications.events.message}checked{/if} disabled>
				<label for="switch-message-events">Message</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-telegram-events" name="notifications[events][telegram]" value="" {if $user->notifications.events.telegram}checked{/if} disabled>
				<label for="switch-telegram-events">Telegram</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-email-events" name="notifications[events][email]" value="" {if $user->notifications.events.email}checked{/if} disabled>
				<label for="switch-email-events">E-Mail</label>
			</span>
		</div>
	</div>
	<div class="row pad-t-sm">
		<div class="col-sm-3"><h5>Games Spielzüge</h5></div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-message-games" name="notifications[games][message]" value="true" {if $user->notifications.games.message}checked{/if}>
				<label for="switch-message-games">Message</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-telegram-games" name="notifications[games][telegram]" value="true" {if $user->notifications.games.telegram}checked{/if}>
				<label for="switch-telegram-games">Telegram</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-email-games" name="notifications[games][email]" value="true" {if $user->notifications.games.email}checked{/if}>
				<label for="switch-email-games">E-Mail</label>
			</span>
		</div>
	</div>
	<div class="row pad-t-sm">
		<div class="col-sm-3 text-muted"><h5>Stockbroker Alerts</h5></div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-message-stockbroker" name="notifications[stockbroker][message]" value="" {if $user->notifications.stockbroker.message}checked{/if} disabled>
				<label for="switch-message-stockbroker">Message</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-telegram-stockbroker" name="notifications[stockbroker][telegram]" value=""{if $user->notifications.stockbroker.telegram}checked{/if} disabled>
				<label for="switch-telegram-stockbroker">Telegram</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-email-stockbroker" name="notifications[stockbroker][email]" value="" {if $user->notifications.stockbroker.email}checked{/if} disabled>
				<label for="switch-email-stockbroker">E-Mail</label>
			</span>
		</div>
	</div>
	<div class="row pad-t-sm">
		<div class="col-sm-3"><h5>Bugtracker Statusupdates</h5></div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-message-bugtracker" name="notifications[bugtracker][message]" value="true" {if $user->notifications.bugtracker.message}checked{/if}>
				<label for="switch-message-bugtracker">Message</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-telegram-bugtracker" name="notifications[bugtracker][telegram]" value="true"{if $user->notifications.bugtracker.telegram}checked{/if}>
				<label for="switch-telegram-bugtracker">Telegram</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-email-bugtracker" name="notifications[bugtracker][email]" value="true" {if $user->notifications.bugtracker.email}checked{/if}>
				<label for="switch-email-bugtracker">E-Mail</label>
			</span>
		</div>
	</div>
	<div class="row pad-t-sm">
		<div class="col-sm-3"><h5>Neue zorg Messages</h5></div>
		<div class="col-sm-3 hide-xs-down">&nbsp;</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-telegram-messages" name="notifications[messagesystem][telegram]" value="true" {if $user->notifications.messagesystem.telegram}checked{/if}>
				<label for="switch-telegram-messages">Telegram</label>
			</span>
		</div>
		<div class="col-sm-3">
			<span class="switch">
				<input type="checkbox" id="switch-email-messages" name="notifications[messagesystem][email]" value="true" {if $user->notifications.messagesystem.email}checked{/if}>
				<label for="switch-email-messages">E-Mail</label>
			</span>
		</div>
	</div>
	{* NEW FEATURE / Not yet implemented
	<div class="row pad-t-sm">
		<div class="col">
			<div class="input-field">
				<h4>Browser Notifications</h4>
				<span class="switch">
					<input type="checkbox" id="browser_notifications" disabled>
					<label for="switch-browser_notifications">Browser notifications aktivieren</label>
				</span>
				<span class="input-hint hide-sm-down"><span class="badge badge-secondary">?</span> native Webbrowser Notifications werden nur angezeigt, wenn Du zorg in einem Tab geöffnet hast.</span>
			</div>
		</div>
	</div> *}
	<div class="row pad-t-sm pad-b-xl">
		<div class="col">
			{if $smarty.get.viewas == ''}<input type="submit" id="send" name="send" class="button" value="Änderungen speichern" formaction="{$form_action}">{/if}
		</div>
	</div>

	<div class="row">
		<div class="col">
			<h2>zorg Settings</h2>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-3">
			<h3>Games</h3>
		</div>
		<div class="col-sm-4">
			<div class="input-field">
				<input type="hidden" id="addle" name="checkbox[addle]" value="{if $user->addle === '1'}1{else}0{/if}">
				<label for="switch-addle">Ich will <input type="checkbox" id="switch-addle" onclick="$(getElementById('addle')).attr('value',this.checked ? '1' : '0');" {if $user->addle === '1'}checked{/if}> <strong class="text-primary">Addle</strong> spielen</label>
			</div>
		</div>
		<div class="col-sm-5">
			<div class="input-field">
				<input type="hidden" id="chess" name="checkbox[chess]" value="{if $user->chess === '1'}1{else}0{/if}">
				<label for="switch-chess" class="text-muted">Ich will <input type="checkbox" id="switch-chess" onclick="$(getElementById('chess')).attr('value',this.checked ? '1' : '0');" {if $user->chess === '1'}checked{/if} disabled> <strong class="text-muted">Schach</strong> spielen</label>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col">
			<h3>Forum</h3>
			<fieldset>
				<legend>Abonnierte Forum-Boards</legend>
				{forum_boards boards=$user->forum_boards_unread updateable='unreads'}
				<span class="input-hint hide-sm-down"><span class="badge badge-secondary">?</span> für die gewählten Boards werden Dir alle ungelesenen Comments angezeigt.</span>
			</fieldset>
			<div class="input-field">
				<span class="switch">
					<input type="hidden" id="show_comments" name="checkbox[show_comments]" value="{if $user->show_comments === '1'}1{else}0{/if}">
					<input type="checkbox" id="switch-show_comments" onclick="$(getElementById('show_comments')).attr('value', this.checked ? '1' : '0')" {if $user->show_comments}checked{/if}>
					<label for="switch-show_comments">Comments standardmässig anzeigen</label>
				</span>
				<span class="input-hint hide-sm-down"><span class="badge badge-secondary">?</span> wenn 'off' dann ist das Commenting initial immer zugeklappt.</span>
			</div>
			<div class="input-field">
				<label for="email">Anzeigetiefe für Comments in Forum-Threads</label>
				<output id="forummaxthreadSelection" class="badge mar-l-md">{$user->maxdepth}</output>
				<input type="range" name="forummaxthread" id="forummaxthread" class="w-50 pad-l-lg" list="tickmarks" min="1" max="50" step="1" value="{$user->maxdepth}" oninput="javascript:forummaxthreadSelection.value = forummaxthread.value">
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col">
			<h3>Layout</h3>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-3">
			<h5>[z] Layout</h5>
		</div>
		<div class="col-sm-2">
			<div class="input-field">
				<label class="pad-xs"><input type="radio" name="zorger" value="0" {if !$user->zorger}checked{/if}> zorg (modern)</label>
			</div>
		</div>
		<div class="col-sm">
			<div class="input-field">
				<label class="pad-xs">&nbsp;<input type="radio" name="zorger" value="1" {if $user->zorger}checked{/if}> zooomclan (retro)</label>
			</div>
		</div>
	</div>
	{* @deprecated
	<div class="row">
		<div class="col-sm-3">
			<h5>Menu Layout</h5>
		</div>
		<div class="col-sm-9">
			<div class="input-field">
				<label class="pad-xs"><input type="radio" name="menulayout" value="" {if $user->menulayout === ''}checked{/if}> Standard</label>
				<label class="pad-xs">&nbsp;<input type="radio" name="menulayout" value="1" {if $user->menulayout === '1'}checked{/if}> Style A</label>
				<label class="pad-xs">&nbsp;<input type="radio" name="menulayout" value="2" {if $user->menulayout === '2'}checked{/if}> Style B</label>
				<label class="pad-xs">&nbsp;<input type="radio" name="menulayout" value="3" {if $user->menulayout === '3'}checked{/if}> Style C</label>
			</div>
		</div>
	</div> *}
	<div class="row">
		<div class="col-sm-3">
			<h5>My Menu</h5>
			<span class="input-hint hide-sm-down"><span class="badge badge-secondary">?</span> dieses Menu wird Dir immer angezeigt</span>
		</div>
		<div class="col-sm-9">
			<div class="input-field">
				{if $smarty_menus|@count > 0}
					<select name="mymenu" id="mymenu">
						<option value="null" {if $user->mymenu === ''}selected{/if}>-- Menu auswählen --</option>
						{foreach from=$smarty_menus item=menu name=smarty_menus_foreach}
						<option value="{$menu.id}" {if $user->mymenu === $menu.id}selected{/if}>{$menu.name} [tpl #{$menu.id}]</option>
						{/foreach}
					</select>
				{else}
					<code>Keine Menüs zur Auswahl</code>
				{/if}
			</div>
			<div>
				{if $user->mymenu}<strong>Vorschau:</strong>
				{include file="tpl:`$user->mymenu`"}{/if}
			</div>
		</div>
	</div>
	<div class="row pad-t-sm pad-b-xl">
		<div class="col">
			{if $smarty.get.viewas == ''}<input type="submit" id="send" name="send" class="button" value="Änderungen speichern" formaction="{$form_action}">{/if}
		</div>
	</div>
{if $smarty.get.viewas == ''}</form>{/if}

{if $smarty.get.viewas == ''}<form method="post" enctype="multipart/form-data" action="{$form_action}">{/if}
	<input type="hidden" name="do" value="change_password">
	<div class="row">
		<div class="col">
			<h2>Login</h2>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<fieldset>
				<legend>Passwort ändern</legend>
				<div class="input-field">
					<label for="old_pass">Aktuelles Passwort</label>
					<input type="password" name="old_pass" id="old_pass" maxlength="75" autocomplete="off" value="">
				</div>
				<hr>
				<div class="input-field">
					<label for="new_pass">Neues Passwort</label>
					<input type="password" name="new_pass" id="new_pass" maxlength="75" autocomplete="off" value="">
				</div>
				<div class="input-field">
					<label for="new_pass2">Neues Passwort wiederholen</label>
					<input type="password" name="new_pass2" id="new_pass2" maxlength="75" autocomplete="off" value="">
				</div>
				{if $smarty.get.viewas == ''}<input type="submit" id="send" name="send" class="button" value="Passwort ändern">{/if}
			</fieldset>
		</div>
	</div>
{if $smarty.get.viewas == ''}</form>{/if}

{*include file="tpl:189"*}
{if $smarty.get.viewas == ''}<form method="post" enctype="multipart/form-data" action="/actions/profil.php?do=aussperren">{/if}
	<div class="row">
		<div class="col">
			<h2>Force-Logout</h2>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<fieldset>
				<legend>Mich von {$smarty.const.SITE_HOSTNAME} aussperren bis…</legend>
				<h5>Datum</h5>
				<div class="input-group">
					{html_select_date start_year="`$smarty.now`"|date_format:"%Y" end_year="+1 year"|date_format:"%Y" prefix="aussperren" time="`$smarty.now`"}
				</div>
				<h5>Uhrzeit</h5>
				<div class="input-group">
					{html_select_time time="`$smarty.now`" display_minutes=false display_seconds=false prefix="aussperren"}
				</div>
				{assign_array var="error" value="array('type'=>'warn', 'title'=>'⚠️ Achtung: unwiderruflich!', 'message'=>'Du kannst Dich erst nach Ablauf der gesetzten Frist wieder einloggen.')"}
				{include file="file:layout/elements/block_error.tpl"}
				{if $smarty.get.viewas == ''}<input type="submit" id="send" name="send" class="button button-block button-danger" value="Aussperren" {if $sun == "down" || $user->zorger}style="filter: invert(100%);"{/if}>{/if}
			</fieldset>
		</div>
	</div>
{if $smarty.get.viewas == ''}</form>{/if}
<script>{literal}
/** Select-all Checkboxes */
$("input[id$='-all']").on('click', function(){
	var switchType = $(this).attr('id').replace('-all', '');
	$("input:checkbox[id^='"+switchType+"']").prop('checked', $(this).is(':checked'));
	$("input:checkbox[id^='"+switchType+"']").each(function(){
		if ($(this).prop('disabled') === true) {
			$(this).prop('checked',false);
		}
	});
});
{/literal}</script>
