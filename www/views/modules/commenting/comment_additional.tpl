<table class="forum" style="background-color: #{comment_colorfade depth=$sizeof_hdepth color=$color.newcomment}">
	<tr>
		<td class="title" style="vertical-align: top;background-color: #{$color.newcomment}">
			<a href="{get_changed_url change="parent_id='.$parent_id.'"}">Additional posts</a>
			{if $user->id!=0}<a href="/profil.php?do=view">(du hast Forumanzeigeschwelle <b>{$user->maxdepth}</b> eingestellt)</a>{/if}
		</td>
	</tr>
</table>