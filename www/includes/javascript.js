function onoff(id)
{
	layer = document.getElementById("layer" + id)
	image = "img" + id;
	if(layer.style.visibility == "hidden") {
		document.images[image].src = "/images/forum/'.$layouttype.'/minus.gif";
		layer.style.display = "block";
		layer.style.visibility = "visible";
	} else {
		document.images[image].src = "/images/forum/'.$layouttype.'/plus.gif";
		layer.style.display = "none";
		layer.style.visibility = "hidden";
	}
}


function reply()
{
	location.hash = "reply";
	document.commentform.text.focus();
}


function addsymbol(symbol)
{
	document.commentform.text.value = document.commentform.text.value + symbol;
	document.commentform.text.focus();
}
	

function unreads_2_title(unreads_indicator)
{
	if (unreads_indicator != null) var unreads_data = document.unreads_indicator.firstChild.data;
	else return
	
	if (unreads_data != null) {
		document.title = document.title + ' (' + unreads_data + ')';
	}
}


function confirmPopup(question)
{
	var reply = confirm(question);
	return reply;
}


function swisstimeJS()
{
	var jetzt = new Date();
	var monet = jetzt.getMonth();
	var tag = jetzt.getDate();
	var tag_i_dae_woche = jetzt.getDay();
	var johr = jetzt.getFullYear();
	var minute = Math.floor(jetzt.getMinutes());
	 var stund = "";
	if (minute <= 25) stund = jetzt.getHours();
	else  stund = jetzt.getHours()+1;
	var tagesziit = "";
	
	var minute_text = new Array("F&uuml;f ab", "Z&auml;h ab", "Viertel ab", "Zwanzg ab", "F&uuml;f vor halbi", "halbi", "F&uuml;f ab halbi", "Zwanzg vor", "Viertel vor", "Z&auml;h vor", "F&uuml;f vor", "");
	var stunde_text = new Array("Zw&ouml;lfi", "Eis", "Zwei", "Dr&uuml;&uuml;", "Vieri", "F&uuml;fi", "Sechsi", "Siebni", "Achti", "N&uuml;ni", "Zehni", "Elfi", "Zw&ouml;lfi", "Eis", "Zwei", "Dr&uuml;&uuml;", "Vieri", "F&uuml;fi", "Sechsi", "Siebni", "Achti", "N&uuml;ni", "Zehni", "Elfi");
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
	
	if (stund >= 5 && stund < 12) tagesziit = "am Morg&auml;"
	else if (stund >= 12 && stund < 18) tagesziit = "am Nomitag"
	else if (stund >= 18 && stund <= 23) tagesziit = "am Abig"
	else if (stund >= 0 && stund < 5) tagesziit = "i d&auml; Nacht"
	
	
	document.write(wochetag[tag_i_dae_woche] + " " + tag + ". " +  moenet[monet]  + " " + minute_text[minute_ziit] + " " + stunde_text[stund] + " " + tagesziit);
}

// Bild als MyPic markieren
// @author IneX
// @date 21.10.2013
function markAsMypic()
{
  var conf = confirm(unescape("W%F6tsch du DICH SELBER w%FCrklich uf dem Bild markiere%3F"));
  if (conf) { return true; } else { return false; }
}