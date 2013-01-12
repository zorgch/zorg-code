<?php
/*
    This file is part of gLuV.

    gLuV is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    gluV is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with gLuV; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

 require('options.php');
 require('objects.php');
 require('names.php');
 require('stat.php');
 require('page.php');

 $gluVer="1.1.9";                          // current version
 if(empty($loc) || $loc<0 || $loc>=count($servers) || ord($loc)<48 || ord($loc)>57)
   $loc=0;
 $ip=strtok($servers[$loc], ":");          // parse server into ip and port
 $port=strtok(":");
 $query = "\xff\xff\xff\xffgetstatus";     // setup the server query string
 $in=getServerStatus($ip, $port, $query, 0);
 $id=0;                                    // for html element ids
/**********************
 *    parsing code    *
 **********************/
 $rules[] = new Rule;
 $token=strtok($in, "\\");                 // get rid of extra stuff
 for($i=0;$token;$i++)                     // format="rule\value\rule\value\.."
 {
   $token=strtok("\\");
   if($token)
   {
     $rules[$i]->rule=$token;
     $token=strtok("\\");
     if($rules[$i]->rule=="Players_Red")
       $Red=$token;
     else if($rules[$i]->rule=="Players_Blue")
       $Blue=$token;
     $rules[$i]->value=$token;
     if(!$token)
       $token=1;
   }
 }
 $maxi=$i-1;                               // player list is after last rule
 $token=strtok($rules[$maxi-1]->value, "\x0a");
                                           // $token is now the last rule
 $numRed=substr_count($Red, " ");
 $numBlue=substr_count($Blue, " ");
 if($Red=="(None)")
   $numRed=0;
 else
 {
   $Reds[0]=strtok($Red, " ");
   for($i=1;$i<$numRed;$i++)
     $Reds[$i]=strtok(" ");
 }    
 if($Blue=="(None)")
   $numBlue=0;
 else
 {
   $Blues[0]=strtok($Blue, " ");
   for($i=1;$i<$numBlue;$i++)
     $Blues[$i]=strtok(" ");
 }
                                           // get the player list
 $playerstr=substr($rules[$maxi-1]->value, strlen($token));
 $rules[$maxi-1]->value=$token;            // set the last rule(w/o list)
 $nextRed=0;
 $nextBlue=0;
 $temp = new Player;
 $specs[] = new Player;
 $reds[] = new Player;
 $blues[] = new Player;
 $temp->score=strtok($playerstr, " ");
 $temp->ping=strtok(" ");
 $temp->name=strtok("\"");                 // initialize loop
 if($Reds[0]==1)                           // 1st player a red?
 {
   $reds[0]->score=$temp->score;
   $reds[0]->ping=$temp->ping;
   $reds[0]->name=$temp->name;
   $si=0;
   $ri=1;
   $bi=0;
 }
 else if($Blues[0]==1)                     // 1st player a blue?
 {
   $blues[0]->score=$temp->score;
   $blues[0]->ping=$temp->ping;
   $blues[0]->name=$temp->name;
   $si=0;
   $ri=0;
   $bi=1;
 }
 else                                      // 1st player a spec?
 {
   $specs[0]->score=$temp->score;
   $specs[0]->ping=$temp->ping;
   $specs[0]->name=$temp->name;
   $si=1;
   $ri=0;
   $bi=0;
 }
 for($i=1;$temp->name;$i++)                // next player
 {
   $temp->score=strtok(" ");
   $temp->ping=strtok(" ");
   $temp->name=strtok("\"");
   if($Reds[$ri]==$i+1)                    // current player a Red?
   {
     $reds[$ri]->name=$temp->name;
     $reds[$ri]->score=$temp->score;
     $reds[$ri]->ping=$temp->ping;
     $ri++;
   }
   else if($Blues[$bi]==$i+1)              // current player a Blue?
   {
     $blues[$bi]->name=$temp->name;
     $blues[$bi]->score=$temp->score;
     $blues[$bi]->ping=$temp->ping;
     $bi++;
   }
   else                                    // current player a Spec?
   {
     $specs[$si]->name=$temp->name;
     $specs[$si]->score=$temp->score;
     $specs[$si]->ping=$temp->ping;
     $si++;
   }
 }
 $numPlayers=$i-1;
 $numSpec=$si-1; 
 usort($specs, "cmp_player");              // sort all of the lists
 usort($reds, "cmp_player");
 usort($blues, "cmp_player");
 usort($rules, "cmp_rule");
 printPage($reds, $blues, $specs, $rules); // have all the info..print the page!
?>
