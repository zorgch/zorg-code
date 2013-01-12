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
						
require('page-map.php');
require('page-lists.php');
/********************
 *  Main Page Code  *
 ********************/
function printPage($reds, $blues, $specs, $rules)
{
  global $numRed, $numBlue, $numSpec, $ip, $port, $gluVer;

                /* vars from options.php */
  global $game, $refresh, $title, $css, $allowDragging, $showRules;
  $osp=0;

  if(!empty($rules[0]->rule))
    $numRule=count($rules);
  else
    $numRule=0;
  for($i=0;$i<$numRule;$i++)               // find the hostname
    if($rules[$i]->rule=="sv_hostname") {$servername=$rules[$i]->value; break;}
?>
  <html>
  <HEAD>
  <TITLE><?= $title ?></TITLE>
  <link rel="stylesheet" type="text/css" href="<?= $css ?>">
  <?php require('js.php'); ?>
  </HEAD>
  <body>
  <center>
  <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;">
  </div>
  <script language="JavaScript" src="overlib.js">
    <!-- overLIB (c) Erik Bosrup -->
  </script>
  <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
    <tr>
      <td style="border-width: 0" VALIGN=top>
        <table BORDER=1 CELLSPACING=0 CELLPADDING=2 WIDTH=100%>
          <tr>
            <td COLSPAN=3 CLASS="cellHeading">
              <table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
                <tr>
                  <td style="text-align: center" class="transparent">
                    <font class="headingText">
                      <?= $servername."<br>".$ip.":".$port ?>
                    </font>
                  </td>
                  <td align=right valign=top width=10 class="transparent">
                    <?php
                      if($showRules!=-1)
                      {
                    ?>
                        <a id="linkRules" href="javascript:if(!showRules){openRules();}">
                          <img id="imgRules" src="images/transparent.gif" class="close" border=0>
                        </a>
                    <?php
                      }
                    ?>
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
        <?php
                     /* Map Section */
          printMap($rules, $numRule);
        ?>
      </td>
    </tr>
    <tr>
      <td style="border-width: 0" VALIGN=top>
				<?php
                /* Player/Rule List Section */
          printLists($reds, $blues, $specs, $rules);
        ?>	
      </td>
    </tr>
    <table id="footer" BORDER=1 CELLSPACING=0 CELLPADDING=2 WIDTH=100%> 
      <tr>
        <td style="text-align:left;" class="transparent">
          <a href="index.php">
            Back to Server Listing
          </a>
        </td>
        <td style="text-align:right;" class="transparent">
          <a href="http://www.digitaltorque.com/gluv/">
            gLuV
          </a>
           version <?= $gluVer ?> - Copyright 2002 by 
          <a href="mailto:SegFault@sc.rr.com">
            John Wu
          </a>
        </td>
      </tr>
    </table>
  </table>
</center>
</body>
</html>
<?php
}
?>
