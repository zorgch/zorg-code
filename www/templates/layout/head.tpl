<!DOCTYPE html>
<html lang="de">
	<head>{if $daytime eq ''}{if $sun == 'up'}{assign var=daytime value=day}{else}{assign var=daytime value=night}{/if}{/if}
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="application-name" content="zorg.ch Website"/>
		<meta name="geo.position" content="47.4233;9.37">
		<meta name="geo.region" content="CH-SG">
		<meta name="geo.placename" content="St. Gallen">
		<meta name="ICBM" content="47.4233, 9.37">
		<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
		<title>{$tplroot.page_title}{$smarty.const.PAGETITLE_SUFFIX}</title>
		<meta name="twitter:card" content="summary">{*summary_large_image*}
		<meta name="twitter:title" content="{$tplroot.page_title}{$smarty.const.PAGETITLE_SUFFIX}">
		<meta property="og:title" content="{$tplroot.page_title}{$smarty.const.PAGETITLE_SUFFIX}">
		{if $smarty.const.TWITTER_NAME != ''}
			<meta name="twitter:site" content="{$smarty.const.TWITTER_NAME}">
			<meta name="twitter:creator" content="{$smarty.const.TWITTER_NAME}">
		{/if}
		<meta property="og:site_name" content="{$smarty.const.SITE_HOSTNAME}">
		<meta property="og:url" content="{$smarty.const.SITE_URL}{$smarty.server.REQUEST_URI}">
		<meta property="og:type" content="website">
		{if $tplroot.meta_description != ''}
		{assign var=meta_description value=$tplroot.meta_description|truncate:155:'…'}
			<meta name="twitter:description" content="{$meta_description}">
			<meta property="og:description" content="{$meta_description}">
			<meta itemprop="description" content="{$meta_description}">
			<meta name="description" content="{$meta_description}">
		{/if}
		{if $tplroot.page_image != ''}
		{*assign var=page_image = value='{$smarty.const.SITE_URL}/images/zorg.jpg'*}
			<meta name="twitter:image" content="{$tplroot.page_image}">
			<meta property="og:image" content="{$tplroot.page_image}">
		{/if}
		<meta property="fb:app_id" content="{$smarty.const.FACEBOOK_APPID}">
		{if $tplroot.page_link != '' || $tplroot.word == 'home' || $tplroot.id == 23}
		<link rel="canonical" href="{$smarty.const.SITE_URL}{$tplroot.page_link}" />
		{/if}
		{include file="file:layout/partials/head/favicons.tpl" scope=parent}
		<link rel="stylesheet" type="text/css" href="{$smarty.const.CSS_DIR}css.php?v=4-0-1&layout={$daytime}{if $tplroot.sidebar_tpl || $sidebarHtml <> ''}&sidebar=true{/if}" >
		<script src="{$smarty.const.JS_DIR}zorg.js?v=4-0-1"></script>
		<script src="{$smarty.const.JS_DIR}ie11cssproperties.min.js"></script>
		<script src="{$smarty.const.JS_DIR}highlight-js/highlight.pack.js"></script>
		<link class="codestyle" rel="stylesheet" href="{$smarty.const.JS_DIR}highlight-js/styles/github-gist.css">
		{*<link rel="stylesheet" href="{$smarty.const.CSS_DIR}fileicon.min.css">*}

		<!-- Webfonts -->
		<link rel="stylesheet" href="{$smarty.const.CSS_DIR}fonts/segoe-ui.css">
		<link rel="stylesheet" href="{$smarty.const.CSS_DIR}fonts/iosevka-web.css">

		<!-- RSS Feeds -->
		<link rel="alternate" type="application/rss+xml" title="RSS{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=forum" />
		<link rel="alternate" type="application/rss+xml" title="Forum Feed{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=forum&board=f" />
		<link rel="alternate" type="application/rss+xml" title="Events Feed{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=forum&board=e" />
		<link rel="alternate" type="application/rss+xml" title="Gallery Feed{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=forum&board=i" />
		<link rel="alternate" type="application/rss+xml" title="Rezepte Feed{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=forum&board=r" />
		<link rel="alternate" type="application/rss+xml" title="Neuste Activities{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=activities" />
	</head>

	{* Wenn es ein eingeloggter User ist, wird im Fenstertitel die Anzahl Unreads angezeigt... *}
	<body onload="init()">
		<header class="zorghead" {if $tplroot.write_rights neq '' && tpl_permission($tplroot.write_rights, $tplroot.owner)}onDblClick="document.location.href='{edit_url}';"{/if}>
			{if $user->id}{user_notifications assignto='myUpdates'}
			<div class="notifications">
				<ul id="notifications-list">{*foreach from=$myUpdates item=notification name=notifications*}
					{if $myUpdates.messages.unread > 0}<li id="messages"><a href="/profil.php?user_id={$user->id}">{$myUpdates.messages.unread|quantity:"Message":"Messages"}</a></li>{/if}
					{if $myUpdates.comments.unread > 0}<li id="unreads"><a href="/actions/comment_gotolastunread.php">{$myUpdates.comments.unread|quantity:"Comment":"Comments"}</a></li>{/if}
					{if $myUpdates.events.new|count > 0}<li id="events">{link tpl=158 param="event_id=`$myUpdates.events.new[0].id`"}{$myUpdates.events.new|count|quantity:"new Event":"new Events"}{/link}</li>{/if}
					{if $myUpdates.addle.open > 0}<li id="addles"><a href="/addle.php">{$myUpdates.addle.open|quantity:"Addlezug":"Addlezüge"}</a></li>{/if}
					{if $myUpdates.peter.open > 0}<li id="peter"><a href="/peter.php?game_id={$myUpdates.peter.open}">{$myUpdates.peter.open} Peter</a></li>{/if}
					{if $myUpdates.hz.open > 0}<li id="hzzuege">{link tpl=103}{$myUpdates.hz.open|quantity:"Hz Zug":"Hz Züge"}{/link}</li>{/if}
					{if $myUpdates.hz.new > 0}<li id="hzgames">{link tpl=100}{$myUpdates.hz.new|quantity:"offenes Hunting z Spiel":"offene Hunting z Spiele"}{/link}</li>{/if}
					{if $myUpdates.go.open > 0}<li id="gozuege">{link tpl=699}{$myUpdates.go.open|quantity:"GO Zug":"GO Züge"}{/link}</li>{/if}
					{if $myUpdates.go.new}<li id="gogames">{link tpl=698}{$myUpdates.go.new|quantity:"GO-Herausforderung":"GO-Herausforderungen"}{/link}</li>{/if}
					{if $myUpdates.chess.open > 0}<li id="chess">{link tpl=139}{$myUpdates.chess.open|quantity:"Schachzug":"Schachzüge"}{/link}</li>{/if}
					{if $myUpdates.stl.open.num > 0}<li id="stlzuege"><a href="/stl.php?do=game&game_id={$myUpdates.stl.open.id}">{$myUpdates.stl.open.num|quantity:"STL-Shot":"STL-Shots"}</a></li>{/if}
					{if $myUpdates.stl.new.num > 0}<li id="stlgames"><a href="/stl.php?do=game&game_id={$myUpdates.stl.new.id}">Join {$myUpdates.stl.new.num|quantity:"open STL-Game":"open STL-Games"}</a></li>{/if}
					{if $myUpdates.rezepte.new}<li id="rezepte">{link tpl=129}{$myUpdates.rezepte.new|quantity:"neues Rezept":"neue Rezepte"}{/link}</li>{/if}
					{if $myUpdates.tauschangebote.new}<li id="angebote">{link tpl=190}{$myUpdates.tauschangebote.new|quantity:"neues Tauschangebot":"neue Tauschangebote"}{/link}</li>{/if}
					{if $user->typ >= 1 && $myUpdates.bugtracker.new > 0}<li id="newbugs"><a href="/bugtracker.php?show[]=new&show[]=open&show[]=notdenied">{$myUpdates.bugtracker.new|quantity:"new Bug":"new Bugs"}</a></li>{/if}
					{if $myUpdates.bugtracker.own > 0}<li id="mybugs"><a href="/bugtracker.php?show[]=own&=open&show[]=assigned&show[]=notdenied&show[]=open">{$myUpdates.bugtracker.own|quantity:"own Bug":"own Bugs"}</a></li>{/if}
					{if $user->typ >= 2 && $myUpdates.bugtracker.open > 0}<li id="openbugs"><a href="/bugtracker.php?show[]=open&show[]=notdenied&show[]=unassigned&show[]=new&show[]=old&show[]=own&show[]=notown">{$myUpdates.bugtracker.open|quantity:"unassigned Bug":"unassigned Bugs"}</a></li>{/if}
					{if $user->typ >= 2 && $num_errors > 0}<li id="sqlerrors">{link tpl=162}{$num_errors|quantity:"error":"errors"}{/link}</li>{/if}
				{*/foreach*}</ul>
			</div>{/if}
			<nobr class="logo"><a href="/" id="top">{if $user->zorger}<img src="{$smarty.const.IMAGES_DIR}logo{if $sun == "down"}_night{/if}.png" border="0" style="max-width: 100%;">{else}{$smarty.const.SITE_HOSTNAME}{/if}</a></nobr>
			<div class="announcements">
				{foreach from=$nextevents item=nextevent}<span class="event">
					<a href="/smarty.php?tpl=158&event_id={$nextevent.id}">
					<span class="name">{$nextevent.name}</span> | 
					{if $nextevent.startdate|date_format:"%d%e%Y" != $nextevent.enddate|date_format:"%d%e%Y"}
						{$nextevent.startdate|date_format:"%d %b"}-{$nextevent.enddate|date_format:"%d %b"}
					{else}
						{$nextevent.startdate|date_format:"%d. %b %HUhr"}
					{/if}
					{if $nextevent.numunread > 0} {$nextevent.numunread} unread{/if}</a>&nbsp;
					{if $user->id}
						{*assign_event_hasjoined event_id=$nextevent.id*}
						{if $event_hasjoined == true}<a class="unjoin" href="/actions/events.php?unjoin={$nextevent.id}&url={$url}">unjoin</a>
						{else}<a class="join" href="/actions/events.php?join={$nextevent.id}&url={$url}">join</a>{/if}
					{/if}
				</span><br>{/foreach}
			</div>

			<aside class="service">
				{include file='file:layout/partials/loginform.tpl' scope=parent}
			</aside>

			<div class="onlineuser" id="onlineuser-list">
				{if count($online_users) > 0}
					{foreach from=$online_users item=userid key=i}
						{$userid|username}
					{/foreach}
				{/if}
			</div>

			<div class="infos">
				<span class="solarstate">
					{if !$user->id}{if $country != ""}<img class="countryflag" src='{$country_image}' alt='{$country}' title='{$country}'>{/if}{/if}
					{* Sunset: *}{if $sun == "up"}<img class="event" src='/images/sunset.jpg' alt='Sunset @ {$sunset}' title='Sunset @ {$sunset}'><nobr class="time">{$sunset} Uhr</nobr>{/if}
					{* Sunrise: *}{if $sun == "down"}<img class="event" src='/images/sunrise.jpg' alt='Sunrise @ {$sunrise}' title='Sunrise @ {$sunrise}'><nobr class="time">{$sunrise} Uhr</nobr>{/if}
				</span>
			</div>
		</header>

		{include file='file:layout/navigation.tpl' scope=parent}

		<main class="main-content">
			{if $error.title <> ''}{include file="file:layout/elements/block_error.tpl" scope=parent}{/if}