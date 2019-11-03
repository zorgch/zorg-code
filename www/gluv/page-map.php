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
 *    Map Section    *
 *********************/
function printMap($rules, $numRule)
{
  global $numRed, $numBlue, $numSpec, $ip, $port, $osp;
  global $game;                                // vars from options.php
  $numPlayers=$numRed+$numBlue+$numSpec;
  for($i=0;$i<$numRule;$i++)                   // get needed rules
  {
     if($rules[$i]->rule=="capturelimit") {$caplimit=$rules[$i]->value;}
     else if($rules[$i]->rule=="g_gametype") {$gametype=$rules[$i]->value;}
     else if($rules[$i]->rule=="timelimit") {$timelimit=$rules[$i]->value;}
     else if($rules[$i]->rule=="fraglimit") {$fraglimit=$rules[$i]->value;}
     else if($rules[$i]->rule=="dmflags") {$dmflags=$rules[$i]->value;}
     else if($rules[$i]->rule=="sv_hostname") {$servername=$rules[$i]->value;}
     else if($rules[$i]->rule=="sv_maxclients") {$maxclients=$rules[$i]->value;}
     else if($rules[$i]->rule=="version") {$version=$rules[$i]->value;}
     else if($rules[$i]->rule=="mapname") {$mapname=$rules[$i]->value;}
     else if($rules[$i]->rule=="gameversion") {$gameversion=$rules[$i]->value;}
     else if($rules[$i]->rule=="gamename") {$gamename=$rules[$i]->value;}
     else if($rules[$i]->rule=="Score_Red") {$redscore=$rules[$i]->value;}
     else if($rules[$i]->rule=="Score_Blue") {$bluescore=$rules[$i]->value;}
     else if($rules[$i]->rule=="Score_Time") {$time=$rules[$i]->value;}
     else if($rules[$i]->rule=="sv_maxRate") {$rate=$rules[$i]->value;}
     else if($rules[$i]->rule=="Players_Red") {$Red=$rules[$i]->value;}
     else if($rules[$i]->rule=="Players_Blue") {$Blue=$token;}
  }
  if(stristr($gameversion, "osp"))
    $osp=1;
  ?>
  <table BORDER=1 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <tr>
      <td COLSPAN=3 CLASS="row1" style="text-align: center"><?php echo $version ?>
      </td>
    </tr>
    <tr>
      <td WIDTH=40%%>
        <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%%>
          <tr>
            <td CLASS="row2" style="text-align: center">Players: <?php echo $numPlayers ?>/<?php echo $maxclients ?>
	          </td>
          </tr>
	        <tr>
            <td CLASS="row1" style="text-align: center">Cap Limit: <?php echo $caplimit ?>
	          </td>
          </tr>
          <tr>
            <td CLASS="row2" style="text-align: center">
	            <a href="javascript:void(0);" onmouseover="return overlib('<?php
                if($dmflags & 8) { printf("No Falling Damage<br>"); }
                if($dmflags & 16) { printf("Fixed FOV<br>"); }
                if($dmflags & 32) { printf("No Footsteps"); }
		          ?>', CSSCLASS, CAPTION, 'DM Flags:<br>', FGCLASS, 'cellHeading', BGCLASS, 'row2', TEXTFONTCLASS, 'cell1', CAPTIONFONTCLASS, 'caption',  BORDER, 0, TEXTSIZE, 1);" onmouseout="return nd();">
	              DM Flags: <?php echo $dmflags ?>
	            </a>
	          </td>
	        </tr>
	        <tr>
	          <td CLASS="row1" style="text-align: center">Max Rate: <?php echo $rate ?>
	          </td>
          </tr>
	        <?php
            if($osp==1)
            {
              printf("<tr>\n");
              printf("<td CLASS=\"red\" style=\"text-align: center\"><b>Red Score: $redscore</b><br>Players: $numRed</td>\n");
              printf("</tr>\n");
            }
          ?>
          <tr>
            <td CLASS="neutral" style="text-align: center">
	            <?php
                if($osp!=1)
                {
                  printf("<br>\n");
                  printf("<br>\n");
                }
              ?>
              <br>
              <br>
              <br>
              <br>
              <br>
              <br>
              <br>
            </td>
          </tr>
        </table>
			</td>
      <td CLASS="cellHeading" WIDTH=20%%>
        <?php echo $mapname ?>
	      <br>
        <img TITLE="<?php echo strtolower($mapname); ?>" WIDTH=128 HEIGHT=128 SRC="images/maps/<?php echo strtolower($mapname); ?>.jpg">
      </td>
      <td WIDTH=40%%>
        <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%%>
          <tr>
	          <?php
              if(!empty($gameversion))
                printf("<td CLASS=\"row2\" style=\"text-align: center\">$gameversion</td>\n");
              else if(!empty($gamename))
                printf("<td CLASS=\"row2\" style=\"text-align: center\">$gamename</td>\n");
              else
                printf("<td CLASS=\"row2\" style=\"text-align: center\">&nbsp;</td>\n");
            ?>
          </tr>
          <tr>
            <td CLASS="row1" style="text-align: center">Frag Limit: <?php echo $fraglimit ?>
	          </td>
          </tr>
          <tr>
            <td CLASS="row2" style="text-align: center">Time Limit: <?php echo $timelimit ?>
	          </td>
          </tr>
          <tr>
            <td CLASS="row1" style="text-align: center">Time Left: <?php echo $time ?>
	          </td>
          </tr>
	        <?php
            if($osp==1)
            {
              printf("<tr>\n");
              printf("<td CLASS=\"blue\" style=\"text-align: center\"><b>Blue Score: $bluescore</b><br>Players: $numBlue</td>\n");
              printf("</tr>\n");
            }
	        ?>
          <tr>
            <td CLASS="neutral" style="text-align: center">
	            <?php
                if($osp!=1)
                {
                  printf("<br>\n");
                  printf("<br>\n");
                }
		          ?>
              <br>
              <br>
              <br>
              <br>
              <br>
              <br>
              <br>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
<?php
}
?>
