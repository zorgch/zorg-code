{if $user->typ >= 2}
<div class="center hide-mobile">[&nbsp;<a href="/gallery.php?show=editAlbumV2">add new Album</a>&nbsp;]</div>
<h3><i>-- Leere Galleries --</i></h3>
{foreach from=$galleriesEmptyIdList key=index item=gallery}
	<p>«<a href="{$self}?show=editAlbumV2&albID={$gallery.id}">{$gallery.name}</a>»<br><small>{$gallery.created|datename}</small></p>
{foreachelse}
	<i>Keine…</i>
{/foreach}
{/if}

<!-- h3>Overview</h3 -->
{foreach from=$galleriesOverviewGrouped key=groupname item=galleriesGroup}
	<h4>{$groupname|upper}</h4>
	<ul style="list-style-type: none;">
		{foreach from=$galleriesGroup key=index item=gallery}
		<li><a href="{$self}?show=albumThumbs&albID={$gallery.id}">{$gallery.name}</a> <small>{$gallery.numpics|quantity:Pic:Pics}&nbsp;pics{if $gallery.created > 0}<br>{$gallery.created|datename}{/if}<br><br></small></li>
		{/foreach}
	</ul>
{/foreach}


{*{$galleriesOverviewGrouped}*}
{*foreach ($alphabeticalList as $groupchar => $albumlist) {
	$listEntryHtml .= sprintf('<h5>%s</h5>', strtoupper($groupchar));
	$listEntryHtml .= '<ul>';
	foreach ($albumlist as $key => $data) {
		$listEntryHtml .= sprintf('<li><small><a href="%s?show=albumThumbs&albID=%d">%s</a> %s (%d pics)</small></li>', $_SERVER['PHP_SELF'], $data['id'], $data['name'], (!empty($data['created']) ? ' — '.date('Y', $data['created']) : ''), $data['numpics']);
	}
	$listEntryHtml .= '</ul>';
}*}
