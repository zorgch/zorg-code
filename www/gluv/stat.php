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
						
/**********************
 * server status code *
 **********************/
function getServerStatus($host, $port, $query, $noErr)
{
  if(!($ds = fsockopen("udp://$host", $port, $errno, $errstr, 1)))
  {
    echo "$errstr ($errno)<br>\n";
    exit;
  }
  fwrite($ds, $query);
  socket_set_timeout($ds, 1);
  fread($ds, 4);
  $stream_info=socket_get_status($ds);
  if($stream_info > 0)
    $in=fread($ds, $stream_info["unread_bytes"]);
  fclose($ds);
  return $in;
}

?>
