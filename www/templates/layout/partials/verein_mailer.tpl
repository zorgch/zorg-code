<!DOCTYPE html>
<html lang="de">
{assign var=dev value=false}
{assign_array var=president value="array('userid'=>117,'value'=>'president','label'=>'Präsidentensache')"}
{assign_array var=actuary value="array('userid'=>11,'value'=>'actuary','label'=>'Aktuarssache')"}
{assign_array var=treasurer value="array('userid'=>52,'value'=>'treasurer','label'=>'Kassiersache')"}
{assign_array var=eventmanager value="array('userid'=>714,'value'=>'eventmanager','label'=>'Eventsache')"}
<head>
	<meta charset="utf-8">
	<title>zorg Verein - Mailer</title>
	<meta name="description" content="Page um schöne Verein E-Mails an die Mitglieder zu verschicken">
	<meta name="robots" content="none, noarchive, nosnippet, noodp, notranslate, noimageindex" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	{include file="file:layout/partials/head/favicons.tpl"}
	<link rel="stylesheet" href="{$smarty.const.CSS_DIR}shoelace/shoelace.min.css">
	{if $user->id > 0 && ($user->id == $president.userid || $user->id == $actuary.userid || $user->id == $treasurer.userid)}
	{if !$dev}<script src="https://kit.fontawesome.com/e9effb9c00.js" crossorigin="anonymous"></script>{else}<link href="{$smarty.const.CSS_DIR}fontawesome.min.css" rel="stylesheet">{/if}
	{*<link href="/js/emoji-picker/css/emoji.css" rel="stylesheet">*}
	<script src="{$smarty.const.JS_DIR}jquery-3.5.1.min.js"></script>
	<script src="{$smarty.const.CSS_DIR}shoelace/shoelace.min.js"></script>
	<script src="{$smarty.const.JS_DIR}nanobar.min.js"></script>
	<!-- Quill - Rich Text Editor: https://quilljs.com/ -->
	<link rel="stylesheet" href="{$smarty.const.JS_DIR}quill-richtexteditor/quill.snow.css" />
	<script src="{$smarty.const.JS_DIR}quill-richtexteditor/quill.min.js"></script>
	<style>{literal}
	.intrinsic-container{-webkit-overflow-scrolling:touch;overflow-y:scroll;overflow:scroll;position:relative;height:0;background:url(/images/mobilez/ajax-loader-black.gif) center center no-repeat}.intrinsic-container-16x9{padding-bottom:56.25%}.intrinsic-container-4x3{padding-bottom:75%}.intrinsic-container iframe{position:absolute;top:0;left:0;width:100%;height:100%;border:1px solid #ddd;border-radius:.25rem}.emoji-picker-icon{pointer-events:all!important}.emoji-menu{height:250px}.nanobar .bar{background-color:#0074d9}#mail_message{height:auto;min-height:100%}#mail_message .ql-editor{font-size:14px;overflow-y:visible}#commenting{height:100%;min-height:100%;overflow-y:auto}.ql-snow.ql-toolbar button{color:#000;line-height:0!important}.ql-tooltip.ql-editing{z-index:1}.ql-tooltip.ql-editing input{line-height:0!important}
	.ql-editor { background: #000 !important; color: #fff !important; font-family: Verdana, Sans-Serif !important; }
	.ql-snow .ql-editor h2, .ql-snow .ql-editor h3, .ql-snow .ql-editor h4, .ql-snow .ql-editor ul, .ql-snow .ql-editor ol {padding-top:10px;}
	.ql-snow .ql-editor h2, .ql-snow .ql-editor h3, .ql-snow .ql-editor h4 {padding-bottom:5px;}
	.ql-snow .ql-editor ul, .ql-snow .ql-editor ol {padding-bottom:10px;}
	.ql-snow .ql-editor p {padding-top:5px;padding-bottom:5px;}
	.ql-snow .ql-editor div p {padding-top:10px;padding-bottom:10px;}
	.ql-snow .ql-editor li {margin-left:25px !important}
	.ql-snow .ql-editor a{color:#CBBA79;text-decoration:none}
	.ql-snow .ql-editor a:hover{text-decoration:underline}
	{/literal}</style>
	{/if}
</head>

<body>{if $user->id > 0 && ($user->id == $president.userid || $user->id == $actuary.userid || $user->id == $treasurer.userid || $user->id == $eventmanager.userid)}
	<header class="text-center">
		<h1>zorg Verein - Mailer</h1>
		<p class="text-secondary text-small">
			Eine E-Mailversand Applikation f&uuml;r Mails an die Zorg Verein Mitglieder<br>
			<a href="{$smarty.const.SITE_URL}">↩ back to zorg</a>
		</p>
		<hr>
	</header>
	<main class="container">
		<div class="row">
			<div class="col">
				<!--button type="button" name="button_new_message" id="button_new_message" class="mar-t-sm mar-b-sm">Create new Message</button-->
				<h3>Select an existing Message:</h3>
				<div class="input-group">
					<select name="dropdown_template_select" id="dropdown_template_select">
						<option label="--- Message auswählen ---" selected disabled>--- Message auswählen ---</option>
					</select>
					<button type="button" id="button_delete_message" class="button-danger" disabled><i class="fa fa-trash"></i> delete</button>
					<button type="button" id="button_load_message"><i class="fa fa-edit"></i> load</button>
				</div>
				<hr>
			</div>
		</div>
	<form id="mail_settings">
		<input type="hidden" name="template_id" id="template_id" value="">
		<div class="row">
			<div class="col">
				<h2>Absender</h2>
				<div class="input-field">
					<label><input type="radio" name="topic" id="radio_president" value="{$president.value}" {if $user->id == $president.userid}checked{/if}> {$president.label}</label> <br class="hide-md-up">
					<label><input type="radio" name="topic" id="radio_actuary" value="{$actuary.value}" {if $user->id == $actuary.userid}checked{/if}> {$actuary.label}</label> <br class="hide-md-up">
					<label><input type="radio" name="topic" id="radio_treasurer" value="{$treasurer.value}" {if $user->id == $treasurer.userid}checked{/if}> {$treasurer.label}</label> <br class="hide-md-up">
					<label><input type="radio" name="topic" id="radio_eventmanager" value="{$eventmanager.value}" {if $user->id == $eventmanager.userid}checked{/if}> {$eventmanager.label}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<h2>E-Mail</h2>
				<div class="input-field">
					<label for="text_mail_subject">Betreff</label>
					<input type="text" name="text_mail_subject" id="text_mail_subject" maxlength="47" data-emojiable="true" data-emoji-input="unicode" placeholder="E-Mail Betreff" autocomplete="off" {if $dev}value="🔥Zorg Verein - Mailtemplate Testing"{/if}>
					<div class="progress progress-xs" id="text_mail_subject_progressbar"><div class="progress-bar" id="text_mail_subject_progress"></div></div>
				</div>

				<div class="input-field">
					<label for="text_mail_description">Vorschautext</label>
					<span class="input-hint hide-xs"><a class="badge" href="https://litmus.com/blog/the-ultimate-guide-to-preview-text-support" target="_blank">?</a> Wird in E-Mailclients als Vorschautext der E-Mail angezeigt</span>
					<input type="text" name="text_mail_description" id="text_mail_description" maxlength="99" placeholder="Kurzbeschreibung / Vorschau Text" autocomplete="off" {if $dev}value="Protokoll der GV 2017, wichtigste Beschlüsse & Highlights"{/if}>
					<div class="progress progress-xs" id="text_mail_description_progressbar"><div class="progress-bar" id="text_mail_description_progress"></div></div>
				</div>

				<div class="input-field">
					<label class="text-muted">[z] Layout</label>
					<label class="bg-dark text-muted pad-xs"><input type="radio" name="layout" value="night" checked disabled> Night</label>
					<label class="bg-light text-muted pad-xs">&nbsp;<input type="radio" name="layout" value="day" disabled> Day&nbsp;</label>
				</div>

				<label>Nachricht</label>
				<div class="commenting">
					<div id="toolbar">
						<span class="ql-format-group">
							<select class="ql-header">
								<option value="2"></option>
								<option value="3"></option>
								<option value="false" selected></option>
							</select>
						</span>
						<span class="ql-format-group">
							<select title="Text Color" class="ql-color">
								<option value="rgb(0, 0, 0)" label="rgb(0, 0, 0)" selected></option>
								<option value="rgb(230, 0, 0)" label="rgb(230, 0, 0)"></option>
								<option value="rgb(255, 153, 0)" label="rgb(255, 153, 0)"></option>
								<option value="rgb(255, 255, 0)" label="rgb(255, 255, 0)"></option>
								<option value="rgb(0, 138, 0)" label="rgb(0, 138, 0)"></option>
								<option value="rgb(0, 102, 204)" label="rgb(0, 102, 204)"></option>
								<option value="rgb(153, 51, 255)" label="rgb(153, 51, 255)"></option>
								<option value="rgb(255, 255, 255)" label="rgb(255, 255, 255)"></option>
								<option value="rgb(250, 204, 204)" label="rgb(250, 204, 204)"></option>
								<option value="rgb(255, 235, 204)" label="rgb(255, 235, 204)"></option>
								<option value="rgb(255, 255, 204)" label="rgb(255, 255, 204)"></option>
								<option value="rgb(204, 232, 204)" label="rgb(204, 232, 204)"></option>
								<option value="rgb(204, 224, 245)" label="rgb(204, 224, 245)"></option>
								<option value="rgb(235, 214, 255)" label="rgb(235, 214, 255)"></option>
								<option value="rgb(187, 187, 187)" label="rgb(187, 187, 187)"></option>
								<option value="rgb(240, 102, 102)" label="rgb(240, 102, 102)"></option>
								<option value="rgb(255, 194, 102)" label="rgb(255, 194, 102)"></option>
								<option value="rgb(255, 255, 102)" label="rgb(255, 255, 102)"></option>
								<option value="rgb(102, 185, 102)" label="rgb(102, 185, 102)"></option>
								<option value="rgb(102, 163, 224)" label="rgb(102, 163, 224)"></option>
								<option value="rgb(194, 133, 255)" label="rgb(194, 133, 255)"></option>
								<option value="rgb(136, 136, 136)" label="rgb(136, 136, 136)"></option>
								<option value="rgb(161, 0, 0)" label="rgb(161, 0, 0)"></option>
								<option value="rgb(178, 107, 0)" label="rgb(178, 107, 0)"></option>
								<option value="rgb(178, 178, 0)" label="rgb(178, 178, 0)"></option>
								<option value="rgb(0, 97, 0)" label="rgb(0, 97, 0)"></option>
								<option value="rgb(0, 71, 178)" label="rgb(0, 71, 178)"></option>
								<option value="rgb(107, 36, 178)" label="rgb(107, 36, 178)"></option>
								<option value="rgb(68, 68, 68)" label="rgb(68, 68, 68)"></option>
								<option value="rgb(92, 0, 0)" label="rgb(92, 0, 0)"></option>
								<option value="rgb(102, 61, 0)" label="rgb(102, 61, 0)"></option>
								<option value="rgb(102, 102, 0)" label="rgb(102, 102, 0)"></option>
								<option value="rgb(0, 55, 0)" label="rgb(0, 55, 0)"></option>
								<option value="rgb(0, 41, 102)" label="rgb(0, 41, 102)"></option>
								<option value="rgb(61, 20, 102)" label="rgb(61, 20, 102)"></option>
							</select>
							<button class="ql-bold"></button>
							<button class="ql-italic"</button>
							<button class="ql-underline"</button>
							<button class="ql-script" value="sub"></button>
							<button class="ql-script" value="super"></button>
						</span>
						<span class="ql-format-group">
							<button class="ql-list" value="bullet"></button>
							<button class="ql-list" value="ordered"></button>
							<button class="ql-blockquote"></button>
							<button class="ql-link"></button>
						</span>
						<span class="ql-format-group">
							<button class="ql-clean"></button>
						</span>
						<span class="ql-format-separator"></span>
						<span class="ql-format-group">
							<button id="info-block"><i class="fa fa-info-circle"></i></button>
							<button id="button-block"><i class="fa fa-share-square"></i></button>
							<button id="username-block"><i class="fa fa-user-circle"></i></button>
							<!--button id="useraddress-block"><i class="fa fa-address-book"></i></button-->
							<button id="swissqrbill-block"><i class="far fa-money-bill-alt"></i></button>
						</span>
					</div>
					<div id="quill_editor">{if $dev}
						<h2>Geschätztes Vereinsmitglied</h2>
						<p>Der alljährliche Zorg Dezembertreff & die GV 2017 sind bereits vorüber — mit vielen positiven Entwicklungen zum Verein durch die Beschlüsse, welche die 10 anwesenden Mitgliedern getroffen haben.<p>{/if}</div>
						<input type="hidden" name="text_mail_message" id="text_mail_message">
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col text-center">
				<button type="button" class="button-success mar-sm" id="button_save_template">Save Template</button>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<fieldset>
					<legend>Preview</legend>
					<div id="div_mail_preview" class="intrinsic-container intrinsic-container-16x9 hide-xs-up">
						<iframe id="frame_mail_preview" allowfullscreen>Use "Update Preview" to load the message&hellip;</iframe>
					</div>
				</fieldset>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<h2>Empf&auml;nger</h2>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<h3>Vorstand</h3>
				<label class="text-bold"><input type="checkbox" name="checkbox_list_vorstand" id="checkbox_list_vorstand_alle" value="*"> Alle</label><br>
				<div class="input-field" id="list_vorstand"></div>
			</div>
			<div class="col">
				<h3>Kenner</h3>
				<label class="text-bold"><input type="checkbox" name="checkbox_list_kenner" id="checkbox_list_kenner_alle" value="*"> Alle</label><br>
				<div class="input-field" id="list_kenner"></div>
			</div>
			<div class="col">
				<h3>Mitglieder</h3>
				<label class="text-bold"><input type="checkbox" name="checkbox_list_mitglieder" id="checkbox_list_mitglieder_alle" value="*"> Alle</label><br>
				<div class="input-field" id="list_mitglieder"></div>
			</div>
		</div>
		<input type="hidden" name="hidden_selected_recipients" id="hidden_selected_recipients">
	</form>
		<div class="row">
			<div class="col text-center">
				<button type="button" class="button-warning mar-lg hide-xs-up" id="button_send_message">Send E-Mail</button>
			</div>
		</div>
	</main>
	{*<script src="/js/emoji-picker/js/config.js"></script>
	<script src="/js/emoji-picker/js/util.js"></script>
	<script src="/js/emoji-picker/js/jquery.emojiarea.js"></script>
	<script src="/js/emoji-picker/js/emoji-picker.js"></script>
	<script>{literal}
	$(function() {
		window.emojiPicker = new EmojiPicker({
		emojiable_selector: '[data-emojiable=true]',
		assetsPath: '/js/emoji-picker/img/',
		popupButtonClasses: 'fa fa-smile-o'
		});
		window.emojiPicker.discover();
	});
	{/literal}</script>*}
	<script>{literal}
	const existing_tpls = $('#dropdown_template_select');
	const existing_tpls_initial = $('#dropdown_template_select').html();
	const form_elements = $('#mail_settings');
	const hidden_template_id = $('#template_id');
	const text_mail_subject = $('#text_mail_subject');
	const text_mail_description = $('#text_mail_description');
	const text_mail_message = $('#text_mail_message');
	const save_button = $('#button_save_template');
	const send_button = $('#button_send_message');
	const div_mail_preview = $('#div_mail_preview');
	const preview_iframe = $("#frame_mail_preview");
	const preview_div = $("#div_mail_preview");
	const hidden_selected_recipients = $('#hidden_selected_recipients');
	const preview_mode = 'frame';
	const president_userid = {/literal}{$president.userid}{literal};
	const actuary_userid = {/literal}{$actuary.userid}{literal};
	const treasurer_userid = {/literal}{$treasurer.userid}{literal};
	const eventmanager_userid = {/literal}{$eventmanager.userid}{literal};
	var update_mode = false;

	// Quill - Rich Text Editor
	var quill = new Quill('#quill_editor', {
		modules: {
		toolbar: '#toolbar'
		},
		scrollingContainer: '#commenting',
		theme: 'snow'
	});

	// Quill - Custom Buttons
	$('#info-block').on('click', function(e){
		var textPrefix = '{mail_infoblock topic="headline"}';
		var textPlaceholder = 'text';
		var textSuffix = '{/mail_infoblock}';
		e.preventDefault()
		quill.focus();
		var textSelection = quill.getSelection();
		if (textSelection) {
			if (textSelection.length == 0) {
				quill.insertText(textSelection.index, textPrefix + textPlaceholder + textSuffix, false);
			} else {
				quill.insertText(textSelection.index, textPrefix, false);
				quill.insertText(textSelection.index + textPrefix.length + textSelection.length, textSuffix, false);
			}
		}
		quill.setSelection(textSelection.index + textPrefix.length);
	});
	$('#button-block').on('click', function(e){
		var textPrefix = '{mail_button style="NULL|secondary" position="left|center|right" action="mail|link" href="url"}';
		var textPlaceholder = 'button-text';
		var textSuffix = '{/mail_button}';
		e.preventDefault()
		quill.focus();
		var textSelection = quill.getSelection();
		if (textSelection) {
			if (textSelection.length == 0) {
				quill.insertText(textSelection.index, textPrefix + textPlaceholder + textSuffix, false);
			} else {
				quill.insertText(textSelection.index, textPrefix, false);
				quill.insertText(textSelection.index + textPrefix.length + textSelection.length, textSuffix, false);
			}
		}
		quill.setSelection(textSelection.index + textPrefix.length);
	});
	$('#username-block').on('click', function(e){
		var textPrefix = '{$user_param|name}';
		e.preventDefault()
		quill.focus();
		var textSelection = quill.getSelection();
		if (textSelection) {
			quill.insertText(textSelection.index, textPrefix, false);
		}
		quill.setSelection(textSelection.index + textPrefix.length);
	});
	$('#swissqrbill-block').on('click', function(e){
		var textPrefix = '{swissqrbillcode size="m" user=$user_param betrag=23.00}';
		var textPlaceholder = 'zorg Verein Mitgliederbeitrag';
		var textSuffix = '{/swissqrbillcode}';
		e.preventDefault()
		quill.focus();
		var textSelection = quill.getSelection();
		if (textSelection) {
			if (textSelection.length == 0) {
				quill.insertText(textSelection.index, textPrefix + textPlaceholder + textSuffix, false);
			} else {
				quill.insertText(textSelection.index, textPrefix, false);
				quill.insertText(textSelection.index + textPrefix.length + textSelection.length, textSuffix, false);
			}
		}
		quill.setSelection(textSelection.index + textPrefix.length);
	});
	// Quill - Duplicate HTML into Input-Field
	function updateMessageHtml(html) {
		if (typeof html != 'undefined' && html) {
			console.info('Updating text_mail_message HTML...');
			text_mail_message.val(html);
		}
	}

	/**
	 * Load existing Templates
	 */
	function getTemplates(container) {
		var selected_tpl_id = hidden_template_id.val();
		$.ajax({
			url: "/js/ajax/verein_mailer/get-mailtemplateslist.php?action=list",
			type: 'GET',
			success: function(data) {
					var list_html = existing_tpls_initial;
					for( var i=0; i<data.length; i++) {
						var option_id = container + '_' + data[i].tplid;
						var option_label = '#' + data[i].tplid + ' &laquo;' + data[i].subject + '&raquo; von ' + data[i].updated;
						var option_value = data[i].tplid;
						var option_selected = (data[i].tplid == selected_tpl_id ? 'selected' : '');
						list_html += '<option id="' + option_id + '" label="' + option_label + '" value="' + option_value + '" '+option_selected+'>' + option_label + '</option>';
					}
					$('#' + container).html(list_html);
				},
			error: function(data) {
					console.error('No templates found or invalid request.');
				}
		});
	}
	function loadTemplate(tpl_id) {
		console.info('Loading Template ' + tpl_id);
		preview_iframe.attr('src', '');
		const params = {
			tpl_id: tpl_id
		};
		$.ajax({
			url: "/js/ajax/verein_mailer/get-mailtemplate.php?action=load",
			type: 'POST',
			data: JSON.stringify(params),
			success: function(data) {
					if (Number(data.owner) == president_userid) $('#radio_president').prop('checked', true);
					if (Number(data.owner) == actuary_userid) $('#radio_actuary').prop('checked', true);
					if (Number(data.owner) == treasurer_userid) $('#radio_treasurer').prop('checked', true);
					if (Number(data.owner) == eventmanager_userid) $('#radio_eventmanager').prop('checked', true);
					text_mail_subject.val(data.subject);
					text_mail_description.val(data.preview);
					quill.root.innerHTML = data.message;
					updateMailPreview(tpl_id)
					console.info('Template loaded successfully!');
					checkRecipientStatus(tpl_id);
				},
			error: function(data) {
					console.error('No templates found or invalid request.');
				}
		});
	}

	/**
	 * Unload current Template
	 */
	function unloadTemplate() {
		console.info('Closing Template...');
		enableTemplateUpdateMode(false);
		//tpl_id = 'undefined';
		div_mail_preview.addClass('hide-xs-up');
		preview_iframe.attr('src', '');
		text_mail_subject.val('');
		text_mail_description.val('');
		text_mail_subject.val('');
		$('.progress-bar').html('0');
		$('.progress-bar').width('0%');
		quill.root.innerHTML = '';
		send_button.removeClass('button-danger button-success');
		send_button.addClass('hide-xs-up');
		$("input[id$='_alle']").prop('checked', false);
		$("div[id^='list'] fieldset label").removeClass('text-success text-primary');
		$("div[id^='list'] fieldset label span").removeClass('badge-success');
		$("span[id$='_status_read']").html('<i class="fa fa-eye"></i>');
		$("div[id^='list'] fieldset label input:checkbox").prop('checked', false);
		hidden_selected_recipients.val('');
		getTemplates('dropdown_template_select');
		existing_tpls.prop('disabled', false);
		console.info('Template closed!');
	}

	/**
	 * Load Recipients
	 * member_type => 'mitglieder' | 'vorstand' | 'kenner'
	 */
	function getRecipients(type, container) {
		const params = {
			member_type: type
		};
		$.ajax({
			url: "/js/ajax/verein_mailer/get-recipientslist.php?action=list",
			type: 'POST',
			data: JSON.stringify(params),
			success: function(data) {
					var list_html = $('#' + container).html();
					list_html += '<fieldset><legend>' + type + ' <span class="text-primary">(' + data.length + ')</span></legend>';
					for( var i=0; i<data.length; i++) {
						list_html += '<label style="white-space: nowrap;"><input type="checkbox" name="' + container + '" id="' + container + '_' + data[i].userid + '" value="' + data[i].userid + '"> ' +  data[i].username + ' <span id="' + container + '_' + data[i].userid + '_status_sent" class="badge badge-secondary" title="E-Mail sent status"><i class="fa fa-envelope"></i></span> <span id="' + container + '_' + data[i].userid + '_status_read" class="badge badge-secondary"  title="E-Mail read status"><i class="fa fa-eye"></i></span></label><br>';
					}
					list_html += '</fieldset>';
					$('#' + container).html(list_html);
				},
			error: function(data) {
					console.error('No recipients found or invalid request.');
				}
		});
	}

	/**
	 * Update a Recipient's E-Mail status
	 */
	function checkRecipientStatus(template) {
		var recipientsList = $("div[class='input-field'] fieldset label input:checkbox");
		console.info('Checking Mail status for all users on template ' + template);

		recipientsList.each(function(e){
			//console.info('Checking Mail status for user ' + $(this).val() + ' and template ' + template);
			var recipient_label = $(this).parent();
			var mail_sent_status = $(this).siblings("span[id$='sent']");
			var mail_read_status = $(this).siblings("span[id$='read']");
			const params = {
				template_id: template,
				recipient_id: $(this).val()
			};
			$.ajax({
				url: "/js/ajax/verein_mailer/get-recipientstatus.php?action=check",
				type: 'POST',
				data: JSON.stringify(params),
				success: function(data) {
						if (data) {
							recipient_label.addClass('text-success');
							mail_sent_status.addClass('badge-success');
							if (data.read_status) {
								var read_status_info = mail_read_status.html();
								read_status_info += ' <small>' + data.read_datetime + '</small>';
								mail_read_status.html(read_status_info);
								mail_read_status.addClass('badge-success');
							}
						}
					},
				error: function(data) {
						console.error('No recipients found or invalid request.');
					}
			});
		});
	}

	function updateMailPreview(tpl_id) {
		if (tpl_id <= 0 || typeof tpl_id == 'undefined') {
			console.error('tpl_id empty, not set or otherwise invalid. (updateMailPreview)');
			return false;
		}
		if (preview_mode == 'frame') {
			div_mail_preview.removeClass('hide-xs-up');
			preview_iframe.attr('src', '');
			enableTemplateUpdateMode(true);
			preview_iframe.attr('src', '/js/ajax/verein_mailer/get-mailpreview.php?action=preview&mailtpl_id=' + tpl_id);
		} else if (preview_mode == 'div') {
			console.info('Loading Template ' + tpl_id + ' into ' + preview_mode + '...');
			preview_div.load('/js/ajax/verein_mailer/get-mailpreview.php?action=preview&mailtpl_id=' + tpl_id, form_elements.serializeArray(), function(response, status, xhr){
			if(status == "success")
				console.info("Preview loaded successfully!");
			if(status == "error")
				console.error("Error: " + xhr.status + ": " + xhr.statusText);
			});
		}
		send_button.removeClass('hide-xs-up');
		hidden_template_id.val(tpl_id);
	}

	function enableTemplateUpdateMode(status) {
		if (status === true) {
			save_button.html('Update Template');
			update_mode = true;
			console.info('Update Mode: enabled');
		} else {
			save_button.html('Save Template');
			update_mode = false;
			console.info('Update Mode: disabled');
		}
	}

	function addUseridToRecipients(recipient_id) {
		hidden_selected_recipients.val(hidden_selected_recipients.val() + '"' + recipient_id + '",');
	}
	function removeUseridFromRecipients(recipient_id) {
		hidden_selected_recipients.val(hidden_selected_recipients.val().replace('"' + recipient_id + '",', ''));
	}

	// Nanobar Progressbar{/literal}
{if !$dev}{literal}
	var ajax_progressbar = new Nanobar();
	var progress = 0;
	var timer = setInterval(updateProgressbar, 1);
	//var timer = setInterval(updateProgressbar, 1);
	function updateProgressbar(){
	    ajax_progressbar.go(++progress);
	    if(progress == 100)
	        clearInterval(timer);
	}
	// Global AJAX request progress indicator
	$(document).ajaxStart(function() {
		updateProgressbar();
	});
	$(document).ajaxStop(function() {
		ajax_progressbar.go(0);
	});
{/literal}{/if}{literal}

	$(document).ready(function(){
		/**
		 * text inputs - Update Progressbar
		 */
		$("input[id^='text_']").on('input', function(){
			var thisId = $(this).attr('id');
			var textLength = $(this).val().length;
			var maxLength = $(this).attr('maxlength');
			var textLengthToPercent = 100/maxLength*textLength;
			var progressBar = $('#' + thisId + '_progressbar');
			var progressBarCount = $('#' + thisId + '_progress');
			progressBarCount.html(maxLength - textLength);
			progressBarCount.width(textLengthToPercent + '%');
			if(textLengthToPercent >= '80' && textLengthToPercent < '95') {
				progressBar.addClass("progress-warning").removeClass("progress-danger");
			} else if (textLengthToPercent >= '90') {
				progressBar.addClass("progress-danger").removeClass("progress-warning");
			} else {
				progressBar.removeClass("progress-warning").removeClass("progress-danger");
			}
		});

		/**
		 * Select-all Checkboxes
		 */
		$("input[id$='_alle']").on('click', function(){
			$(this).parent().toggleClass('text-primary');
			$(this).parent().siblings("div[class='input-field']").find("fieldset input:checkbox").prop('checked', $(this).is(':checked'));
			if ($(this).is(':checked')) {
				$(this).parent().siblings("div[class='input-field']").find("fieldset label").addClass('text-primary');
				$(this).parent().siblings("div[class='input-field']").find("fieldset input:checkbox").each(function(){
					addUseridToRecipients($(this).val());
					console.log('Adding user id to recipients: ' + $(this).val());
				});
			} else {
				$(this).parent().siblings("div[class='input-field']").find("fieldset label").removeClass('text-primary');
				$(this).parent().siblings("div[class='input-field']").find("fieldset input:checkbox").each(function(){
					removeUseridFromRecipients($(this).val());
					console.log('Removing user id from recipients: ' + $(this).val());
				});
			}
		});

		/**
		 * Add or remove recipient to hidden field
		 */
		$("div[id^='list_']").on('click', "input[type='checkbox']", function(){
			if ($(this).is(':checked')) {
				addUseridToRecipients($(this).val());
				console.log('Adding user id to recipients: ' + $(this).val());
			} else {
				removeUseridFromRecipients($(this).val());
				console.log('Removing user id from recipients: ' + $(this).val());
			}
			$(this).parent().toggleClass('text-primary');
		});

		/**
		 * Quill - Cleanup HTML
		 */
		quill.on('text-change', function(){
			const h_style = 'margin:0; mso-line-height-rule:exactly;';
			const p_style = 'margin:0;';
			const olul_style = 'padding:0; margin:0;';
			const li_style = 'margin:0;';
			var html = quill.root.innerHTML;
			console.info('Cleaning up HTML and updating text_mail_message...');
			html = html.replace(/\<h2>/g, '<h2 style="'+h_style+'">'); // <h2>
			html = html.replace(/\<h3>/g, '<h3 style="'+h_style+'">'); // <h3>
			html = html.replace(/\<p><\/p>/g, '<br>'); // <p></p> => <br>
			html = html.replace(/\<p>/g, '<p style="'+p_style+'">'); // <p>
			html = html.replace(/\<ol>/g, '<ol style="'+olul_style+'">'); // <ol>
			html = html.replace(/\<ul>/g, '<ul style="'+olul_style+'">'); // <ul>
			html = html.replace(/\<li>/g, '<li style="'+li_style+'">'); // <li>
			text_mail_message.val(html);
		});

		/**
		 * Load existing message
		 */
		$('#button_load_message').click(function(e){
			if (update_mode === true) {
				//$(this).children(':first').toggleClass('fa-edit').toggleClass('fa-close');
				$('#button_delete_message').prop('disabled', true);
				$(this).html('<i class="fa fa-edit"></i> load');
				$(this).removeClass('button-secondary');
				unloadTemplate();
			} else {
				var tpl_id = existing_tpls.val();
				existing_tpls.prop('disabled', true);
				$(this).prop('disabled', true);
				if (tpl_id <= 0 || typeof tpl_id == 'undefined') {
					console.error('tpl_id empty, not set or otherwise invalid. (#button_load_message)');
					$(this).prop('disabled', false);
					return false;
				} else {
					loadTemplate(tpl_id);
					$(this).html('<i class="fa fa-times-circle"></i> close');
					$(this).addClass('button-secondary');
					$('#button_delete_message').prop('disabled', false);
				}
			}
			$(this).prop('disabled', false);
		});

		/**
		 * button_save_template - Save or Update a Template
		 */
		save_button.click(function(){
			$(this).prop('disabled', true);
			if (update_mode === true && hidden_template_id.val() > 1) {
				console.info('Updating Mail Template ('+hidden_template_id.val()+')...');
				var action = 'update';
			} else {
				console.info('Saving Mail Template ('+hidden_template_id.val()+')...');
				var action = 'save';
			}
			$.ajax({
				url: '/js/ajax/verein_mailer/set-mailtemplate.php?action=' + action,
				type: 'POST',
				data: form_elements.serialize(),
				success: function(data) {
						console.info( action + ' Template ID: ' + data );
						updateMailPreview(data);
					},
				error: function(data) {
						console.error('Error while '+action+' Template ('+hidden_template_id.val()+')');
					}
			});
			getTemplates('dropdown_template_select');
			existing_tpls.find('option[value="'+hidden_template_id.val()+'"]').prop('selected', true);
			$(this).prop('disabled', false);
		});

		/**
		 * Send message
		 */
		send_button.click(function(e){
			var data_items = '';
			$(this).prop('disabled', true);
			$.ajax({
				url: '/js/ajax/verein_mailer/set-mailsend.php?action=send',
				type: 'POST',
				data: form_elements.serialize(),
				dataType: 'JSON',
				success: function(data) {
						console.log(data);
						$.each(data, function(index, item){
							data_items += '\n' + data[index].value;
							//form_elements.find("input[value='"+data+"']").parent().addClass('text-success');
							//form_elements.find("input[value='"+data+"']").siblings("span[id$='sent']").addClass('badge-success');
						});
						console.info( 'Sent e-mail to user id ' + data_items );
						send_button.addClass('button-success');
						checkRecipientStatus(hidden_template_id.val());
					},
				error: function(data) {
						console.log(data);
						$.each(data, function(index, value){
							data_items += '\n' + data[index].userid;
						});
						console.error('Error while sending mail to user ' . data_items);
						send_button.addClass('button-danger');
					}
			});
			$(this).prop('disabled', false);
		});

		/**
		 * Startup
		 */
		// Load existing mail templates
		getTemplates('dropdown_template_select');

		// Load recipient lists on page load
		getRecipients('vorstand', 'list_vorstand');
		getRecipients('kenner', 'list_kenner');
		getRecipients('mitglied', 'list_mitglieder');
	});
	{/literal}</script>
{else}
	<header class="text-center"><h1>Nothing to see here</h1>&hellip;oder Du muesch zerscht iilogge:
	<div class="row  row-around">
		<div class="col-4">
			{include file='file:layout/partials/loginform.tpl'}
		</div>
	</div>
{/if}
</body>
</html>
