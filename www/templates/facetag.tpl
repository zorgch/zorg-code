{literal}<style>.tooltip{position:absolute;border:2px;border-style:solid;border-color:red}
.tooltip .tooltiptext{visibility:hidden;background-color:#000;color:#fff;text-align:center;padding:5px 0;border-radius:6px;position:absolute;z-index:1;width:120px;bottom:100%;left:50%;margin-bottom:10px;margin-left:-60px}
.tooltip .tooltiptext::after{content:"";position:absolute;top:100%;left:50%;margin-left:-5px;border-width:5px;border-style:solid;border-color:#000 transparent transparent}
.tooltip:hover .tooltiptext{visibility:visible}
.tribute-container ul{background:#42300A !important;border:1px solid #62502A !important}.tribute-container li.highlight,.tribute-container li:hover{background:#62502A !important}</style>{/literal}
{* Display Data *}
{*<ul>
{section name=record loop=$pics}
    {foreach from=$pics[record] item=entry key=name}
       <li align='center'>{$name} is {$entry}</li>
    {/foreach}
 {/section}
</ul>*}
{section name=i loop=$pics}
	{if $pics[i].pic_id > 0}
		<h2>{$h2}</h2>
		{if ($currindex-1 >= 0)}<a href="?action=getpic&amp;index={$currindex-1}" id="prev" tabindex="4">&larr; prev</a> {/if}<input id="select_user" type="text" placeholder="Username..." autocomplete="off" tabindex="1" onkeydown = "if (event.keyCode == 13 && Number(userid_input.value) > 0) save_btn.click();" /> <label for="userid"> = </label><input id="userid" type="text" value="" placeholder="User-ID" autocomplete="off" disabled /> <input id="save_btn" type="button" class="button" value="markier&auml;!" tabindex="2" disabled />{if ($currindex+1 >= 1)} <a href="javascript:;" tabindex="3" onclick="getNewPic();">-- NEW PIC --</a> <a href="?action=getpic&amp;index={$currindex+1}" id="next" tabindex="4">next &rarr;</a>{/if}
		<div style="display:inline-block;position:relative;">
			<img id="img_{$pics[i].pic_id}" src="https://zorg.ch{$pics[i].img_path}" style="display:block;">
			{*foreach $pics as $pic*}
			{if $pics[i].headpose_roll_angle}
				{assign var='transform_tooltip' value='transform: rotateZ(`$pics[i].headpose_roll_angle`deg);'}
				{assign var='calc_tooltiptext' value=-1*$pics[i].headpose_roll_angle}
				{assign var='transform_tooltiptext' value='transform: rotateZ(`$calc_tooltiptext`deg);'}
			{/if}
			<div class="tooltip" id="tooltip_{$pics[i].pic_id}" style="top:{$pics[i].top}px;left:{$pics[i].left}px;width:{$pics[i].width}px;height:{$pics[i].height}px;{$transform_tooltip}">
				<span class="tooltiptext" id="tooltiptext_{$pics[i].pic_id}" style="{$transform_tooltiptext}">
					{$pics[i].gender}, {$pics[i].age}{if $pics[i].smiling}, {$pics[i].smiling}{/if}
				</span>
			</div>
			{*/foreach*}
		</div>
		<!-- Tribute - Native @mentions: https://github.com/zurb/tribute/ -->
		<link rel="stylesheet" href="/js/tribute/tribute.css" />
		<script src="/js/tribute/tribute.min.js"></script>
		<script>
		var username_name = '';
		var username_input = document.getElementById('select_user');
		var userid_input = document.getElementById('userid');
		var pic_id = {$pics[i].pic_id};
		var save_btn = document.getElementById('save_btn');
		var img_tag = document.getElementById('img_{$pics[i].pic_id}')
		var tooltip_tag = document.getElementById('tooltip_{$pics[i].pic_id}')
		var tooltiptext_tag = document.getElementById('tooltiptext_{$pics[i].pic_id}')
		{literal}// Tribute - Native @mentions
		var mentionTrigger = '@';
		var tribute = new Tribute({
		 collection:
		  [{
			trigger: mentionTrigger,
			requireLeadingSpace: false,
			allowSpaces: false,
		    values: function (search, usernames) {
		      getUsernames(search, users => usernames(users));
		    },
		    lookup: 'username',
		    fillAttr: 'username',
		    selectTemplate: function (item) {
		      setUserId(item.original.userid);
		      username_name = item.original.username;
		      console.log(username_name);
		      return mentionTrigger + item.original.username;
		    }
		  }]
		});
		tribute.attach(username_input);
		username_input.onfocus = function(){
		  if (save_btn.style.borderColor = 'red') { save_btn.style.borderColor = ''; save_btn.style.backgroundColor = ''; }
		  this.value = '';
		  tribute.showMenuForCollection(this);
		};
		// AJAX request to get usernames from DB
		function getUsernames(search, usernames) {
		  var xhr = new XMLHttpRequest();
		  xhr.onreadystatechange = function ()
		  {
		  if (xhr.readyState === 4) {
		    if (xhr.status === 200) {
		    var data = JSON.parse(xhr.responseText);
		    usernames(data);
		    } else if (xhr.status === 403) {
		      usernames([]);
		    }
		  }
		  };
		  xhr.open('GET', '/js/ajax/get-usernames.php?action=userlist&mention='+search, true);
		  xhr.setRequestHeader('Content-Type', 'application/json');
		  xhr.send();
		}
		function setUserId(userid) {
		  document.getElementById('userid').value = userid;
		  document.getElementById('save_btn').disabled = false;
		}
		
		// AJAX POST Action for the Form Submit
		save_btn.onclick = function(){
			this.disabled = true;
			username_input.disabled = true;
			username_input.blur();
			saveUser(userid_input.value);
		};
		
		function saveUser(id) {
			console.log(pic_id);
			console.log(userid_input.value);
			const params = {
	            picid: Number(pic_id),
	            userid: Number(userid_input.value)
	        };
	        
	        console.log(params);
	        var xhr = new XMLHttpRequest();
	        xhr.onreadystatechange = function ()
			{
			  if (xhr.readyState === 4) {
				if (xhr.status === 200) {
				  // On Success
				  //console.info(xhr.responseText);
				  save_btn.value = '✓ done';
				  save_btn.style.borderColor = 'green';
				  save_btn.style.backgroundColor = 'lightgreen';
				  tooltip_tag.style.borderColor = 'green';
				  tooltiptext_tag.textContent = username_name + ', ' + tooltiptext_tag.textContent;
				  getNewPic()
			    } else {
				  // On Error
			      console.error(JSON.parse(xhr.responseText));
			      save_btn.style.borderColor = 'red';
			      save_btn.style.backgroundColor = 'lightred';
			      username_input.disabled = false;
			    }
			  }
			};
			/*xhr.onload = function(){
	            // On Success
	        };*/
	        xhr.open('POST', '/js/ajax/set-userfaceid.php?action=set', false);
	        xhr.setRequestHeader('Content-Type', 'application/json');
	        xhr.send(JSON.stringify(params)); // Make sure to stringify
		};
		
		function getNewPic() {
		  var xhr = new XMLHttpRequest();
		  xhr.onreadystatechange = function ()
		  {
		  if (xhr.readyState === 4) {
		    if (xhr.status === 200) {
			    // On Success
		        var data = JSON.parse(xhr.responseText);
		        reloadPic(data);
		      } else {
		        // On Error
			    console.error(JSON.parse(xhr.responseText));
		      }
		    }
		  };
		  xhr.open('GET', '/js/ajax/get-userpic.php?action=getpic&index=', true);
		  xhr.setRequestHeader('Content-Type', 'application/json');
		  xhr.send();
		}
		
		function reloadPic(picData) {
			console.log(picData);
			pic_id = picData[0]['pic_id'];
			img_tag.hidden = true;
			img_tag.id = 'img_' + picData[0]['pic_id'];
			img_tag.hidden = false;
			tooltip_tag.id = 'tooltip_' + picData[0]['pic_id'];
			tooltiptext_tag.id = 'tooltiptext_' + picData[0]['pic_id'];
			img_tag = document.getElementById('img_' + picData[0]['pic_id']);
			tooltip_tag = document.getElementById('tooltip_' + picData[0]['pic_id']);
			tooltiptext_tag = document.getElementById('tooltiptext_' + picData[0]['pic_id']);
			img_tag.src = picData[0]['img_path'];
			tooltip_tag.style.borderColor = 'red';
			tooltip_tag.style.top = picData[0]['top'] + 'px';
			tooltip_tag.style.left = picData[0]['left'] + 'px';
			tooltip_tag.style.width = picData[0]['width'] + 'px';
			tooltip_tag.style.height = picData[0]['height'] + 'px';
			tooltip_tag.style.transform = 'rotateZ(' + picData[0]['headpose_roll_angle'] + 'deg)';
			tooltiptext_tag.textContent = picData[0]['gender'] + ', ' + picData[0]['age'];
			if (picData[0]['smiling']) {
				tooltiptext_tag.textContent = tooltiptext_tag.textContent + ', ' + picData[0]['smiling'];
			}
			var tooltiptext_transform = -1*Number(picData[0]['headpose_roll_angle']);
			tooltiptext_tag.style.transform = 'rotateZ(' + tooltiptext_transform + 'deg)';
			username_input.disabled = false;
			username_input.value = '';
			userid_input.value = '';
			username_name = '';
			save_btn.disabled = false;
			save_btn.value = 'markierä!';
			save_btn.style.borderColor = '';
			save_btn.style.backgroundColor = '';
			username_input.focus();
		}
		</script>{/literal}
	{else}
		<h3 style="color:red;">ERROR: Kein Bild oder es konnte nicht geladen werden</h3>
	{/if}
{/section} 
