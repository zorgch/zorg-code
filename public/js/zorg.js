function showhide(id, elem)
{
	var layer = document.getElementById('layer' + id)
	var toggle = elem;
	if(layer.style.visibility == 'hidden') {
		toggle.classList.remove('expand');
		toggle.classList.remove('collapsed');
		toggle.classList.add('collapse');
		layer.style.display = 'block';
		layer.style.visibility = 'visible';
	} else {
		toggle.classList.remove('collapse');
		toggle.classList.add('expand');
		toggle.classList.add('collapsed');
		layer.style.display = 'none';
		layer.style.visibility = 'hidden';
	}
}


function reply()
{
	location.hash = "reply";
	quill.focus();//document.commentform.text.focus();
}


function selectAllMessages() {
	var messagesForm = document.inboxform;
	var selectCheckboxes = messagesForm.querySelectorAll('input[type="checkbox"]');
	for(i=0; i < selectCheckboxes.length; i++)
		selectCheckboxes[i].checked = !selectCheckboxes[i].checked;
}

/*function addsymbol(symbol)
{
	document.commentform.text.value = document.commentform.text.value + symbol;
	document.commentform.text.focus();
}*/


// Zeigt Unread Comments im Webpage Title an
// @author IneX
// @version 2.0
// @since 1.0 function added
// @since 2.0 `27.08.2019` `IneX` refactored function to work with AJAX updateUnreadComments()
function unreads_2_title(numUnreads)
{
	if (typeof origTitle !== 'undefined' && origTitle != null)
	{
		if (numUnreads != null && numUnreads > 0)
		{
			// add unreads count to page title
			var unreads_data = (numUnreads = 1 ? numUnreads + ' Comment' : numUnreads + ' Comments');
			document.title = origTitle + ' (' + unreads_data + ')';
		} else {
			// remove unreads count
			document.title = origTitle;
		}
	} else {
		return;
	}
}


function confirmPopup(question)
{
	var reply = confirm(question);
	return reply;
}


