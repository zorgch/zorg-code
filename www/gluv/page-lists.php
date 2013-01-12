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
						
/*************************
 * Player and Rule Lists *
 *************************/
function printLists($reds, $blues, $specs, $rules)
{
  global $numRed, $numBlue, $numSpec, $osp;
  global $game;                            // vars from options.php
  global $fastID, $slowID;
  $fastID=0;
  $slowID=0;
  if(!empty($rules[0]->rule))
    $numRule=count($rules);
  else
    $numRule=0;
  $numPlayers=$numRed+$numBlue+$numSpec;
  $widthname=64;                           // width percentage values
  $widthscore=18;
  $widthping=18;
  $widthrule=40;
  $widthvalue=60;
?>
  <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <tr>
      <td id="players" VALIGN=top WIDTH=70%>
<?php
                                           // Red Team
  if($numRed>0)
  {
?>
        <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
          <tr>
            <td style="border-width: 0" VALIGN=top>
              <table BORDER=0 CELLSPACING=0 CELLPADDING=2 WIDTH=100%>
                <tr>
                  <td CLASS="redHeading" WIDTH=<?= $widthname ?>%>
                    Player Name
                  </td>
                  <td CLASS="redHeading" WIDTH=<?= $widthscore ?>%>
                    Score
                  </td>
                  <td CLASS="redHeading" WIDTH=<?= $widthping ?>%>
                    Ping
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
<?php
  }
  for($i=0;$i<$numRed;$i++)
  {
    if ($i%2 == 0)
    {
      $cell="cell1";
      $row="row1";
      $name="name1";
    }
    else
    {
      $cell="cell2";
      $name="name2";
      $row="row2";
    }
?>
                <tr CLASS="<?= $row ?>" onMouseOver="this.className='rowHighlight';" onMouseOut="this.className='<?= $row ?>';">
                  <td CLASS="<?= $name ?>" style="text-align: left" WIDTH=<?= $widthname ?>% VALIGN=bottom>
                    <?= funname($reds[$i]->name, $osp); ?>
                  </td>
                  <td CLASS="<?= $name ?>" style="text-align: center" WIDTH=<?= $widthscore ?>% VALIGN=bottom>
                    <?= funscore($reds[$i]->score); ?>
                  </td>
                  <td CLASS="<?= $name ?>" style="text-align: center" WIDTH=<?= $widthping ?>% VALIGN=bottom>
                    <?= funping($reds[$i]->ping); ?>
                  </td>
                </tr>
<?php
  }
  if($numRed>0)
  {
?>
              </table>
            </td>
          </tr>
        </table>
<?php
  }
  if($numBlue>0)                           // Blue Team
  {
?>
        <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
          <tr>
            <td style="border-width: 0" VALIGN=top>
              <table BORDER=0 CELLSPACING=0 CELLPADDING=2 WIDTH=100%>
                <tr>
                  <td CLASS="blueHeading" WIDTH=<?= $widthname ?>%>
                    Player Name
                  </td>
                  <td CLASS="blueHeading" WIDTH=<?= $widthscore ?>%>
                    Score
                  </td>
                  <td CLASS="blueHeading" WIDTH=<?= $widthping ?>%>
                    Ping
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="border-width: 0" VALIGN=top>
              <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
<?php
  }
  for($i=0;$i<$numBlue;$i++)
  {
    if ($i%2 == 0)
    {
      $cell="cell1";
      $row="row1";
      $name="name1";
    }
    else
    {
      $cell="cell2";
      $name="name2";
      $row="row2";
    }
?>
                <tr CLASS="<?= $row ?>" onMouseOver="this.className='rowHighlight';" onMouseOut="this.className='<?= $row ?>';" >
                  <td CLASS="<?= $name ?>" style="text-align: left" WIDTH=<?= $widthname ?>% VALIGN=bottom>
                    <?= funname($blues[$i]->name, $osp); ?>
                  </td>
                  <td CLASS="<?= $name ?>" style="text-align: center" WIDTH=<?= $widthscore ?>% VALIGN=bottom>
                    <?= funscore($blues[$i]->score); ?>
                  </td>
                  <td CLASS="<?= $name ?>" style="text-align: center" WIDTH=<?= $widthping ?>% VALIGN=bottom>
                    <?= funping($blues[$i]->ping); ?>
                  </td>
                </tr>
<?php
}
  if($numBlue>0)
  {
?>
              </table>
            </td>
          </tr>
        </table>
<?php
  }                                        // Spectators
?>
        <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
          <tr>
            <td style="border-width: 0" VALIGN=top>
              <table BORDER=0 CELLSPACING=0 CELLPADDING=2 WIDTH=100%>
                <tr>
                  <td CLASS="cellHeading" WIDTH=<?= $widthname ?>%>
                    Player Name
                  </td>
                  <td CLASS="cellHeading" WIDTH=<?= $widthscore ?>%>
                    Score
                  </td>
                  <td CLASS="cellHeading" WIDTH=<?= $widthping ?>%>
                    Ping
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="border-width: 0" VALIGN=top>
              <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
<?php
  for($i=0;$i<$numSpec;$i++)
  {
    if ($i%2 == 0)
    {
      $cell="cell1";
      $row="row1";
      $name="name1";
    }
    else
    {
      $cell="cell2";
      $name="name2";
      $row="row2";
    }
?>
                <tr CLASS="<?= $row ?>" onMouseOver="this.className='rowHighlight';" onMouseOut="this.className='<?= $row ?>';">
                  <td CLASS="<?= $name ?>" style="text-align: left" WIDTH=<?= $widthname ?>% VALIGN=bottom>
                    <?= funname($specs[$i]->name, $osp); ?>
                  </td>
                  <td CLASS="<?= $name ?>" style="text-align: center" WIDTH=<?= $widthscore ?>% VALIGN=bottom>
                    <?= funscore($specs[$i]->score); ?>
                  </td>
                  <td CLASS="<?= $name ?>" style="text-align: center" WIDTH=<?= $widthping ?>% VALIGN=bottom>
                    <?= funping($specs[$i]->ping); ?>
                  </td>
                </tr>
<?php
  }
?>
              </table>
            </td>
          </tr>
        </table>
      </td>
      <td id="rules" VALIGN=top WIDTH=30%>
        <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
          <tr>
            <td style="border-width: 0" VALIGN=top>
              <table BORDER=0 CELLSPACING=0 CELLPADDING=2 WIDTH=100%>
                <tr>
                  <td CLASS="cellHeading" WIDTH=<?= $widthrule ?>%>
                    Rule
                  </td>
                  <td CLASS="cellHeading" WIDTH=<?= $widthvalue ?>%>
                    <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
                      <tr>
                        <td style="text-align: center" class="transparent">
                          <font class="headingText">
                            Value
                          </font>
                        </td>
                        <td align=right valign=top width=10 class="transparent">
                          <a href="javascript:closeRules();" onmouseover="return overlib('Close the Rule List.', LEFT, CSSCLASS, FGCLASS, 'cellHeading', TEXTFONTCLASS, 'cell1', BORDER, 0)" onmouseout="return nd();"><img src="images/close.jpg" class="close" border=0>
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="border-width: 0" VALIGN=top>
              <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
<?php
  $cell="cell2";
  for($i=0;$i<$numRule;$i++)               // ignore these rules
  {
    if($rules[$i]->rule=="capturelimit") {continue;}
    else if($rules[$i]->rule=="g_gametype") {continue;}
    else if($rules[$i]->rule=="timelimit") {continue;}
    else if($rules[$i]->rule=="fraglimit") {continue;}
    else if($rules[$i]->rule=="dmflags") {continue;}
    else if($rules[$i]->rule=="sv_hostname") {continue;}
    else if($rules[$i]->rule=="sv_maxclients") {continue;}
    else if($rules[$i]->rule=="version") {continue;}
    else if($rules[$i]->rule=="mapname") {continue;}
    else if($rules[$i]->rule=="gameversion") {continue;}
    else if($rules[$i]->rule=="Score_Red") {continue;}
    else if($rules[$i]->rule=="Score_Blue") {continue;}
    else if($rules[$i]->rule=="Score_Time") {continue;}
    else if($rules[$i]->rule=="sv_maxRate") {continue;}
    else if($rules[$i]->rule=="sv_floodProtect") {continue;}
    else if($rules[$i]->rule=="username") {continue;}
    else if($rules[$i]->rule=="sv_allowAnonymous") {continue;}
    else if($rules[$i]->rule=="server_ospauth") {continue;}
    else if($rules[$i]->rule=="server_promode") {continue;}
    else if($rules[$i]->rule=="server_cq3") {continue;}
    else if($rules[$i]->rule=="Players_Red") {continue;}
    else if($rules[$i]->rule=="Players_Blue") {continue;}
    $urlCheck=stristr($rules[$i]->value, "http://");
    if($urlCheck) {$urlCheck=substr($urlCheck, " \n\t\x0a");}
    if ($row=="row2")
    {
      $cell="cell1";
      $row="row1";
    }
    else
    {
      $cell="cell2";
      $row="row2";
    }
?>
                <tr CLASS="<?= $row ?>" onMouseOver="this.className='rowHighlight';" onMouseOut="this.className='<?= $row ?>';">
                  <td CLASS="<?= $cell ?>" style="text-align: left" WIDTH=$<?= widthrule ?>%>
                    <?= $rules[$i]->rule ?>
                  </td>
                  <td CLASS="<?= $cell ?>" style="text-align: left" WIDTH=<?= $widthvalue ?>%>
<?php
    if($urlCheck) {printf("<a href=\"$urlCheck\">");}
    printf($rules[$i]->value);
    if($urlCheck) {printf("</a>");}
?>
                  </td>
                </tr>
<?php
  }
?>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
<?php
}

?>
