<!DOCTYPE html>
<html lang="de">
{assign var=dev value=false}
{assign var='tpl_prefix' value="profile"}
{assign var='tpl_partials' value="layout/partials/`$tpl_prefix`/"}
{include file="file:layout/partials/head/favicons.tpl"}
{if $sun == 'up'}{assign var=daytime value=day}{else}{assign var=daytime value=night}{/if}
<head>
	<meta charset="utf-8">
	<title>{$tplroot.page_title}{$smarty.const.PAGETITLE_SUFFIX}</title>
	<meta name="description" content="Dein zorg Profil und deine Einstellungen bearbeiten.">
	<meta name="robots" content="none, noarchive, nosnippet, noodp, notranslate, noimageindex">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	{if $user->id > 0}
	<link rel="stylesheet" href="{$smarty.const.CSS_DIR}shoelace/shoelace.min.css">
	<script src="{$smarty.const.JS_DIR}jquery-3.5.1.min.js"></script>
	<script src="{$smarty.const.CSS_DIR}shoelace/shoelace.min.js"></script>
	{/if}
</head>

<body{if $sun == "down" || $user->zorger} style="background:#242424; filter:invert(90%);"{/if}>
{if $user->id > 0}
	<header class="text-center">
		<h1>zorg Userprofil</h1>
		<p class="text-secondary text-small">
			Dein Profil und deine Einstellungen für zorg bearbeiten.<br>
			<a href="{$smarty.const.SITE_URL}/user/{$user->name}">↩ back to zorg</a>
		</p>
		{if $sun == "down" || $user->zorger}<div style="filter: invert(100%);">{/if}{$user->id|userpic:'true'}{if $sun == "down" || $user->zorger}</div>{/if}
		<hr>
	</header>
	<main class="container">
		{if $error.title <> ''}{include file="file:layout/elements/block_error.tpl"}{/if}
		{include file="file:`$tpl_partials``$tpl_prefix`_settings.tpl"}
	</main>
{else}
	<header class="text-center"><h1>Nothing to see here</h1>&hellip;oder Du muesch zerscht iilogge:
	<div class="row row-around">
		<div class="col-4">
			{include file='file:layout/partials/loginform.tpl'}
		</div>
	</div>
{/if}
</body>
</html>
