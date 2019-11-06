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
		<meta name="viewport" content="width=device-width, initial-scale=1">
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
		{include file="file:layout/partials/head/favicons.tpl"}
		<link rel="stylesheet" type="text/css" href="{$smarty.const.CSS_DIR}css.php?v=4-0-0&layout={$daytime}{if $tplroot.sidebar_tpl || $sidebarHtml <> ''}&sidebar=true{/if}" >
		<script src="{$smarty.const.JS_DIR}zorg.js?v=4-0-0"></script>
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
		<header class="zorghead" {if $tplroot.write_rights neq '' && tpl_permission($tplroot.write_rights, $tplroot.owner)}onDblClick="document.location.href='{edit_url}';"{/if}><a id="top"></a>
		{include_php file="file:header.php"}
			{if $user->id}
				{if $new_messages > 0}{capture append=myUpdates}<li id="messages"><a href="/profil.php?user_id={$user->id}">{$new_messages|quantity:"Message":"Messages"}</a></li>{/capture}{/if}
				{capture append=myUpdates}<li id="unreads">{if $new_comments>0}<a href="/actions/comment_gotolastunread.php">{$new_comments|quantity:"Comment":"Comments"}</a>{/if}</li>{/capture}
				{if $user->typ > 0 && $num_new_events > 0}{capture append=myUpdates}<li id="events">{link tpl=158 param="event_id=`$event_newest.id`"}{$num_new_events|quantity:"new Event":"new Events"}{/link}</li>{/capture}{/if}
				{if $open_addle>0}{capture append=myUpdates}<li id="addles"><a href="/addle.php">{$open_addle|quantity:"Addlezug":"Addlezüge"}</a></li>{/capture}{/if}
				{get_peter_zuege}{if $peter_zuege[0] > 0}{capture append=myUpdates}<li id="peter"><a href="/peter.php?game_id={$peter_zuege[1]}">{$peter_zuege[0]} Peter</a></li>{/capture}{/if}
				{if $hz_running_games>0}{capture append=myUpdates}<li id="hzzuege">{link tpl=103}{$hz_running_games|quantity:"Hz Zug":"Hz Züge"}{/link}</li>{/capture}{/if}
				{if $hz_open_games>0}{capture append=myUpdates}<li id="hzgames">{link tpl=100}{$hz_open_games|quantity:"offenes Hunting z Spiel":"offene Hunting z Spiele"}{/link}</li>{/capture}{/if}
				{if $go_running_games>0}{capture append=myUpdates}<li id="gozuege">{link tpl=699}{$go_running_games|quantity:"GO Zug":"GO Züge"}{/link}</li>{/capture}{/if}
				{if $go_open_games>0}{capture append=myUpdates}{link tpl=698}<li id="gogames">{$go_open_games|quantity:"GO-Herausforderung":"GO-Herausforderungen"}{/link}</li>{/capture}{/if}
				{get_stl_games}{capture append=myUpdates}<li id="stlzuege">{$stl_shots}</li>{/capture}
				{capture append=myUpdates}<li id="stlgames">{$stl_open_games}</li>{/capture}
				{if $new_rezepte>0}{capture append=myUpdates}<li id="rezepte">{link tpl=129}{$new_rezepte|quantity:"neues Rezept":"neue Rezepte"}{/link}</li>{/capture}{/if}
				{if $user->typ > 0 && $num_new_tauschangebote > 0}{capture append=myUpdates}<li id="angebote">{link tpl=190}{$num_new_tauschangebote|quantity:"neues Tauschangebot":"neue Tauschangebote"}{/link}</li>{/capture}{/if}
				{if $user->typ >= 1 && $new_bugs > 0}{capture append=myUpdates}<li id="newbugs"><a href="/bugtracker.php?show[]=new&show[]=open&show[]=notdenied">{$new_bugs|quantity:"new Bug":"new Bugs"}</a></li>{/capture}{/if}
				{if $own_bugs>0}{capture append=myUpdates}<li id="mybugs"><a href="/bugtracker.php?show[]=own&=open&show[]=assigned&show[]=notdenied&show[]=open">{$own_bugs|quantity:"own Bug":"own Bugs"}</a></li>{/capture}{/if}
				{if $user->typ == 2 && $open_bugs>0}{capture append=myUpdates}<li id="openbugs"><a href="/bugtracker.php?show[]=open&show[]=notdenied&show[]=unassigned&show[]=new&show[]=old&show[]=own&show[]=notown">{$open_bugs|quantity:"unassigned Bug":"unassigned Bugs"}</a></li>{/capture}{/if}
				{if $user->typ == 2 && $num_errors > 0}{capture append=myUpdates}<li id="sqlerrors">{link tpl=162}{$num_errors|quantity:"error":"errors"}{/link}</li>{/capture}{/if}
				<div class="notifications">
				{if $user->id}<ul id="notifications-list">{foreach from=$myUpdates item=notification name=notifications}
					{if $notification != ''}{$notification}{/if}
				{/foreach}</ul>{/if}
				</div>
			{/if}{*if $user->zorger}{include file='tpl:56'}{else}{include file='tpl:672'}{/if*}
			<nobr class="logo"><a href="/">{if $user->zorger}zooomclan{else}{$smarty.const.SITE_HOSTNAME}{/if}</a></nobr>
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
						{assign_event_hasjoined event_id=$nextevent.id}
						{if $event_hasjoined == true}<a class="unjoin" href="/actions/events.php?unjoin={$nextevent.id}&url={$url|base64encode}">unjoin</a>
						{else}<a class="join" href="/actions/events.php?join={$nextevent.id}&url={$url|base64encode}">join</a>{/if}
					{/if}
				</span><br>{/foreach}
			</div>

			<aside class="service">
				{include file='file:layout/partials/loginform.tpl'}
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

		<!--nav class="navigation" -->
		{*if $user->mymenu}{include file="tpl:`$user->mymenu`"}{/if*}
		{include file='file:layout/navigation.tpl'}
		
		<main class="main-content">
			{if $error.title <> ''}{include file="file:layout/elements/block_error.tpl"}{/if}