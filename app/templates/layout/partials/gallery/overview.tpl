<h2 class="bottom_border center">zorg Galleries</h2>
{if $user->vereinsmitglied != '0' && $user->vereinsmitglied != ''}
	{* Smarty Random Generator *}
	{section name="element" loop=$galleriesIdList}
		{assign var="allGalleryIds" value=$allGalleryIds+1}
	{/section}
	{*Korrektur weil array() bei 0 beginnt (max=9), nicht bei 1 (max=10)*}
	{assign var="allGalleryIds" value=$allGalleryIds-1}
	{*Randomizer*}
	{rand min=0 max=$allGalleryIds assign="randomGalleryId"}
	<div class="center">
		{random_albumpic album_id=$galleriesIdList[$randomGalleryId] show_title=true image_quality=high}
	</div>

	<div style="display: flex;flex-direction: row;flex-wrap: wrap;justify-content: center;align-items: stretch;align-content: center;">
		<div style="flex-grow: 0;flex-shrink: 1;flex-basis: auto;align-self: auto;margin: 25px;">
			<h4>{link url="/gallery.php?show=albumThumbs&albID=41"}APOD{/link}</h4>
			{apod}
		</div>
		<div style="flex-grow: 0;flex-shrink: 1;flex-basis: auto;align-self: auto;margin: 25px;">
			<h4>Pic of the Day</h4>
			{daily_pic}
		</div>
		<div style="flex-grow: 0;flex-shrink: 1;flex-basis: auto;align-self: auto;margin: 25px;">
			<h4>{link tpl=670}Bestes zorg Pic{/link}</h4>
			{top_pics album=0 limit=1}
		</div>
	</div>
{else}
	Gallery Pics sind nur für Vereinsmitglieder sichtbar...<br>
	Mitglied werden? Einfach eine <a href="/page/vereinsvorstand">Nachricht an den Vorstand</a>!
{/if}
