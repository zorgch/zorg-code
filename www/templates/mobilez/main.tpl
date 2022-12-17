<!doctype html>
<html>
{assign var='debugMode' value='false'}
{if $debugMode == 'true'}{assign var='debugParam' value='?debug=true'}{/if}
<head>
	<title>Mobile [z]</title>
	<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta charset="utf-8">
	<meta name="msapplication-square70x70logo" content="/images/favicons/smalltile.png">
	<meta name="msapplication-square150x150logo" content="/images/favicons/mediumtile.png">
	<meta name="msapplication-wide310x150logo" content="/images/favicons/widetile.png">
	<meta name="msapplication-square310x310logo" content="/images/favicons/largetile.png">
	<link rel="shortcut icon" href="/images/favicons/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" sizes="57x57" href="/images/favicons/apple-touch-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/images/favicons/apple-touch-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/images/favicons/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/images/favicons/apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/images/favicons/apple-touch-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/images/favicons/apple-touch-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/images/favicons/apple-touch-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/images/favicons/apple-touch-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/images/favicons/apple-touch-icon-180x180.png">
	<link rel="icon" type="image/png" href="/images/favicons/favicon-16x16.png" sizes="16x16">
	<link rel="icon" type="image/png" href="/images/favicons/favicon-32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="/images/favicons/favicon-96x96.png" sizes="96x96">
	<link rel="icon" type="image/png" href="/images/favicons/android-chrome-192x192.png" sizes="192x192">
	<link rel="stylesheet" href="/css/mobilez/mobilez.css">
	<script src="/js/mobilez/jquery-1.10.1.min.js"></script>{*<script src="//code.jquery.com/jquery-1.12.0.min.js"></script>*}
	<script src="/js/mobilez/jquery.mobile-1.4.5.min.js"></script>
	<script src="/js/mobilez/date-format.js"></script>
	<script src="/js/mobilez/dropzone.jquery.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?v=3"></script>
	<script src="/js/mobilez/browsernotifications.js"></script>*}
	<script>{literal}
	$(document).bind("pageinit", function(){
		$("div[id*='popup']").on("popupafteropen", function(event, ui) {
	    	$(".ui-content.background").addClass("blur-filter");
	    	document.body.style.overflow = "hidden"; // Disable page scrolling
		}).on('popupafterclose', function(event, ui) {
	    	$(".ui-content.background").removeClass("blur-filter");
	    	document.body.style.overflow = "visible"; // Enable page scrolling
	    });

	    // Patch to prevent overwriting <title></title>
	    $(":jqmData(role='page')").attr('data-title', document.title);

	    // Patch to prevent scrolling to bottom on Input-Field focus
	    $('#message').click(function(e){
		    $.mobile.silentScroll(0)
		});
	});
	{/literal}</script>
</head>
<body>
	{if $sun == 'up'} {* Day Layout settings *}
		{assign var='layout' value='a'}
		{assign var='layoutReverse' value='b'}
		{assign var='btnIconOptions' value='ui-btn-a'}
	{else}  {* Night Layout settings *}
		{assign var='layout' value='b'}
		{assign var='layoutReverse' value='a'}
		{assign var='btnIconOptions' value='ui-btn-b ui-nodisc-icon'}
	{/if}
	<div data-role="page" id="{assign var='pageId' value='mobilezorg-main'}{$pageId}">

		<div data-role="header" data-theme="{$layout}" data-position="fixed" data-fullscreen="true">
			<h1 style="display: none;">Mobile [z]</h1>
			{include file='file:mobilez/menu.tpl'}
		</div>

		<div id="main" role="main" class="ui-content background {$sun}">
			 {include file='file:mobilez/messages.tpl'}
		</div>

		{if $user->typ > 0}<div data-role="footer" data-theme="{$layout}" data-position="fixed" data-fullscreen="true">
				{include file='file:mobilez/chat_input.tpl'}
		</div>{/if}

		{if $user->typ == 0}{include file='file:mobilez/login.tpl'}{/if}

		{if $errors || isset($smarty.get.error_msg)}<div data-role="popup" id="popupError" data-theme="{$layout}" class="popupError">
			{if $smarty.get.error_msg <> ''}
				<p>{$smarty.get.error_msg}</p>
			{else}
				{foreach from=$errors key=error_nr item=error}
					<p>{$error.message}<br>
						{if $error.file <> ''}<br>{$error.file}{/if}{if $error.line <> ''}:{$error.line}{/if}
						{if $error.class <> ''}<br>{$error.class}::{/if}{if $error.function <> ''}{$error.function}{/if}
					</p>
				{/foreach}
			{/if}
		</div>{/if}

	</div>
