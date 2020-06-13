<?php
/**
 * zorg Smarty Template-Editor Functions
 *
 * @package zorg\Smarty\Tpleditor
 */

/**
 * File includes
 */
require_once dirname(__FILE__).'/main.inc.php';

/**
 * HTML Syntax Validator
 * Check's the html syntax of $str
 *
 * @author [z]biko
 * @version 1.0
 * @since 1.0 `[z]biko` function added
 *
 * @param string $str
 * @return string
 */
function html_syntax_check ($str) {	
	$res = array();
	$err = "";
	$pos = 0;

	/** comments rausnehmen */
	$str = str_replace("\r", "", $str);
	$str = str_replace("\n", "", $str);
	$str = preg_replace("/<!--.*?-->/", "", $str);

	/** smarty-tags rausnehmen */
	$str = preg_replace("/\{[^\{\}]*\}/", "", $str);

	$str = preg_replace("//", "", $str);

	// new query:	'/< *\w*( +\w( *= *(\w*|"[^"]*"|\'[^\']*\'))?)*>/'
	// old query:	"(<[^<>]*<)"

	/** check for invalid tags without > */
	if (preg_match('/< *\w+( +\w( *= *(\w*|"[^"]*"|\'[^\']*\'))?)*</', $tagcheck_str, $res, PREG_OFFSET_CAPTURE)) {
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

			/** wenn ich nicht im xmp bin */
			if ($stack[sizeof($stack)-1][0] != "xmp" || preg_match("/\/\s*xmp.*/", $t)) {
				/** opening tag */
				if (substr($t, 0, 1) != "/") {
					/** tag that doesn't close itself */
					if (!preg_match("(.*/\s*>)", $t)) {
					$t = substr($t, 0, -1);

					$t = rtrim($t);
					$t = explode(" ", $t, 2);
					if ($t[0] == "br" || $t[0] == "input" || $t[0] == "img") {
						continue;
					}
					array_push($stack, array($t[0], $pos));
					}
				/** closing tag */
				}else{
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
 * Unlock the edited Template
 *
 * @author [z]biko
 * @version 1.1
 * @since 1.0 `[z]biko` function added
 * @since 1.1 `IneX` SQL-query functions updated, fixed undefined constants
 *
 * @param int $id
 * @return void
 */
function tpleditor_unlock ($id) {
	global $db, $user;

	$e = $db->query('SELECT lock_user FROM templates WHERE id='.$id, __FILE__, __LINE__, __FUNCTION__);
	$d = $db->fetch($e);
	if ($d['lock_user'] == $user->id) $db->update('templates', ['id', $id], ['lock_user' => '0'], __FILE__, __LINE__, __FUNCTION__);
}

/**
 * Set access lock on currently edited Template
 * Prüft Zugriffsberechtigung und falls gegeben, lockt das Template. Fehler wird in $error gespeichert
 *
 * @author [z]biko
 * @version 1.1
 * @since 1.0 `[z]biko` function added
 * @since 1.1 `IneX` SQL-query functions updated, fixed undefined constants
 *
 * @param int $id
 * @param string $error
 * @return bool True = unlocked | False = locked
 */
function tpleditor_access_lock ($id, &$error)
{
	global $db, $user;

	if (is_numeric($id) && $id > 0)
	{
		$e = $db->query('SELECT *, UNIX_TIMESTAMP(last_update) last_update, UNIX_TIMESTAMP(created) created, UNIX_TIMESTAMP(lock_time) lock_time_stamp, UNIX_TIMESTAMP(NOW()) now FROM templates WHERE id='.$_GET['tplupd'], __FILE__, __LINE__, __FUNCTION__);
		$d = $db->fetch($e);
	
		if ($d && !tpl_permission($d['write_rights'], $d['owner'])) {
			$error = 'Access denied';
			return false;
		}elseif ($d['lock_user'] && $d['lock_user']!=$user->id && $d['lock_time_stamp']+1800 > $d['now']) {  /** 30 min lock time */
			$error = 'Das Template ist gesperrt, da es gerade von '.$user->id2user($d['lock_user'], true).' bearbeitet wird.';
			return false;
		}else{
			$db->query('UPDATE templates SET lock_user='.$user->id.', lock_time=NOW() WHERE id='.$d['id'], __FILE__, __LINE__);
			$error = "";
			return true;
		}
	} else {
		return true;
	}
}

/**
 * Remove invalid HTML from Smarty template.
 *
 * @link https://github.com/zorgch/zorg-code/blob/master/www/actions/tpleditor.php TPLeditor Save Action
 *
 * @TODO deaktiviert bis ein besserer syntax checker gebaut ist ([z]biko)
 * @TODO Wenn benötigt, dann als [Smarty Filter](https://www.smarty.net/docs/en/api.register.filter.tpl) umsetzen? (IneX)
 *
 * @author [z]biko
 * @version 2.0
 * @since 1.0 `[z]biko` function added
 * @since 2.0 `13.05.2020` `IneX` Moved function from previous `smarty.inc.php` to `tpleditor.inc.php` because it's only used for Tpleditor Action
 */
function smarty_remove_invalid_html($tpl)
{
	$tpl = preg_replace("(</*html[^>]*>)", "", $tpl);
	$tpl = preg_replace("(</*body[^>]*>)", "", $tpl);
	return $tpl;
}
