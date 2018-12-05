{*
	@TODO merge Forum::getFormEdit() into tpl/commentform.tpl
	@TODO add Fileicons to File-Links: <span class="file-icon file-icon-xs" data-type="' + fileMime + '">' + item.original.fileName + '</span>
	@TODO make quill.day.css & quill.night.css for Zorg Night-Layout
	@TODO add User Profilepic to Tribute @mention menu?
		menuItemTemplate: function (usernames) { console.table(usernames); => too slow because many "404 not found"
		  return '<img src="/data/userimages/'+ usernames.original.userid + '_tn.jpg" border="0" height="20">' + usernames.original.username;
		}
*}
{* Smarty Random-Quote Generator *}
{assign_array var="quotes" value="array(
  'du stinkst wien Penner am Sack',
  'Machst du jetzt hier einen auf Mädchen oder was?',
  'Ich mach dir ein paar Brote',
  'shit happens when u party naked',
  'Chunnts guet mit denä Schnitzelbröter?',
  'What are you? Sigmund sawed-off fucking Freud?',
  'Shit in one hand and wish in the other and see which one fills up first.',
  'I beat up some kids today, made me feel good about myself.',
  'Man halt die Fresse, ich hab Mittagspause!',
  'Du kannst doch nicht andauernd nur Scheiße baun!',
  'Nein Mann, ich will noch nich gehn!',
  'Lass uns noch n bisschen tanzen',
  'Voll Laser wie du abgehst!',
  'Mir platzt gleicht das Hemd!',
  'Komm schon Alter, ist doch noch nicht so spät',
  'Eew that\\'s dirty!',
  'Look at my horse, my horse is amazing!',
  'Mmh, tastes just like raisins!',
  'Sweet Lemonade, Mmm Sweet lemonade',
  'Do you think so?',
  'Shut up Mom and get on my horse!'
)"}
  {*dä Loooooping*}{section name="element" loop=$quotes}
    {assign var="allstrings" value=$allstrings+1}
  {/section}
  {assign var="allstrings" value=$allstrings-1}{*Korrektur weil array() bei 0 beginnt (max=9), nicht bei 1 (max=10)*}
  {*Randomizer*}{rand min=0 max=$allstrings assign="zeigen"}
