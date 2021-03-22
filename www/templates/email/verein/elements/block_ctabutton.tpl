{if $cta.style == 'secondary'}
	{assign var=background value='#ff8d6b'}
	{assign var=border value='#995440'}
	{assign var=textcolor value='#fff'}
{else}
	{assign var=background value='#79ff75'}
	{assign var=border value='#499946'}
	{assign var=textcolor value='#499946'}
{/if}
<div style="padding:25px 25px;{$cta.position}"><!--[if mso]>
	  <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{if $cta.action == 'mail'}mailto:{$smarty.const.ZORG_VEREIN_EMAIL}?subject={$cta.text}{else}{$cta.href}{/if}" style="height:30px;v-text-anchor:middle;width:200px;" arcsize="10%" strokecolor="{$border}" fillcolor="{$background}">
	    <w:anchorlock/>
	    <center style="color:{$textcolor};font-family:sans-serif;font-size:13px;font-weight:bold;">{$cta.text}</center>
	  </v:roundrect>
	<![endif]--><a href="{if $cta.action == 'mail'}mailto:{$smarty.const.ZORG_VEREIN_EMAIL}?subject={$cta.text}{else}{$cta.href}{/if}"
	style="background-color:{$background};border:1px solid {$border};border-radius:4px;color:{$textcolor};display:inline-block;font-family:sans-serif;font-size:13px;font-weight:bold;line-height:30px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;mso-hide:all;">{$cta.text}</a>
</div>