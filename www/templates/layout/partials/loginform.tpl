{if $user->id}
<h5>Hallo {$user->id|username}{if $country != ""}<img class="countryflag" src='{$country_image}' alt='{$country}' title='{$country}'>{/if}</h5>
<form action="{$self}" method="post" name="logoutform">
	<input type="hidden" name="redirect" value="{$url}">
	<input type="submit" name="logout" value="logout">
</form>
{else}
	{if $smarty.get.showlogin == 'true'}
<form name="loginform" id="loginform" class="login" action="{$self}" method="post">
	<input type="hidden" name="do" value="login">
	<input type="hidden" name="redirect" value="{$url}">
	<!-- Prevent autofocus of "username" on mobile devices --><input type="text" autofocus="autofocus" style="display:none">
	<div class="login-input">
		<label class="emoji user">user <input type="text" name="username" value="{$smarty.post.username}" tabindex="1"></label>
		<a href="/profil.php?do=anmeldung#newuser">new</a>
	</div>
	<div class="login-input">
		<label class="emoji password">pass <input type="password" name="password" tabindex="2"></label>
		<a href="/profil.php?do=anmeldung#pwreset">forgot</a>
	</div>
	<div class="login-input">
		<label>autologin&nbsp;<input type="checkbox" name="cookie" id="cookie" tabindex="3" style="vertical-align: baseline;"></label>
		<input type="submit" value="login" tabindex="4">
	</div>
</form>
	{else}
<form>
	<div class="login-input">
		<a href="/profil.php?do=anmeldung#newuser" style="margin-right: 10px;">signup</a>&nbsp;|&nbsp;
		<a id="show-login" href="javascript:" rel="noindex"><label class="emoji user">&nbsp;login</label></a>
	</div>
</form>
<link rel="stylesheet" type="text/css" href="{$smarty.const.JS_DIR}dialog-polyfill/dialog-polyfill.css">
<script src="{$smarty.const.JS_DIR}dialog-polyfill/dialog-polyfill.js"></script>
<dialog id="login-popup">
	<form name="loginform" id="loginform" class="login" action="{$self}" method="post">
		<!--h4 class="modal-header">Auf zorg einloggen</h4-->
		<input type="hidden" name="do" value="login">
		<input type="hidden" name="redirect" value="{$url}">
		<div class="login-input">
			<label class="emoji user">user <input type="text" name="username" value="{$smarty.post.username}" tabindex="1"></label>
			<a href="/profil.php?do=anmeldung#newuser">new</a>
		</div>
		<div class="login-input">
			<label class="emoji password">pass <input type="password" name="password" tabindex="2"></label>
			<a href="/profil.php?do=anmeldung#pwreset">forgot</a>
		</div>
		<footer class="modal-footer">
			<div class="login-input">
				<label>autologin&nbsp;<input type="checkbox" name="cookie" id="cookie" tabindex="4" style="vertical-align: baseline;"></label>
				<input type="submit" value="login" tabindex="3">
			</div>
		</footer>
	</form>
	<button id="login-popup-xclose" class="close" type="button">&times;</button>
</dialog>
<script>{literal}
	const loginFallbackUrl = '{/literal}{$url|base64decode|change_url:'showlogin=true'}{literal}';
	const showLoginPopup = document.getElementById('show-login');
	const loginPopup = document.getElementById('login-popup');
	const btnXClosePopup = document.getElementById('login-popup-xclose');
	if (typeof dialogPolyfill !== 'undefined') dialogPolyfill.registerDialog(loginPopup);
	showLoginPopup.addEventListener('click', function(){
		if (loginPopup.showModal){
			loginPopup.showModal();
		} else {
			/** Fallback */
			window.location.replace(loginFallbackUrl.replace(/\s/g, ''));
		}
	});
	loginPopup.addEventListener('click', function(){ if (event.target.tagName === 'DIALOG') loginPopup.close() });
	btnXClosePopup.addEventListener('click', () => { loginPopup.close(); });
{/literal}</script>
	{/if}
{/if}
{error msg=$login_error}