<!-- Quill - Rich Text Editor: https://quilljs.com/ -->
<link rel="stylesheet" href="/js/quill-richtexteditor/quill.snow.css" />
<link rel="stylesheet" href="/js/quill-richtexteditor/quill-emoji.css">
<!-- Tribute - Native @mentions: https://github.com/zurb/tribute/ -->
<link rel="stylesheet" href="/js/tribute/tribute.css" />
<script src="/js/tribute/tribute.min.js"></script>
{literal}<style>#form-container{margin-top:10px;}.commenting{height:100%;bottom:0}.#schickenaaab{padding-top:65px;bottom:0;right:0;}.ql-htmleditor{position:absolute;top:0;bottom:0;right:0;left:0;border:none;}.ql-container.ql-snow{border:0px}.ql-toolbar.ql-snow{border:0px}.ql-showHtml{color:{/literal}{if $sun == "up"}#000{else}#444{/if}{literal}}.ql-showHtml:after{content:"html"}.ql-memberOnly{color:{/literal}{if $sun == "up"}#000{else}#444{/if}{literal}}.ql-memberOnly:after{content:"[z]"}.ql-editor p a,a:active{color:{/literal}{if $sun == "up"}#344586{else}#CBBA79{/if}{literal}}.ql-editor p a:hover{text-decoration:underline}.tribute-container ul{background:#42300A;border:1px solid #62502A}.tribute-container li.highlight,.tribute-container li:hover{background:#62502A;}.ql-editor code,.ql-snow.ql-editor pre{padding:5px 10px!important;background-color:{/literal}{if $sun == "up"}#fff{else}#23241f{/if}{literal}!important}</style>{/literal}
<!-- Zorg Comment-Form -->
<div id="form-container" class="border">
	<!--form name="commentform" id="commentform" action="/actions/comment_new.php" method="post">
		<input type="hidden" name="action" value="new">
		<input type="hidden" name="url" value="{$url|base64encode}">
		<input type="hidden" name="board" value="{$board}">
		<input type="hidden" name="thread_id" value="{$thread_id}">
		<input type="hidden" name="parent_id" value="{$parent_id}">
		<a name="reply"></a>
		<div class="commenting">
			<div id="toolbar"></div>
			<div class="text" id="dinimuetter" tabindex="1"></div>
			<input type="hidden" name="text">
		</div>
		<input type="hidden" name="msg_users[]" id="notificationList">
		<input class="button" name="submit" id="schickenaaab" tabindex="2" type="submit" value="Erstellen">
	</form-->
	<table class="border" width="100%">
		<form action="/actions/events.php" method="post">
			<input name="url" type="hidden" value="{$url|base64encode}">
			<input name="action" type="hidden" value="new">
			
			<tr><td colspan="2"><b>Neuen Event eintragen:</b></td></tr>
			
			<tr>
			<td align="left">Name</td>
			<td align="left" colspan="4"><input name="name" type="text" class="text" value=""></td>
			</tr>
			
			<tr>
			<td align="left">Location</td>
			<td align="left" colspan="4">
			<input maxlength="90" name="location" size="30" type="text" class="text" value="">
			</td>
			</tr>
			
			<tr>
			<td align="left">Link:</td>
			<td align="left" colspan=4><input class="text" name="link" size="30" maxlength="100" type="text" value="http://"></td>
			</tr>
			
			<tr>
			<td align="left">Review (url):</td>
			<td align="left" colspan=4><input class="text" name="review_url" size="30" maxlength="100" type="text" value="http://"></td>
			</tr>
			
			<tr>
			<td align="left">Gallery:</td>
			<td align="left" colspan=4>
			<select class="text" name="gallery_id" size="1">{html_options options=$galleries selected=0}</select>
			</td>
			</tr>
			
			<tr>
			<td align="left">Start</td>
			<td align="left">
			{html_select_date end_year="2023" prefix="start" start_year="1998" time="`$smarty.now`"}
			Zeit:
			{html_select_time display_minutes=false display_seconds=false prefix="start" time="`$smarty.now`"}
			uhr
			</td>
			</tr>
			
			<tr>
			<td align="left">End</td>
			<td align="left">
			{html_select_date end_year="2023" prefix="end" start_year="1998" time="`$smarty.now`"}
			Zeit:
			{html_select_time display_minutes=false display_seconds=false prefix="end" time="`$smarty.now`"}
			uhr
			</td>
			</tr>
			
			<tr><td align="left" valign="top">Beschreibung:</td>
			<td align="left" colspan=4><textarea name="description" cols=70 rows=8></textarea></td>
			</tr>
					
			<tr><td align="left" colspan="2"><input type="submit" value="eintragen" class="button" size=60></td></tr>
		
		</form>
	</table>
	<table class="border" width="100%">
<form action="/actions/events.php" method="post">
<input name="url" type="hidden" value="{$url|base64encode}">
<input name="action" type="hidden" value="new">

<tr><td colspan="2"><b>Neuen Event eintragen:</b></td></tr>

<tr>
<td align="left">Name</td>
<td align="left" colspan="4"><input name="name" type="text" class="text" value=""></td>
</tr>

<tr>
<td align="left">Location</td>
<td align="left" colspan="4">
<input maxlength="90" name="location" size="30" type="text" class="text" value="">
</td>
</tr>

<tr>
<td align="left">Link:</td>
<td align="left" colspan=4><input class="text" name="link" size="30" maxlength="100" type="text" value="http://"></td>
</tr>

<tr>
<td align="left">Review (url):</td>
<td align="left" colspan=4><input class="text" name="review_url" size="30" maxlength="100" type="text" value="http://"></td>
</tr>

