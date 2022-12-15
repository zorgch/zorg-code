<!DOCTYPE html>
<html lang="de">{if $sun == 'up' || $smarty.get.sun == 'up'}{assign var=daytime value=day}{else}{assign var=daytime value=night}{/if}
{assign var=dev value=false}
{* Can be used for Album-Edit later: *}
{if $smarty.get.album_id > 0}{assign var=album_id value=$smarty.get.album_id}{/if}
{if $smarty.get.album_name != ''}{assign var=album_name value=$smarty.get.album_name}{/if}
<head>
	<meta charset="utf-8">
	<title>{$tplroot.page_title}</title>
	<meta name="description" content="Neue Galleries braucht zorg!">
	<meta name="robots" content="none, notranslate" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	{include file="file:layout/partials/head/favicons.tpl"}
	<link rel="stylesheet" href="{$smarty.const.CSS_DIR}shoelace/shoelace.min.css">
	{if $user->typ >= $usertyp->member}
	<script src="https://kit.fontawesome.com/e9effb9c00.js" crossorigin="anonymous"></script>{*if !$dev}<link href="{$smarty.const.CSS_DIR}fontawesome.min.css" rel="stylesheet">{/if*}
	<script src="{$smarty.const.JS_DIR}jquery-3.5.1.min.js"></script>
	<script src="{$smarty.const.CSS_DIR}shoelace/shoelace.min.js"></script>
	<script src="{$smarty.const.JS_DIR}nanobar.min.js"></script>
	<!-- DropzoneJS -->
	{if !$dev}<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
	<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css">
	{else}
	<!-- <script src="{$smarty.const.JS_DIR}dropzone.js"></script> -->
	<script src="{$smarty.const.JS_DIR}dropzone/dropzone.min.js"></script>
	<link rel="stylesheet" href="{$smarty.const.JS_DIR}dropzone/dropzone.min.css">{/if}
	<style>{literal}
		/** Nanobar */
		.nanobar .bar{background-color:#0074d9}

		/**
		 * Image Dropzone
		 * @link http://www.dropzonejs.com/
		 * @link http://stackoverflow.com/questions/27732216/on-drag-change-stylesheets-for-all-dropzones-file-upload
		 */
		.dropzone {
			background: transparent !important;
			border: 0.2em dashed lightskyblue !important;
			border-radius: 1em;
			min-height: 1em;
			display: flex;
			flex-wrap: wrap;
			justify-content: center;
		}
		.dropzone .dz-message { margin: 0; }
		.dropzone.dz-drag-hover .dz-message { color: lightgreen; }
		.dz-drag-hover {/*, .dropzone.dz-clickable:hover*/
			opacity: .75;
			border-color: lightgreen !important;
		}
		.dz-message {
			color: lightskyblue;
			font-size: 3em;
		}
		.dz-preview {
			{/literal}{if $daytime == 'night'}filter: invert(100%);{/if}{literal}
			/*border: solid 1px lightgray; -> sieht doof aus
			border-radius: 20px;*/
			z-index: 2;
		}
		.dz-preview.dz-image-preview { background: transparent !important; }
		.dz-image img {
			object-fit: cover;
			width: 100%;
			height: 100%;
		}
		span[data-dz-size]:empty, span[data-dz-name]:empty { display: none; }
		.dz-progress { display: none; }
		.dz-error > .dz-details {
			/* padding: 0 !important; */
			color: lightcoral !important;
		}
		.dz-remove { visibility: hidden !important; }
		.dz-preview:hover > .dz-remove { visibility: visible !important; }
		.dz-remove:hover {
			color: lightcoral !important;
			text-decoration: none !important;
		}
		.dz-succcess-mark svg {
			background: lightgreen;
			border-radius: 30px;
		}
		.dz-error {
			border: 2px solid lightcoral;
			border-radius: 20px;
		}
		.dz-error-message { margin-top: 1.5em; }
		.dz-error-mark svg {
			display: none !important;
			/* background: lightgoldenrodyellow;
			border-radius: 30px; */
		}
		.hint {
			display: inline-block;
			padding-bottom: 1.5rem;
			color: gray;
		}
		/* Mobile */
		@media (max-width: 805px) {
			.dz-image {
				width: 280px !important;
				height: 280px !important;
			}
			.dz-remove {
				font-size: 1.5em !important;
			}
		}

		/** Draggable zone */
		/* div#drop-ems-pix-doooo.in {
			line-height: 50px;
		}
		div#drop-ems-pix-doooo.hover {
			background: #00b9fb40;
			display: block;
		} */

		.hidden { display: none; }
		.alert { {/literal}{if $daytime == 'night'}filter: invert(100%);{/if}{literal} }
	{/literal}</style>
	{/if}
</head>

<body{if $sun == "down" || $user->zorger} style="background:#242424; filter:invert(90%);"{/if}>
{if $user->id > 0}
	<header class="text-center">
		<h1>zorg Gallery Maker</h1>
		<p class="text-secondary text-small">Lad ems Pics ufe doooo</p>
		<a class="text-small" href="{$smarty.const.SITE_URL}/gallery.php{if $album_id > 0}?show=albumThumbs&albID={$album_id}{/if}">↩ back to zorg</a>
		<hr>
		{if $error.title <> ''}{include file="file:layout/elements/block_error.tpl"}{/if}
	</header>

	<main class="container">
	<input type="hidden" name="nonce" id="hidden-nonce" value="{if $nonce <> ''}{$nonce}{/if}">
	<div id="settings-section">
		<div class="row row-around">
			<div class="col">
				<h2>Album auswählen</h2>
			</div>
		</div>
		<div class="row row-around">
			<div class="col-12 col-lg-5">
				<div class="input-group">
					<select name="dropdown_list_select" id="dropdown_list_select" class="input-lg">
						<option label="--- Gallery auswählen ---" disabled selected>--- Gallery auswählen ---</option>
					</select>
					<button type="button" id="load-album" class="button-lg" disabled><i class="fa fa-folder-open"></i> load</button>
				</div>
			</div>
			<div class="col col-lg-1 pad-sm text-center">
				oder&hellip;
			</div>
			<div class="col-12 col-lg-5">
				<label for="album-name" class="hidden">Neues Album erstellen:</label>
				<div class="input-group">
					<input type="text" name="album-name" id="album-name" class="input-lg" minlength="3" maxlength="50" placeholder="Neues Album erstellen…" autocomplete="off" {if $album_name != ''}value="{$album_name}"{else if $dev}value=""{/if}>
					<button type="submit" id="create-album" name="create-album" class="button-primary button-lg" value="ok" {if $album_name == ''}disabled{/if}><i class="fa fa-plus"></i> Create</button>
				</div>
			</div>
		</div>
		<div class="row row-around">
			<div class="col">
				<div id="album-status" class="alert hidden"></div>
			</div>
		</div>
	</div>
	<div id="upload-section" class="hidden">
		<div class="row row-around mar-y-md">
			<div class="col-12">
				<h3 id="dropzone-heading">Bilder auswählen</h3>
			</div>
			<div class="col-12">
				<div id="upload-status" class="alert hidden"></div>
			</div>
			<div class="col">
				<form id="drop-ems-pix-doooo" class="dropzone" method="post" enctype="multipart/form-data" action="/js/ajax/gallery/add-albumpic.php?action=add">
					<input type="hidden" name="album_id" id="album_id" value="{if $album_id > 0}{$album_id}{/if}">
					<input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
					<div class="dz-message" data-dz-message>+ add pics</div>
					<div class="fallback">
						<input type="file" id="add-dems-pix" name="dropzone-pic" class="button-primary mar-xs">
					</div>
				</form>
				<small id="dropzone-hint" class="hint"><strong>PNG</strong> or <strong>JPEG</strong> images of <strong>max. 4 MB each</strong>.</small>
			</div>
		</div>
		<div class="row row-between row-flush">
			<div class="col-12 col-md-6 col-lg-2 order-2 order-md-1 offset-lg-4 text-center">
				<button type="button" id="upload-pics" class="button-block button-lg button-success" disabled {if $daytime == 'night'}style="filter: invert(100%);"{/if}><i class="fa fa-upload"></i> Upload</button>
			</div>
			<div class="col-12 col-md-6 col-lg-6 order-1 order-md-2 offset-0 pad-y-xs">
				<!-- label class="pad-xs"><input type="checkbox" name="add-activity" id="add-activity" value="true"> Share as new Activity</label -->
				<span class="switch switch-primary">
				  <input type="checkbox" id="activity-switch" name="add-activity" value="true" checked disabled>
				  <label for="activity-switch">Als neue Aktivität teilen</label>
				</span>
			</div>
		</div>
		<div class="row row-around"><div class="col mar-y-md">&nbsp;</div></div>
	</div>
	</main>

<script>{literal}
	/** Nanobar */
	var ajax_progressbar = new Nanobar();
	var progress = 0;
	var timer = setInterval(updateProgressbar, 1);
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

	/**
	 * Dropzone
	 */
	const uploaded_pic_ids = [];
	const jpegception = ['jpeg', 'jpe', 'jif', 'jfif', 'jfi', 'jp2', 'j2k', 'jpf', 'jpx', 'jpm', 'mj2'];
	Dropzone.options.dropEmsPixDoooo = {
		 maxFiles: null
		,filesizeBase: 1000
		,maxFilesize: 4 // MB
		,acceptedFiles: 'image/png,image/jpg,image/jpeg'
		,preventDuplicates: true
		,autoProcessQueue: false
		,uploadMultiple: false
		,parallelUploads: 1
		,addRemoveLinks: true
		,fixOrientation: true
		,thumbnailMethod: 'contain'
		,thumbnailWidth: 120
		,paramName: 'dropzone-pic' // Form File Name-ID param for upload transfer (multiple files = paramName[] Array)
		,dictDefaultMessage: '+ add pics'
		,dictCancelUpload: 'Staaahp!'
		,dictCancelUploadConfirmation: 'Jup'
		,dictDuplicateFile: 'Meh vom Gliiche goht doch nöd!'
		,dictRemoveFile: '🗑 remove'
		,renameFile: function(file) { // Invoked before file is uploaded to server to rename it
			var extension = file.name.split('.').pop().toLowerCase();
			if (jpegception.indexOf(extension) >= 0) { extension = 'jpg'; } // Harmonize 'jpeg'-type extensions to 'jpg'
			return 'pic.'+extension;
		}
		//,accept: function(file, done) { // Rules to verify files before accepting them
			// if (file.name == "gallery_pic.jpg") {
			// 	done("Naha, you don't.");
			// } else { done(); }
		//}
		,init: function() {
			mimsDropzone = this;
			this.on('removedfile', function(file){
				showElementCSS(gallery_select_status, false);
				showElementCSS(upload_status, false);
				if (this.files.length <= 0) {
					console.log("All files REMOVED.");
					enableElementProp(notification_switch, false);
					enableElementProp(upload_button, false);
				}
			});
			this.on('addedfile', function(file, errorMessage, xhr){
				showElementCSS(gallery_select_status, false);
				showElementCSS(upload_status, false);

				/* Duplicate check & discard */
				if (this.files.length >= 0) {
					var i, len, pre;
					for (i=0, len=this.files.length; i<len-1; i++) {
						if (this.files[i].name == file.name) {
							this.files.length = this.files.length-1;
							upload_status.removeClass(info+' '+success).addClass(error).html('DUPLICATE: «'+file.name+'» → not added!');
							showElementCSS(upload_status, true);
							//alert("DUPLICATE: «" + file.name + "» → not added")
							return (pre = file.previewElement) != null ? pre.parentNode.removeChild(file.previewElement) : void 0;
						}
					}
				}

				/* Remove the "Remove"-link on Serverfiles */
				if (dropzone_serverfiles) {
					file.previewElement.removeChild(file.previewElement.lastChild);
				} else {
				/* Enable Upload of Accepted files */
					console.log(file);
					if (this.getAcceptedFiles().length >= 0) {
						enableElementProp(notification_switch, true);
						upload_button.html('<i class="fa fa-upload"></i> Upload');
						enableElementProp(upload_button, true);
						console.log('Added Files: '+this.getAddedFiles().length);
					} else {
						/* There were errors with added Files */
						upload_status.removeClass(info+' '+success).addClass(error).html('Pic(s) could NOT be added: '+errorMessage);
						showElementCSS(upload_status, true);
					}
				}
			});
			this.on('processing', function(file){ // Called when a file gets processed
				console.log('Processing file...');
				showElementCSS(gallery_select_status, false);
				showElementCSS(upload_status, false);
			});
			this.on('sending', function(file, xhr, formData){ // Called just before the file is sent
				console.log('Sending file for album '+hidden_albumid_input.val()+'...');
				formData.append('nonce', nonce_token.val());
				formData.append('album_id', hidden_albumid_input.val());
				if (typeof(file.lastModified) !== 'undefined' && file.lastModified > 0) formData.append('lastModified', file.lastModified);
			});
			this.on('complete', function(file){ // upload is finished, either with success or an error
				if (!dropzone_serverfiles) {
					//imageDropzone.removeFile(file);
					console.log('Transfer completed. Queued Files: '+this.getQueuedFiles().length);
					for (i=0; i<this.getQueuedFiles().length; i++) {
						this.processQueue(); // Continue releasing the Kraken...
					}
				}
			});
			this.on('success', function(file, responseText){
				console.info('[success] Gallery Pic: '+file.name);
				uploaded_pic_ids.push(Number(responseText));
				file.previewElement.removeChild(file.previewElement.lastChild); // Remove the "Remove"-link on the uploaded File
				if (this.getQueuedFiles().length === 0 && this.getUploadingFiles().length === 0){
					if (uploaded_pic_ids.length > 0) {
						upload_status.removeClass(info+' '+error).addClass(success).html(uploaded_pic_ids.length+' Pics successfully added! → <a href="'+gallery_deeplink + gallery_id+'" target="_blank">Luegsch dooo</a>');
						upload_button.html('<i class="fa fa-check"></i> done');

						/* Notification - if enabled */
						if (notification_switch.is(':checked')) AJAXpostActivity(encodeURIComponent(sprintf(notification_text, uploaded_pic_ids.length, gallery_deeplink, gallery_id, gallery_name)));
					} else {
						console.warn('AJAX successfull but array data incomplete: ' + uploaded_pic_ids.length);
						upload_status.removeClass(info+' '+success).addClass(error).html('Invalid response from Server received.');
						upload_button.html('<i class="fa fa-upload"></i> Upload');
					}
					showElementCSS(upload_status, true);
					upload_button.removeClass('button-loader');
				}
			});
			this.on('error', function(file, message){
				/* this.removeFile(file); */
				console.error('[error:'+message+'] Gallery Pic: '+file.name);
				if (this.getQueuedFiles().length === 0 && this.getUploadingFiles().length === 0){
					upload_status.removeClass(info+' '+success).addClass(error).html('Error uploading Pic(s).');
					showElementCSS(upload_status, true);
					enableElementProp(notification_switch, true);
					upload_button.html('<i class="fa fa-upload"></i> Upload');
					upload_button.removeClass('button-loader');
					enableElementProp(upload_button, true);
				}
			});
		}
	};

	/**
	 * Dragster - better drag'n'drop
	 */
	!function(e){e.fn.dragster=function(r){var t=e.extend({enter:e.noop,leave:e.noop,over:e.noop,drop:e.noop},r);return this.each(function(){var r=!1,n=!1,o=e(this);o.on({dragenter:function(e){return r?void(n=!0):(r=!0,o.trigger("dragster:enter",e),void e.preventDefault())},dragleave:function(e){n?n=!1:r&&(r=!1),r||n||o.trigger("dragster:leave",e),e.preventDefault()},dragover:function(e){o.trigger("dragster:over",e),e.preventDefault()},drop:function(e){n?n=!1:r&&(r=!1),r||n||o.trigger("dragster:drop",e),e.preventDefault()},"dragster:enter":t.enter,"dragster:leave":t.leave,"dragster:over":t.over,"dragster:drop":t.drop})})}}(jQuery);

	/** Change Element visibility */
	function showElementCSS(elem, show) {
		if (typeof elem !== 'undefined') {
			if (!show) { elem.addClass('hidden'); }
			else {
				elem.removeClass('hidden');
				$('html,body').animate({scrollTop: elem.offset().top},'fast'); // Scroll to element
			}
		} else {
			console.warn('Cannot add Class to ' + elem + ': undefined');
		}
	}

	/** Upload Section visibility */
	function enableElementProp(elem, enable) {
		if (typeof elem !== 'undefined') {
			if (!enable) { elem.prop('disabled', true); }
			else { elem.prop('disabled', false); }
		} else {
			console.warn('Cannot add Attribute to ' + elem + ': undefined');
		}
	}

	/** Add HTML Element to existing DOM-element */
	function addElementHtml(parentElem, childHtml) {
		if (typeof parentElem !== 'undefined') {
			parentElem.append(childHtml);
		} else {
			console.warn('Cannot attach to ' + parentElem + ': undefined');
		}
	}

	/** Validate String for only regular Unicode Chars */
	function validateString(text) {
		let badValues = /[^\p{L}\w\s\d\-\.]/giu;
		return text = text.replace('  ', ' ').replace(badValues, '');
	}

	/** Load existing Galleries */
	function AJAXgetGalleries(container) {
		var selected_album_id = hidden_albumid_input.val();
		$.ajax({
			url: '/js/ajax/gallery/get-albums.php?action=list&showall=true',
			type: 'GET',
			success: function(data) {
				var list_html = existing_list_initial;
				if ($.trim(data) != '') {
					for (var i=0; i<data.length; i++) {
						var option_id = container + '_' + data[i].id;
						//var option_label = data[i].name + ' — #' + data[i].id + (!!data[i].created ? ' @ ' + data[i].created : '');
						var option_value = data[i].id;
						var option_selected = (data[i].id == selected_album_id ? 'selected' : '');
						list_html += '<option id="' + option_id + '" label="' + data[i].name + '" value="' + option_value + '" '+option_selected+'>' + data[i].name + '</option>';
					}
				} else {
					/* No (empty) Galleries returned */
					list_html += '<option id="empty" label="[ No empty Galleries ]" value="" selected>[ No empty Galleries ]</option>';
					enableElementProp(dropdown_list, false);
					enableElementProp(load_gallery_button, false);
				}
				$('#' + container).html(list_html);
				//addElementHtml('#' + container, list_html);

				/* Update pre-selected Gallery Vars */
				if ($.trim(data) != '' && hidden_albumid_input.val() > 0) {
					gallery_id = hidden_albumid_input.val();
					dropdown_list.change();
					gallery_name = dropdown_list.find('option:selected').attr('label');
				}
			},
			error: function(data) {
				console.error('No Galleries found - or invalid request.');
			}
		});
	}

	/** Load existing Pics of a Gallery */
	function AJAXgetPics(album) {
		if (typeof(Number(album)) == 'number' && Number(album) > 0)
		{
			$.ajax({
				url: '/js/ajax/gallery/get-albumpics.php?action=fetch&album_id=' + album,
				type: 'GET',
				success: function(data) {
					var list_html = existing_list_initial;
					if ($.trim(data) != '') {
						dropzone_serverfiles = true; // Enable
						for (var i=0; i<data.length; i++) {
							var dzFilename = ($.trim(data[i].title) != '' ? $('<div/>').html(data[i].title).text() : '#'+data[i].id );
							let dzFileTemplate = { name: dzFilename, size: 0 };
							let dzCallback = null; // Optional callback when it's done
							let dzCossorigin = null; // Added to the `img` tag for crossOrigin handling
							let dzResizeThumbnail = false; // Tells Dropzone whether it should resize the image first
							mimsDropzone.displayExistingFile(dzFileTemplate, data[i].url, dzCallback, dzCossorigin, dzResizeThumbnail);
						}
					} else {
						/* No Pics returned / empty Gallery */
						console.info('No Pics found - empty Gallery maybe.');
					}
					dropzone_serverfiles = false; // Disable
					upload_status.removeClass(error+' '+success).addClass(info).html((data.length > 0 ? data.length+' Pics der ' : '')+'<a href="'+gallery_deeplink + gallery_id+'" target="_blank">Gallery</a> erfolgreich geladen');
					showElementCSS(upload_status, true);
					showElementCSS(upload_section, true);
				},
				error: function(data) {
					console.error('No Pics found - or invalid request.');
					enableElementProp(dropdown_list, true);
					enableElementProp(load_gallery_button, true);
					enableElementProp(name_input, true);
					enableElementProp(add_gallery_button, true);
					showElementCSS(upload_status, false);
					showElementCSS(upload_section, false);
				}
			});
		} else {
			console.error('Invalid Album-ID (not numeric/integer!):' + album);
			enableElementProp(dropdown_list, true);
			enableElementProp(load_gallery_button, true);
			enableElementProp(name_input, true);
			enableElementProp(add_gallery_button, true);
			showElementCSS(upload_status, false);
			showElementCSS(upload_section, false);
		}
	}

	/** Add a new Gallery Album */
	function AJAXaddAlbum(name) {
		var action = 'add';
		var datastream = nonce_token.serialize() + '&' + name_input.serialize(); // Serialize only works when Input NOT DISABLED!
		enableElementProp(name_input, false);
		$.ajax({
			url: '/js/ajax/gallery/add-album.php?action='+action,
			type: 'POST',
			data: datastream,
			success: function(data) {
				console.info( action + ' Gallery: ' + data );
				var new_gallery_id = Number(data);
				if (typeof(new_gallery_id) == 'number' && new_gallery_id > 0) {
					hidden_albumid_input.val(new_gallery_id);
					gallery_id = new_gallery_id;
					var dropdown_list_entry_text = gallery_name;// + ' — #' + gallery_id + ' @ ' + (new Date().toDateString());
					var dropdown_list_entry = '<option id="' + gallery_id + '" label="' + dropdown_list_entry_text + '" value="' + gallery_id + '" selected>' + dropdown_list_entry_text +'</option>';
					addElementHtml(dropdown_list, dropdown_list_entry);
					url.searchParams.set('album_id', gallery_id);// Update URL with Parameter
					window.history.pushState(null, null, url);
					dropzone_heading.text('Bilder auswählen für «' + gallery_name + '»');
					gallery_select_status.removeClass(info+' '+error).addClass(success).html('Neue Gallery mit id #' + gallery_id + ' erfolgreich erstellt!');
					showElementCSS(upload_section, true);
				} else {
					console.warn('AJAX successfull but data not a valid Integer: ' + data);
					gallery_select_status.removeClass(info+' '+success).addClass(error).html('Neue Gallery konnte nicht erstellt werden - probiers nachememe <a href="{/literal}{$self}{literal}?album_name='+encodeURIComponent(name_input.val())+'">Page reload</a> nomel.');
					enableElementProp(name_input, true);
					enableElementProp(add_gallery_button, true);
					enableElementProp(dropdown_list, true);
					enableElementProp(load_gallery_button, true);
				}
				showElementCSS(gallery_select_status, true);
			},
			error: function(data) {
				console.error('Error while '+action+' Gallery: '+gallery_name);
				gallery_select_status.removeClass(info+' '+success).addClass(error).html('Neue Gallery konnte nicht erstellt werden - probiers nachememe <a href="{/literal}{$self}{literal}?album_name='+encodeURIComponent(name_input.val())+'">Page reload</a> nomel.');
				enableElementProp(name_input, true);
				enableElementProp(add_gallery_button, true);
				enableElementProp(dropdown_list, true);
				enableElementProp(load_gallery_button, true);
			}
		});
	}

	/** Post new Activity */
	function AJAXpostActivity(text) {
		if (notification_switch.is(':checked'))
		{
			var action = 'post';
			var datastream = nonce_token.serialize() + '&' + 'activity='+text;
			$.ajax({
				url: '/js/ajax/activities/add-activity.php?action='+action,
				type: 'POST',
				data: datastream,
				success: function(data) {
					console.info( action + ' Activity: ' + data );
				},
				error: function(data) {
					console.warn('Error while '+action+' Activity: '+data);
				}
			});
			notification_switch.prop('checked', false);
		}
	}

	/** Init - once Page is loaded */
	const sprintf = (str, ...argv) => !argv.length ? str : sprintf(str = str.replace(sprintf.token||'$', argv.shift()), ...argv);
	const gallery_deeplink = '/gallery.php?show=albumThumbs&albID=';
	const nonce_token = $('#hidden-nonce');
	const settings_section = $('#settings-section');
	const dropdown_list = $('#dropdown_list_select');
	const existing_list_initial = $('#dropdown_list_select').html();
	const load_gallery_button = $('#load-album');
	const name_input = $('#album-name');
	const add_gallery_button = $('#create-album');
	const gallery_select_status = $('#album-status');
	const upload_section = $('#upload-section');
	const dropzone_heading = $('#dropzone-heading');
	const upload_status = $('#upload-status');
	const upload_button = $('#upload-pics');
	const notification_switch = $('#activity-switch');
	const notification_text = 'hat $ Pics zur Gallery «<a href="$$">$</a>» hinzugefügt.';
	const success = 'alert-success';
	const info = 'alert-info';
	const warn = 'alert-warning';
	const error = 'alert-danger';
	var url = new URL(window.location.href);
	var dropzone_serverfiles = false;
	var hidden_albumid_input = $('#album_id');
	var gallery_id = 0;
	var gallery_name = '';

	$(document).ready(function(){
		/** dropdown_list_select - Get Album Name value */
		dropdown_list.on('change', function() {
			if (dropdown_list.val() > 0) {
				gallery_name = $(this).find('option:selected').attr('label');
				enableElementProp(load_gallery_button, true);
				//enableElementProp(name_input, false);
				//enableElementProp(add_gallery_button, false);
			} else {
				gallery_name = '';
				enableElementProp(load_gallery_button, false);
				enableElementProp(name_input, true);
				enableElementProp(add_gallery_button, true);
			}
		});

		/** name_input - Check Album Name value */
		name_input.on('input', function(){
			$(this).val(validateString($(this).val()));
			var textLength = $(this).val().length;
			var minLength = $(this).attr('minlength');
			var maxLength = $(this).attr('maxlength');
			if(textLength >= minLength && textLength <= maxLength) {
				enableElementProp(add_gallery_button, true);
				enableElementProp(dropdown_list, false);
				enableElementProp(load_gallery_button, false);
			} else {
				enableElementProp(add_gallery_button, false);
				enableElementProp(dropdown_list, true);
				enableElementProp(load_gallery_button, true);
			}
		});

		/** load-album button - Load an existing Album */
		load_gallery_button.click(function(){
			if (!load_gallery_button.is(':disabled') && dropdown_list.val() > 0) {
				$(this).prop('disabled', true);
				enableElementProp(dropdown_list, false);
				enableElementProp(name_input, false);
				enableElementProp(add_gallery_button, false);
				var selected_gallery_id = dropdown_list.val();
				console.log('Loading Gallery #' + selected_gallery_id);
				hidden_albumid_input.val(selected_gallery_id);
				gallery_id = selected_gallery_id;
				url.searchParams.set('album_id', gallery_id);// Update URL with Parameter
				window.history.pushState(null, null, url);
				dropzone_heading.text('Bilder verwalten für «' + gallery_name + '»');
				AJAXgetPics(gallery_id)
			} else {
				$(this).prop('disabled', false);
				enableElementProp(name_input, true);
				enableElementProp(add_gallery_button, true);
				showElementCSS(upload_status, false);
				showElementCSS(upload_section, false);
			}
		});

		/** add_album button - Add a new Album */
		add_gallery_button.click(function(){
			if (!add_gallery_button.is(':disabled')) {
				$(this).prop('disabled', true);
				//enableElementProp(name_input, false);
				enableElementProp(dropdown_list, false);
				enableElementProp(load_gallery_button, false);
				gallery_name = name_input.val();
				AJAXaddAlbum(gallery_name);
			} else {
				console.log('Album name invalid');
				$(this).prop('disabled', false);
				enableElementProp(name_input, true);
				enableElementProp(dropdown_list, true);
				enableElementProp(load_gallery_button, true);
				showElementCSS(gallery_select_status, false);
				showElementCSS(upload_section, false);
			}
		});

		/** upload-pic - Start uploading all Dropzone Pics */
		upload_button.click(function(){
			$(this).addClass('button-loader');
			enableElementProp($(this), false);
			enableElementProp(notification_switch, false);
			if (Number(hidden_albumid_input.val()) > 0) {
				if (mimsDropzone.getAcceptedFiles().length > 0) {
					console.info('Starting upload... ('+mimsDropzone.getAcceptedFiles().length+' accepted, '+mimsDropzone.getQueuedFiles().length+' queued)');
					mimsDropzone.processQueue(); // Release the Kraken...
				} else {
					console.warn('No Files to upload added in Dropzone!');
					upload_status.removeClass(info+' '+success).addClass(error).text('Kei (neui) Bilder zum ufelade?!');
					showElementCSS(upload_status, true);
					enableElementProp(notification_switch, true);
					$(this).removeClass('button-loader');
					enableElementProp($(this), true);
				}
			} else {
				console.error('Gallery ID for Pic seems to mismatch: '+album+' passed, '+hidden_albumid_input.val()+' found');
				enableElementProp(notification_switch, true);
				$(this).val('Upload');
				$(this).removeClass('button-loader');
				enableElementProp($(this), true);
			}
		});

		/* Autoexec on DOM ready */
		AJAXgetGalleries(dropdown_list.attr('id'));
	});
{/literal}</script>

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