// Schwiizer Ziit vom Bsuecher usrechne
// @author IneX
// @version 1.5
// @since 1.0 `21.10.2013` `IneX` funktion hinzuegfüegt
// @since 1.5 `28.08.2019` `IneX` Mitternachts-Fix ('24' statt 'undefined') und Suffix für i dä Nacht ergänzt
function swisstimeJS()
{
	var jetzt = new Date();
	var monet = jetzt.getMonth();
	var tag = jetzt.getDate();
	var tag_i_dae_woche = jetzt.getDay();
	var johr = jetzt.getFullYear();
	var minute = Math.floor(jetzt.getMinutes());
	var stund = jetzt.getHours();
	if (minute >= 25) stund = jetzt.getHours()+1;
	var tagesziit = '';

	var minute_text = new Array("F&uuml;f ab", "Z&auml;h ab", "Viertel ab", "Zwanzg ab", "F&uuml;f vor halbi", "halbi", "F&uuml;f ab halbi", "Zwanzg vor", "Viertel vor", "Z&auml;h vor", "F&uuml;f vor", "");
	var stunde_text = new Array("Zw&ouml;lfi", "Eis", "Zwei", "Dr&uuml;&uuml;", "Vieri", "F&uuml;fi", "Sechsi", "Siebni", "Achti", "N&uuml;ni", "Zehni", "Elfi", "Zw&ouml;lfi", "Eis", "Zwei", "Dr&uuml;&uuml;", "Vieri", "F&uuml;fi", "Sechsi", "Siebni", "Achti", "N&uuml;ni", "Zehni", "Elfi", "Zw&ouml;lfi");
	var wochetag = new Array("Sunntig", "M&auml;ntig", "Ziistig", "Mittwoch", "Donschtig", "Friitig", "Samschtig");
	var moenet = new Array("Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "Auguscht", "September", "Oktober", "November", "Dezember");

	if (minute > 2 && minute <= 7) minute_ziit = 0;
	else if (minute > 7 && minute <= 12) minute_ziit = 1;
	else if (minute > 12 && minute <= 17) minute_ziit = 2;
	else if (minute > 17 && minute <= 22) minute_ziit = 3;
	else if (minute > 22 && minute <= 27) minute_ziit = 4;
	else if (minute > 27 && minute <= 32) minute_ziit = 5;
	else if (minute > 32 && minute <= 37) minute_ziit = 6;
	else if (minute > 37 && minute <= 42) minute_ziit = 7;
	else if (minute > 42 && minute <= 47) minute_ziit = 8;
	else if (minute > 47 && minute <= 52) minute_ziit = 9;
	else if (minute > 52 && minute <= 57) minute_ziit = 10;
	else if (minute > 57 || minute <= 2) minute_ziit = 11;

	if (stund >= 0 && stund < 6) tagesziit = "i d&auml; Nacht"
	else if (stund >= 6 && stund < 12) tagesziit = "am Morg&auml;"
	else if (stund >= 12 && stund < 17) tagesziit = "am Nomitag"
	else if (stund >= 17 && stund <= 22) tagesziit = "am Abig"
	else if (stund > 22 && stund >= 24) tagesziit = "i d&auml; Nacht"

	aktuelli_schwiizerziit = wochetag[tag_i_dae_woche] + " " + tag + ". " +  moenet[monet]  + ", " + minute_text[minute_ziit] + " " + stunde_text[stund] + " " + tagesziit;
	var htmltag = document.getElementById('swisstime');

	if (typeof htmltag !== 'undefined') {
		htmltag.innerHTML = aktuelli_schwiizerziit;
	} else {
		console.warn(' HTML Element ' + htmltag + ' not found.');
	}
}


// Bild als MyPic markieren
// @author IneX
// @date 21.10.2013
function markAsMypic()
{
	var conf = confirm(unescape("W%F6tsch du DICH SELBER w%FCrklich uf dem Bild markiere%3F"));
	if (conf) { return true; } else { return false; }
}


// Usernamen markierter Personen auf Bild zeichen
// @author IneX
// @date 21.10.2013
/*function drawUsernameOnPic(username, x, y)
{
	var canvas = document.getElementById("zpic");
	var con = canvas.getContext("2d");

	//clear background
	//con.fillStyle = "white";
	//con.fillRect(0,0, 200, 200);
	//con.globalAlpha = 0.5;
	// draw font in red
	con.fillStyle = "rgb(255,255,255)";
	con.font = "20pt sans-serif";
	con.fillText(username, x, y);
}*/


// Aktuelle Onlineuser dynamisch aktualisieren
// @author IneX
// @date 27.08.2019
function updateOnlineuser(elementId, displayFormat) {
	var domElement = document.getElementById(elementId);
	if (typeof domElement !== 'undefined' && domElement != null) {
		var oldOnlineUserHtml = domElement.innerHTML;
		var xhr = new XMLHttpRequest();
		xhr.open('GET', '/js/ajax/get-onlineuser.php?style='+displayFormat);
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.onload = function() {
			//console.info(xhr.responseText);
			if (xhr.status === 200 || xhr.status === 204) {
				// On Success
				if (xhr.responseText) {
					var jsonResponse = JSON.parse(xhr.responseText);

					// Check if there ist at least 1 online user
					if (jsonResponse.data.length > 0) {
						var links = [];

						// Clear the existing content of the domElement
						while (domElement.firstChild) {
							domElement.removeChild(domElement.firstChild);
						}

						// Iterate over the data array from the JSON response
						jsonResponse.data.forEach(function(user) {
							// Create an anchor element for the user profile link
							var link = document.createElement('a');
							link.href = '/profil.php?user_id=' + user.id;
							link.textContent = user.username;
							link.classList.add('blink');

							links.push(link.outerHTML);
						});

						// Create a comma-separated string of the links
						var linksString = links.join(', ');
					}
				} else {
					//
					var linksString = '&nbsp;';
				}

				// Set the innerHTML of domElement to the linksString
				domElement.innerHTML = linksString;

				// Remove the 'blink' class of users that are already online
				var userLinks = Array.from(domElement.querySelectorAll('a'));
				// Create a temporary DOM element for oldOnlineUserHtml
				var tempElement = document.createElement('div');
				tempElement.innerHTML = oldOnlineUserHtml;

				userLinks.forEach(function (userLink) {
					var linkUsername = userLink.textContent;

					// Check if the username exists in the oldOnlineUserHtml DOM element
					var usernameExists = Array.from(tempElement.querySelectorAll('a')).some(function (oldUserLink) {
						return oldUserLink.textContent === linkUsername;
					});

					if (usernameExists) {
						userLink.classList.remove('blink');
					}

					// Remove the temporary DOM element
					tempElement.remove();
				});
			} else {
				// On Error
				console.error(xhr.status + ' ' + xhr.responseText);
			}
		};
		xhr.send();
	}
	else {
		clearTimeout(werischonline);
		werischonline = null;
		console.info('Stopped checking werischonline');
		return;
	}
}


// Anzahl unread Comments dynamisch aktualisieren
// @author IneX
// @date 27.08.2019
function updateUnreadComments()
{
	let notificationsContainer = document.getElementById('notifications-list');
	let unreadsContainer = document.getElementById('unreads');
	if (typeof unreadsContainer !== 'undefined' && unreadsContainer != null)
	{
		let unreadsForUser = parseInt(unreadsContainer.dataset.userid);
		var xhr = new XMLHttpRequest();
		xhr.open('GET', '/js/ajax/get-unreadcomments.php?user='+unreadsForUser);
		xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		xhr.onload = function() {
			//console.info(xhr.responseText);
			if (xhr.status === 200)
			{
				// New unreads
				var xhrResponseText = xhr.responseText;
				var newUnreadCount = parseInt(xhrResponseText.match(/\d/g).join(''), 10);
				if (typeof unreadsContainer === 'undefined' || unreadsContainer == null)
				{
					// Unreads counter doesn't exist, add it
					let newUnreadsContainer = document.createElement('li');
					newUnreadsContainer.setAttribute('id', 'unreads');
					let newUnreadsLinkWrapper = newUnreadsContainer.createElement('a');
					newUnreadsLinkWrapper.setAttribute('href', '/actions/comment_gotolastunread.php');
					newUnreadsLinkWrapper.classList.add('blink');
					newUnreadsLinkWrapper.textContent = xhrResponseText;
					notificationsContainer.prepend(newUnreadsContainer);
				}
				else {
					// Changed num of unreads (+ or -)
					if (unreadsContainer.textContent !== '' && unreadsContainer.textContent != null)
					{
						var oldUnreadCount = parseInt(unreadsContainer.textContent.match(/\d/g).join(''), 10);
						// The existing unreadsContainer.textContent is not empty
						if (oldUnreadCount < newUnreadCount || oldUnreadCount > newUnreadCount)
						{
							unreadsContainer.children[0].classList.add('blink');
						}
						else {
							// Same num of unreads
							unreadsContainer.children[0].classList.remove('blink');
						}
					}
					else {
						let newUnreadsLinkWrapper = document.createElement('a');
						newUnreadsLinkWrapper.setAttribute('href', '/actions/comment_gotolastunread.php');
						unreadsContainer.prepend(newUnreadsLinkWrapper);
						newUnreadsLinkWrapper.classList.add('blink');
					}
					unreadsContainer.children[0].textContent = xhrResponseText;
				}
				unreads_2_title(newUnreadCount);
			}
			else if (xhr.status === 204) {
				// No unreads
				if (unreadsContainer.textContent !== '' && unreadsContainer.textContent != null)
				{
					// Unreads counter still visible
					unreadsContainer.children[0].textContent = '';
				}
				unreads_2_title(null);
			}
			else {
				// On Error
				console.error(xhr.status + ' ' + xhr.responseText);
			}
		};
		xhr.send();
	}
	else {
		clearTimeout(wahaniverpasst);
		wahaniverpasst = null;
		console.info('Stopped checking wahaniverpasst');
		return;
	}
}
let wahaniverpasst = setTimeout(function commentshole() {
	updateUnreadComments();
	if (wahaniverpasst) wahaniverpasst = setTimeout(commentshole, 5000);
}, 100);

// Update CSS grid-template-areas of body{} if Sidebar is in HTML DOM
// @version 4.0
// @since 1.0 function added
// @since 4.0 `09.09.2019` `IneX` updated init() triggers
function init()
{
	let werischonline = setTimeout(function nomelprobiere() {
		updateOnlineuser('onlineuser-list', 'list');
		if (werischonline) werischonline = setTimeout(nomelprobiere, 5000);
	}, 100);

	let ziitupdate = setTimeout(function aktuelliziit() {
		swisstimeJS();
		ziitupdate = setTimeout(aktuelliziit, 60000);
	}, 100);

	swisstimeJS();

	//drawUsernameOnPic('Username Test Drawing', 200, 300);

	// Allows removing gray Tap-Highlight on links with iOS Safari using -webkit-tap-highlight-color:
	document.addEventListener('touchstart', function(){}, true);
}

const origTitle = document.title;
