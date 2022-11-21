		{include file='file:layout/sidebar.tpl'}

		<!--googleoff: all-->
		<footer class="footer">
			<hr class="shadow">
			<section class="flex-one-column">
				<i class="emoji {$daytime}"></i><span id="swisstime"></span>
			</section>
			{if $code_info.version != ''}<section class="flex-one-column small">
				<a href="{$smarty.const.GIT_REPOSITORY_URL}{$code_info.last_commit}" target="_blank">{$code_info.version}#{$code_info.last_commit}</a>&nbsp;(changed {$code_info.last_update|datename})
			</section>{/if}
			{if $tplroot.id > 0}
			<section class="flex-two-column">
				<div class="icon">
					{*<object data="{$smarty.const.IMAGES_DIR}icons/codefile-black.svg#night"></object>*}
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 125" x="0px" y="0px"><title>codefile-black.svg</title><path d="M84.32,30a2,2,0,0,0-.57-1.4L61.09,5.51a2,2,0,0,0-1.43-.6h-33a11,11,0,0,0-11,11V84.09a11,11,0,0,0,11,11H73.32a11,11,0,0,0,11-11ZM61.51,11.65,78.49,29h-15a2,2,0,0,1-2-2ZM73.32,91.09H26.68a7,7,0,0,1-7-7V15.91a7,7,0,0,1,7-7H57.51V27a6,6,0,0,0,6,6H80.32v51.1A7,7,0,0,1,73.32,91.09Z"/><path d="M52.95,44.52a2,2,0,0,0-2.32,1.62l-5.19,29a2,2,0,0,0,3.94.71l5.19-29A2,2,0,0,0,52.95,44.52Z"/><path d="M64.15,50.92A2,2,0,1,0,61,53.4L67,61l-6,7.6a2,2,0,0,0,3.14,2.48l7-8.84a2,2,0,0,0,0-2.48Z"/><path d="M38.66,50.59a2,2,0,0,0-2.81.33l-7,8.84a2,2,0,0,0,0,2.48l7,8.84A2,2,0,0,0,39,68.6L33,61l6-7.6A2,2,0,0,0,38.66,50.59Z"/></svg>
				</div>
				<div class="data">
					<ul>
						{* --NOT IMPLEMENTED YET-- if $user->id}<li>{if $tplroot.id == $user->tpl_favourite}<a href="?usersetfavourite=0">[unfav]</a>
						{else}<a href="?usersetfavourite={$tplroot.id}">[fav]</a>{/if}</li>{/if*}
						<li><a href="/tpl/{$tplroot.id}">tpl={$tplroot.id}</a></li>
						{if $tplroot.word != ''}<li><a href="/page/{$tplroot.word}">page={$tplroot.word}</a></li>{/if}
					</ul>
					<ul>
						<li><span style="font-weight: lighter;">read:</span>&nbsp;{$tplroot.read_rights|usergroup}</li>
						<li><span style="font-weight: lighter;">write:</span>&nbsp;{$tplroot.write_rights|usergroup}</li>
					</ul>
					<ul>
						<li><span style="font-weight: lighter;">updated:</span>&nbsp;{$tplroot.update_user|username}, {$tplroot.last_update|datename}</li>
						{if $tplroot.write_rights <= $user->typ || $tplroot.owner == $user->id}<li>{edit_link tpl=$tplroot.id}[edit]{/edit_link}</li>{/if}
					</ul>
				</div>
			</section>
			{/if}
			<section class="flex-two-column">
				<div class="icon">
					{*<object data="{$smarty.const.IMAGES_DIR}icons/dashboard-black.svg"></object>*}
					<svg xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd" xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape" version="1.1" x="0px" y="0px" viewBox="0 0 100 125"><title>dashboard-black.svg</title><g transform="translate(0,-952.36218)"><g transform="translate(7.0019695,959.32871)"><path style="text-indent:0;text-transform:none;direction:ltr;block-progression:tb;baseline-shift:baseline;color:#000000;enable-background:accumulate;" d="m 42.99803,-0.96653 c -24.27684,0 -43.9999993,19.7231 -43.9999993,44 0,24.2768 19.7231593,44 43.9999993,44 24.276841,0 44,-19.7232 44,-44 0,-24.2769 -19.723159,-44 -44,-44 z m 0,4 c 22.11486,0 40,17.8851 40,40 0,22.1149 -17.88514,40 -40,40 -22.11486,0 -39.9999994,-17.8851 -39.9999994,-40 0,-22.1149 17.8851394,-40 39.9999994,-40 z m -0.25,1.9688 c -0.97176,0.1222 -1.77284,1.0521 -1.75,2.0312 l 0.0312,6.9375 c -0.0149,1.0566 0.94327,2.0285 2,2.0285 1.05673,0 2.01494,-0.9719 2,-2.0285 l -0.0312,-6.9687 c 0.009,-1.1359 -1.12294,-2.1419 -2.25,-2 z m -18.91325,5.2294 c -0.74567,0.6108 -1.09696,1.7671 -0.58675,2.6143 l 3.5,6 c 0.5236,0.9081 1.82626,1.258 2.73437,0.7344 0.90812,-0.5236 1.25798,-1.8263 0.73438,-2.7344 l -3.5,-6 c -0.94334,-1.1098 -1.76793,-1.1605 -2.882,-0.6143 z m 35.41325,0.6143 -3.4375,6.0313 c -0.5236,0.9081 -0.17374,2.2108 0.73438,2.7344 0.90811,0.5236 2.21077,0.1737 2.73437,-0.7344 l 3.4375,-6.0313 c 0.48944,-1.347 0.129711,-2.0519 -0.80713,-2.8181 -1.16795,-0.4826 -1.86832,0.044 -2.66162,0.8181 z m -47.59375,12.1563 c -0.85858,0.051 -1.64375,0.7185 -1.8325497,1.5576 -0.18881,0.8391 0.2348497,1.7785 0.9887997,2.1924 l 6.03125,3.4687 c 0.908101,0.5235 2.2106,0.1736 2.73417,-0.7345 0.52356,-0.908 0.17382,-2.2105 -0.73417,-2.7342 -2.399259,-1.0627 -4.6679,-3.3273 -7.1875,-3.75 z m 61.5,0.2812 -6,3.5 c -0.90813,0.5318 -1.25055,1.8419 -0.71877,2.75 0.53178,0.9082 1.84189,1.2506 2.75002,0.7188 l 6,-3.5 c 1.05637,-1.0324 1.11053,-1.7381 0.7019,-2.862 -0.93112,-0.9371 -1.671039,-0.9839 -2.73315,-0.6068 z m -14.65625,1.5628 c -2.07443,1.042 -3.980269,2.7393 -5.625,4.0313 l -9.1875,9.1874 c -0.226889,-0.032 -0.45247,-0.031 -0.6875,-0.031 -2.73773,0 -5,2.2622 -5,5 0,2.7377 2.26227,5 5,5 2.73774,0 5,-2.2623 5,-5 0,-0.2391 -0.0292,-0.4881 -0.0625,-0.7188 l 9.1875,-9.1874 c 1.49464,-1.9582 2.95508,-3.5697 4.03125,-5.625 0.3024,-0.8323 -0.15686,-1.5462 -0.5,-2.1563 -0.74533,-0.6228 -1.32321,-0.6091 -2.15625,-0.5002 z m -44.5625,16.156 -6.9374994,0.031 c -1.05663,-0.015 -2.02848,0.9433 -2.02848,2 0,1.0567 0.97185,2.0149 2.02848,2 l 6.9374994,-0.031 c 1.05663,0.015 2.02848,-0.9433 2.02848,-2 0,-1.0567 -0.97185,-2.0149 -2.02848,-2 z m 65.0625,0 -6.96875,0.031 c -1.05515,0 -2.016329,0.9607 -2.008089,2.0158 0.008,1.0551 0.98438,2.0049 2.039339,1.9842 l 6.9375,-0.031 c 1.05663,0.015 2.02848,-0.9433 2.02848,-2 0,-1.0567 -0.97185,-2.0149 -2.02848,-2 z m -51.21875,18.0624 c -0.97225,0.107 -1.78704,1.0219 -1.78125,2 l 0,10 c 9e-5,1.0472 0.95283,1.9999 2,2 l 30,0 c 1.04717,-1e-4 1.99991,-0.9528 2,-2 l 0,-10 c -9e-5,-1.0472 -0.95283,-1.9999 -2,-2 -10.06697,0 -20.167789,0 -30.21875,0 z m 2.21875,4 6,0 0,6 -6,0 z m 10,0 6,0 0,6 -6,0 z m 10,0 6,0 0,6 -6,0 z" fill="#000000" fill-opacity="1" stroke="none" marker="none" visibility="visible" display="inline" overflow="visible"/></g></g></svg>
				</div>
				<div class="data">
					<ul>{math assign='parsetime' equation='end-start' start=$parsetime_start end="true"|microtime format="%.2f"}
						<li><span style="font-weight: lighter;">Parsetime</span>&nbsp;{$parsetime}s</li>
						<li><span style="font-weight: lighter;">Rendertime</span>&nbsp;{'stop'|rendertime:true|round:2}s</li>
					</ul>
					{if $tplroot.id > 0 || $smarty.session.noquerys > 0}<ul>
						{if $tplroot.id > 0}<li>Size {$tplroot.size}&nbsp;bytes</li>{/if}
						{if $smarty.session.noquerys > 0}<li>{if $user->sql_tracker}<a href="/page/sql-query-tracker">{/if}{$smarty.session.noquerys}&nbsp;SQL&nbsp;queries{if $user->sql_tracker}</a>{/if}</li>{/if}
					</ul>{/if}
				</div>
			</section>
			{if $spaceweather}<section class="flex-two-column">
				<div class="icon"><!--img src="{$smarty.const.IMAGES_DIR}icons/satellite-black.svg"-->
					{*<object data="{$smarty.const.IMAGES_DIR}icons/satellite-black.svg"></object>*}
					<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 100 125" enable-background="new 0 0 100 100" xml:space="preserve"><title>satellite-black.svg</title><g><path fill="none" d="M35.182,33.455c-0.108-0.114-0.253-0.178-0.411-0.178c-0.143,0-0.28,0.054-0.384,0.153l-6.846,6.415   c-0.181,0.169-0.229,0.442-0.116,0.665c0.025,0.049,0.055,0.093,0.088,0.127l5.424,5.789c0.108,0.115,0.254,0.178,0.411,0.178   c0.143,0,0.28-0.054,0.385-0.152l6.846-6.416c0.11-0.102,0.172-0.242,0.177-0.391c0.005-0.151-0.049-0.294-0.152-0.404   L35.182,33.455z"/><path fill="none" d="M34.105,29.634c0.108,0.115,0.254,0.178,0.411,0.178c0.143,0,0.279-0.054,0.384-0.152l6.847-6.416   c0.227-0.212,0.238-0.569,0.026-0.795l-5.422-5.784c-0.108-0.115-0.254-0.179-0.412-0.179c-0.143,0-0.279,0.054-0.384,0.152   l-6.846,6.417c-0.11,0.102-0.172,0.242-0.177,0.391c-0.005,0.15,0.049,0.293,0.152,0.403L34.105,29.634z"/><path fill="none" d="M32.772,30.883l-5.421-5.785c-0.108-0.115-0.254-0.178-0.411-0.178c-0.143,0-0.28,0.054-0.385,0.153   l-6.846,6.415c-0.11,0.102-0.172,0.242-0.177,0.391c-0.005,0.151,0.049,0.294,0.152,0.404l5.421,5.785   c0.108,0.114,0.254,0.178,0.411,0.178c0.143,0,0.28-0.054,0.385-0.153l6.845-6.415C32.973,31.467,32.984,31.109,32.772,30.883z"/><path fill="none" d="M44.182,25.02c-0.107-0.114-0.253-0.178-0.41-0.178c-0.123,0-0.239,0.039-0.336,0.111l-6.895,6.458   c-0.11,0.102-0.173,0.242-0.178,0.391c-0.005,0.15,0.049,0.293,0.152,0.403l5.421,5.785c0.108,0.115,0.254,0.179,0.411,0.179   c0.143,0,0.28-0.054,0.384-0.153l6.846-6.416c0.226-0.212,0.238-0.568,0.025-0.795L44.182,25.02z"/><path fill="none" d="M47.543,36.525l4.504-4.221c0.226-0.212,0.238-0.569,0.025-0.796L28.308,6.149   c-0.107-0.115-0.253-0.178-0.41-0.178c-0.143,0-0.28,0.054-0.385,0.153L9.771,22.751c-0.226,0.212-0.238,0.568-0.026,0.794   l23.765,25.359c0.205,0.219,0.577,0.233,0.796,0.026l5.123-4.801c0.001-0.001,0.002-0.002,0.003-0.003l8.109-7.6   C47.542,36.526,47.543,36.526,47.543,36.525z M16.552,30.387l-5.421-5.785c-0.585-0.624-0.553-1.607,0.071-2.193l6.845-6.416   c0.496-0.465,1.269-0.501,1.846-0.17c-0.123-0.234-0.19-0.495-0.181-0.766c0.013-0.414,0.187-0.799,0.489-1.082l6.846-6.416   c0.6-0.563,1.631-0.529,2.193,0.071l5.419,5.783c0.587,0.627,0.555,1.611-0.068,2.197l-6.686,6.264   c-0.001,0.001-0.001,0.002-0.001,0.002l-0.161,0.15c-0.287,0.27-0.664,0.418-1.059,0.418c-0.282,0-0.543-0.099-0.781-0.241   c0.309,0.601,0.205,1.356-0.311,1.84l-6.847,6.416c-0.288,0.27-0.665,0.419-1.061,0.419C17.256,30.878,16.844,30.7,16.552,30.387z    M24.383,38.744l-5.421-5.785c-0.585-0.625-0.554-1.608,0.071-2.194l6.846-6.415c0.495-0.464,1.269-0.501,1.846-0.17   c-0.123-0.234-0.19-0.494-0.181-0.766c0.013-0.414,0.187-0.798,0.49-1.082l6.846-6.416c0.6-0.564,1.631-0.528,2.193,0.072   l5.422,5.784c0.585,0.625,0.553,1.608-0.071,2.194l-6.846,6.415c-0.288,0.27-0.665,0.419-1.06,0.419   c-0.273,0-0.535-0.08-0.768-0.213c0.291,0.596,0.183,1.334-0.326,1.812l-6.845,6.416c-0.289,0.27-0.666,0.419-1.061,0.419   C25.089,39.235,24.676,39.056,24.383,38.744z M42.348,39.159c-0.272,0-0.533-0.079-0.766-0.212c0.11,0.225,0.171,0.471,0.162,0.728   c-0.014,0.415-0.187,0.799-0.49,1.083l-6.846,6.415c-0.288,0.27-0.666,0.419-1.061,0.419c-0.427,0-0.84-0.179-1.133-0.491   l-5.421-5.785c-0.096-0.1-0.181-0.222-0.251-0.358c-0.31-0.613-0.177-1.367,0.322-1.835l6.846-6.415   c0.495-0.464,1.269-0.5,1.845-0.17c-0.123-0.234-0.19-0.495-0.182-0.766c0.014-0.413,0.187-0.798,0.49-1.082l6.845-6.416   c0.048-0.043,0.092-0.078,0.134-0.111c0.613-0.457,1.538-0.376,2.06,0.183l5.422,5.785c0.584,0.625,0.553,1.608-0.071,2.194   l-6.846,6.416C43.12,39.01,42.743,39.159,42.348,39.159z"/><path fill="none" d="M33.69,65.489c-2.176-2.22-5.09-3.443-8.203-3.443c-2.76,0-5.397,0.981-7.481,2.773l15.696,16.748   c4.277-4.364,4.391-11.389,0.174-15.89L33.69,65.489z"/><path fill="none" d="M41.847,46.703l8.109-7.6c0.226-0.212,0.238-0.569,0.025-0.795l-0.969-1.035   c-0.107-0.114-0.253-0.178-0.411-0.178c-0.143,0-0.28,0.054-0.384,0.153l-8.11,7.6c-0.109,0.102-0.172,0.241-0.177,0.39   c-0.005,0.15,0.049,0.293,0.152,0.403l0.969,1.035C41.257,46.898,41.629,46.91,41.847,46.703z"/><path fill="none" d="M24.941,22.526l-5.422-5.785c-0.108-0.114-0.253-0.178-0.411-0.178c-0.143,0-0.28,0.054-0.385,0.153   l-6.845,6.414c-0.227,0.213-0.238,0.57-0.026,0.796l5.421,5.785c0.107,0.115,0.253,0.178,0.411,0.178   c0.143,0,0.28-0.054,0.384-0.152l6.847-6.416C25.142,23.11,25.153,22.753,24.941,22.526z"/><path fill="none" d="M18.034,71.774c0.061-0.098,0.082-0.217,0.055-0.334c-0.027-0.116-0.097-0.215-0.198-0.278   c-0.072-0.046-0.154-0.07-0.238-0.07c-0.155,0-0.296,0.078-0.379,0.21c-0.101,0.16-2.416,3.96,1.262,9.264   c2.727,3.933,6.29,4.358,7.695,4.358c0.246,0,0.398-0.014,0.429-0.017c0.129-0.014,0.246-0.083,0.32-0.189   c0.043-0.062,0.092-0.166,0.077-0.303c-0.025-0.227-0.215-0.399-0.442-0.399c-0.082,0.006-0.206,0.017-0.41,0.017   c-0.98,0-4.377-0.287-6.936-3.976C15.909,75.208,18.012,71.808,18.034,71.774z"/><path fill="none" d="M29.352,93.014c-0.181,0.022-0.711,0.093-1.563,0.093c-0.934,0-2.41-0.089-4.131-0.515   c-4.266-1.055-7.945-3.54-10.935-7.386c-7.927-10.197-3.704-18.052-3.66-18.129c0.119-0.215,0.041-0.488-0.175-0.608   c-0.067-0.038-0.14-0.056-0.216-0.056c-0.163,0-0.313,0.088-0.391,0.23c-0.013,0.022-1.176,2.144-1.277,5.564   c-0.092,3.12,0.703,8.002,5.013,13.547c5.584,7.183,12.235,8.256,15.703,8.256c0,0,0,0,0,0c1.012,0,1.634-0.098,1.702-0.108   c0.121-0.02,0.226-0.086,0.296-0.186c0.066-0.096,0.093-0.213,0.074-0.327C29.756,93.175,29.567,93.014,29.352,93.014z"/><path fill="none" d="M38.101,66.367c-0.516-0.316-0.996-0.699-1.421-1.153l-2.161-2.306c-0.423-0.451-0.767-0.954-1.049-1.486   c-0.023-0.017-0.049-0.028-0.07-0.049c-0.102-0.11-0.242-0.173-0.391-0.178c-0.157-0.021-0.294,0.049-0.404,0.152l-0.781,0.732   c-0.189,0.178-0.079,0.559,0.138,0.799c0.875,0.534,1.697,1.167,2.434,1.919l0.202,0.206c0.882,0.941,1.59,1.985,2.124,3.09   c0.087,0.078,0.271,0.18,0.61-0.136l0.782-0.732c0.109-0.102,0.172-0.242,0.177-0.392c0.004-0.15-0.049-0.293-0.152-0.401   C38.123,66.412,38.116,66.388,38.101,66.367z"/><path fill="none" d="M26.166,21.162l1.184-0.122l6.565-6.151c0.226-0.212,0.238-0.57,0.026-0.796l-5.422-5.785   c-0.108-0.114-0.253-0.178-0.411-0.178c-0.143,0-0.28,0.054-0.385,0.153l-6.846,6.415c-0.11,0.102-0.172,0.242-0.177,0.392   c-0.005,0.151,0.049,0.293,0.151,0.402L26.166,21.162z"/><path fill="none" d="M68.537,44.079C68.537,44.079,68.537,44.079,68.537,44.079l1.249,1.333c0.427,0.454,1.195,0.48,1.651,0.054   l1.972-1.848c0.469-0.44,0.493-1.18,0.053-1.651l-13.36-14.255c-0.223-0.239-0.526-0.369-0.852-0.369   c-0.298,0-0.582,0.112-0.799,0.316l-1.971,1.847c-0.228,0.213-0.358,0.502-0.369,0.814c-0.01,0.311,0.102,0.609,0.316,0.837   L68.537,44.079C68.537,44.079,68.537,44.079,68.537,44.079z"/><path fill="none" d="M55.065,60.808l8.109-7.599c0.11-0.102,0.173-0.242,0.178-0.392c0.005-0.151-0.049-0.294-0.152-0.404   l-0.969-1.034c-0.107-0.114-0.253-0.178-0.411-0.178c-0.091,0-0.176,0.029-0.255,0.07l-8.28,7.738   c-0.177,0.215-0.18,0.53,0.015,0.738l0.97,1.035C54.476,61.002,54.847,61.016,55.065,60.808z"/><path fill="none" d="M74.885,73.278c0.107,0.115,0.254,0.178,0.412,0.178c0.143,0,0.28-0.054,0.384-0.152l6.845-6.416   c0.11-0.102,0.173-0.242,0.178-0.392c0.005-0.15-0.049-0.293-0.152-0.403l-5.422-5.785c-0.107-0.115-0.253-0.178-0.41-0.178   c-0.144,0-0.281,0.054-0.385,0.153l-6.846,6.415c-0.226,0.212-0.238,0.568-0.025,0.795L74.885,73.278z"/><path fill="none" d="M75.962,77.099c-0.107-0.115-0.253-0.178-0.411-0.178c-0.144,0-0.28,0.054-0.384,0.152l-6.846,6.416   c-0.181,0.169-0.229,0.442-0.117,0.666c0.024,0.047,0.055,0.091,0.09,0.128l5.423,5.787c0.107,0.115,0.253,0.178,0.41,0.178   c0.144,0,0.281-0.054,0.385-0.153l6.846-6.415c0.226-0.212,0.238-0.569,0.025-0.795L75.962,77.099z"/><path fill="none" d="M73.552,74.527l-5.421-5.785c-0.107-0.114-0.253-0.178-0.411-0.178c-0.143,0-0.28,0.054-0.384,0.152   l-6.846,6.416c-0.109,0.102-0.173,0.242-0.178,0.392c-0.005,0.15,0.049,0.293,0.152,0.403l5.422,5.784   c0.107,0.115,0.253,0.179,0.411,0.179c0.143,0,0.28-0.054,0.384-0.153l6.845-6.415C73.752,75.111,73.764,74.753,73.552,74.527z"/><path fill="none" d="M69.088,49.794c-0.107-0.115-0.253-0.178-0.41-0.178c-0.144,0-0.281,0.054-0.385,0.153L50.552,66.395   c-0.227,0.212-0.239,0.569-0.026,0.795l23.765,25.359c0.206,0.22,0.575,0.232,0.795,0.025l17.742-16.626   c0.226-0.212,0.238-0.568,0.025-0.794L69.088,49.794z M57.331,74.031l-5.421-5.784c-0.283-0.301-0.432-0.696-0.418-1.111   c0.013-0.414,0.187-0.799,0.49-1.083l6.845-6.414c0.495-0.465,1.27-0.501,1.847-0.17c-0.314-0.602-0.211-1.362,0.308-1.849   l6.846-6.416c0.6-0.562,1.63-0.53,2.194,0.071l5.419,5.783c0.586,0.627,0.554,1.61-0.07,2.196l-6.845,6.415   c-0.289,0.27-0.666,0.42-1.061,0.42c-0.282,0-0.544-0.099-0.782-0.242c0.12,0.232,0.186,0.49,0.177,0.758   c-0.013,0.414-0.186,0.799-0.489,1.082l-6.846,6.416c-0.289,0.27-0.666,0.419-1.061,0.419   C58.037,74.523,57.623,74.343,57.331,74.031z M65.163,82.388l-5.421-5.784c-0.283-0.302-0.432-0.696-0.418-1.111   c0.014-0.414,0.187-0.799,0.49-1.082l6.846-6.416c0.493-0.464,1.269-0.5,1.846-0.169c-0.315-0.602-0.212-1.363,0.307-1.85   l6.846-6.415c0.601-0.562,1.63-0.529,2.194,0.071l5.421,5.785c0.283,0.301,0.432,0.696,0.418,1.111   c-0.013,0.414-0.187,0.799-0.49,1.083l-6.845,6.415c-0.289,0.27-0.666,0.419-1.061,0.419c-0.272,0-0.535-0.079-0.768-0.213   c0.291,0.596,0.182,1.335-0.327,1.812l-6.845,6.415c-0.289,0.27-0.666,0.419-1.061,0.419C65.869,82.879,65.456,82.7,65.163,82.388z    M91.034,75.968l-6.845,6.415c-0.29,0.27-0.667,0.419-1.062,0.419c-0.272,0-0.534-0.079-0.767-0.212   c0.291,0.596,0.183,1.334-0.326,1.811l-6.846,6.416c-0.289,0.27-0.666,0.419-1.062,0.419c-0.427,0-0.839-0.179-1.132-0.491   l-5.421-5.785c-0.098-0.102-0.183-0.224-0.251-0.358c-0.31-0.615-0.177-1.369,0.324-1.835l6.845-6.416   c0.496-0.464,1.269-0.5,1.846-0.169c-0.314-0.601-0.212-1.361,0.306-1.848l6.848-6.416c0.038-0.036,0.084-0.074,0.131-0.11   c0.617-0.461,1.542-0.374,2.061,0.183l5.422,5.784c0.283,0.302,0.432,0.697,0.417,1.112C91.51,75.3,91.336,75.684,91.034,75.968z"/><path fill="none" d="M60.875,50.563l6.601-6.169L57.43,33.674L35.506,54c-0.981,0.921-1.599,2.114-1.784,3.448   c-0.344,2.489,0.947,4.175,1.518,4.785l2.161,2.306c1.064,1.136,2.506,1.789,4.062,1.839c1.56,0.049,3.037-0.507,4.173-1.572   l6.929-6.476c0.029-0.032,0.053-0.068,0.085-0.098l8.11-7.6C60.793,50.601,60.839,50.59,60.875,50.563z"/><path fill="none" d="M84.961,68.666c-0.107-0.115-0.253-0.179-0.411-0.179c-0.122,0-0.238,0.039-0.334,0.11l-6.896,6.458   c-0.225,0.212-0.237,0.569-0.025,0.795l5.422,5.785c0.107,0.115,0.252,0.178,0.41,0.178c0.144,0,0.28-0.054,0.385-0.153   l6.845-6.415c0.11-0.102,0.173-0.242,0.178-0.392c0.005-0.15-0.048-0.294-0.152-0.403L84.961,68.666z"/><path fill="none" d="M66.945,64.806l1.186-0.123l6.563-6.151c0.226-0.212,0.238-0.569,0.025-0.795l-5.421-5.785   c-0.107-0.115-0.253-0.178-0.41-0.178c-0.144,0-0.281,0.054-0.385,0.153l-6.846,6.415c-0.226,0.212-0.238,0.569-0.025,0.795   L66.945,64.806z"/><path fill="none" d="M65.872,66.574c0.005-0.151-0.049-0.294-0.152-0.404l-5.421-5.785c-0.108-0.115-0.254-0.178-0.411-0.178   c-0.144,0-0.28,0.054-0.385,0.153l-6.845,6.415c-0.11,0.102-0.173,0.242-0.178,0.392c-0.005,0.15,0.049,0.293,0.152,0.403   l5.422,5.784c0.205,0.22,0.577,0.234,0.795,0.026l6.846-6.415C65.804,66.863,65.867,66.724,65.872,66.574z"/><path fill="#000000" d="M25.592,24.043c0.517-0.484,0.62-1.239,0.311-1.84c0.238,0.142,0.499,0.241,0.781,0.241   c0.396,0,0.772-0.149,1.059-0.418l0.161-0.15c0.001-0.001,0.001-0.002,0.001-0.002l6.686-6.264c0.624-0.585,0.655-1.57,0.068-2.197   L29.24,7.63c-0.563-0.601-1.593-0.635-2.193-0.071l-6.846,6.416c-0.302,0.283-0.476,0.668-0.489,1.082   c-0.009,0.272,0.058,0.532,0.181,0.766c-0.576-0.331-1.35-0.295-1.846,0.17l-6.845,6.416c-0.625,0.585-0.656,1.569-0.071,2.193   l5.421,5.785c0.292,0.312,0.704,0.491,1.132,0.491c0.396,0,0.772-0.149,1.061-0.419L25.592,24.043z M20.878,14.697l6.846-6.415   c0.105-0.099,0.242-0.153,0.385-0.153c0.157,0,0.303,0.064,0.411,0.178l5.422,5.785c0.212,0.226,0.2,0.584-0.026,0.796   l-6.565,6.151l-1.184,0.122l-5.314-5.671c-0.102-0.109-0.156-0.251-0.151-0.402C20.705,14.939,20.768,14.799,20.878,14.697z    M17.684,29.889c-0.157,0-0.303-0.063-0.411-0.178l-5.421-5.785c-0.212-0.226-0.2-0.583,0.026-0.796l6.845-6.414   c0.105-0.099,0.242-0.153,0.385-0.153c0.157,0,0.303,0.064,0.411,0.178l5.422,5.785c0.212,0.227,0.201,0.583-0.025,0.795   l-6.847,6.416C17.964,29.835,17.828,29.889,17.684,29.889z"/><path fill="#000000" d="M33.422,32.4c0.509-0.477,0.617-1.216,0.326-1.812c0.233,0.133,0.495,0.213,0.768,0.213   c0.396,0,0.772-0.149,1.06-0.419l6.846-6.415c0.625-0.585,0.656-1.569,0.071-2.194l-5.422-5.784   c-0.563-0.601-1.593-0.637-2.193-0.072l-6.846,6.416c-0.302,0.284-0.477,0.668-0.49,1.082c-0.009,0.272,0.058,0.532,0.181,0.766   c-0.576-0.331-1.351-0.294-1.846,0.17l-6.846,6.415c-0.625,0.585-0.656,1.569-0.071,2.194l5.421,5.785   c0.292,0.312,0.705,0.491,1.133,0.491c0.396,0,0.772-0.149,1.061-0.419L33.422,32.4z M28.709,23.055l6.846-6.417   c0.105-0.098,0.241-0.152,0.384-0.152c0.157,0,0.303,0.064,0.412,0.179l5.422,5.784c0.212,0.226,0.2,0.583-0.026,0.795L34.9,29.66   c-0.104,0.098-0.241,0.152-0.384,0.152c-0.157,0-0.303-0.063-0.411-0.178l-5.422-5.785c-0.103-0.11-0.156-0.253-0.152-0.403   C28.537,23.296,28.599,23.157,28.709,23.055z M25.516,38.246c-0.157,0-0.303-0.064-0.411-0.178l-5.421-5.785   c-0.102-0.11-0.156-0.253-0.152-0.404c0.005-0.15,0.068-0.289,0.177-0.391l6.846-6.415c0.105-0.099,0.242-0.153,0.385-0.153   c0.157,0,0.303,0.063,0.411,0.178l5.421,5.785c0.212,0.226,0.2,0.583-0.026,0.795l-6.845,6.415   C25.796,38.192,25.659,38.246,25.516,38.246z"/><path fill="#000000" d="M50.326,30.13l-5.422-5.785c-0.522-0.558-1.446-0.639-2.06-0.183c-0.043,0.033-0.086,0.068-0.134,0.111   l-6.845,6.416c-0.303,0.284-0.477,0.668-0.49,1.082c-0.009,0.272,0.059,0.532,0.182,0.766c-0.576-0.331-1.35-0.295-1.845,0.17   l-6.846,6.415c-0.499,0.469-0.632,1.222-0.322,1.835c0.07,0.136,0.156,0.258,0.251,0.358l5.421,5.785   c0.292,0.312,0.705,0.491,1.133,0.491c0.396,0,0.773-0.149,1.061-0.419l6.846-6.415c0.302-0.284,0.476-0.668,0.49-1.083   c0.008-0.257-0.052-0.503-0.162-0.728c0.232,0.132,0.494,0.212,0.766,0.212c0.395,0,0.772-0.149,1.061-0.419l6.846-6.416   C50.879,31.738,50.91,30.755,50.326,30.13z M40.578,40.035l-6.846,6.416c-0.105,0.098-0.242,0.152-0.385,0.152   c-0.157,0-0.303-0.063-0.411-0.178l-5.424-5.789c-0.033-0.034-0.063-0.077-0.088-0.127c-0.113-0.222-0.065-0.496,0.116-0.665   l6.846-6.415c0.105-0.099,0.241-0.153,0.384-0.153c0.157,0,0.303,0.064,0.411,0.178l5.422,5.785   c0.102,0.11,0.156,0.253,0.152,0.404C40.751,39.794,40.688,39.933,40.578,40.035z M49.578,31.601l-6.846,6.416   c-0.104,0.099-0.241,0.153-0.384,0.153c-0.157,0-0.303-0.064-0.411-0.179l-5.421-5.785c-0.103-0.11-0.157-0.253-0.152-0.403   c0.005-0.15,0.068-0.289,0.178-0.391l6.895-6.458c0.098-0.072,0.213-0.111,0.336-0.111c0.156,0,0.302,0.064,0.41,0.178l5.421,5.785   C49.816,31.033,49.804,31.389,49.578,31.601z"/><path fill="#000000" d="M93.575,74.477L69.81,49.117c-0.564-0.602-1.594-0.634-2.194-0.071l-3.389,3.176   c-0.071-0.177-0.172-0.341-0.306-0.484l-0.969-1.034c-0.127-0.136-0.283-0.233-0.447-0.31l5.647-5.278l0.911,0.973   c0.407,0.434,0.98,0.682,1.575,0.682c0.55,0,1.073-0.207,1.474-0.583l1.972-1.848c0.867-0.812,0.911-2.18,0.1-3.049l-13.36-14.255   c-0.784-0.836-2.213-0.882-3.05-0.1l-1.971,1.847c-0.42,0.394-0.663,0.928-0.681,1.504c-0.019,0.576,0.187,1.125,0.582,1.546   l1.049,1.12l-5.705,5.289c-0.067-0.22-0.178-0.43-0.345-0.609l-0.97-1.035c-0.134-0.143-0.297-0.249-0.472-0.328l3.462-3.244   c0.624-0.585,0.656-1.57,0.071-2.194L29.03,5.473c-0.562-0.601-1.592-0.635-2.193-0.071L9.095,22.029   c-0.624,0.585-0.656,1.569-0.071,2.193L32.789,49.58c0.292,0.312,0.705,0.491,1.133,0.491c0.395,0,0.772-0.149,1.061-0.418   l4.073-3.817c0.071,0.176,0.172,0.34,0.306,0.483l0.969,1.035c0.144,0.153,0.317,0.273,0.506,0.357l-6.004,5.567   c-1.134,1.063-1.876,2.496-2.089,4.035c-0.137,0.989-0.052,1.975,0.226,2.901c-0.394,0.018-0.755,0.145-1.039,0.412l-0.781,0.732   c-0.262,0.245-0.384,0.551-0.406,0.867c-1.625-0.756-3.405-1.167-5.255-1.167c-3.177,0-6.205,1.198-8.525,3.371   c-0.096,0.09-0.152,0.214-0.156,0.345c-0.004,0.131,0.043,0.259,0.133,0.355l16.386,17.485c0.097,0.104,0.229,0.156,0.361,0.156   c0.121,0,0.243-0.044,0.338-0.133c3.787-3.549,4.859-8.921,3.141-13.5c0.288-0.057,0.578-0.208,0.844-0.457l0.782-0.732   c0.285-0.267,0.45-0.624,0.48-1.01c0.684,0.254,1.409,0.405,2.159,0.43c0.076,0.002,0.152,0.004,0.227,0.004   c1.735-0.001,3.381-0.65,4.654-1.842l5.967-5.577c0.069,0.17,0.166,0.332,0.299,0.474l0.97,1.035   c0.133,0.142,0.293,0.252,0.466,0.334l-4.138,3.878c-0.625,0.585-0.657,1.57-0.072,2.194l23.765,25.359   c0.292,0.312,0.704,0.491,1.132,0.491c0.395,0,0.773-0.149,1.062-0.419L93.504,76.67C94.128,76.086,94.16,75.101,93.575,74.477z    M56.427,31.156c-0.213-0.228-0.326-0.526-0.316-0.837c0.011-0.312,0.141-0.602,0.369-0.814l1.971-1.847   c0.217-0.204,0.501-0.316,0.799-0.316c0.327,0,0.629,0.13,0.852,0.369l13.36,14.255c0.44,0.47,0.416,1.211-0.053,1.651   l-1.972,1.848c-0.456,0.426-1.224,0.4-1.651-0.054l-1.249-1.332c0,0,0,0,0-0.001c0,0,0,0-0.001,0L56.427,31.156z M39.429,44.129   l-5.123,4.801c-0.218,0.207-0.59,0.193-0.796-0.026L9.745,23.545c-0.212-0.226-0.2-0.583,0.026-0.794L27.513,6.124   c0.105-0.099,0.242-0.153,0.385-0.153c0.157,0,0.303,0.063,0.41,0.178l23.764,25.359c0.213,0.227,0.201,0.584-0.025,0.796   l-4.504,4.221c-0.001,0.001-0.002,0.001-0.002,0.001l-8.109,7.6C39.431,44.127,39.43,44.128,39.429,44.129z M40.082,45.643   c-0.103-0.11-0.156-0.253-0.152-0.403c0.005-0.149,0.068-0.288,0.177-0.39l8.11-7.6c0.104-0.099,0.242-0.153,0.384-0.153   c0.157,0,0.303,0.064,0.411,0.178l0.969,1.035c0.213,0.226,0.201,0.583-0.025,0.795l-8.109,7.6   c-0.218,0.207-0.59,0.194-0.796-0.026L40.082,45.643z M33.702,81.568L18.007,64.82c2.084-1.792,4.721-2.773,7.481-2.773   c3.113,0,6.026,1.223,8.203,3.443l0.186,0.188C38.094,70.178,37.979,77.203,33.702,81.568z M38.116,67.224l-0.782,0.732   c-0.34,0.316-0.523,0.215-0.61,0.136c-0.534-1.105-1.242-2.148-2.124-3.09l-0.202-0.206c-0.737-0.752-1.559-1.385-2.434-1.919   c-0.217-0.241-0.327-0.622-0.138-0.799l0.781-0.732c0.11-0.102,0.246-0.173,0.404-0.152c0.15,0.005,0.289,0.068,0.391,0.178   c0.02,0.022,0.047,0.032,0.07,0.049c0.281,0.532,0.626,1.035,1.049,1.486l2.161,2.306c0.425,0.454,0.905,0.836,1.421,1.153   c0.014,0.021,0.022,0.045,0.04,0.065c0.102,0.108,0.156,0.251,0.152,0.401C38.288,66.983,38.225,67.122,38.116,67.224z    M52.565,58.33l-6.929,6.476c-1.136,1.065-2.613,1.621-4.173,1.572c-1.555-0.05-2.998-0.703-4.062-1.839l-2.161-2.306   c-0.571-0.61-1.862-2.295-1.518-4.785c0.185-1.334,0.803-2.527,1.784-3.448L57.43,33.674l10.046,10.72l-6.601,6.169   c-0.036,0.028-0.082,0.038-0.116,0.07l-8.11,7.6C52.618,58.262,52.594,58.297,52.565,58.33z M53.301,59.748   c-0.196-0.208-0.193-0.523-0.015-0.738l8.28-7.738c0.079-0.041,0.164-0.07,0.255-0.07c0.157,0,0.303,0.064,0.411,0.178l0.969,1.034   c0.102,0.11,0.156,0.253,0.152,0.404c-0.005,0.15-0.068,0.29-0.178,0.392l-8.109,7.599c-0.218,0.208-0.589,0.194-0.794-0.025   L53.301,59.748z M92.828,75.947L75.086,92.573c-0.22,0.207-0.589,0.195-0.795-0.025L50.526,67.19   c-0.213-0.226-0.201-0.583,0.026-0.795l17.741-16.626c0.104-0.099,0.242-0.153,0.385-0.153c0.156,0,0.302,0.063,0.41,0.178   l23.765,25.36C93.065,75.379,93.054,75.736,92.828,75.947z"/><path fill="#000000" d="M66.371,67.687c0.302-0.283,0.476-0.668,0.489-1.082c0.009-0.269-0.057-0.526-0.177-0.758   c0.238,0.142,0.5,0.242,0.782,0.242c0.395,0,0.772-0.15,1.061-0.42l6.845-6.415c0.624-0.585,0.656-1.569,0.07-2.196l-5.419-5.783   c-0.564-0.602-1.594-0.634-2.194-0.071l-6.846,6.416c-0.519,0.487-0.622,1.247-0.308,1.849c-0.577-0.331-1.352-0.295-1.847,0.17   l-6.845,6.414c-0.302,0.284-0.477,0.668-0.49,1.083c-0.014,0.414,0.135,0.81,0.418,1.111l5.421,5.784   c0.292,0.312,0.705,0.492,1.133,0.492c0.395,0,0.772-0.149,1.061-0.419L66.371,67.687z M61.657,58.341l6.846-6.415   c0.104-0.099,0.242-0.153,0.385-0.153c0.156,0,0.302,0.063,0.41,0.178l5.421,5.785c0.213,0.226,0.201,0.583-0.025,0.795   l-6.563,6.151l-1.186,0.123l-5.313-5.67C61.42,58.91,61.431,58.552,61.657,58.341z M58.054,73.355l-5.422-5.784   c-0.102-0.11-0.156-0.253-0.152-0.403c0.005-0.151,0.068-0.29,0.178-0.392l6.845-6.415c0.105-0.099,0.242-0.153,0.385-0.153   c0.156,0,0.302,0.063,0.411,0.178l5.421,5.785c0.102,0.11,0.156,0.253,0.152,0.404c-0.005,0.15-0.068,0.289-0.177,0.391   l-6.846,6.415C58.631,73.588,58.259,73.575,58.054,73.355z"/><path fill="#000000" d="M85.684,67.989c-0.52-0.556-1.444-0.643-2.061-0.183c-0.047,0.036-0.094,0.074-0.131,0.11l-6.848,6.416   c-0.518,0.488-0.62,1.247-0.306,1.848c-0.577-0.331-1.35-0.295-1.846,0.169l-6.845,6.416c-0.5,0.467-0.634,1.22-0.324,1.835   c0.069,0.134,0.154,0.256,0.251,0.358l5.421,5.785c0.293,0.312,0.705,0.491,1.132,0.491c0.396,0,0.773-0.149,1.062-0.419   l6.846-6.416c0.508-0.477,0.617-1.215,0.326-1.811c0.233,0.133,0.495,0.212,0.767,0.212c0.395,0,0.772-0.149,1.062-0.419   l6.845-6.415c0.302-0.284,0.476-0.668,0.49-1.082c0.014-0.414-0.134-0.81-0.417-1.112L85.684,67.989z M81.358,83.679l-6.846,6.415   c-0.104,0.099-0.242,0.153-0.385,0.153c-0.156,0-0.302-0.063-0.41-0.178l-5.423-5.787c-0.035-0.037-0.066-0.08-0.09-0.128   c-0.112-0.223-0.064-0.497,0.117-0.666l6.846-6.416c0.104-0.098,0.241-0.152,0.384-0.152c0.157,0,0.303,0.063,0.411,0.178   l5.421,5.785C81.596,83.11,81.584,83.468,81.358,83.679z M90.357,75.245l-6.845,6.415c-0.105,0.099-0.242,0.153-0.385,0.153   c-0.157,0-0.302-0.063-0.41-0.178l-5.422-5.785c-0.212-0.226-0.2-0.583,0.025-0.795l6.896-6.458c0.097-0.071,0.213-0.11,0.334-0.11   c0.157,0,0.303,0.064,0.411,0.179l5.422,5.784c0.103,0.109,0.156,0.253,0.152,0.403C90.53,75.004,90.468,75.143,90.357,75.245z"/><path fill="#000000" d="M74.202,76.045c0.509-0.477,0.618-1.216,0.327-1.812c0.233,0.133,0.495,0.213,0.768,0.213   c0.395,0,0.772-0.149,1.061-0.419l6.845-6.415c0.302-0.284,0.477-0.668,0.49-1.083c0.014-0.414-0.135-0.81-0.418-1.111   l-5.421-5.785c-0.564-0.601-1.593-0.634-2.194-0.071l-6.846,6.415c-0.519,0.487-0.622,1.247-0.307,1.85   c-0.577-0.331-1.352-0.295-1.846,0.169l-6.846,6.416c-0.302,0.283-0.476,0.668-0.49,1.082c-0.014,0.414,0.135,0.809,0.418,1.111   l5.421,5.784c0.293,0.312,0.706,0.492,1.133,0.492c0.395,0,0.772-0.149,1.061-0.419L74.202,76.045z M69.489,66.698l6.846-6.415   c0.104-0.099,0.242-0.153,0.385-0.153c0.156,0,0.302,0.063,0.41,0.178l5.422,5.785c0.102,0.11,0.156,0.253,0.152,0.403   c-0.005,0.151-0.068,0.29-0.178,0.392l-6.845,6.416c-0.104,0.098-0.242,0.152-0.384,0.152c-0.157,0-0.304-0.063-0.412-0.178   l-5.421-5.785C69.251,67.266,69.263,66.909,69.489,66.698z M66.296,81.89c-0.157,0-0.303-0.064-0.411-0.179l-5.422-5.784   c-0.102-0.11-0.156-0.253-0.152-0.403c0.005-0.15,0.069-0.29,0.178-0.392l6.846-6.416c0.104-0.098,0.242-0.152,0.384-0.152   c0.157,0,0.303,0.064,0.411,0.178l5.421,5.785c0.213,0.226,0.2,0.583-0.026,0.795l-6.845,6.415   C66.576,81.836,66.439,81.89,66.296,81.89z"/><path fill="#000000" d="M19.053,71.22c-0.085-0.374-0.311-0.693-0.636-0.895c-0.652-0.411-1.572-0.199-1.98,0.451   c-0.114,0.182-2.761,4.518,1.286,10.355c2.994,4.317,6.947,4.783,8.508,4.783c0.307,0,0.497-0.019,0.535-0.023   c0.414-0.043,0.788-0.265,1.027-0.608c0.197-0.285,0.285-0.63,0.248-0.974c-0.079-0.731-0.692-1.283-1.426-1.283   c-0.052,0-0.103,0.003-0.151,0.008c-0.136,0.012-3.704,0.321-6.382-3.541c-2.933-4.229-1.283-7.073-1.214-7.188   C19.073,71.979,19.138,71.595,19.053,71.22z M19.27,80.056c2.558,3.689,5.956,3.976,6.936,3.976c0.203,0,0.328-0.012,0.41-0.017   c0.227,0,0.418,0.172,0.442,0.399c0.014,0.137-0.034,0.242-0.077,0.303c-0.074,0.106-0.191,0.176-0.32,0.189   c-0.031,0.003-0.183,0.017-0.429,0.017c-1.405,0-4.968-0.425-7.695-4.358c-3.678-5.304-1.363-9.104-1.262-9.264   c0.083-0.131,0.224-0.21,0.379-0.21c0.084,0,0.166,0.023,0.238,0.07c0.101,0.063,0.171,0.162,0.198,0.278   c0.027,0.117,0.006,0.237-0.055,0.334C18.012,71.808,15.909,75.208,19.27,80.056z"/><path fill="#000000" d="M29.352,92.025c-0.077,0-0.155,0.006-0.226,0.018c-0.053,0.008-0.537,0.074-1.337,0.074   c-0.88,0-2.269-0.084-3.894-0.486c-4.045-1.001-7.542-3.367-10.392-7.033c-7.46-9.596-3.615-16.971-3.576-17.042   c0.384-0.693,0.133-1.569-0.56-1.953c-0.67-0.371-1.582-0.107-1.951,0.558c-0.053,0.095-1.293,2.356-1.402,6.016   c-0.096,3.282,0.731,8.407,5.222,14.183C17.077,93.877,24.07,94.999,27.719,95c0,0,0,0,0,0c1.068,0,1.738-0.101,1.861-0.122   c0.387-0.063,0.724-0.274,0.949-0.598c0.214-0.309,0.298-0.682,0.238-1.05C30.654,92.532,30.058,92.025,29.352,92.025z    M29.717,93.716c-0.07,0.1-0.175,0.166-0.296,0.186c-0.068,0.011-0.689,0.108-1.702,0.108c0,0,0,0,0,0   c-3.468-0.001-10.119-1.073-15.703-8.256c-4.31-5.545-5.105-10.427-5.013-13.547c0.101-3.421,1.265-5.542,1.277-5.564   c0.079-0.142,0.228-0.23,0.391-0.23c0.076,0,0.149,0.018,0.216,0.056c0.216,0.12,0.294,0.392,0.175,0.608   c-0.044,0.077-4.267,7.932,3.66,18.129c2.99,3.846,6.669,6.331,10.935,7.386c1.721,0.426,3.197,0.515,4.131,0.515   c0.853,0,1.383-0.071,1.563-0.093c0.215,0,0.404,0.161,0.439,0.376C29.81,93.504,29.783,93.621,29.717,93.716z"/></g></svg>
				</div>
				<div class="data">
					{section name=i loop=$spaceweather max=2}
					<ul><li><span style="font-weight: lighter;">{$spaceweather[i].type}:</span> {$spaceweather[i].value}</li></ul>
					{/section}
					<ul><li><a href="/spaceweather.php">[more]</a></li></ul>
				</div>
			</section>{/if}
			<section class="flex-one-column" style="font-weight: lighter;">
				<ul>
					<li><a href="https://www.facebook.com/{$smarty.const.FACEBOOK_PAGENAME}"><i class="emoji facebook"></i> <span class="hide-mobile">fb/{$smarty.const.FACEBOOK_PAGENAME}</span></a></li>
					<li><a href="https://www.twitter.com/{$smarty.const.TWITTER_NAME}"><i class="emoji twitter"></i> <span class="hide-mobile">@{$smarty.const.TWITTER_NAME}</span></a></li>
					<li><a href="{$smarty.const.GIT_REPOSITORY_URL}"><i class="emoji github"></i> <span class="hide-mobile">zorg-code</span></a></li>
				</ul>
			</section>
			{if $tplroot.id > 0}
			<section class="flex-one-column">
				<ul>
					<li class="uppercase"><a href="/page/impressum">Impressum</a></li>
					<li class="uppercase"><a href="/page/privacy">Privacy</a></li>
					<li class="uppercase"><a href="/page/verein">zorg Verein</a></li>
				</ul>
			</section>
			{/if}
		</footer>
	<script>const layout = '{$daytime}';</script>
	{*if $tplroot.page_title == 'Home' && !$smarty.get.tpleditor}<!-- Redirect Mobile Devices, kudos to http://detectmobilebrowsers.com/ -->
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
	{/literal}</script>{/if*}
	<!--googleon: all--></body>
</html>
