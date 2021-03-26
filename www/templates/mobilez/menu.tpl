{* Header *}
<h1>Mobilezorg V2</h1>
{*<a href="#menu" class="ui-btn ui-btn-left ui-corner-all ui-nodisc-icon ui-icon-bars ui-btn-icon-notext">Menu</a>*}
<div class="ui-btn ui-btn-right" data-role="controlgroup" data-type="horizontal">
	{if $errors || isset($smarty.get.error_msg)}<a href="#popupError" data-position-to="window" data-rel="popup" class="ui-btn ui-corner-all {$btnIconOptions} ui-icon-alert ui-btn-icon-notext">Errors</a>{/if}
	{if $user->typ > 0}<a href="#popupBugtracker" data-position-to="window" data-rel="popup" class="ui-btn ui-corner-all {$btnIconOptions} ui-icon-bug ui-btn-icon-notext">Bugs</a>{/if}
	<a href="https://zorg.ch/" rel="external" data-ajax="false" class="ui-btn ui-corner-all {$btnIconOptions} ui-btn-icon-notext ui-nosvg ui-icon-desktop">Desktop</a>
	<a href="{if $user->typ > 0}#popupLogout{else}#popupLogin{/if}" data-rel="popup" data-position-to="window" class="ui-btn ui-corner-all {$btnIconOptions} ui-icon-user ui-btn-icon-notext {if $errors}ui-disabled{/if}">User</a>
</div>

{if $user->typ > 0}
{* Menu left *}
{*<div data-role="panel" data-display="push" data-theme="a" id="{assign var='menuId' value='menu'}{$menuId}">
	<ul data-role="listview>
		<li class="ui-btn ui-corner-all ui-nodisc-icon" data-icon="delete" data-theme="b"><a href="#" data-rel="close">Close</a></li>
		<li><a href="#panel-responsive-channel1">#zooomclan</a></li>
		<li><a href="#panel-responsive-channel2">#events</a></li>
		<li><a href="#panel-responsive-channel5">#construct</a></li>
		<li><a href="#panel-responsive-channel3">#alleschrankichinder</a></li>
		<li><a href="#panel-responsive-channel4">#grüüsigi</a></li>
		<li><a href="#panel-responsive-channel5">#dinimüettere</a></li>
	</ul>
</div>
<script>
$(document).on("pagecreate", "#{$pageId}", function(){ldelim}
    $(document).on("swipeleft swiperight", "#{$pageId}", function(e){ldelim}
        // We check if there is no open panel on the page because otherwise
        // a swipe to close the left panel would also open the right panel (and v.v.).
        // We do this by checking the data that the framework stores on the page element (panel: open).
        if ($(".ui-page-active").jqmData("panel") !== "open") {ldelim}
			if (e.type === "swiperight" ) {ldelim}
				$("#{$menuId}").panel("open");
			{rdelim}
        {rdelim}
    {rdelim});
{rdelim});</script>*}

{* Menu right *}
<div data-role="popup" id="{assign var='menuRight' value='menuProfile'}{$menuRight}" data-theme="{$layout}">
	<ul data-role="listview">
		<li><a href="#popupLogout" data-rel="popup">Abmelden</a></li>
		<li data-role="list-divider">Profil Details:</li>
		<li><a href="#">Anzeigen</a></li>
		<li><a href="#">Meine Bilder</a></li>
	</ul>
</div>
<div data-role="popup" id="popupBugtracker" data-theme="{$layout}" class="ui-content" data-dismissible="true">
    <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-left">Close</a>
	<h3>Bug gefunden oder Feature request?</h3>
	<form id="formReportBug" data-ajax="true" method="post" action="ajax_post_bug.php">
		<input type="hidden" name="from_mobile" id="from_mobile" value="{$user_mobile}">
		<label for="title" class="ui-hidden-accessible">Titel:</label>
		<input type="text" name="title" id="title" placeholder="Titel" data-theme="a">
		<label for="description" class="ui-hidden-accessible">Beschreibung:</label>
		<textarea name="description" id="description" cols="" rows="" placeholder="Beschreibung" data-theme="a"></textarea>
		<button type="button" name="buttonReportBug" id="buttonReportBug" value="Eintragen" class="ui-btn ui-corner-all ui-nodisc-icon ui-btn-b ui-btn-icon-right ui-icon-mail">Melden</button>
		<a href="../bugtracker.php" rel="external" data-ajax="false" target="_blank" data-role="button" name="buttonBugtrackerOpen" id="buttonBugtrackerOpen" class="ui-btn ui-corner-all ui-nodisc-icon ui-btn-b ui-btn-icon-right ui-icon-arrow-r">Bugtracker &ouml;ffnen</a>
	</form>
</div>
<div data-role="popup" id="popupLogout" data-theme="{$layout}" class="ui-content" data-dismissible="false">
    <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-left">Close</a>
	<h3>Willst Du Dich wirklich aus zorg.ch abmelden?</h3>
	<form data-ajax="false" method="post" action="{$smarty.server.SCRIPT_NAME}">
		<a data-role="button" href="#" data-rel="back" data-inline="true" data-mini="true" class="ui-btn ui-corner-all ui-nodisc-icon ui-btn-b ui-btn-icon-left ui-icon-delete">Oops nei doch nö</a>
		<button type="submit" id="logout" name="logout" value="iwillbeback" data-inline="true" data-mini="true" class="ui-btn ui-corner-all ui-nodisc-icon ui-btn-b ui-btn-icon-left ui-icon-thumbs-up">Jawohl!</button>
	</form>
</div>
<script>{literal}
$(document).ready(function() {
    $('#buttonReportBug').click(function(ev) {
        if ($.trim($('#title').val()).length > 1) {
			$.ajax({
				type: $('#formReportBug').attr('method'),
				url: $('#formReportBug').attr('action'),
				data: $('#formReportBug').serialize(),
				success: function (data) {
					console.log(data);
					$('#popupBugtracker').popup('close');
					//window.location.reload(true);
				},
				error: function(jqXHR, textStatus, errorThrown) {
		           alert(textStatus + ' ' + errorThrown);
		        }
			});
			ev.preventDefault(); // avoid to execute the actual submit of the form.
		} else {
			alert('Ähem, bitte kei leeri Bugs erfasse!');
			return false;
		}
    });
});
{/literal}</script>
{/if}