</body>
<script>
{if $user->typ > 0}var myUserId = {$user->id};{/if}
var loadmoreIconClass = 'ui-btn-icon-refresh-{$layoutReverse}';
var divLoadmoreDOM;
{literal}$('#btnLoadmore').click(function(e){
	$(this).buttonMarkup({ icon: loadmoreIconClass });
	var previousdate = '';
	var lastMessageId = $('#messages div:last-child').attr('data-id');
	$.getJSON('ajax_get_messages.php?lastentry_id=' + lastMessageId, function(data){
        $.each(data, function(index) {
	        var previousdateString = new Date(previousdate*1000).setHours(0,0,0,0);
	        var dateString = new Date(data[index].date*1000).setHours(0,0,0,0);
	        if (previousdateString != dateString || previousdate == '') {
		        var dateStringOutput = new Date(dateString);
				$('#messages div:last').after('<div class="message date"><p>' + Date.format(dateStringOutput, 'd. MMM yyyy') + '</p></div>');
				previousdate = data[index].date;
			}
            var newMessageDiv = $('#messages div:last').clone();
			newMessageDiv.attr('data-id', data[index].date);
			{/literal}{if $user->typ > 0}{literal}
			if (data[index].user_id == myUserId) {
				newMessageDiv.attr('class', 'message me');
				newMessageDiv.html(data[index].text);
			}
			else {
			{/literal}{/if}{literal}
				newMessageDiv.attr('class', 'message them');
				newMessageDiv.html('<a href="/profil.php?user_id=' + data[index].user_id + '" class="ui-link">' + data[index].user_name + '</a>: ' + data[index].text);
			{/literal}{if $user->typ > 0}{literal}
			}
			{/literal}{/if}{literal}
			$('#messages div:last').after(newMessageDiv);
        });
    }).fail(function(d, textStatus, error) {
	    console.error('getJSON failed, status: ' + textStatus + ', error: ' + error)
	});
	$(this).buttonMarkup({ icon: 'ui-icon-refresh' });
});

// Resize Images in Chat
var max_width = 320; var max_height = 180;
$(document).bind("pageinit", function(){
	$('div#messages div img').each(function(){
	    var w = $(this).width();
	    var h = $(this).height();
	    var scale = null;
	    if (w >= h) { if (w > max_width) { scale = 1 / (w / max_width); } }
	    else { if (h > max_height) { scale = 1 / (h / max_height); } }
	    if (scale) {
	        $(this).width(w * scale);
	        $(this).height(h * scale);
	    }
	});
});
{/literal}
{*
// Reload all Messages
function loadNewMessages(){
	$.get('ajax_exec_reload_chat.php', function(data){
		$('div#main').html(data);
		console.log('Chat reloaded');
	});
};
setInterval(function(){
	loadNewMessages();
},5000);

OLDER:
function loadNewMessages(){
	/*localStorage['lpid']=$("#messages :last").attr("data-id");
	$("#messages").load("ajax_handler.php",function(){
		if(localStorage['lpid']!=$("#messages :last").attr("data-id")){
			scrollTop();
		}
	});*/
	$.ajax({
		url: "ajax_handler.php",
        type: "post",
        data: "last_message=" + $("#messages div:first-child").attr("data-id"),
        success: function (response) {},
        error: function(jqXHR, textStatus, errorThrown) {
           console.log(textStatus, errorThrown);
        }
	}).done(function(html) {
        $("#messages div:first-child").before(html);
    });
}

setInterval(function(){
	loadNewMessages();
},5000);*}</script>
</html>
