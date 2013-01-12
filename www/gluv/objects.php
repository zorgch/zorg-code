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
 *      Objects      *
 *********************/
class InfoRes                           // Result Set(for server listing)
{
  var $index;
	var $name;
	var $ip;
	var $map;
	var $maxPlayers;
	var $players;
	var $game;
	var $gametype;
}

class Player                            // Players
{
  var $name;
  var $ping;
  var $score;
}

class Rule                              // Rules
{
  var $rule;
  var $value;
}

/*********************
 *    Comparisons    *
 *********************/
function cmp_player($a, $b)             // These are for sorting
{
  if($a->score==$b->score) return 0;
  return ($a->score > $b->score) ? -1 : 1;
}

function cmp_rule($a, $b)
{
  if($a->rule==$b->rule) return 0;
  return (strcmp($a->rule,$b->rule));
}

function cmp_res($a, $b)
{
  if($a->players==$b->players) return 0;
  return ($a->players > $b->players) ? -1 : 1;
}

?>
