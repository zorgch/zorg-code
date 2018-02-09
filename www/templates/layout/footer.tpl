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
					{if $code_info.version != ''}
						| Code Version: {$code_info.version}, {$code_info.last_commit} (updated: {$code_info.last_update|datename}){if $user->typ == 2} <a href="https://bitbucket.org/zorgvorstand/zorg.ch/commits/{$code_info.last_commit}" target="_blank">[view]</a>{/if}
					{/if}
				</td>
			</tr>
		</table>
	</center>
	{if $tplroot.page_title == 'Home' && !$smarty.get.tpleditor}<!-- Redirect Mobile Devices, kudos to http://detectmobilebrowsers.com/ -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/dialog-polyfill/0.4.9/dialog-polyfill.min.js"></script>
	<div id="popup-dialog" style="padding: 0">
		<dialog id="mobile-zchat-popup" style="display:none;">
			<h4 class="modal-header">[z]Chat anzeigen?</h4>
			<footer class="modal-footer">
				<button id="zchat-redirect-close" type="button">Nein</button>
				<button id="zchat-redirect-open" type="button">Ja</button>
			</footer>
			<button id="zchat-redirect-xclose" class="close" type="button">&times;</button>
		</dialog>
	</div>
	</body>
	<script type="text/javascript">{literal}
		var isMobile = false;
		const popupDialog = document.getElementById('popup-dialog');
		const mobilePopup = document.getElementById('mobile-zchat-popup');
		const btnClosePopup = document.getElementById('zchat-redirect-close');
		const btnXClosePopup = document.getElementById('zchat-redirect-xclose');
		const btnOpenRedirect = document.getElementById('zchat-redirect-open');
		dialogPolyfill.registerDialog(mobilePopup);
		popupDialog.addEventListener('click', function(event){ if (event.target.tagName === 'DIALOG') mobilePopup.close() });
		btnClosePopup.addEventListener('click', () => { mobilePopup.close(); });
		btnXClosePopup.addEventListener('click', () => { mobilePopup.close(); });
		btnOpenRedirect.addEventListener('click', () => { window.location="mobilezorg-v2/" });
		(function(a,b){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))isMobile=b})(navigator.userAgent||navigator.vendor||window.opera,true);
		if (isMobile) {
		  mobilePopup.style.display = '';
		  mobilePopup.showModal();
		}
	{/literal}</script>{/if}
	<script>swisstimeJS()</script>
</html>
