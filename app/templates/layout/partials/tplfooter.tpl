<div class="tpl-footer">
	<section>
		<ul>
			<li><span style="font-weight: lighter;">read:</span>&nbsp;{$tpl.read_rights|usergroup}</li>
			<li><span style="font-weight: lighter;">write:</span>&nbsp;{$tpl.write_rights|usergroup}</li>
			<li><span style="font-weight: lighter;">updated:</span>&nbsp;{$tpl.update_user|username}, {$tpl.last_update|datename}</li>
			<li><a href="/tpl/{$tpl.id}">tpl={$tpl.id}</a></li>
			{if $tpl.word != ''}<li><a href="/page/{$tpl.word}">page={$tpl.word}</a></li>{/if}
			{if tpl_permission($tpl.write_rights, $tpl.owner)}<li>{edit_link tpl=$tpl.id}[edit]{/edit_link}</li>{/if}
		</ul>
	</section>
</div>