<tr>
<td align="left">Gallery:</td>
<td align="left" colspan=4>
<select class="text" name="gallery_id" size="1">{html_options options=$galleries selected=0}</select>
</td>
</tr>

<tr>
<td align="left">Start</td>
<td align="left">
{html_select_date end_year="2023" prefix="start" start_year="1998" time="`$smarty.now`"}
Zeit:
{html_select_time display_minutes=false display_seconds=false prefix="start" time="`$smarty.now`"}
uhr
</td>
</tr>

<tr>
<td align="left">End</td>
<td align="left">
{html_select_date end_year="2023" prefix="end" start_year="1998" time="`$smarty.now`"}
Zeit:
{html_select_time display_minutes=false display_seconds=false prefix="end" time="`$smarty.now`"}
uhr
</td>
</tr>

<tr><td align="left" valign="top">Beschreibung:</td>
<td align="left" colspan=4><textarea name="description" cols=70 rows=8></textarea></td>
</tr>
		
<tr><td align="left" colspan="2"><input type="submit" value="eintragen" class="button" size=60></td></tr>

</form>
</table>
</div>

<script src="/js/quill-richtexteditor/quill.min.js"></script>
<script src="/js/quill-richtexteditor/fuse.min.js"></script>
<script src="/js/quill-richtexteditor/quill-emoji.js"></script>
{literal}<script type="text/javascript">
// Quill - Rich Text Editor
var quill = new Quill('#dinimuetter', {
  modules: {
    syntax: true,
    toolbar: {
      container: [
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        ['bold', 'italic', 'underline', 'strike',],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['link', 'blockquote', 'code-block', 'memberOnly'],
        //['emoji'],
        ['clean', 'showHtml']
      ],
      handlers: {'emoji': function() {}}
    },
    short_name_emoji: true
    //textarea_emoji: true,
    //toolbar_emoji: true,
    /*keyboard: {
      bindings: { tab: false }
    }*/
  },
  scrollingContainer: '#commenting',
  theme: 'snow'
});

// Quill - Toolbar additional buttons
var memberOnly = document.querySelector('.ql-memberOnly');
memberOnly.addEventListener('click', function() {
  var textPrefix = '{member}';
  var textSuffix = '{/member}';
  quill.focus();
  var textSelection = quill.getSelection();
  if (textSelection) {
    if (textSelection.length == 0) {
      //console.log('User cursor is at index', textSelection.index);
      quill.insertText(textSelection.index, textPrefix + textSuffix, false);
    } else {
      //console.log('User has highlighted: ', textSelection);
      quill.insertText(textSelection.index, textPrefix, false);
      quill.insertText(textSelection.index + textPrefix.length + textSelection.length, textSuffix, false);
    }
  }
  quill.setSelection(textSelection.index + textPrefix.length);
});

// Quill - Plain HTML Editor Mode: https://codepen.io/anon/pen/ZyEjrQ
var plainText = document.createElement('textarea');
plainText.style.cssText = "display:none;width:100%;height:100%;margin:0px;background:rgb(29,29,29);box-sizing:border-box;color:rgb(204,204,204);font-size:15px;outline:none;padding:20px;line-height:24px;font-family:Consolas,Menlo,Monaco,Courier,monospace;"
var htmlEditor = quill.addContainer('ql-htmleditor')
htmlEditor.style.display = 'none';
htmlEditor.appendChild(plainText)
var quillEditor = document.querySelector('#dinimuetter')
quill.on('text-change', (delta, oldDelta, source) => {
  var html = quillEditor.children[0].innerHTML
  plainText.value = html
})
var showHtml = document.querySelector('.ql-showHtml');
showHtml.addEventListener('click', function() {
  if (plainText.style.display === '') {
  var html = plainText.value
  self.quill.pasteHTML(html)
  }
  plainText.style.display = plainText.style.display === 'none' ? '' : 'none';
  htmlEditor.style.display = htmlEditor.style.display === 'none' ? '' : 'none';
});

