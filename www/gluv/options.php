<?php
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

/*********************
 *      Options      *
 *********************/

// Determines what is shown in the title bar of the browser.
$title="gLuV - gLu's Voyeur - A PHP, Web Based Q3 Server Monitor";

// Determines the width that gLuV uses on the pop-up.
$width=800;

// Determines the height that gLuV uses on the pop-up.
$height=600;

// Time in milliseconds to wait before auto-refreshing.
//    Use 0 to not refresh automatically.
$refresh=30000;

// CSS file to use
$css="glu.css";

// OSP Blinking Names: 1 = Show OSP blinking names (may make browsers sluggish)
//                     0 = Do not show OSP blinking names
$OSPBlink=0;

// Rules Table: -1 = Don't show on load, and don't let the user open.
//               0 = Don't show on first load (user can open them).
//               1 = Show on first load (user can close them).
$showRules=1;

// Cookie Expire Time: Set how may days browsers will remember gLuV settings
//                     (Whether the rules table is open, window locations, etc)
//                       A setting of "" expires at end of session.
$expire=1;

// Pop-ups: 1 = Clicking on a server will load it in a pop-up window
//          0 = Clicking on a server will load it in current window
$popup=0;

// Server Listing:  Include all servers that you want listed on your page.
//   Format:        "ip:port",  // comment
$servers=array(
"quake.zooomclan.org:27960"   // gLu2
);

?>
