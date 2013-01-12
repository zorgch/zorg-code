<script language="JavaScript1.2">
/*
    This file is part of gLuV.

    gLuV is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    gLuV is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with gLuV; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

<?php
  require('options.php');
  if($refresh)
    printf("window.setInterval('location.reload(true)', $refresh);\n");
?>
var ie=document.all;
var ns6=document.getElementById && !document.all;

// globals for rules table closing and opening
var rules, cookie;
var showRules=<?= printf($showRules); ?>;
var expire=new Date();
expire.setTime(expire.getTime()+<?= printf($expire*86400000); ?>);

function openRules()
{
  var image, link;
  var players, parent, height;
  players=document.getElementById("players");
	parent=players.parentNode;
	height=players.offsetHeight;
	rules=parent.appendChild(rules);
	height=rules.offsetHeight-height
	if(height<0)
	  height=0;
	players.setAttribute("width","70%");
	cookie="1";
 	setCookie("showServerRules", cookie, expire);
	if(showRules!=-1)
	{
	  showRules=1;
	  image=document.getElementById("footer");
	  image.style.top=image.offsetTop+height;
	  image=document.getElementById("imgRules");
	  image.src="images/transparent.gif";
	  link=document.getElementById("linkRules");
	  link.href="javascript:void(0);";
	  link.onmouseover=null
	  link.onmouseout=null
	  nd();
	}
}

function closeRules()
{
  var image, link;
  var players, parent, height;
  players=document.getElementById("players");
  rules=document.getElementById("rules");
	height=rules.offsetHeight;
	parent=rules.parentNode;
	rules=parent.removeChild(rules);
	players.setAttribute("width","100%");
	height=height-players.offsetHeight;
	if(height<0)
	  height=0;
	cookie="0";
 	setCookie("showServerRules", cookie, expire);
	image=document.getElementById("footer");
	image.style.top=image.offsetTop-height;
	if(showRules!=-1)
	{
	  showRules=0;
	  image=document.getElementById("imgRules");
	  image.src="images/open.jpg";
	  link=document.getElementById("linkRules");
	  link.href="javascript:openRules();";
	  link.onmouseover=new Function("return overlib('Open the Rule List.', LEFT, CSSCLASS, FGCLASS, 'cellHeading', TEXTFONTCLASS, 'cell1', BORDER, 0);");
	  link.onmouseout=new Function("return nd();");
	  nd();
	}
}

function setCookie(name, value, expires, path, domain, secure)
{
  var curCookie = name + "=" + escape(value) +
	    ((expires) ? "; expires=" + expires.toGMTString() : "") +
	    ((path) ? "; path=" + path : "") +
	    ((domain) ? "; domain=" + domain : "") +
	    ((secure) ? "; secure" : "");
	document.cookie = curCookie;
}

function getCookie(name)
{
  var dc = document.cookie;
	var prefix = name + "=";
	var begin = dc.indexOf("; " + prefix);
	if (begin == -1)
	{
	  begin = dc.indexOf(prefix);
		if (begin != 0) return null;
	}
	else
    begin += 2;
  var end = document.cookie.indexOf(";", begin);
  if (end == -1)
  end = dc.length;
  return unescape(dc.substring(begin + prefix.length, end));
}

// stuff to do on load(load cookie values mostly)
function loadit()
{
	var cookieVal=getCookie("showServerRules");
 	if(!cookieVal)
  {
		if(showRules!=1)
		{
cookie="0";
		  closeRules();
		}
		else
cookie="1";
 	  setCookie("showServerRules", rules, expire);
 	}
  else
 	{
	  if(cookieVal=="1" && showRules!=-1)
		  showRules=1;
		else if(cookieVal=="0" && showRules!=-1)
		  showRules=0;
    if(showRules!=1)
closeRules();
    cookie=cookieVal;
 	}
<?php
  if($OSPBlink)
  {
    printf("fadeinit();");
  }
?>
}

<?php
if($OSPBlink)
{
?>
// begin text fading code
// globals for text fading
var colorslow, dir = -5;
var colorfast, count = 0;

function fadeinit()
{ 
  var fast = 0, slow = 0, text;
  colorslow = new Array();
  colorfast = new Array();
  text=document.getElementById("fadeFast"+fast);
  for( ; text; )
  { 
    colorfast.push(text.color);
    fast++;
    text=document.getElementById("fadeFast"+fast);
  }     
  text=document.getElementById("fadeSlow"+slow);
  for( ; text; )
  {   
    colorslow.push(text.color);
    slow++;
    text=document.getElementById("fadeSlow"+slow);
  } 
  setTimeout("fadefast()", 30);  // call every 30ms
  setTimeout("fadeslow()", 30);  // call every 30ms
}   
    
function fadefast()
{   
  var itr = 0, color, red, blue, green;
  var redmax, greenmax, bluemax;
  var redmin, greenmin, bluemin, bgcolor;
  var reddir, greendir, bluedir;
  var parentred, parentgreen, parentblue;
  var origred, origgreen, origblue;
  var text, parent, regex, parentcolor; 
  text=document.getElementById("fadeFast"+itr); 
  for( ; text; )
  { 
    reddir = 1, greendir = 1, bluedir = 1;
    if(ns6)
    { 
      parent=text.parentNode;
      while(parent.className!="row1" && parent.className!="row2" && parent.className!="rowHighlight")
        parent=parent.parentNode;
      bgcolor=document.defaultView.getComputedStyle(parent, '').getPropertyValue("background-color");
      regex = /^rgb\((\d+)\,[ ]?(\d+)\,[ ]?(\d+)\)$/;
      parentcolor=regex.exec(String(bgcolor));
      parentred = parentcolor[1];
      parentgreen = parentcolor[2];
      parentblue = parentcolor[3];
    } 
    else
    {
      parent=text.parentElement;
      while(parent.className!="row1" && parent.className!="row2" && parent.className!="rowHighlight")
        parent=parent.parentElement;
      bgcolor = parent.currentStyle.backgroundColor;
      parentred = parseInt(bgcolor.substr(1, 2), 16);
      parentgreen = parseInt(bgcolor.substr(3, 2), 16);
      parentblue = parseInt(bgcolor.substr(5, 2), 16);
    } 
    parentmax = parentred;
    if(parentgreen > parentmax)
      parentmax = parentgreen;
    if(parentblue > parentmax)
      parentmax = parentblue;
    color = text.color;
    red = parseInt(color.substr(1, 2), 16);
    green = parseInt(color.substr(3, 2), 16);
    blue = parseInt(color.substr(5, 2), 16);
    origred = parseInt(colorfast[itr].substr(1, 2), 16);
    redmax=origred;
    if(parentred > redmax)
    {
      redmin = redmax;
      redmax = parentred;
      reddir *= -1;
    }
    else
      redmin = parentred;
    origgreen = parseInt(colorfast[itr].substr(3, 2), 16);
    greenmax=origgreen;
    if(parentgreen > greenmax)
    {
      greenmin = greenmax;
      greenmax = parentgreen;
      greendir *= -1;
    }
    else
      greenmin = parentgreen;
    origblue = parseInt(colorfast[itr].substr(5, 2), 16);
    bluemax=origblue;
    if(parentblue > bluemax)
    {
      bluemin = bluemax;
      bluemax = parentblue;
      bluedir *= -1;
    }
    else
      bluemin = parentblue;
    reddir *= Math.abs(origred-parentred)/255;
    greendir *= Math.abs(origgreen-parentgreen)/255;
    bluedir *= Math.abs(origblue-parentblue)/255;
    red = red + Math.round(dir*2*reddir);       // 10*reddir
    green = green + Math.round(dir*2*greendir); // 10*greendir
    blue = blue + Math.round(dir*2*bluedir);    // 10*bluedir
    if(red < redmin)
      red = redmin;
    else if(red > redmax)
      red = redmax;
    if(green < greenmin)
      green = greenmin;
    else if(green > greenmax)
      green = greenmax;
    if(blue < bluemin)
      blue = bluemin;
    else if(blue > bluemax)
      blue = bluemax;
    if(red < 16)
      color = "#0" + red.toString(16);
    else
      color = "#" + red.toString(16);
    if(green < 16)
      color += "0" + green.toString(16);
    else
      color += green.toString(16);
    if(blue < 16)
      color += "0" + blue.toString(16);
    else
      color += blue.toString(16);

    text.color = color;
    itr++;
    text=document.getElementById("fadeFast"+itr);
  }
  count++;
  if(count == 25)
  {
    count = 0;
    dir = -dir;
  }
  setTimeout("fadefast()", 30);  // call every 30ms
}

function fadeslow()
{
  var itr = 0, color, red, blue, green;
  var redmax, greenmax, bluemax;
  var redmin, greenmin, bluemin, bgcolor;
  var reddir = 1, greendir = 1, bluedir = 1;
  var parentred, parentgreen, parentblue;
  var origred, origgreen, origblue;
  var text, parent, regex, parentcolor; 
  text=document.getElementById("fadeSlow"+itr);
  for( ; text; )
  {
    reddir = 1, greendir = 1, bluedir = 1;
    if(ns6)
    {
      parent=text.parentNode;
      while(parent.className!="row1" && parent.className!="row2" && parent.className!="rowHighlight")
        parent=parent.parentNode;
      bgcolor=document.defaultView.getComputedStyle(parent, '').getPropertyValue("background-color");
      regex = /^rgb\((\d+)\,[ ]?(\d+)\,[ ]?(\d+)\)$/;
      parentcolor=regex.exec(String(bgcolor));
      parentred = parentcolor[1];
      parentgreen = parentcolor[2];
      parentblue = parentcolor[3];
    }
    else
    {
      parent=text.parentElement;
      while(parent.className!="row1" && parent.className!="row2" && parent.className!="rowHighlight")
        parent=parent.parentElement;
      bgcolor = parent.currentStyle.backgroundColor;
      parentred = parseInt(bgcolor.substr(1, 2), 16);
      parentgreen = parseInt(bgcolor.substr(3, 2), 16);
      parentblue = parseInt(bgcolor.substr(5, 2), 16);
    }
    parentmax = parentred;
    if(parentgreen > parentmax)
      parentmax = parentgreen;
    if(parentblue > parentmax)
      parentmax = parentblue;
    color = text.color;
    red = parseInt(color.substr(1, 2), 16);
    green = parseInt(color.substr(3, 2), 16);
    blue = parseInt(color.substr(5, 2), 16);
    origred = parseInt(colorslow[itr].substr(1, 2), 16);
    redmax=origred;
    if(parentred > redmax)
    {
      redmin = redmax;
      redmax = parentred;
      reddir = -1;
    }
    else
      redmin = parentred;
    origgreen = parseInt(colorslow[itr].substr(3, 2), 16);
    greenmax=origgreen;
    if(parentgreen > greenmax)
    {
      greenmin = greenmax;
      greenmax = parentgreen;
      greendir = -1;
    }
    else
      greenmin = parentgreen;
    origblue = parseInt(colorslow[itr].substr(5, 2), 16);
    bluemax=origblue;
    if(parentblue > bluemax)
    {
      bluemin = bluemax;
      bluemax = parentblue;
      bluedir = -1;
    }
    else
      bluemin = parentblue;
    reddir *= Math.abs(origred-parentred)/255;
    greendir *= Math.abs(origgreen-parentgreen)/255;
    bluedir *= Math.abs(origblue-parentblue)/255;
    red = red + Math.round(dir*reddir);
    green = green + Math.round(dir*greendir);
    blue = blue + Math.round(dir*bluedir);
    if(red < redmin)
      red = redmin;
    else if(red > redmax)
      red = redmax;
    if(green < greenmin)
      green = greenmin;
    else if(green > greenmax)
      green = greenmax;
    if(blue < bluemin)
      blue = bluemin;
    else if(blue > bluemax)
      blue = bluemax;
    if(red < 16)
      color = "#0" + red.toString(16);
    else
      color = "#" + red.toString(16);
    if(green < 16)
      color += "0" + green.toString(16);
    else
      color += green.toString(16);
    if(blue < 16)
      color += "0" + blue.toString(16);
    else
      color += blue.toString(16);

    text.color = color;
    itr++;
    text=document.getElementById("fadeSlow"+itr);
  }
  setTimeout("fadeslow()", 30);  // call every 30ms
}
<?php
}
?>

window.onload=loadit;
</script>