// Quill - Autosave: https://quilljs.com/playground/#autosave
// https://stackoverflow.com/questions/27273444/save-and-load-input-values-using-local-storage
var Delta = Quill.import('delta');
var QuillEditorId = 'dinimuetter';
var pageId = encodeURI(window.location.pathname).replace('/', '-').replace('.', '-');
var localStoreId = 'z_commentform_draft' + pageId;
var localDraft = localStorage[localStoreId];
// Load Draft
function loadDraft(key) {
  if (typeof(Storage) !== 'undefined') {
  if (key !== undefined && key !== 'undefined' && key !== null && key !== '') {
    //console.info('Draft found in Local Storage: ' + localDraft);
    quill.setContents(JSON.parse(key));
  } else {
  //console.info('No Draft in Local Storage, using default text');
  quill.insertText(0,{/literal}"{$quotes[$zeigen]}"{literal});
  }
  }
}
function deleteDraft(key) {
  if (typeof(Storage) !== 'undefined') {
    if (key !== undefined && key !== 'undefined' && key !== null && key !== '') {
      console.info('Deleting draft... ' + key);
      localStorage.removeItem(key);
    }
  }
}
loadDraft(localDraft);
// Store accumulated changes
var change = new Delta();
quill.on('text-change', function(delta) {
  change = change.compose(delta);
  if(quill.getLength() > 1){
  document.getElementById('schickenaaab').disabled = false;
  } else {
  document.getElementById('schickenaaab').disabled = true;
  }
});
// Save periodically
var autoSaveOn = null;
autoSaveOn = setInterval(function() {
  if (change.length() > 0) {
  if (typeof(Storage) != 'undefined') {
  console.info('Saving changes...');//, change);
    localStorage.setItem(localStoreId, JSON.stringify(quill.getContents()));
  }
  change = new Delta();
  }
}, 5*1000);
// Check for unsaved data
window.onbeforeunload = function() {
  if (change.length() > 0) {
  return 'There are unsaved changes. Are you sure you want to leave?';
  }
}

