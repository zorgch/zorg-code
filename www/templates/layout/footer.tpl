					</div>
					<br/>
				</td>
			</tr>
			<tr>
				<td width="100%" align="center" valign="center" class="small" style="background-color:{$smarty.const.TABLEBACKGROUNDCOLOR}; padding:2px; border-top-style:solid; border-top-width:1px; border-top-color:{$smarty.const.BORDERCOLOR}">
					{if $tplroot.id != ''}Template: {$tplroot.size} bytes
						&nbsp;| r: {$tplroot.read_rights|usergroup}
						&nbsp;| w: {$tplroot.write_rights|usergroup}
						&nbsp;| updated: {$tplroot.update_user|username}, {$tplroot.last_update|datename}
						&nbsp;| <a href="/?tpl={$tplroot.id}">tpl={$tplroot.id}</a>
						{if $tplroot.word}&nbsp;| <a href="/?word={$tplroot.word}">word={$tplroot.word}</a>{/if}
						{if $tplroot.write_rights || $tplroot.owner == $user->id}&nbsp;| {edit_link tpl=$tplroot.id}[edit]{/edit_link}{/if}
						<br/>
					{/if}
					<span id="swisstime"></span> | Parsetime: {$smarty.now-$parsetime_start|round:2}s | Rendertime: {'stop'|rendertime:true|round:2}s {if $smarty.session.noquerys > 0}| {$smarty.session.noquerys} SQL Queries{if $user->sql_tracker} <a href="/?word=sql-query-tracker">[details]</a>{/if}{/if}
					{if $spaceweather}<br />
						{section name=i loop=$spaceweather max=2}
							{$spaceweather[i].type}: {$spaceweather[i].value} |&nbsp;
						{/section}
						<a href="spaceweather.php">[more]</a>
					{/if}
					<br /><a href="/?word=impressum">Impressum</a> | <a href="/?word=privacy">Privacy-Policy</a> | <a href="/?word=verein">Zorg Verein</a>
				</td>
			</tr>
		</table>
	</center>
	</body>
	<script>swisstimeJS()</script>
</html>
