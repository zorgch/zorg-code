<form id="formNewMessage" data-ajax="true" method="post" action="ajax_post_message.php">
	<input type="hidden" name="from_mobile" id="from_mobile" value="1{*$user_mobile*}">
	<input type="text" name="message" id="message" data-clear-btn="true" placeholder="Nachricht" autocomplete="off">
</form>
<div class="chatinputs">
	<div class="buttons">
		<a href="#" name="btnShareLocation" id="btnShareLocation" data-role="button" data-inline="true" data-mini="false" class="ui-btn ui-corner-all {$btnIconOptions} ui-btn-icon-notext ui-icon-location">Standort</a> <a href="#" name="btnShareImage" id="btnShareImage" data-role="button" data-inline="true" data-mini="false" class="ui-btn ui-corner-all {$btnIconOptions} ui-btn-icon-notext ui-icon-camera">Bild</a>
	</div>
	<div id="droparea" class="dz-message"></div>
	<div id="dropzone-previews" class="dropzone-previews"></div>
</div>
<script>{literal}
$('#message').focus(function(){
	$.mobile.silentScroll(0);
});
$('#message').keypress(function(e){
	if (e.which == 13){
		$(this).attr('disabled', 'disabled');
		if ($.trim($('#message').val()).length > 1) {
			$('#formNewMessage').submit(function(ev){
				$.ajax({
					type: $('#formNewMessage').attr('method'),
					url: $('#formNewMessage').attr('action'),
					data: $('#formNewMessage').serialize(),
					success: function (data) {
						$('#message').val('');
						window.location.reload(true);
					},
					error: function(jqXHR, textStatus, errorThrown) {
			           alert(textStatus + ' ' + errorThrown);
			        }
				});
				ev.preventDefault(); // avoid to execute the actual submit of the form.
			});
			$(this).removeAttr('disabled');
		} else {
			alert('Spinnsch, chasch doch nöd ä leeri nachricht abschicke!');
			$(this).removeAttr('disabled');
			return false;
		}
	}
});

/**
 * Google Maps API
 * @link https://developers.google.com/maps/documentation/javascript/examples/map-geolocation
 */
$('#btnShareLocation').click(function(){
	if (navigator.geolocation) {
	  	navigator.geolocation.getCurrentPosition(success);
	} else {
	  	console.log('Geolocation not supported!');
	  	$(this).remove();
	}
});
	function success(position) {
		var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
		latlng = String(latlng).replace('(', "").replace(')', "");
		var data = JSON.stringify({
	            from_mobile: '1',
	            location: latlng
	        }); console.log(data);
		$.ajax({
			type: 'post',
			url: 'ajax_post_location.php',
			data: { locationData: data },
			success: function (data) {
				console.log(data);
				window.location.reload(true);
			},
			error: function(jqXHR, textStatus, errorThrown) {
	           alert(textStatus + ' ' + errorThrown);
	        }
		});
	}

/**
 * Dragster - better drag'n'drop
 */
!function(e){e.fn.dragster=function(r){var t=e.extend({enter:e.noop,leave:e.noop,over:e.noop,drop:e.noop},r);return this.each(function(){var r=!1,n=!1,o=e(this);o.on({dragenter:function(e){return r?void(n=!0):(r=!0,o.trigger("dragster:enter",e),void e.preventDefault())},dragleave:function(e){n?n=!1:r&&(r=!1),r||n||o.trigger("dragster:leave",e),e.preventDefault()},dragover:function(e){o.trigger("dragster:over",e),e.preventDefault()},drop:function(e){n?n=!1:r&&(r=!1),r||n||o.trigger("dragster:drop",e),e.preventDefault()},"dragster:enter":t.enter,"dragster:leave":t.leave,"dragster:over":t.over,"dragster:drop":t.drop})})}}(jQuery);

/**
 * Image File Upload
 */
var imageDropzone = new Dropzone('#droparea', {
	url: 'ajax_post_image.php',
	clickable: '#btnShareImage',
	maxFilesize: 10,
	uploadMultiple: false,
	paramName: 'upload_file',
	addRemoveLinks: true,
	acceptedFiles: 'image/jpeg',//'image/*',
	dictDefaultMessage: 'Yo, drop dat pic here!',
	dictInvalidFileType: "Die Datei wird nöd unterstützt",
	dictFileTooBig: "Da Bild isch leider z'gross",
	dictMaxFilesExceeded: "Nur 1 Bild ufsmol bitte",
	success: function(file, response){
    	console.log(response);
    	window.location.reload(true);
    }
})
.on('addedfile', function(file) {
	//$('#footer').height("+=50");
})
.on('complete', function(file) {
	//imageDropzone.removeFile(file);
});
// Add/remove class when file is dragged over the dropzone. Hover effect
$('#droparea').dragster({
    enter : function(){
        $(this).addClass('hover');
    },
    leave : function(e){
        e.stopPropagation();
        $(this).removeClass('hover');
    }
});
// Show/hide dropzones until a file is dragged into the browser window. Hide dropzones after file is dropped or dragging is stopped
$(document).dragster({
	enter : function(){
        $('#droparea').show();
    },
    leave : function(){
		setTimeout(function(){
			$('#droparea').hide();
		}, 2000);
    }
})
// Prevent defaults (file is openened in the browser) if user drop file outside a dropzone
.on('dragover', function(e){
    e.preventDefault();
})
.on('drop', function(e){
    e.preventDefault();
    $(document).trigger('dragleave');
});
{/literal}</script>