// Tribute - Native @mentions
var mentionPrefix = '<a class="mention" href="';//'<span class="mention">@'
var mentionSuffix = '</a>';//'</span>'
var mentionTrigger = '@';
var currUserId = {/literal}{$user->id}{literal};
var tribute = new Tribute({
 collection:
  [{
	trigger: mentionTrigger,
	requireLeadingSpace: true,
	allowSpaces: false,
    values: function (search, usernames) {
      getUsernames(search, users => usernames(users));
    },
    lookup: 'username',
    fillAttr: 'username',
    selectTemplate: function (item) {
      addUserIdNotification(item.original.userid);
      return mentionPrefix + '/profil.php?user_id=' + item.original.userid + '">' + mentionTrigger + item.original.username + mentionSuffix;
    }
  },{
    trigger: '#',
    requireLeadingSpace: true,
    allowSpaces: false,
    values: function (search, templates) {
      getTemplates(search, tpl => templates(tpl));
    },
    lookup: 'tplTitle',
    fillAttr: 'tplTitle',
    selectTemplate: function (item) {
      return '{include file=' + item.original.tplId + '}{* ' + item.original.tplTitle + ' *}';
    }
  },{
    trigger: '!',
    requireLeadingSpace: true,
    allowSpaces: false,
    values: function (search, userfiles) {
      getUserfiles(search, file => userfiles(file));
    },
    lookup: 'fileName',
    fillAttr: 'fileName',
    selectTemplate: function (item) {
	  var fileSrc = '/files/' + currUserId + '/' + item.original.fileName;
	  //var fileMime = item.original.fileType.substring( item.original.fileType.indexOf('/') + 1 );
	  //var fileMime = item.original.fileName.substring( item.original.fileName.indexOf('.') + 1 );
	  //var fileIcon = ( ['zip','rar','pdf','txt','mp3','wma','m4a','flac','mp4','wmv','mov','avi','mkv'].indexOf(fileMime) >= 0 ? '<span class="file-icon file-icon-xs" data-type="' + fileMime + '">' + item.original.fileName + '</span>' : item.original.fileName );
      var returnString = '<a target="_blank" href="' + fileSrc + '">' + ( item.original.fileType.indexOf('image/') >= 0 ? '<img src="' + fileSrc + '" title="' + item.original.fileName + '" />' : item.original.fileName ) + '</a>'; //fileIcon
      return returnString;
    }
  }]
});
tribute.attach(document.getElementsByClassName('ql-editor'));
// AJAX request to get usernames from DB
var allUserMentions = [];
var usersToNotify = [];
var notificationList = document.getElementById('notificationList');
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
function addUserIdNotification(userid) {
  //console.info('allUserMentions[] adding: ' + userid);
  allUserMentions.push(userid);
  // Remove duplicate UserIDs - https://stackoverflow.com/a/40482714/5750030
  var usersToNotify = allUserMentions.reduce(function(hash){
    return function(prev,curr){
      !hash[curr] && (hash[curr]=prev.push(curr));
      return prev;
    };
  }(Object.create(null)),[]);
  //console.info(usersToNotify);
  if( usersToNotify && usersToNotify !== 'null' && usersToNotify !== 'undefined' ) {
    notificationList.value = usersToNotify;
    console.info('Notify users: ' + usersToNotify);
  }
}
// AJAX request to get templates from DB
function getTemplates(search, templates) {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function ()
  {
  if (xhr.readyState === 4) {
    if (xhr.status === 200) {
    var data = JSON.parse(xhr.responseText);
    templates(data);
    } else if (xhr.status === 403) {
      templates([]);
    }
  }
  };
  xhr.open('GET', '/js/ajax/get-templates.php?action=templates&mention='+search, true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.send();
}
// AJAX request to get userfiles from DB
function getUserfiles(search, userfiles) {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function ()
  {
  if (xhr.readyState === 4) {
    if (xhr.status === 200) {
    var data = JSON.parse(xhr.responseText);
    userfiles(data);
    } else if (xhr.status === 403) {
      userfiles([]);
    }
  }
  };
  xhr.open('GET', '/js/ajax/get-userfiles.php?action=userfiles&userid='+currUserId+'&mention='+search , true);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.send();
}

// Commentform - Submit
var form = document.querySelector('form[name=commentform]');
document.getElementById('schickenaaab').onclick = function() {
  var comment = document.querySelector('input[name=text]');
  var html = quill.root.innerHTML;
  if (typeof html != 'undefined' && html) {
    comment.value = quillHtmlFix0r(html);
  }
  if( document.querySelector('input[name=parent_id]:checked') && document.querySelector('input[name=parent_id]:checked') !== 'null' && document.querySelector('input[name=parent_id]:checked') !== 'undefined' ) {
    replyToId = document.querySelector('input[name=parent_id]:checked').value;
    form.querySelector('input[name=parent_id]').value = replyToId;
  }
  clearInterval(autoSaveOn); // stop Save periodically
  deleteDraft(localStoreId);
  return true;//false;
};

/**
 * Quill - Fix unwanted Tags & Classes
 * @TODO Fix Quill not sending custom classes like .ql-syntax, hljs-tag, etc.
 */
function quillHtmlFix0r(html) {
  console.info('Cleaning up Quill HTML...');
  html = html.replace('<pre class="ql-syntax" spellcheck="false">', '\{literal\}<pre><code>'); // Quill Code-Block Syntax
  html = html.replace('</pre>', '</code></pre>\{/literal\}'); // Quill Code-Block Syntax
  //html = html.replace('{', '&lbrace;'); // Replace left-facing Curly-Brackets
  //html = html.replace('}', '&rbrace;'); // Replace right-facing Curly-Brackets
  return html;
}
</script>{/literal}
