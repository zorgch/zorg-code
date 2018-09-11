<?php
/**
 * File includes
 * @include config.inc.php
 * @include usersystem.inc.php
 * @include colors.inc.php
 * @include strings.inc.php	Strings die im Zorg Code benutzt werden
 */
require_once( __DIR__ . '/config.inc.php');
require_once( __DIR__ . '/usersystem.inc.php');
include_once( __DIR__ . '/colors.inc.php');
include_once( __DIR__ . '/strings.inc.php');

/**
 * Poll anzeigen
 *
 * @author ?, IneX
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 11.09.2018 fixed SQL-Query (Polls were broken for not-loggedin users)
 *
 * @TODO Extract HTML-View into Template-File and use $smarty->display()
 *
 * @see templates/layout/partials/polls/poll.tpl
 * @param $id Poll-ID to display
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @return string HTML-markup to display the Poll
 */
function getPoll ($id) {
	global $db, $user;

	$ret = "";
	$redirect_url = base64_encode("$_SERVER[PHP_SELF]?".url_params());
	$action = '/actions/poll_vote.php?redirect='.$redirect_url;

	$sql = 'SELECT
				 p.*
				,UNIX_TIMESTAMP(p.date) date
				,(SELECT count(*) FROM poll_votes WHERE poll='.$id.') tot_votes
				'.($user->islogged_in() ? ',(SELECT count(*) FROM poll_votes WHERE poll='.$id.' AND user='.$user->id.') myvote' : '').'
			FROM polls p
			WHERE id='.$id.'
			GROUP BY p.id';
	$poll = $db->fetch($db->query($sql, __FILE__, __LINE__, __FUNCTION__));

	if (!$poll) {
		user_error(t('invalid-poll_id', 'poll', $id), E_USER_WARNING);
	}

	if (user_has_vote_permission($poll['type']) && !$poll['myvote'] && $poll['state']=="open") {
		$display = "vote";
	}else{
		$display = "results";
	}

	if ($display == "vote") {
		$ret .= "<form name='poll' action='$action' method='post'>";
		$ret .= "<input type='hidden' name='poll' value='$poll[id]'>";
	}

	$ret .= 
		"<table cellspacing=2 cellpadding=0 class='border' width=204 bgcolor='".BACKGROUNDCOLOR."'>".
			"<tr><td align='left'><small><b>$poll[text]</b> ".
			"<br />(".$user->id2user($poll['user']).", ".datename($poll['date']);
	if ($poll['type'] == "member") $ret .= ", <nobr>Member only</nobr>";
	if ($poll['state'] == "closed") $ret .= ", closed";
	$ret .= 
			")</small></td></tr>".
			"<tr><td><img src='/images/pixel_border.gif' height='1' width='100%'></td></tr>";
	;
	
	$e = $db->query('SELECT count(user) anz FROM poll_votes WHERE poll='.$poll['id'].' GROUP BY answer', __FILE__, __LINE__, __FUNCTION__);
	$maxvotes = 0;
	while ($d = $db->fetch($e)) if ($maxvotes < $d['anz']) $maxvotes = $d['anz'];
		
	
	$aw_e = $db->query(
		'SELECT a.*, count(v.user) votes
		FROM poll_answers a
		LEFT JOIN poll_votes v ON v.answer=a.id
		WHERE a.poll='.$poll['id'].'
		GROUP BY a.id
		ORDER BY a.id',
		__FILE__, __LINE__, __FUNCTION__
	);
	
	if ($display == "vote") {
		while ($aw = $db->fetch($aw_e)) {
			$ret .= 
				"<tr><td align='left'><table><tr>".
				"<td align='left' valign='middle' width=10>".
					"<input type='radio' value='$aw[id]' name='vote' onClick='document.location.href=\"$action&poll=$poll[id]&vote=$aw[id]\"'>".
				"</td>".
				"<td align='left' valign='middle'><small> $aw[text]</small></td>".
				"</tr></table></td></tr>"
			;
		}
		$ret .= "<tr><td align='center'><input type='submit' class='button' value=' vote '></td></tr>";
	}else{
		while ($aw = $db->fetch($aw_e)) {
			$maxwdt = 200;
			if ($aw['votes'] == 0) $wdt = 1;
			else $wdt = round($aw['votes'] / $maxvotes * $maxwdt);
			$swdt = $maxwdt - $wdt;
			
			$ret .= "<tr><td><img src='/images/spc.gif' height=2 width=1></td></tr>";
			$ret .= "<tr><td align='left'><small>";
			if ($poll['myvote'] == $aw['id']) $ret .= "<b>";
			$ret .= "$aw[text] ($aw[votes])";
			if ($poll['myvote'] == $aw['id']) {
				$ret .= "</b>";
				
				if ($poll['myvote'] && $poll['state']=="open" && user_has_vote_permission($poll['type'])) {
					$old_url = base64_encode("$_SERVER[PHP_SELF]?".url_params());
					$ret .= " / <a href='/actions/poll_unvote.php?poll=$poll[id]&redirect=$old_url'>unvote</a>";
				}
			}
			
			if ($poll['type'] == "member") {
				$v_e = $db->query("SELECT u.username FROM user u, poll_votes v WHERE v.user=u.id AND v.answer=$aw[id]", __FILE__, __LINE__, __FUNCTION__);
				$voters = "";
				while ($v = $db->fetch($v_e)) $voters .= "$v[username], ";
				
				if ($voters) {
					$voters = substr($voters, 0, -2);
					$ret .= ": <i>$voters</i>";
				}
			}
			
			$ret .= "<br />";
			
			
			$ret .= "</td></tr>";
			$ret .= 
				"<tr><td><table cellspacing=0 cellpadding=0><tr>".
				"<td background='/images/poll_bar.gif' style='background-repeat:repeat-x;'><img src='/images/spc.gif' height='6' width='$wdt'</td>".
				"<td><img src='/images/spc.gif' height='1' width='$swdt'></td>".
				"</tr></table></td></tr>"
			;
		}
	}
	
	if ($poll['myvote'] && $poll['state']=="open" || $user->id==$poll['user'] && user_has_vote_permission($poll['type'])) {
		$ret .= "<tr><td align='center'><small>";
		
		if ($poll['state'] == "open" && $user->id==$poll['user']) {
			$ret .= "| <a href='/actions/poll_state.php?poll=$poll[id]&state=closed&".url_params()."'>close</a> | ";
		}elseif ($poll['state'] == "closed" && $user->id==$poll['user']) {
			$ret .= "| <a href='/actions/poll_state.php?poll=$poll[id]&state=open&".url_params()."'>open</a> | ";
		}
		
		$ret .= "</small></td></tr>";
	}
	
	$ret .= "</table>";
	if ($display == "vote") $ret .= "</form>";
	else $ret .= "<br />";
	return $ret;
}


function user_has_vote_permission ($poll_type) {
	global $user;
	
	if ($poll_type == "standard" && $user->id || $poll_type == "member" && $user->typ == USER_MEMBER) return true;
	else return false;
}
