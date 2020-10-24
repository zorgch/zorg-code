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
 *  fun name parsing  *
 **********************/

// Draw part of a name with a foreground and background color
function name($name, $background, $foreground, $blink)
{
  global $fastID, $slowID;
?><span style="position: relative"><span style="position: absolute; top: 1px; left: 2px;"><font color=<?php echo printf($background); ?><?php
if($blink==1)
  printf(" id=\"fadeFast".$fastID++."\"");
else if($blink==2)
  printf(" id=\"fadeSlow".$slowID++."\"");
?>><tt><?php echo printf($name); ?></tt></font></span><span style="position: relative;"><font color=<?php echo printf($foreground); ?><?php
if($blink==1)
  printf(" id=\"fadeFast".$fastID++."\"");
else if($blink==2)
  printf(" id=\"fadeSlow".$slowID++."\"");
?>><tt><?php echo printf($name); ?></tt></font></span></span><?php

/* I had to take out the whitespaces in the above code for it to look
     correct in browsers...here is what it would look like:

<span style="position: relative">
  <span style="position: absolute; top: 1px; left: 2px;">
    <font color=<?php echo printf($background); ?>>
      <tt><?php echo printf($name); ?></tt>
    </font>
  </span>
  <span style="position: relative;">
    <font color=<?php echo printf($foreground); ?>>
      <tt><?php echo printf($name); ?></tt>
    </font>
  </span>
</span>
*/

}

// Parse the funname and draw each part as we go
function funname($name, $osp)
{
  //printf($name);
  $blink=0;
  $foreground="#FFFFFF";
  $background="#000000";
  $fname="";                               // currently parsed section
  for($i=0;$i<strlen($name);$i++)
  {                                        // control character
    if($name[$i]=="^" && $name[$i+1]!="^")
    {
      if($fname!="")                       // print what we have so far
      {
			  $fname=htmlspecialchars($fname, ENT_QUOTES);
        if($fname[0]==" ")
			    $fname="&nbsp;".substr($fname, 1);
        name($fname, $background, $foreground, $blink);
        $fname="";
      }
      $i++;                                // skip the control character
      if(($name[$i]=="F" || $name[$i]=="f") && $osp)
        { continue; }                      // can't do this yet
      if($name[$i]=="b" && $osp)
      {
        $blink=1;
        continue;
      }
      if($name[$i]=="B" && $osp)
      {
        $blink=2;
        continue;
      }
      if(($name[$i]=="N" || $name[$i]=="n") && $osp)
      {
        if($blink)
          $blink=0;
        else
          $foreground=$background;
        continue;
      }
      if(($name[$i]=="X" || $name[$i]=="x") && $osp) // OSP colors
      {
        $background="#".(substr($name, $i+1, 6));
        $i+=6;
        continue;
      }
      if($osp!=1)
      {
        $name[$i]=ord($name[$i])%8;
      }
      else
      {
        if(ord($name[$i])==56)
          $name[$i]=8;
        else if(ord($name[$i])==57)
          $name[$i]=9;
        else if(ord($name[$i]) > 47 && ord($name[$i]) < 58)
          $name[$i]=ord($name[$i])%8;
        else
          $name[$i]=7;
      }
      switch($name[$i])                    // others interpreted as normal color
      {
        case 0:
          $foreground="#000000";
          break;
        case 1:
          $foreground="#FF0000";
          break;
        case 2:
          $foreground="#00FF00";
          break;
        case 3:
          $foreground="#FFFF00";
          break;
        case 4:
          $foreground="#0000FF";
          break;
        case 5:
          $foreground="#00FFFF";
          break;
        case 6:
          $foreground="#FF00FF";
          break;
        case 7:
          $foreground="#FFFFFF";
          break;
        case 8:
          $foreground="#FF8000";
          break;
        case 9:
          $foreground="#777777";
          break;
      }
    }
    else                                   // Was not a control character
		{
      $fname.=(substr($name, $i, 1));    // add the chr to the name
      if($name[$i]=="^" && $str[$i+1]=="^")
			  $i++;
		}
  }
  if($fname!="")                           // draw any leftover name
  {
    $fname=htmlspecialchars($fname, ENT_QUOTES);
    if($fname[0]==" ")
      $fname="&nbsp;".substr($fname, 1);
    name($fname, $background, $foreground, $blink);
  }
}

// Draw OSP colored pings
function funping($ping)
{
  $background="#000000";
  $fname="";
  if($ping<40)
    $foreground="#FFFFFF";
  else if($ping<70)
    $foreground="#00FF00";
  else if($ping<120)
    $foreground="#FFFF00";
  else if($ping<190)
    $foreground="#FF8000";
  else if($ping<400)
    $foreground="#FF00FF";
  else
    $foreground="#FF0000";
  name($ping, $background, $foreground, 0);
}

// Draw scores in white on black as in the Q3 scoreboard
function funscore($score)
{
  $background="#000000";
  $foreground="#FFFFFF";
  name($score, $background, $foreground, 0);
}

?>
