<div data-role="popup" id="popupLogin" data-theme="a" class="ui-corner-all" style="padding:10px 20px;">
	<form id="formLogin" data-ajax="false" method="post" action="{$smarty.server.SCRIPT_NAME}">
			<h3>Bitte anmelden</h3>
			<p style="color:red;">{$login_error}</p>
			<label for="user" class="ui-hidden-accessible">Username:</label>
			<input type="text" name="username" id="user" placeholder="Username" data-theme="a" value="{$smarty.post.username}">
			<label for="pass" class="ui-hidden-accessible">Passwort:</label>
			<input type="password" name="password" id="pass" placeholder="Passwort" data-theme="a">
			<label for="cookie">Angemeldet bleiben?</label>
			<select name="cookie" id="cookie" data-role="slider" data-mini="true">
			    <option value="false">Nein</option>
			    <option value="true">Ja</option>
			</select>
			<button type="submit" name="login" id="loginButton" value="login" class="ui-btn ui-corner-all ui-nodisc-icon ui-btn-b ui-btn-icon-right ui-icon-check">Login</button>
			<a id="showPwReset" href="#popupPwReset" data-rel="popup" data-position-to="origin">Passwort vergessen?</a>
	</form>
</div>
<div data-role="popup" id="popupPwReset" data-theme="a" class="ui-corner-all" style="padding:10px 20px;">
	<form id="formPwReset" data-ajax="false" method="post" action="ajax_exec_pwreset.php">
			<h3>Achtung, Hiermit wird dir ein neues Passwort gesetzt und zugesendet!</h3>
			<p>Du kannst es dann sp&auml;ter in deinem Profil auf zorg.ch wieder &auml;ndern</p>
			<p style="color:red;">{$login_error}</p>
			<label for="email" class="ui-hidden-accessible">Deine E-Mail:</label>
			<input type="email" name="email" id="email" placeholder="Deine E-Mail" data-theme="a">
			<button type="submit" name="resetPw" id="buttonPwReset" value="true" class="ui-btn ui-corner-all ui-nodisc-icon ui-btn-b ui-btn-icon-right ui-icon-refresh">Neues Passwort</button>
			<a data-role="button" href="#" data-rel="back" data-inline="true" data-mini="true" class="ui-btn ui-corner-all ui-nodisc-icon ui-btn-b ui-btn-icon-left ui-icon-delete">Oops abbreche, ABBRECHE!</a>
	</form>
</div>
<script>
$(document).on("pageinit", '.ui-page', function(event){ldelim}
	{if $login_error <> ''}setTimeout(function(){ldelim}$('#popupLogin').popup('open'){rdelim}, 100);{/if}
{rdelim});

{literal}
$(document).ready(function() {
	$('#showPwReset').click(function(){
		$('#popupLogin').popup('close');
		window.setTimeout(function(){ $('#popupPwReset').popup('open') }, 50);
	});

    $('#buttonPwReset').click(function(ev) {
        if ($.trim($('#email').val()).length > 1) {
			$.ajax({
				type: $('#formPwReset').attr('method'),
				url: $('#formPwReset').attr('action'),
				data: $('#formPwReset').serialize(),
				success: function (data) {
					console.log(data);
					$('#popupPwReset').popup('close');
					window.setTimeout(function(){ $('#popupLogin').popup('open') }, 50);
					//window.location.reload(true);
				},
				error: function(jqXHR, textStatus, errorThrown) {
		           alert(textStatus + ' ' + errorThrown);
		        }
			});
			ev.preventDefault(); // avoid to execute the actual submit of the form.
		} else {
			alert('Also dini E-Mailadresse muesch halt scho au ageh ;-)');
			return false;
		}
    });
});
{/literal}

{* -- Input Form Validator (enable Submit button only when all fields valid) --
	$('#formLogin input').on('keyup blur', function (){
		if ($("#user").val().length > 0) {
			if ($("#pass").val().length > 0) {
				$("#loginButton").prop('disabled', false);
			} else {
				$("#loginButton").prop('disabled', 'disabled');
			}
		} else {
			$("#loginButton").prop('disabled', 'disabled');
		}
		});
	});
*}
</script>