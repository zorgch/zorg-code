<div data-ui-role="popup" id="popupLogin" data-theme="a" class="ui-corner-all" style="padding:10px 20px;">
	<form id="formLogin" data-ajax="false" method="post" action="{$smarty.server.SCRIPT_NAME}">
		<input type="hidden" name="do" id="do" value="login">
		<h3>Bitte anmelden</h3>
		<p id="login_error" style="color:red;">{$login_error}</p>
		<label for="user" class="ui-hidden-accessible">Username:</label>
		<input type="text" name="username" id="user" placeholder="Username" data-theme="a" value="{if isset($smarty.post.username)}{$smarty.post.username}{/if}">
		<label for="pass" class="ui-hidden-accessible">Passwort:</label>
		<input type="password" name="password" id="pass" placeholder="Passwort" data-theme="a">
		<label for="autologin">Angemeldet bleiben?</label>
		<select name="autologin" id="autologin" data-role="slider" data-mini="true">
		    <option value="false">Nein</option>
		    <option value="cookie">Ja</option>
		</select>
		<button type="submit" name="login" id="loginButton" value="login" class="ui-btn ui-corner-all ui-nodisc-icon ui-btn-b ui-widget-icon-floatend ui-icon-check">Login</button>
		<p><a id="showPwReset" href="#popupPwReset" data-rel="popup" data-position-to="origin">Passwort vergessen?</a></p>
		<p><a id="showNewUser" href="#popupNewUser" data-rel="popup" data-position-to="origin">Neuen User erstellen?</a></p>
	</form>
</div>
<div data-ui-role="popup" id="popupPwReset" data-theme="a" class="ui-corner-all" style="padding:10px 20px;">
	<form id="formPwReset" data-ajax="false" method="post" action="ajax_exec_pwreset.php">
		<h3>Achtung, Hiermit wird dir ein neues Passwort gesetzt und zugesendet!</h3>
		<p>Du kannst es dann sp&auml;ter in deinem Profil auf zorg.ch wieder &auml;ndern</p>
		<p style="color:red;">{$login_error}</p>
		<label for="email" class="ui-hidden-accessible">Deine E-Mail:</label>
		<input type="email" data-ui-role="email" name="email" id="email" placeholder="Deine E-Mail" data-theme="a">
		<button type="submit" name="resetPw" id="buttonPwReset" value="true" class="ui-btn ui-corner-all ui-nodisc-icon ui-btn-b ui-widget-icon-floatend ui-icon-refresh">Neues Passwort</button>
		<a data-ui-role="button" href="#" data-rel="back" data-inline="true" class="ui-btn ui-mini ui-corner-all ui-nodisc-icon ui-btn-b ui-widget-icon-floatbeginning ui-icon-delete">Oops abbreche, ABBRECHE!</a>
	</form>
</div>
<div data-ui-role="popup" id="popupNewUser" data-theme="a" class="ui-corner-all" style="padding:10px 20px;">
	<form id="formNewUser" data-ajax="false" method="post" action="ajax_exec_newuser.php">
		<h3>Neuen Zorg-User erstellen</h3>
		<p>Anschliessend wird Dir eine E-Mail mit Aktivierungslink geschickt</p>
		<p style="color:red;">{$login_error}</p>
		<label for="new_username" class="ui-hidden-accessible">Wunsch Username:</label>
		<input type="text" data-ui-role="text" name="new_username" id="new_username" placeholder="Username" data-theme="a">
		<label for="new_password" class="ui-hidden-accessible">Passwort:</label>
		<input type="password" data-ui-role="password" name="new_password" id="new_password" placeholder="Passwort" data-theme="a">
		<label for="new_password2" class="ui-hidden-accessible">Passwort wiederholen:</label>
		<input type="password" data-ui-role="password" name="new_password2" id="new_password2" placeholder="Passwort wiederholen" data-theme="a">
		<label for="new_email" class="ui-hidden-accessible">Deine E-Mail:</label>
		<input type="email" data-ui-role="email" name="new_email" id="new_email" placeholder="Deine E-Mail" data-theme="a">
		<button type="submit" name="newuser" id="buttonNewUser" value="true" class="ui-btn ui-corner-all ui-nodisc-icon ui-btn-b ui-widget-icon-floatend ui-icon-user">User erstellen</button>
		<a data-ui-role="button" href="#" data-rel="back" data-inline="true" class="ui-btn ui-mini ui-corner-all ui-nodisc-icon ui-btn-b ui-widget-icon-floatbeginning ui-icon-delete">Hmm... lieber nöd</a>
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
	$('#showNewUser').click(function(){
		$('#popupLogin').popup('close');
		window.setTimeout(function(){ $('#popupNewUser').popup('open') }, 50);
	});

    $('#buttonPwReset').click(function(ev) {
        if ($.trim($('#email').val()).length > 1) {
			$.ajax({
				type: $('#formPwReset').attr('method'),
				url: $('#formPwReset').attr('action'),
				data: $('#formPwReset').serialize(),
				success: function (data) {
					console.log(data);
					$('#login_error').html(data).css('color','green');
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
    
    $('#buttonNewUser').click(function(ev) {
        if (($.trim($('#new_username').val()).length > 1) && ($.trim($('#new_email').val()).length > 1)) {
	        if ($('#new_password').val() == $('#new_password2').val()) {
				$.ajax({
					type: $('#formNewUser').attr('method'),
					url: $('#formNewUser').attr('action'),
					data: $('#formNewUser').serialize(),
					success: function (data) {
						console.log(data);
						$('#login_error').html(data).css('color','green');
						$('#popupNewUser').popup('close');
						window.setTimeout(function(){ $('#popupLogin').popup('open') }, 50);
						//window.location.reload(true);
					},
					error: function(jqXHR, textStatus, errorThrown) {
			           console.log(textStatus + ' ' + errorThrown);
			        }
				});
				ev.preventDefault(); // avoid to execute the actual submit of the form.
			} else {
				alert('Passwörter stimmed nöd überein - vertippt?');
				return false;
			}
		} else {
			alert('Username und E-Mail bruuchsch zwingend!');
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
