<?php
/**
 * File Includes
 */
require_once( __DIR__ .'/main.inc.php');

/**
 * @return string
 * @param string $str
 * @desc Check's the html syntax of $str
*/
function html_syntax_check ($str) {   	
   	$res = array();
      $err = "";
      $pos = 0;
      
      
      // comments rausnehmen
      $str = str_replace("\r", "", $str);
      $str = str_replace("\n", "", $str);
      $str = preg_replace("/<!--.*?-->/", "", $str);
      
      // smarty-tags rausnehmen
      $str = preg_replace("/\{[^\{\}]*\}/", "", $str);
      
      $str = preg_replace("//", "", $str);
      
      
      // new query:	'/< *\w*( +\w( *= *(\w*|"[^"]*"|\'[^\']*\'))?)*>/'
      // old query:	"(<[^<>]*<)"
      
      if (preg_match('/< *\w+( +\w( *= *(\w*|"[^"]*"|\'[^\']*\'))?)*</', $tagcheck_str, $res, PREG_OFFSET_CAPTURE)) { // check for invalid tags without >
      	print_array($res);
      	
         $t = substr($res[0][0], 1);
         $t = ltrim($t);
         $t = explode(" ", $t, 2);
         $err = "Im Tag &lt;$t[0]&gt; fehlt das schliessende '&gt;'";
         $pos = $res[0][1];
      }else{
      
         $tags = array();
         $stack = array();
         $anz = preg_match_all('/< *\w+( +\w( *= *(\w*|"[^"]*"|\'[^\']*\'))?)*>/', $str, $tags, PREG_OFFSET_CAPTURE);
         
         $tags = $tags[0];
         for ($i=0; $i<$anz; $i++) {
            $pos = $tags[$i][1];
            $t = substr($tags[$i][0], 1);
            $t = ltrim($t);
            
            if ($stack[sizeof($stack)-1][0] != "xmp" || preg_match("/\/\s*xmp.*/", $t)) {  // wenn ich nicht im xmp bin
	            if (substr($t, 0, 1) != "/") {  // opening tag 	            
	               if (!preg_match("(.*/\s*>)", $t)) { // tag that doesn't close itself
	                  $t = substr($t, 0, -1);
	                  
	                  $t = rtrim($t);
	                  $t = explode(" ", $t, 2);
	                  if ($t[0] == "br" || $t[0] == "input" || $t[0] == "img") {
	                     continue;
	                  }
	                  array_push($stack, array($t[0], $pos));
	               }
	            }else{  // closing tag
	               $t = substr($t, 1, -1);
	               $t = rtrim(ltrim($t));
	               
	               if (sizeof($stack) == 0) {$err = "&Ouml;ffnendes Tag für &lt;/$t&gt; fehlt"; break;}
	               
	               $last = array_pop($stack);
	               
	               if ($last[0] != $t) {
	               	$err = "Schliessendes Tag für &lt;$last[0]&gt; fehlt"; 
	               	$pos = $last[1];
	               	break;
	               }
	            }
            }
         }
         if (!$err) {
            if (sizeof($stack) > 0) {
               $last = array_pop($stack);
               $err = "Schliessendes Tag für &lt;$last[0]&gt; fehlt";
               $pos = $last[1];
            }else{
               return "";
            }
         }
      }
      
      
      $err .= " in der Nähe von: <br />";
      if (strlen($str)-$pos < 70) return $err . htmlspecialchars(substr($str, $pos));
      else return $err . htmlspecialchars(substr($str, $pos, 70));
      
   }
   
   
   /**
   * @return void
   * @param int $id
   * @desc Unlock the Template
   */
   function tpleditor_unlock ($id) {
   	global $db, $user;
   	
   	$e = $db->query("SELECT lock_user FROM templates WHERE id='$id'", __FILE__, __LINE__);
	   $d = mysql_fetch_array($e);
	   if ($d[lock_user] == $user->id) {
	      $db->query("UPDATE templates SET lock_user='0' WHERE id='$id'", __FILE__, __LINE__);
	   }
   }
   
   /**
    * @return bool
    * @param int $id
    * @param string $error
    * @desc Prüft Zugriffsberechtigung und falls gegeben, lockt das Template. Fehler wird in $error gespeichert
    */
   function tpleditor_access_lock ($id, &$error) {
   	global $db, $user;
   	
   	$e = $db->query("SELECT *, UNIX_TIMESTAMP(last_update) last_update, UNIX_TIMESTAMP(created) created, UNIX_TIMESTAMP(lock_time) lock_time_stamp, UNIX_TIMESTAMP(NOW()) now FROM templates WHERE id='$_GET[tplupd]'", __FILE__, __LINE__);
	   $d = mysql_fetch_array($e);
	   
	   if ($d && !tpl_permission($d[write_rights], $d[owner])) {
	      $error = "Access denied";
	      return false;
	   }elseif ($d[lock_user] && $d[lock_user]!=$user->id && $d[lock_time_stamp]+1800 > $d[now]) {  // 30 min lock time
	      $error = "Das Template ist gesperrt, da es gerade von ".$user->id2user($d[lock_user], true)." bearbeitet wird.";
	      return false;
	   }else{
	   	$db->query("UPDATE templates SET lock_user='".$user->id."', lock_time=NOW() WHERE id='$d[id]'", __FILE__, __LINE__);
	   	$error = "";
	   	return true;
	   }
   }
   
