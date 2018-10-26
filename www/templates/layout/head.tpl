<!DOCTYPE html>
{if $sun == 'up'}{assign var=daytime value=day}{else}{assign var=daytime value=night}{/if}
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="geo.position" content="47.4233;9.37">
		<meta name="geo.region" content="CH-SG">
		<meta name="geo.placename" content="St. Gallen">
		<meta name="ICBM" content="47.4233, 9.37">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="twitter:card" content="summary">{*summary_large_image*}
		<meta name="twitter:title" content="{$tplroot.page_title}">
		<meta property="og:title" content="{$tplroot.page_title}">
		<meta itemprop="headline" content="{$tplroot.page_title}">
		{if $smarty.const.TWITTER_NAME != ''}
			<meta name="twitter:site" content="{$smarty.const.TWITTER_NAME}">
			<meta name="twitter:creator" content="{$smarty.const.TWITTER_NAME}">
		{/if}
		<meta property="og:site_name" content="{$smarty.const.SITE_HOSTNAME}">
		<meta property="og:url" content="{$smarty.const.SITE_URL}{$smarty.server.REQUEST_URI}">
		<meta property="og:type" content="website">
		{*if $meta_description != ''}
			{assign var=meta_description value=$string|truncate:156:'…'}
			<meta name="twitter:description" content="{$meta_description}">
			<meta property="og:description" content="{$meta_description}">
			<meta itemprop="description" content="{$meta_description}">
			<meta name="description" content="{$meta_description}">
		{/if*}
		{*assign var=page_image = value='{$smarty.const.SITE_URL}/images/zorg.jpg'}
		<meta name="twitter:image" content="<?php echo $page_image; ?>">
		<meta property="og:image" content="<?php echo $page_image; ?>">
		<meta itemprop="image" content="<?php echo $page_image; ?>">*}
		<meta property="fb:app_id" content="{$smarty.const.FACEBOOK_APPID}">
		<title>{$tplroot.page_title}{$smarty.const.PAGETITLE_SUFFIX}</title>
		<link rel="shortcut icon" href="{$smarty.const.IMAGES_DIR}favicons/fav_{$sun}.ico" type="image/x-icon">
		<meta name="msapplication-square70x70logo" content="{$smarty.const.IMAGES_DIR}favicons/smalltile.png">
		<meta name="msapplication-square150x150logo" content="{$smarty.const.IMAGES_DIR}favicons/mediumtile.png">
		<meta name="msapplication-wide310x150logo" content="{$smarty.const.IMAGES_DIR}favicons/widetile.png">
		<meta name="msapplication-square310x310logo" content="{$smarty.const.IMAGES_DIR}favicons/largetile.png">
		<link rel="apple-touch-icon" sizes="57x57" href="{$smarty.const.IMAGES_DIR}favicons/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="{$smarty.const.IMAGES_DIR}favicons/apple-touch-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="{$smarty.const.IMAGES_DIR}favicons/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="{$smarty.const.IMAGES_DIR}favicons/apple-touch-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="{$smarty.const.IMAGES_DIR}favicons/apple-touch-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="{$smarty.const.IMAGES_DIR}favicons/apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="{$smarty.const.IMAGES_DIR}favicons/apple-touch-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="{$smarty.const.IMAGES_DIR}favicons/apple-touch-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="{$smarty.const.IMAGES_DIR}favicons/apple-touch-icon-180x180.png">
		<link rel="icon" type="image/png" href="{$smarty.const.IMAGES_DIR}favicons/favicon-16x16.png" sizes="16x16">
		<link rel="icon" type="image/png" href="{$smarty.const.IMAGES_DIR}favicons/favicon-32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="{$smarty.const.IMAGES_DIR}favicons/favicon-96x96.png" sizes="96x96">
		<link rel="icon" type="image/png" href="{$smarty.const.IMAGES_DIR}favicons/android-chrome-192x192.png" sizes="192x192">
		<link rel="stylesheet" type="text/css" href="{$smarty.const.CSS_DIR}{$daytime}.css" >
		{*<link rel="stylesheet" href="{$smarty.const.CSS_DIR}fileicon.min.css">*}
		<script type="text/javascript" src="{$smarty.const.JS_DIR}zorg.js"></script>
		<script src="{$smarty.const.JS_DIR}highlight-js/highlight.pack.js"></script>
		<link class="codestyle" rel="stylesheet" href="{$smarty.const.JS_DIR}highlight-js/styles/github-gist.css">
		
		<!-- RSS Feeds -->
		<link rel="alternate" type="application/rss+xml" title="RSS{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=forum" />
		<link rel="alternate" type="application/rss+xml" title="Forum Feed{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=forum&board=f" />
		<link rel="alternate" type="application/rss+xml" title="Events Feed{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=forum&board=e" />
		<link rel="alternate" type="application/rss+xml" title="Gallery Feed{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=forum&board=i" />
		<link rel="alternate" type="application/rss+xml" title="Rezepte Feed{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=forum&board=r" />
		<link rel="alternate" type="application/rss+xml" title="Neuste Activities{$smarty.const.PAGETITLE_SUFFIX}" href="{$smarty.const.RSS_URL}&type=activities" />
		
		{if $tplroot.page_link != ''}
		<!-- SEO -->
		<link rel="canonical" href="{$smarty.const.SITE_URL}{$tplroot.page_link}" />
		{/if}
	</head>
	
	{* Wenn es ein eingeloggter User ist, wird im Fenstertitel die Anzahl Unreads angezeigt... *}
	<body{if $user->id > 0} onload="init()"{/if}>
	<center>
		<table height="97%" bgcolor="{$smarty.const.BODYBACKGROUNDCOLOR}" cellspacing="0" cellpadding="0" width="860">
			<tr>
				<td valign="top" bgcolor="{$smarty.const.BACKGROUNDCOLOR}" height="100%">
					{if $user->zorger}{include file='tpl:56'}{else}{include file='tpl:672'}{/if}
					<div {$smarty.const.BODYSETTINGS}>
						{if $user->mymenu}{include file="tpl:`$user->mymenu`"}{/if}
						