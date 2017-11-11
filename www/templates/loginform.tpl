{if $user->id}
	<td align="right" valign="middle">
		<b class="small">{$user->id|username} eingeloggt</b>
		<form action="{$self}" method="post" name="logoutform">
			<input name="logout" type="submit" value="logout" class="button">
		</form>
	</td>
{else}
	<td align="right">
		<table>
			<tr>
				<td align="left" class="small">
					<form action="{$self}" method="post" name="loginform">
						<a href="/profil.php?do=anmeldung&menu_id=13">&#10003; Account erstellen</a> <a href="/profil.php?do=anmeldung&menu_id=13">? PW vergessen</a><br />
						user <input tabindex="1" size="15" type="text" name="username" value="{$smarty.post.username}" class="text" />&nbsp;&nbsp;<input tabindex="3" type="checkbox" name="cookie" id="cookie" /><label for="cookie"> autologin</label><br />
					pass <input tabindex="2" size="15" type="password" name="password" class="text" />&nbsp;
						<input tabindex="4" type="submit" value="&rarr; login" class="button" /><br />
					</form>
					{*if $login_error <> ""}<span class="error small">{$login_error}</span>{/if*}
					{error msg=$login_error}
				</td>
			</tr>
		</table>
	  </td>
{/if}