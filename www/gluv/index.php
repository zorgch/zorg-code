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
																								
require('options.php');
require('objects.php');
require('stat.php');

$widthname=40;                          // width percentages for page
$widthip=20;
$widthmap=10;
$widthplayers=10;
$widthgame=10;
$widthgametype=10;

$query = "\xff\xff\xff\xffgetinfo";     // query string for Q3 servers
$resultSet[count($servers)-1] = new InfoRes;
for($i=0;$i<count($servers);$i++)       // query all servers
{
  $resultSet[$i]->index=$i;
  $resultSet[$i]->ip=$servers[$i];
  $in=getServerStatus(strtok($servers[$i], ":"), strtok(":"), $query, 1);
  if(!$in)
  {
    $in="Server Timed Out";
    $resultSet[$i]->name=$in;
    $resultSet[$i]->map="-";
    $resultSet[$i]->maxPlayers=" ";
    $resultSet[$i]->players=" ";
    $resultSet[$i]->game="-";
    $resultSet[$i]->gametype="-";
  }
  else
  {
    /**********************
     *    parsing code    *
     **********************/
    $token=strtok($in, "\\");
    while($token)
    {
      $token=strtok("\\");
      if($token)
      {
        if($token=="game")
          $resultSet[$i]->game=strtok("\\");
        else if($token=="gametype")
          $resultSet[$i]->gametype=strtok("\\");
        else if($token=="sv_maxclients")
          $resultSet[$i]->maxPlayers=strtok("\\");
        else if($token=="clients")
          $resultSet[$i]->players=strtok("\\");
        else if($token=="mapname")
          $resultSet[$i]->map=strtok("\\");
        else if($token=="hostname")
          $resultSet[$i]->name=strtok("\\");
        else
        {
          $token=strtok("\\");
          $token=1;
        }
      }
    }
  }
  switch($resultSet[$i]->game)
  {
    case "q3ut2":
      switch($resultSet[$i]->gametype)
      {
        case 0:
        case 1:
        case 2:
          $resultSet[$i]->gametype="FFA";
          break;
        case 3:
          $resultSet[$i]->gametype="TDM";
          break;
        case 4:
          $resultSet[$i]->gametype="TS";
          break;
        case 5:
          $resultSet[$i]->gametype="FTL";
          break;
        case 6:
          $resultSet[$i]->gametype="C&amp;H";
          break;
        case 7:
          $resultSet[$i]->gametype="CTF";
          break;
      }
      break;
    default:
      switch($resultSet[$i]->gametype)
      {
        case 0:
          $resultSet[$i]->gametype="FFA";
          break;
        case 1:
          $resultSet[$i]->gametype="1v1";
          break;
        case 3:
          $resultSet[$i]->gametype="TDM";
          break;
        case 4:
          $resultSet[$i]->gametype="CTF";
          break;
      }
      break;
  }
} // for
usort($resultSet, "cmp_res");

/*********************
 *     html code     *
 *********************/
?>

<html>
<HEAD>
<script language="JavaScript">
<?php
  if($refresh)
    printf("window.setInterval('location.reload(true)', $refresh);\n");
  if($popup)
  {
?>
    //  On click open gLuV in a pop-up window
    function gluv(index)
    {
      settings='top=0,left=0,width=<?= printf($width); ?>,height=<?= printf($height); ?>,toolbar=no,scrollbars=no,menubar=no,directories=no,location=no,status=no,resizable=yes';
      window.open("gluv.php?loc="+index,"blah",settings);
      return false;
    }
<?php
  }
 else
 {
?>
   // On click load gLuV in current window
   function gluv(index)
   {
     location.href=("gluv.php?loc="+index);
     return false;
   }
<?php
  }
?>
</script>
<TITLE><?= $title ?></TITLE>
<LINK REL=stylesheet HREF="<?= $css ?>" TYPE="text/css">
</HEAD>
<body>

<?php 
// title table
?>

<table BORDER=0 CELLSPACING=0 CELLPADDING=2 WIDTH=100%%>
  <tr>
    <td CLASS="cellHeading" WIDTH=$100%>
      gLuV - Server Listing - Click a server for full stats
      <br>
      <br>
    </td>
  </tr>
</table>

<?php 
// Server Listing Headers
?>

<table BORDER=0 CELLSPACING=0 CELLPADDING=2 WIDTH=100%>
  <tr>
    <td CLASS="cellHeading" WIDTH=<?= $widthname ?>%>Server Name</td>
    <td CLASS="cellHeading" WIDTH=<?= $widthip ?>%>IP</td>
    <td CLASS="cellHeading" WIDTH=<?= $widthmap ?>%>Map</td>
    <td CLASS="cellHeading" WIDTH=<?= $widthplayers ?>%>Players</td>
    <td CLASS="cellHeading" WIDTH=<?= $widthgame ?>%>Game Name</td>
    <td CLASS="cellHeading" WIDTH=<?= $widthgametype ?>%>Game Type</td>
  </tr>
</table>

<?php 
// Server Listing
?>

<table BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>
<?php
  for($i=0;$i<count($resultSet);$i++)   // alternate row class
  {
    if ($i%2 == 0)
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

<?php 
// Output servers

  if($resultSet[$i]->name=="Server Timed Out") {
?>
    <tr CLASS="<?= $row ?>" onMouseOver="this.className='rowHighlight';" onMouseOut="this.className='<?= $row ?>';" onfocus="this.blur();">
<?php
  } else {
?>
    <tr CLASS="<?= $row ?>" style="cursor: pointer;" onMouseOver="this.className='rowHighlight';" onMouseOut="this.className='<?= $row ?>';" onfocus="this.blur();" onclick="gluv(<?= $resultSet[$i]->index ?>);">
<?php
  }
?>
      <td CLASS="<?= $cell ?>" style="text-align: left" WIDTH=<?= $widthname ?>%>
        <?= $resultSet[$i]->name ?>
      </td>
      <td CLASS="<?= $cell ?>" style="text-align: center" WIDTH=<?= $widthip ?>%>
        <?= $resultSet[$i]->ip ?>
      </td>
      <td CLASS="<?= $cell ?>" style="text-align: center" WIDTH=<?= $widthmap ?>%>
        <?= $resultSet[$i]->map ?>
      </td>
      <td CLASS="<?= $cell ?>" style="text-align: center" WIDTH=<?= $widthplayers ?>%>
        <?= $resultSet[$i]->players."/".$resultSet[$i]->maxPlayers ?>
      </td>
      <td CLASS="<?= $cell ?>" style="text-align: center" WIDTH=<?= $widthgame ?>%>
        <?= $resultSet[$i]->game ?>
      </td>
      <td CLASS="<?= $cell ?>" style="text-align: center" WIDTH=<?= $widthgametype ?>%>
        <?= $resultSet[$i]->gametype ?>
      </td>
    </tr>
<?php
  }
?>

<?php 
// Footer (credits...if you modify gLuV add your name on next row =))
?>

</table>
<table id="footer" BORDER=1 CELLSPACING=0 CELLPADDING=2 WIDTH=100%>
  <tr>
    <td style="text-align:right;" class="transparent">
      <a href="http://www.digitaltorque.com/gluv/">
        gLuV
      </a>
       - Copyright 2002 by 
      <a href="mailto:SegFault@sc.rr.com">
        John Wu
      </a>
    </td>
  </tr>
</table>
</body>
</html>
