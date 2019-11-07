{if $user->id}
<h5>Hallo {$user->id|username}{if $country != ""}<img class="countryflag" src='{$country_image}' alt='{$country}' title='{$country}'>{/if}</h5>
<form action="{$self}" method="post" name="logoutform">
	<input type="hidden" name="redirect" value="{$url}">
	<input name="logout" type="submit" value="logout">
</form>
{else}
<form name="loginform" id="loginform" class="login" action="{$self}" method="post">
	<input type="hidden" name="do" value="login">
	<input type="hidden" name="redirect" value="{$url}">
	<div class="login-input">
		<label class="emoji user">user <input tabindex="1" type="text" name="username" value="{$smarty.post.username}"></label>
		<a href="/profil.php?do=anmeldung#newuser">new</a>
	</div>
	<div class="login-input">
		<label class="emoji password">pass <input tabindex="2" type="password" name="password"></label>
		<a href="/profil.php?do=anmeldung#pwreset">forgot</a>
	</div>
	<fieldset>
		<label><input tabindex="3" type="checkbox" name="cookie" id="cookie">&nbsp;autologin</label>
		<input tabindex="4" type="submit" value="login">
	</fieldset>
</form>
{/if}
{error msg=$login_error}