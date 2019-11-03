{include_php file="file:tpleditor.php"}
{include_php file="file:tploverview.php"}
{include_php file="file:menu_overview.php"}
{include_php file="file:packages_overview.php"}

{if $smarty.get.tpleditorupd && $smarty.get.tpl neq "new" && $tpleditor_frm.id neq "new"}
	{if $tplupdnew == 1}
		{include file=$smarty.get.tplupd}
	{else}
		{include file=$smarty.get.tpl}
	{/if}
{elseif $tpleditor_strongerror}
	{error msg=$tpleditor_strongerror}
{else}
	{if $tpleditor_error}{error msg=$tpleditor_error}{/if}
	{if $tpleditor_state}{state msg=$tpleditor_state}{/if}

{* Custom Layout for Tpleditor with flexible Sidebar *}
<style>{literal}
	.tpleditor-custom-layout {
		display: flex;
		flex: 1 auto;
		flex-wrap: wrap;
	}
	.the-content {
		flex: 3;
		flex-basis: minmax(min-content, max-content);
		padding-bottom: 1vw;
	}
	.the-sidebar {
		flex: 1;
		flex-basis: minmax(min-content, max-content);
		margin: 1.5em;
		padding-bottom: 1.5vw;
	}
	@media (max-width: 767px) {
		.tpleditor-custom-layout {
			flex-direction: row;
		}
		.the-content, .the-sidebar {
			flex: 1;
		}
	}
	ul.tplinfos { padding: 0; }
	ul.tplinfos li {
		display: inline;
		white-space: nowrap;
		list-style-type: none;
	}
	ul.tplinfos li + li:not(:empty)::before {
		content: "\a0|\a0";
		color: gray;
	}
{/literal}</style>
{form action='tpleditor.php'}
<input type="hidden" id="tplid" name="frm[id]" value="{$tpleditor_frm.id}"></strong></li>
<section class="tpleditor-custom-layout">
	<div class="the-content">
		<h2>{if $tpleditor_frm.id neq "new" && $smarty.get.tpl neq "new"}
				{if $tpleditor_frm.title}&laquo;{$tpleditor_frm.title}&raquo; bearbeiten (tpl #{$tpleditor_frm.id})
				{else}&laquo;tpl {$tpleditor_frm.id}&raquo; bearbeiten{/if}
		{else}Neues Template erstellen{/if}</h2>
		{if $tpleditor_frm.id neq "new" && $smarty.get.tpl neq "new"}<ul class="tplinfos small">
			<li><span style="font-weight: lighter">Owner:</span>&nbsp;{$tpleditor_frm.owner|username} @ <span style="display:inline-block;">{$tpleditor_frm.created|datename}</span></li>
			<li><span style="font-weight: lighter">Last update:</span>&nbsp;{$tpleditor_frm.update_user|username} @ <span style="display:inline-block;">{$tpleditor_frm.last_update|datename}</span></li>
		</ul>{/if}

		<div id="tplsettings">
			<h3>Template Bezeichnung</h3>
				<input type="text" size="50" id="tpl_title" name="frm[title]" autocomplete="off" value="{$tpleditor_frm.title}">
			<h3>Seiten Titel</h3>
				<input type="text" size="30" id="page_title" name="frm[page_title]" autocomplete="off" value="{$tpleditor_frm.page_title}">{$smarty.const.PAGETITLE_SUFFIX}
			<h3>Kurz-URL / Wiki-Word</h3>
				{if !$tpleditor_frm.word}<span class="tiny info">Keine Sonderzeichen verwenden! Ohne Kurz-URL kann Template nur via <code>/tpl/tpl_id</code> aufgerufen werden.</span><br>
					<input type="text" size="30" id="word" name="frm[word]" autocomplete="off" value="{$tpleditor_frm.word}">
				{else}<pre>{$smarty.const.SITE_URL}/page/<a href="/page/{$tpleditor_frm.word}" target="_blank">{$tpleditor_frm.word}</a><input type="hidden" name="frm[word]" value="{$tpleditor_frm.word}" autocomplete="off"></pre>{/if}
		</div>
		
		<!-- Template Editor with ACE -->
		<h3>Template content</h3>
		<style type="text/css" media="screen">
		#tpleditor{ldelim}width: 100%;height: 100%;resize: vertical;{rdelim}</style>
		<select onchange="insertStyle(this, this.selectedIndex);"><option selected disabled>Style</option><option value="h2">Heading 2</option><option value="h3">Heading 3</option><option value="h4">Heading 4</option><option value="pre">Preformatted</option></select><button type="button" onclick="insertStrong()"><strong>b</strong></button><button type="button" onclick="insertEm();"><em>i</em></button><button type="button" onclick="insertU();"><u>u</u></button><button type="button" onclick="insertStrike();"><strike>s</strike></button><button type="button" onclick="insertLink();"><a onclick="javascript:return false;">link</a></button><span style="display: inline-block;margin: 0 20px;"></span><button type="button" onclick="insertUl();">• ...</button><button type="button" onclick="insertBlockquote();">&rdquo;</button><button type="button" onclick="insertCode();">&lt;/&gt;</button><button type="button" onclick="insertZmember();">[z]</button>
		<textarea id="content" name="frm[tpl]">{$tpleditor_frm.tpl}</textarea>
		<div id="tpleditor">{$tpleditor_frm.tpl}</div>
		<script src="{if !$dev}https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.6/{else}{$smarty.const.JS_DIR}ace-editor/{/if}ace.js" integrity="sha256-CVkji/u32aj2TeC+D13f7scFSIfphw2pmu4LaKWMSY8=" crossorigin="anonymous"></script>
		<script src="{if !$dev}https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.6/{else}{$smarty.const.JS_DIR}ace-editor/{/if}ext-language_tools.js" integrity="sha256-5GsAp93HE/XYlhrUPMw4VBAoawG9n3c7+DbQE4tRwW0=" crossorigin="anonymous"></script>
		<script>{literal}
			var editor = ace.edit('tpleditor');
			/** Settings */
			editor.setTheme('ace/theme/tomorrow{/literal}{if $daytime == 'night'}_night_bright{/if}{literal}');
			editor.session.setMode('ace/mode/smarty');
			//editor.session.setMode('ace/mode/html');
			//editor.session.setMode('ace/mode/markdown');
			editor.setOptions({
				navigateWithinSoftTabs: true,
				autoScrollEditorIntoView: true,
				minLines: 20,
				maxLines: Infinity,
				wrap: 'free',
				tabSize: 4,
				printMargin: false,
			});
			/** Hotkeys to insert HTML */
			editor.commands.addCommand({
                name: "htmlStrong",
                bindKey: { win: "Ctrl-B", mac: "Command-B" },
                exec: function(){ insertStrong(); }
			});
			editor.commands.addCommand({
                name: "htmlEm",
                bindKey: { win: "Ctrl-I", mac: "Command-I" },
                exec: function(){ insertEm(); }
			});
			editor.commands.addCommand({
                name: "htmlU",
                bindKey: { win: "Ctrl-U", mac: "Command-U" },
                exec: function(){ insertU(); }
			});
			editor.commands.addCommand({
                name: "htmlStrike",
                bindKey: { win: "Ctrl-S", mac: "Command-Shift-S" },
                exec: function(){ insertStrike(); }
			});
			editor.commands.addCommand({
                name: "htmlBlockquote",
                bindKey: { win: "Ctrl-Shift-9", mac: "Command-Shift-9" },
                exec: function(){ insertBlockquote(); }
			});
			editor.commands.addCommand({
                name: "htmlPre",
                bindKey: { win: "Ctrl-Shift-M", mac: "Command-Shift-M" },
                exec: function(){ insertPre(); }
			});
			editor.commands.addCommand({
                name: "htmlCode",
                bindKey: { win: "Ctrl-Shift-D", mac: "Command-Shift-D" },
                exec: function(){ insertCode(); }
			});
			editor.commands.addCommand({
                name: "htmlZmember",
                bindKey: { win: "Ctrl-M", mac: "Ctrl-M" },
                exec: function(){ insertZmember(); }
			});
			editor.commands.addCommand({
                name: "htmlOList",
                bindKey: { win: "Ctrl-Shift-7", mac: "Command-Shift-7" },
                exec: function(){ insertOl(); }
			});
			editor.commands.addCommand({
                name: "htmlUOList",
                bindKey: { win: "Ctrl-Shift-8", mac: "Command-Shift-8" },
                exec: function(){ insertUl(); }
			});
			editor.commands.addCommand({
                name: "htmlLink",
                bindKey: { win: "Ctrl-K", mac: "Command-K" },
                exec: function(){ insertLink(); }
			});
			editor.commands.addCommand({
                name: "htmlImage",
                bindKey: { win: "Ctrl-Shift-M", mac: "Command-Shift-M" },
                exec: function(){ insertImg(); }
			});
			editor.commands.addCommand({
                name: "htmlH2",
                bindKey: { win: "Ctrl-Alt-2", mac: "Ctrl-Shift-2" },
                exec: function(){ insertHeading(2); }
			});
			editor.commands.addCommand({
                name: "htmlH3",
                bindKey: { win: "Ctrl-Alt-3", mac: "Ctrl-Shift-3" },
                exec: function(){ insertHeading(3); }
			});
			editor.commands.addCommand({
                name: "htmlH4",
                bindKey: { win: "Ctrl-Alt-4", mac: "Ctrl-Shift-4" },
                exec: function(){ insertHeading(4); }
			});

			/** Extensions */
			editor.setOption('enableBasicAutocompletion', 'true');
			editor.setOption('enableLiveAutocompletion', 'true');

			/** Textarea update */
			var textarea = document.getElementById('content');
			textarea.style.display = 'none';
			//editor.setValue() = textarea.value;
			editor.getSession().on('change', function(){
				textarea.value = editor.session.getValue();
			});

			/** Initial resize */
			editor.resize();

			/** Insert HTML to ace */
			function insertStrong(){ editor.session.replace(editor.selection.getRange(), '<strong>' + editor.getSelectedText() + '</strong>'); editor.focus(); }
			function insertEm(){ editor.session.replace(editor.selection.getRange(), '<em>' + editor.getSelectedText() + '</em>'); editor.focus(); }
			function insertU(){ editor.session.replace(editor.selection.getRange(), '<u>' + editor.getSelectedText() + '</u>'); editor.focus(); }
			function insertStrike(){ editor.session.replace(editor.selection.getRange(), '<strike>' + editor.getSelectedText() + '</strike>'); editor.focus(); }
			function insertBlockquote(){ editor.session.replace(editor.selection.getRange(), '<blockquote>' + editor.getSelectedText() + '</blockquote>'); editor.focus(); }
			function insertPre(){ editor.session.replace(editor.selection.getRange(), '<pre>' + editor.getSelectedText() + '</pre>'); editor.focus(); }
			function insertHeading(level){ editor.session.replace(editor.selection.getRange(), '<h' + level + '>' + editor.getSelectedText() + '</h' + level + '>'); editor.focus(); }
			function insertCode(){ editor.session.replace(editor.selection.getRange(), '<pre><code>\n' + editor.getSelectedText() + '\n</code></pre>'); editor.focus(); }
			function insertZmember(){ editor.session.replace(editor.selection.getRange(), '{member}\n' + editor.getSelectedText() + '\n{/member}'); editor.focus(); }
			function insertStyle(selectRoot, optionIndex)
			{
				var openTag = '<'+selectRoot.options[optionIndex].value+'>';
				var closeTag = '</'+selectRoot.options[optionIndex].value+'>\n';
				editor.session.replace(editor.selection.getRange(), openTag + editor.getSelectedText() + closeTag);
				selectRoot.selectedIndex = 0;
				editor.focus();
			}
			function insertUl()
			{
				editor.session.insert(editor.getCursorPosition(),'<ul>\n');
				editor.indent();
					editor.session.insert(editor.getCursorPosition(),'<li>bullet1</li>\n');
					editor.indent();
					editor.session.insert(editor.getCursorPosition(),'<li>bullet2</li>\n');
					editor.indent();
					editor.session.insert(editor.getCursorPosition(),'<li>...</li>\n');
				editor.session.insert(editor.getCursorPosition(),'</ul>\n');
				editor.focus();
			}
			function insertOl()
			{
				editor.session.insert(editor.getCursorPosition(),'<ol>\n');
				editor.indent();
					editor.session.insert(editor.getCursorPosition(),'<li>entry1</li>\n');
					editor.indent();
					editor.session.insert(editor.getCursorPosition(),'<li>entry2</li>\n');
					editor.indent();
					editor.session.insert(editor.getCursorPosition(),'<li>...</li>\n');
				editor.session.insert(editor.getCursorPosition(),'</ol>\n');
				editor.focus();
			}
			function insertLink()
			{
				editor.session.replace(editor.selection.getRange(), '<a href="https://...">' + editor.getSelectedText() + '</a>');
				editor.focus();
			}
			function insertImg()
			{
				editor.session.replace(editor.selection.getRange(), '<img src="https://..." />');
				editor.focus();
			}
			
		{/literal}</script>
		{*<div id="tpleditor" class="tpleditor">
			<textarea id="content" name="frm[tpl]" class="text" rows="30">{$tpleditor_frm.tpl}</textarea>
		</div>*}
	</div>
	<div class="the-sidebar">		
		<h3>Layout</h3>
		<fieldset>
			<label for="menus">Menus
				<select id="menus" name="frm[menus][]" onChange="update(this)" data-sorted-values="" multiple style="width: 100%;">
		  			{foreach from=$menus item=menu}
		  			<option {if in_array($menu.id, $tpleditor_frm.menus)}selected{/if} value="{$menu.id}">{$menu.name} (tpl#{$menu.tpl_id})</option>
		  			{/foreach}
				</select>
			</label>
		</fieldset>
		<fieldset>
			<label for="frm[border]">Border<br>
				{html_radios name='frm[border]' values=$bordertypids checked=$tpleditor_frm.border separator='<br>' output=$bordertypnames}
			</label>
		</fieldset>
		<fieldset>
			<label for="sidebar">Sidebar Template
				<select id="sidebar" name="frm[sidebar_tpl]" size="1">
					<option {if $smarty.get.tpl == "new" || $tpleditor_frm.sidebar_tpl <= 0}selected{/if} value="">-- no Sidebar --</option>
		  			{foreach from=$tploverview item=template key=n}
		  				{*if $template.read_rights >= $user->typ || $template.owner == $user->id*}
		  				{if tpl_permission($template.read_rights, $template.owner)}
			  				{if tpl_permission($template.write_rights, $template.owner)}
			  					<option {if $tpleditor_frm.sidebar_tpl == $template.id}selected{/if} value="{$template.id}">{if $template.title != ""}{$template.title|stripslashes}{/if} (tpl#{$template.id})</option>
			  				{/if}
			  			{/if}
		  			{/foreach}
				</select>
			</label>
		</fieldset>

		<h3>Commenting</h3>
		<fieldset>
			<label><input type="checkbox" id="commenting" name="frm[allow_comments]" value="1" {if $tpleditor_frm.allow_comments == 1}checked{/if}> Commenting aktivieren</label>
		</fieldset>

		<h3>Berechtigungen</h3>
		<fieldset>
			<label>Lese-Rechte<br>
				{html_radios name='frm[read_rights]' values=$rgroupids checked=$tpleditor_frm.read_rights separator='<br>' output=$rgroupnames}
			</label>
		</fieldset>
		<fieldset>
			<label>Schreib-Rechte<br>
				{html_radios name='frm[write_rights]' values=$wgroupids checked=$tpleditor_frm.write_rights separator='<br>' output=$wgroupnames}
			</label>
		</fieldset>

		<h3>Code</h3>
		<fieldset>
			<label for="packages">Required PHP Packages<br>
				<select id="packages" name="frm[packages][]" onChange="update(this)" data-sorted-values="" multiple style="width: 100%;">
		  			{foreach from=$packages item=package}
		  			<option {if in_array($package.id, $tpleditor_frm.packages)}selected{/if} value="{$package.id}">{$package.name}</option>
		  			{/foreach}
				</select>
			</label>
		</fieldset>

		<h3>Save & close</h3>
		<div id="tpleditor_actions" style="padding:10px 0 10px 0; text-align: center;">
			<input type="submit" class="primary" id="save" value="Speichern &amp; schliessen">
			{spc i=8}
			{button url=$tpleditor_close_url}Abbrechen{/button}
		</div>
	</div>
</section>
{/form}
{/if}