{capture append=sidebarHtmlArr}
<div><a class="twitter-timeline" href="https://twitter.com/ZorgCH" data-chrome="noheader nofooter noborders noscrollbar transparent" data-dnt="true" data-tweet-limit="4" data-height="860" data-widget-id="393755214169120768" data-link-color="#cbba79" data-theme="dark"></a></div>
<script>{literal}!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");{/literal}</script>
{/capture}
{foreach from=$sidebarHtmlArr item=sidebarHtmlEle name=sidebarElements}
	{assign var='sidebarHtml' value=$sidebarHtmlEle}
{/foreach}

{include file='file:layout/head.tpl'}

<center>
	<h2>ERR. These are no the robots you're looking for.</h2>
	<h3>{if $tplroot.word neq ''}Die Seite «{$tplroot.word}»{else}Das Template ID #{$tplroot.id}{/if} gibt es nicht.</h4>
	{if $user->typ > 0}Aber du kannst <a href="/tpl/33?query={if $tplroot.word neq ''}{$tplroot.word}{else}{$tplroot.id}{/if}">danach suchen</a> - oder sie <a href="/tpl/17?tpleditor=1&tplupd=new">neu erstellen</a>.{/if}
</center>

{include file='file:layout/footer.tpl'}
