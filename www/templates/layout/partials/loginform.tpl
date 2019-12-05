{if $user->id}
<h5>Hallo {$user->id|username}{if $country != ""}<img class="countryflag" src='{$country_image}' alt='{$country}' title='{$country}'>{/if}</h5>
<form action="{$self}" method="post" name="logoutform">
	<input type="hidden" name="redirect" value="{$url}">
	<input type="submit" name="logout" value="logout">
</form>
{else}
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
	<fieldset>
		<label>autologin&nbsp;<input type="checkbox" name="cookie" id="cookie" tabindex="3" style="vertical-align: baseline;"></label>
		<input type="submit" value="login" tabindex="4">
	</fieldset>
</form>
{/if}
{error msg=$login_error}