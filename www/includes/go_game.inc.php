<?php
/**
 * GO Funktionen
 * 
 * ...
 * ...
 * @TODO Das alles müsste in eine PHP Klasse "class Go { ... }"
 *
 * @author [z]bert
 * @author [z]domi
 * @package zorg\Games\Go
 */

/**
 * File includes
 * @include mysql.inc.php
 * @include config.inc.php
 */
require_once( __DIR__ .'/mysql.inc.php');
require_once( __DIR__ .'/config.inc.php');

/**
 * @const OFFSET_PIC 		Anzahl pixel, um welche das board nach unten gerückt wird, um den userpics platz zu machen.
 * @const LINKRADIUS 		Etwas kleiner, damit es einen Zwischenraum gibt
 * @const FIELDSIZE			Weitere globale Variablen fürs GO
 * @const LINEWIDTH			Weitere globale Variablen fürs GO
 * @const STARDOTWIDTH		Weitere globale Variablen fürs GO
 * @const STONEBIGWIDTH		Weitere globale Variablen fürs GO
 * @const LASTSTONEWIDTH	Weitere globale Variablen fürs GO
 * @const GOIMGPATH			Weitere globale Variablen fürs GO
 * @const LINE				Weitere globale Variablen fürs GO
 * @const STARDOT			Weitere globale Variablen fürs GO
 * @const BLACKSTONE		Weitere globale Variablen fürs GO
 * @const WHITESTONE		Weitere globale Variablen fürs GO
 * @const BLACKSTONESEMI	Weitere globale Variablen fürs GO
 * @const WHITESTONESEMI	Weitere globale Variablen fürs GO
 * @const BLACKSTONEBIG		Weitere globale Variablen fürs GO
 * @const WHITESTONEBIG		Weitere globale Variablen fürs GO
 * @const LASTSTONE			Weitere globale Variablen fürs GO
 */
define('OFFSET_PIC', 250);
define('LINKRADIUS', 15);
define('FIELDSIZE', 40);
define('LINEWIDTH', 2);
define('STARDOTWIDTH', 10);
define('STONEBIGWIDTH', 190);
define('LASTSTONEWIDTH', 10);
define('GOIMGPATH', PHP_IMAGES_DIR.'go/');
define('LINE', imagecreatefrompng(GOIMGPATH.'go_line.png'));
define('STARDOT', imagecreatefrompng(GOIMGPATH.'go_stardot.png'));
define('BLACKSTONE', imagecreatefrompng(GOIMGPATH.'go_black.png'));
define('WHITESTONE', imagecreatefrompng(GOIMGPATH.'go_white.png'));
define('BLACKSTONESEMI', imagecreatefrompng(GOIMGPATH.'go_black_semi.png'));
define('WHITESTONESEMI', imagecreatefrompng(GOIMGPATH.'go_white_semi.png'));
define('BLACKSTONEBIG', imagecreatefrompng(GOIMGPATH.'go_pl2.png'));
define('WHITESTONEBIG', imagecreatefrompng(GOIMGPATH.'go_pl1.png'));
define('LASTSTONE', imagecreatefrompng(GOIMGPATH.'go_last.png'));


/**
 * Alle laufenden GO Spiele
 *
 * @author [z]bert
 * @author [z]domi
 * @version 1.0
 * @since 1.0
 */
function go_running_games ()
{
    global $db, $user;
    $e = $db->query(
		    'SELECT count(*) anz
		      FROM go_games g 
		      WHERE g.nextturn='.$user->id.' AND g.state="running"',
		    __FILE__, __LINE__
		    );
    $d = $db->fetch($e);
    return $d['anz'];
}


/**
 * Alle offenen GO Spiele
 *
 * @author [z]bert
 * @author [z]domi
 * @version 1.0
 * @since 1.0
 */
function go_open_games ()
{
    global $db, $user;
    $e = $db->query(
		    'SELECT count(*) anz
		      FROM go_games g
		      WHERE g.pl2='.$user->id.' AND g.state="open"', 
		    __FILE__, __LINE__
		    );
    $d = $db->fetch($e);
    return $d['anz'];
}


/**
 * GO Spiel schliessen
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @since 1.0
 * 
 * @param integer $gid ID des GO-Spiels
 * @global array $db Globales Class-Object mit allen MySQL-Methoden
 * @global array $user Globales Class-Object mit den User-Methoden & Variablen
 */
function go_close_game ($gid)
{
	global $db, $user;
    $e = $db->query('DELETE
    	FROM go_games
    	WHERE state="open"
    	AND id='.$gid.'
    	AND pl1='.$user->id,
    	__FILE__, __LINE__);
}


/**
 * GO Spiel ablehnen
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @since 1.0
 * 
 * @param integer $gid ID des GO-Spiels
 * @global array $db Globales Class-Object mit allen MySQL-Methoden
 * @global array $user Globales Class-Object mit den User-Methoden & Variablen
 */
function go_decline_game ($gid) {
	global $db, $user;
   	$e = $db->query('DELETE
		 FROM go_games
		 WHERE state="open"
		   AND id='.$gid.'
		   AND pl2='.$user->id,
		 __FILE__, __LINE__);
}


/**
 * GO Spiel akzeptieren
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @since 1.0
 * 
 * @param integer $gid ID des GO-Spiels
 * @global array $db Globales Class-Object mit allen MySQL-Methoden
 * @global array $user Globales Class-Object mit den User-Methoden & Variablen
 */
function go_accept_game ($gid) {
	global $db, $user;
   	$e = $db->query('UPDATE go_games
		 SET state="running"
		 WHERE state="open"
		   AND id='.$gid.'
		   AND pl2='.$user->id,
		 __FILE__, __LINE__);
}


/**
 * Neues GO Spiel
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @since 1.0
 * 
 * @param integer $opponent ID des Gegners
 * @param integer $size Board-Grösse
 * @global array $db Globales Class-Object mit allen MySQL-Methoden
 * @global array $user Globales Class-Object mit den User-Methoden & Variablen
 * @exception user_error
 */
function go_new_game ($opponent, $size, $handicap) {
	global $db, $user;
	
	if (!$user->id) user_error( t('error-newgame-not-logged-in'), E_USER_ERROR);

        $e = $db->query("SELECT u.id
			 FROM user u
			 WHERE id='".$opponent."'",
			 __FILE__, __LINE__);
        if ($size < 20 && $size >= 9){
	    if ($db->fetch($e)){
			for ($i = 0; $i < $size*$size; $i++) $board .= "0";
			$e = $db->query("INSERT
					 INTO go_games (pl1, pl2, size, data, nextturn, state, round, handicap)
					 VALUES ('".$user->id."', '".$opponent."', '".$size."', '".$board."', '".$opponent."', 'open', 1-$handicap, '".$handicap."')",
					 __FILE__, __LINE__);

			// Activity Eintrag auslösen
			Activities::addActivity($user->id, 0, t('activity-newgame', 'go', [ usersystem::id2user($opponent, TRUE), SITE_URL, $game ]), 'go');
	    }
	    else user_error( t('error-game-player-unknown'), E_USER_ERROR);
	}
        else user_error( t('invalid-size', 'go'), E_USER_ERROR);
}


function go_luck($gameid)
{
    global $db, $user;
    
    if (!$user->id) user_error( t('error-newgame-not-logged-in'), E_USER_ERROR);

    $game = readGame($gameid);
    if (!$game)
    {
		user_error( t('error-game-invalid', 'global', $gameid), E_USER_ERROR );
		return;
    }
    
    if ($user->id != $game['pl1'] && $user->id != $game['pl2'])
    {
		user_error("Not your game!");
		return;
    }
    
    if ($user->id == $game['pl1'])
    {
		$e = $db->query("UPDATE go_games
				 SET pl1luck=1
				 WHERE pl1luck=0
				   AND id='".$gameid."'",
				 __FILE__, __LINE__);
		
		// Activity Eintrag auslösen
		Activities::addActivity($user->id, $game['pl2'], "hat ".$user->id2user($game['pl2'])." im GO Gl&uuml;ck gew&uuml;nscht!<br/><br/>", 'go');
		
    } else {
		$e = $db->query("UPDATE go_games
				 SET pl2luck=1
				 WHERE pl2luck=0
				   AND id='".$gameid."'",
				 __FILE__, __LINE__);
		
		// Activity Eintrag auslösen
		Activities::addActivity($user->id, $game['pl1'], "hat ".$user->id2user($game['pl1'])." im GO Gl&uuml;ck gew&uuml;nscht!<br/><br/>", 'go');	
    }
}


function go_thank($gameid)
{
    global $db, $user;
    
    if (!$user->id) user_error( t('error-newgame-not-logged-in'), E_USER_ERROR);

    $game = readGame($gameid);
    
    if (!$game)
    {
		user_error( t('error-game-invalid', 'global', $gameid), E_USER_ERROR );
		return;
    }
    
    if ($user->id != $game['pl1'] && $user->id != $game['pl2'])
    {
		user_error("Not your game!");
		return;
    }
    
    if ($user->id == $game['pl1'])
    {
		$e = $db->query("UPDATE go_games
				 SET pl1thank=1
				 WHERE pl1thank=0
				   AND id='".$gameid."'",
				 __FILE__, __LINE__);
		
		// Activity Eintrag auslösen
		Activities::addActivity($user->id, $game['pl2'], "hat sich bei ".$user->id2user($game['pl2'])." &uuml;ber das GO-Spiel bedankt.<br/><br/>", 'go');
		
    }
    else {
		$e = $db->query("UPDATE go_games
				 SET pl2thank=1
				 WHERE pl2thank=0
				   AND id='".$gameid."'",
				 __FILE__, __LINE__);
		
		// Activity Eintrag auslösen
		Activities::addActivity($user->id, $game['pl1'], "hat sich bei ".$user->id2user($game['pl1'])." &uuml;ber das GO-Spiel bedankt.<br/><br/>", 'go');
		
    }
}


function go_count_game($gameid){
	global $db, $user;
	        $e = $db->query("UPDATE go_games
		 SET state='counting'
		 WHERE state='running'
		   AND id='".$gameid."'",
		 __FILE__, __LINE__);
}

    function go_finish_game($gameid){
    global $db, $user;
    
    $game = readGame($gameid);
    $size = $game['size'];
    $wholesdone = array();
    $game['pl1points'] = 0;
    $game['pl2points'] = 0;
    
    for ($which = 0; $which < $size * $size; $which++){
	if ($game['board'][$which] == 1 || $game['board'][$which] == 2 || in_array($which, $wholesdone)) continue;
	$area = get_area($which, $game);
	
	$neighbours = get_neighbourstones_of_area($game, $area);
	
	$differentowners = array();
	for ($k = 0; $k < count($neighbours); $k++)
	    if (!in_array($game['board'][$neighbours[$k]], $differentowners))
	        $differentowners[] = $game['board'][$neighbours[$k]];
	
	if (count($differentowners) == 1){
	    if ($differentowners[0] == 1) $game['pl1points'] += count($area);
	    else $game['pl2points'] += count($area);
	}
	
	for ($k = 0; $k < count($area); $k++)
	    if (!in_array($area[$k], $wholesdone))
	        $wholesdone[] = $area[$k];
    }
    
    $game['pl1points'] -= $game['pl1lost'];
    $game['pl2points'] -= $game['pl2lost'];
    $game['pl1points'] += get_komi($size);
    
    if ($game['pl1points'] > $game['pl2points']) $game['winner'] = $game['pl1'];
    else $game['winner'] = $game['pl2'];
    
    $e = $db->query("UPDATE go_games
		      SET state='finished', pl1points='".$game['pl1points'].
			"', pl2points='".$game['pl2points'].
		        "', winner='".$game['winner']."'
		      WHERE state='counting'
		      AND id='".$gameid."'",
		    __FILE__, __LINE__);
}


function get_komi($size){
    if ($size > 13) return 6.5;
    if ($size > 9) return 4.5;
    return 3.5;
}


/**
 * Spielzug machen
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @since 1.0
 * 
 * @param integer $x ...
 * @param integer $y ...
 * @param integer $gameid ID des GO-Spiels
 * @global array $db Globales Class-Object mit allen MySQL-Methoden
 * @global array $user Globales Class-Object mit den User-Methoden & Variablen
 * @exception user_error
 */
function go_move($which, $gameid)
{
	global $db, $user;
    
    $game=readGame($gameid);
    
    if (!$game)
    {
		user_error( t('error-game-invalid', 'global', $gameid), E_USER_ERROR );
		return;
    }
    
    if ($game['state'] != 'running')
    {
		user_error( t('invalid-gamestate', 'go', 'running'), E_USER_NOTICE);
		return;
    }
    
    if ($game['nextturn'] != $user->id)
    {
		user_error( t('error-game-notyourturn'), E_USER_NOTICE);
		return;
    }
    
    $size = $game['size'];
    $y = floor($which / $size);
    $x = $which - $y*$size;
    
    if ($which < 0 || $which >= $size * $size)
    {
		user_error( t('invalid-coordinates', 'go', [ $which, $x, $y ]), E_USER_WARNING);
		return;
    }
    
    if ($game['board'][$which] == 1 || $game['board'][$which] == 2)
    {
		user_error("Feld $which (x=$x, y=$y) ist bereits von einem anderen Stein belegt!");
		return;
    }
    
    $hit_area = check_hit($which, $game);
    
    if (count($hit_area) == 0 && check_suicide($which, $game))
    {
		user_error( t('suicide-prevention', 'go'), E_USER_NOTICE);
		return;
    }
    
    if ($game['ko_sit'] == $which && count($hit_area) == 1)
    {
		user_error( t('ko-situation', 'go'), E_USER_NOTICE);
		return;
    }
    
    $game['ko_sit'] = -1;
    
    for ($k = 0; $k < $size * $size; $k++) { if ($game['board'][$k] > 2) $game['board'][$k] = 0; }
    
    //eraseStones($game);
    
    $hit_area = check_hit($which, $game);
    if (count($hit_area) > 0)
    {
		for ($i = 0; $i < count($hit_area); $i++) $game['board'][$hit_area[$i]] += 2;
			if ($game['nextturn'] == $game['pl1']) $game['pl2lost'] += count($hit_area);
			else $game['pl1lost'] += count($hit_area);
	}
    
    if (count($hit_area) == 1) $game['ko_sit'] = $hit_area[0];
        
    $game['board'][$which] = $game['pl1'] == $game['nextturn'] ? 1 : 2;
    
    $game['last2'] = $game['last1'];
    $game['last1'] = $which;
    
    writeGame($game);
 }
 
 
 
 function go_skip($gameid)
 {
    global $user, $db;
    $game=readGame($gameid);
    
    if (!$game)
    {
		user_error( t('error-game-invalid', 'global', $gameid), E_USER_ERROR );
		return;
    }
    
    if ($game['state'] != 'running')
    {
		user_error( t('invalid-gamestate', 'go', 'running'), E_USER_NOTICE);
		return;
    }
    
    if ($game['nextturn'] != $user->id)
    {
		user_error(t('error-game-notyourturn'), E_USER_NOTICE);
		return;
    }
    
    $game['ko_sit'] = -1;
    for ($k = 0; $k < $game['size'] * $game['size']; $k++) if ($game['board'][$k] > 2) $game['board'][$k] = 0;
    
    if ($game['last1'] == -1)
    {
		go_count_game($gameid);
		return;
    }
    
    $game['last2'] = $game['last1'];
    $game['last1'] = -1;
    
    writeGame($game);
}

/**
 * ??
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @since 1.0
 * 
 * @param integer $gameid ID des GO-Spiels
 * @global array $db Globales Class-Object mit allen MySQL-Methoden
 * @global array $user Globales Class-Object mit den User-Methoden & Variablen
 * @see LINKRADIUS, FIELDSIZE, LINEWIDTH
 * @return string HTML-Code...
 */
function nextstone_map($gameid)
{
    global $db, $user;
	    
    $ret = '<map name="moves">';
    
    $game = readGame($gameid);
    $ko_sit = $game['ko_sit'];
        $size = $game['size'];
    
    if ($game['state'] == 'running' || $game['state'] == 'finished')
      for ($i = 0; $i < $size; $i++) for ($j = 0; $j < $size; $j++){
	
	$which = $i + $j*$size;
	
	if ($game['board'][$which] == 1 || $game['board'][$which] == 2){
	    $area = get_area($which, $game);
	    $freedoms = get_freedoms($game, $area);
	    $msg = t('gebietssteine-freiheiten', 'go', [ count($area), count($freedoms) ]);
	    $islink = false;
	}
	else if ($user->id == $game['nextturn'] && $game['state'] == 'running'){
	    
	    $countHit = count(check_hit($which, $game));
	    if ($countHit > 0){
			if ($ko_sit == $which && $countHit == 1){
			    $msg = t('ko-warning', 'go');
			    $islink = false;
	        }
	        else {
		    // check if you can hit the opponent
     		    $msg = t('hit-check', 'go');
	 			$islink = true;
			}
	    }
	    else if (check_suicide($which, $game)){
		// check for suicide
			$msg = t('suicide-prevention', 'go');
			$islink = false;
	    }
	    else{
			$msg = t('maaachs', 'go');
			$islink = true;
	    }
	}
	else continue;
    
    $ret .= '<area shape="rect" coords="'.(($i+1)*FIELDSIZE-LINKRADIUS).','.(OFFSET_PIC+($j+1)*FIELDSIZE-LINKRADIUS)
                                 .','.(($i+1)*FIELDSIZE+LINKRADIUS).','.(OFFSET_PIC+($j+1)*FIELDSIZE+LINKRADIUS).'" ';
    if ($islink) $ret .= 'href="/actions/go_game.php?tpl=699&action=move&move='.$which.'&game='.$gameid.'"';
		$ret .= 'alt="'.$msg.'"'.'title="'.$msg.'">';
    }
    
    if ($game['state'] == 'counting' && $game['nextturn'] == $user->id)
      for ($i = 0; $i < $size; $i++) for ($j = 0; $j < $size; $j++){
	
	$which = $i + $j*$size;
	
	if ($game['board'][$which] == 1 || $game['board'][$which] == 2){
	    $area = get_area($which, $game);
	    if (count($area) == 1) $msg = 'Zu den Gefangenen mit dir!';
	    else $msg = 'Diese '.count($area).' Steine zu den Gefangenen gesellen.';
	}
	else if ($game['board'][$which] == 3 || $game['board'][$which] == 4){
	    $area = get_area($which, $game);
	    if (count($area) == 1) $msg = 'Du doch nöd. Chum zrugg!';
	    else $msg = 'Diese '.count($area).' Steine doch nicht als tot betrachten.';
	}
	else continue;
	
	$ret.= '<area shape="rect" coords="'.(($i+1)*FIELDSIZE-LINKRADIUS).','.(OFFSET_PIC+($j+1)*FIELDSIZE-LINKRADIUS)
	                                .','.(($i+1)*FIELDSIZE+LINKRADIUS).','.(OFFSET_PIC+($j+1)*FIELDSIZE+LINKRADIUS).'" ';
        $ret .= 'href="/actions/go_game.php?tpl=699&action=count&move='.$which.'&game='.$gameid.'"';
	$ret .= 'alt="'.$msg.'"'.'title="'.$msg.'">';
    }
    
    $ret .= '</map>';
    return $ret;
}

function check_hit($which, $game)
{
    
    $opponentcolor = $game['nextturn'] == $game['pl1'] ? 2 : 1;
    $neighbours = get_neighbours($which, $game, array($opponentcolor));
    $hit_areas = array();
    
    for ($i = 0; $i < count($neighbours); $i++){
	
	$area = get_area($neighbours[$i], $game);
	$freedoms = get_freedoms($game, $area);
	if (count($freedoms) == 1) for ($k = 0; $k < count($area); $k++) $hit_area[] = $area[$k];
    }
 
    return $hit_area;
}

function check_suicide($which, $game)
{
    
    $mycolor = $game['nextturn'] == $game['pl1'] ? 1 : 2;
    $neighbours = get_neighbours($which, $game, array($mycolor));
    
    $checksuicide = true;
    for ($i = 0; $i < count($neighbours); $i++){
	
	$freedoms = get_freedoms($game, get_area($neighbours[$i], $game));
	if (count($freedoms) > 1) $checksuicide = false;
    }
    if (count(get_neighbours($which, $game, array(0, 3, 4))) == 0 && $checksuicide) return true;
 
    return false;
}

function get_freedoms($game, $area)
{    
    $freedoms = array();
    
    for ($i = 0; $i < count($area); $i++){
	
	$wholes = get_neighbours($area[$i], $game, array(0, 3, 4));
	for ($k = 0; $k < count($wholes); $k++) if (!in_array($wholes[$k], $freedoms)) $freedoms[] = $wholes[$k];
    }
    
    return $freedoms;
}

function get_neighbourstones_of_area($game, $area)
{    
    $neighbours = array();
    for ($i = 0; $i < count($area); $i++){
	$wholes = get_neighbours($area[$i], $game, array(1, 2));
	for ($k = 0; $k < count($wholes); $k++) if (!in_array($wholes[$k], $neighbours)) $neighbours[] = $wholes[$k];
    }
    
    return $neighbours;
}

function get_area($which, $game)
{    
    $stonesdone = array();
    
    $mycolor = $game['board'][$which];
    $mycolors = array();
    if ($mycolor == 0){
	$mycolors[] = 0;
	$mycolors[] = 3;
	$mycolors[] = 4;
    }
    else $mycolors[] = $mycolor;
    
    $stonesdone = get_area_rec($which, $game, $mycolors, $stonesdone);
    
    return $stonesdone;
}

function get_area_rec($which, $game, $mycolors, $stonesdone)
{
    if (in_array($which, $stonesdone)) return;
    
    $stonesdone[] = $which;
    
    $neighbours = get_neighbours($which, $game, $mycolors);
    
    for ($i = 0; $i < count($neighbours); $i++){
	
	$updatestones = get_area_rec($neighbours[$i], $game, $mycolors, $stonesdone);
	for ($k = 0; $k < count($updatestones); $k++)
	  if (!in_array($updatestones[$k], $stonesdone)) $stonesdone[] = $updatestones[$k];
    }
    
    return $stonesdone;
}


function get_neighbours($which, $game, $items)
{
   
    $neighbours = array();
    $size = $game['size'];
    $y = floor($which/$size);
    $x = $which - $y*$size;
    
    if (field_equals($x-1, $y, $game, $items) == 1) $neighbours[] = $which-1;
    if (field_equals($x+1, $y, $game, $items) == 1) $neighbours[] = $which+1;
    if (field_equals($x, $y-1, $game, $items) == 1) $neighbours[] = $which-$size;
    if (field_equals($x, $y+1, $game, $items) == 1) $neighbours[] = $which+$size;
        
    return $neighbours;
}


function field_equals($x, $y, $game, $items){
    
    $size = $game['size'];
    if ($x < 0 || $y < 0 || $x >= $size || $y >= $size) return -1;
    if (in_array($game['board'][$x + $y*$size], $items)) return 1;
	else return 0;
}


/**
 * ??
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @since 1.0
 * 
 * @param integer $gameid ID des GO-Spiels
 * @global array $db Globales Class-Object mit allen MySQL-Methoden
 * @global array $user Globales Class-Object mit den User-Methoden & Variablen
 * @exception user_error
 * @return string ...
 */
function readGame($gameid){
    global $db, $user;
    $e = $db->query( //read in the game data from db
		    "SELECT *
		    FROM go_games g
		    WHERE g.id = '$gameid'", __FILE__, __LINE__);
    $game = $db->fetch($e);
    if (!$game){
		user_error( t('error-game-invalid', 'global', $gameid), E_USER_ERROR );
		return;
    }
    //make the board string from the db into an array
    $game['board'] = str_split($game['data'], 1);
    return $game;
}


/**
 * GO-Zug in die DB speichern
 *
 * @version 2.0
 * @since 1.0 function added
 * @since 2.0 26.11.2018 updated to use new $notifcation Class
 *
 * @param integer $game
 * @global object $db Globales Class-Object mit allen MySQL-Methoden
 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
 * @global object $notification Globales Class-Object mit allen Notification-Methoden
 * @return void
 */
function writeGame($game)
{
    global $db, $user, $notification;

    if ($game)
    {
		$game['data'] = implode("", $game['board']);

		if ($game['round'] >= 0) $game['nextturn'] = ($game['nextturn'] == $game['pl1'] ? $game['pl2'] : $game['pl1']);

		$game['round'] += ($game['nextturn'] == $game['pl2'] ? 1 : 0); //advance a round?
		$e = $db->query("UPDATE go_games
			 SET pl1lost='".$game['pl1lost']."', pl2lost='".$game['pl2lost']."', data='".$game['data'].
			      "', last1='".$game['last1']."', last2='".$game['last2'].
			      "', ko_sit='".$game['ko_sit']."', nextturn='".$game['nextturn']."', round='".$game['round']."'
			 WHERE id='".$game['id']."'
			 AND nextturn='".$user->id."'", __FILE__, __LINE__);

		/** Gegenspieler benachrichten, dass ein Zug gemacht wurde */
		if($user->id != $game['nextturn'])
		{
			$notification_text = t('message-your-turn', 'go', [ SITE_URL, $game['id'] ]);
			$notification_status = $notification->send($game['nextturn'], 'games', ['from_user_id'=>$user->id, 'subject'=>t('message-subject', 'go'), 'text'=>$notification_text, 'message'=>$notification_text]);
			if (DEVELOPMENT) error_log(sprintf('[DEBUG] <%s:%d> $notification_status "%s" for user=%d to user=%d', __FUNCTION__, __LINE__, ($notification_status===true?'true':'false'), $game['nextturn'], $user->id));
			/** @DEPRECATED
			Messagesystem::sendMessage(
				 $user->id
				,$game['nextturn']
				,t('message-subject', 'go')
				,t('message-your-turn', 'go', [ SITE_URL, $game['id'] ])
				,$game['nextturn']
			);*/
		}
    }
}

/**
 * GO Board generieren
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @since 1.0
 * 
 * @param integer $size Groesse des Feldes in "Kreuzungen"
 * @global array $db Globales Class-Object mit allen MySQL-Methoden
 * @global array $user Globales Class-Object mit den User-Methoden & Variablen
 * @exception user_error
 * @return image ...
 */
 function draw_go_base ($size)
 {
    $imgsizex = ($size+1)*FIELDSIZE;
    $imgsizey = ($size+1)*FIELDSIZE + OFFSET_PIC;
    $im = ImageCreateTrueColor ($imgsizex,$imgsizey);

    $col_bg = imagecolorallocate($im, 200, 200, 200);
    imagefilledrectangle($im, 0, 0, $imgsizex-1, $imgsizey-1, $col_bg);

    return $im;
 }

/**
 * ??
 *
 * @author [z]bert
 * @author [z]domi
 * @date nn.nn.nnnn
 * @version 1.0
 * @since 1.0
 * 
 * @param image $im Das zu bemalende Bild
 * @param integer $size Groesse des Feldes in "Kreuzungen"
 * @global array $db Globales Class-Object mit allen MySQL-Methoden
 * @global array $user Globales Class-Object mit den User-Methoden & Variablen
 * @exception user_error
 * @return ...
 */
function draw_grid(&$im, $size)
{
    $offset = -LINEWIDTH / 2;
    imagesetbrush($im, LINE);

    for ($i = 0; $i < $size; $i++)
    {
        imageline($im, $offset + FIELDSIZE,         $offset + ($i+1)*FIELDSIZE + OFFSET_PIC,
	           $offset + $size*FIELDSIZE,   $offset + ($i+1)*FIELDSIZE + OFFSET_PIC,  IMG_COLOR_BRUSHED);
        imageline($im, $offset + ($i+1)*FIELDSIZE,  $offset + OFFSET_PIC + FIELDSIZE,
	           $offset + ($i+1)*FIELDSIZE,  $offset + OFFSET_PIC + $size*FIELDSIZE, IMG_COLOR_BRUSHED);
	}
}


function draw_stardots(&$im, $size)
{    
    $coords = array();
    if ($size == 9)
    {
		$coords[] = 2 + 2 * $size;
		$coords[] = 6 + 2 * $size;
		$coords[] = 4 + 4 * $size;
		$coords[] = 2 + 6 * $size;
		$coords[] = 6 + 6 * $size;
    }
    else if ($size == 13)
    {
		$coords[] = 3 + 3 * $size;
		$coords[] = 9 + 3 * $size;
		$coords[] = 6 + 6 * $size;
		$coords[] = 3 + 9 * $size;
		$coords[] = 9 + 9 * $size;
    }
    else if ($size == 19)
    {
		$coords[] =  3 + 3 * $size;
		$coords[] =  9 + 3 * $size;
		$coords[] = 15 + 3 * $size;
		$coords[] =  3 + 9 * $size;
		$coords[] =  9 + 9 * $size;
		$coords[] = 15 + 9 * $size;
		$coords[] =  3 + 15 * $size;
		$coords[] =  9 + 15 * $size;
		$coords[] = 15 + 15 * $size;
    }
    else return;
    
    $offset = -STARDOTWIDTH / 2 - 1; // musste um -1 korrigieren, damits richtig aussieht. k.A. warum...
    
    for ($i = 0; $i < count($coords); $i++)
    {
		$y = floor($coords[$i] / $size);
		$x = $coords[$i] - $y*$size;
        imagecopy($im, STARDOT, $offset + ($x+1)*FIELDSIZE, $offset + OFFSET_PIC + ($y+1)*FIELDSIZE, 0, 0, imagesx(STARDOT), imagesy(STARDOT));
    }
}

 /**
  * ??
  *
  * @author [z]bert
 * @author [z]domi
  * @date nn.nn.nnnn
  * @version 1.0
  * @since 1.0
  * 
  * @param image &$im Das zu bemalende Bild
  * @param integer $x X-Koordinate auf dem Spielfeld
  * @param integer $y Y-Koordinate auf dem Spielfeld
  * @param integer $which Spieler 1 oder 2?
  * @global array $db Globales Class-Object mit allen MySQL-Methoden
  * @global array $user Globales Class-Object mit den User-Methoden & Variablen
  * @exception user_error
  * @return ...
 */
 function draw_go_stone(&$im, $x, $y, $which, $luck){

    if ($which == 1) $stone = WHITESTONE;
    else if ($which == 2) $stone = BLACKSTONE;
    else if ($which == 3) $stone = WHITESTONESEMI;
    else if ($which == 4) $stone = BLACKSTONESEMI;
    else return;

    $offset = -imagesx(BLACKSTONE)/2;
    if ($luck) $offsetluck = 0;
    else $offsetluck = rand(-2, 2);
    imagecopy($im, $stone, $offset + $offsetluck + ($x+1) * FIELDSIZE, $offset + $offsetluck + OFFSET_PIC + ($y+1) * FIELDSIZE, 0, 0, imagesx($stone), imagesy($stone));
 }

function draw_go_last(&$im, $size, $which)
{
	if ($which < 0) return;
    
    $y = floor($which / $size);
    $x = $which - $size * $y;
    
    $offset = -LASTSTONEWIDTH / 2 - 1;
    imagecopy($im, LASTSTONE, $offset + ($x+1) * FIELDSIZE, $offset + OFFSET_PIC + ($y+1) * FIELDSIZE, 0, 0, imagesx(LASTSTONE), imagesy(LASTSTONE));  
}


function draw_go_players(&$im, $game)
{ 
    $spacing = 7;
    
    if (!$game || !$im) return;
    
    $radius = floor(STONEBIGWIDTH / 2);
    $center1 = $spacing + $radius;
    $center2 = imagesx($im) - $center1;
    
    imagecopy($im, WHITESTONEBIG, $center1 - $radius, $center1 - $radius, 0, 0, imagesx(WHITESTONEBIG), imagesy(WHITESTONEBIG));
    imagecopy($im, BLACKSTONEBIG, $center2 - $radius, $center1 - $radius, 0, 0, imagesx(BLACKSTONEBIG), imagesy(BLACKSTONEBIG));

    $im_pl1 = get_userpic($game['pl1']);
    $im_pl2 = get_userpic($game['pl2']);
    //if ($game['pl1luck'] == 0) imagerotate($im_pl1, 180, 0); // imagerotate tuet noed :-(
    //if ($game['pl2luck'] == 0) imagerotate($im_pl2, 180, 0);
    imagecopy($im, $im_pl1, $center1 - imagesx($im_pl1)/2, $center1 - imagesy($im_pl1)/2, 0, 0, imagesx($im_pl1), imagesy($im_pl1));
    imagecopy($im, $im_pl2, $center2 - imagesx($im_pl2)/2, $center1 - imagesy($im_pl2)/2, 0, 0, imagesx($im_pl2), imagesy($im_pl2));
    
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);    
    if ($game['handicap'] != 0) imagestring($im, 3, $center1 - $radius*2/5, $center1 + imagesy($im_pl1)/2 + 3, 'Handicap: '.$game['handicap'], $black);
    
    if ($game['state'] == 'finished')
    {
		imagestring($im, 5, $center1 - 10, $center1 - imagesy($im_pl1)/2 - 26, $game['pl1points'], $black);
		imagestring($im, 5, $center2 - 10, $center1 - imagesy($im_pl1)/2 - 26, $game['pl2points'], $white);
    }
    
    
    imagecopy($im, BLACKSTONESEMI, $center1 - imagesx(BLACKSTONE)/2, $center1 + $radius + $spacing, 0, 0, imagesx(BLACKSTONE), imagesy(BLACKSTONE));
    imagecopy($im, WHITESTONESEMI, $center2 - imagesx(WHITESTONE)/2, $center1 + $radius + $spacing, 0, 0, imagesx(WHITESTONE), imagesy(WHITESTONE));

    $y_klaut = $center1 + $radius + $spacing + imagesy(BLACKSTONE)/2 - 8; // -8 isch öpe di halb schriftgrössi
    imagestring($im, 5, 2*$spacing, $y_klaut, 'Klaut:', $black);
    imagestring($im, 5, $center1 + imagesx(BLACKSTONE)/2 + $spacing, $y_klaut, 'x '.$game['pl2lost'], $black);
    imagestring($im, 5, $center2 + imagesx(WHITESTONE)/2 + $spacing, $y_klaut, 'x '.$game['pl1lost'], $black);
}

function get_userpic($user_id)
{    
    $image = imagecreatefromjpeg(USER_IMGPATH.$user_id.'.jpg');
    
    $w = imagesx($image);
    $h = imagesy($image);
    $max_side = STONEBIGWIDTH * 2 / 3;
    
    if ($h > $w)
    {
		$w_new = $w * $max_side / $h;
		$h_new = $max_side;
    }
    else{
		$w_new = $max_side;
		$h_new = $h * $max_side / $w;
    }
    
    $img_scaled = imagecreatetruecolor($w_new, $h_new);
    imagecopyresampled($img_scaled, $image, 0, 0, 0, 0, $w_new, $h_new, $w, $h);
    
    return $img_scaled;
}


function go_count($which, $gameid){
    global $db, $user;
    
    $game=readGame($gameid);
    
    if (!$game)
    {
		user_error( t('error-game-invalid', 'global', $gameid), E_USER_ERROR );
		return;
    }

    if ($game['state'] != 'counting')
    {
		user_error(t('invalid-gamestate', 'go', 'counting'), E_USER_NOTICE);
		return;
    }

    if (!$game['nextturn'] == $user->id)
    {
		user_error(t('error-game-notyourturn'), E_USER_NOTICE);
		return;
    }

    $size = $game['size'];
        $y = floor($which / $size);
        $x = $which - $size*$y;

    if ($which < 0 || $which >= $size * $size)
    {
		user_error( t('invalid-coordinates', 'go', [ $which, $x, $y ]), E_USER_WARNING);
		return;
    }
    
    if ($game['board'][$which] == 0)
    {
		user_error( t('invalid-field', 'go', [ $which, $x, $y ]), E_USER_WARNING);
        return;
    }
    
    $area = get_area($which, $game);
    $oldone = $game['board'][$which];
    
    if ($oldone == 1) $newone = 3;
    else if ($oldone == 2) $newone = 4;
    else if ($oldone == 3) $newone = 1;
    else if ($oldone == 4) $newone = 2;
    else {
  		user_error(t('invalid-datastate', 'go', $oldone), E_USER_NOTICE);
  		return;
    }
    
    for ($k = 0; $k < count($area); $k++) $game['board'][$area[$k]] = $newone;
    	    
    $game['countchanged'] = 1;
        writeGameCount($game);
 }

 function go_count_propose($gameid)
 {
     global $db, $user;
    
    $game=readGame($gameid);
    
    if (!$game){
	user_error( t('error-game-invalid', 'global', $gameid), E_USER_ERROR );
	return;
    }

    if ($game['state'] != 'counting'){
		user_error(t('invalid-gamestate', 'go', 'counting'), E_USER_NOTICE);
		return;
    }

    if (!($game['nextturn'] == $user->id && $game['countchanged'] == 1)){
		user_error(t('error-game-notyourturn'), E_USER_NOTICE);
		return;
    }

    $game['countchanged'] = 0;
    if ($user->id == $game['pl1']) $game['nextturn'] = $game['pl2'];
    else $game['nextturn'] = $game['pl1'];
    writeGameCount($game);
}

function go_count_accept($gameid)
{
	global $db, $user;

    $game=readGame($gameid);
    
    if (!$game)
    {
		user_error( t('error-game-invalid', 'global', $gameid), E_USER_ERROR );
		return;
    }

    if ($game['state'] != 'counting')
    {
		user_error(t('invalid-gamestate', 'go', 'counting'), E_USER_NOTICE);
		return;
    }

    if (!($game['nextturn'] == $user->id && $game['countchanged'] == 0))
    {
		user_error(t('error-game-notyourturn'), E_USER_NOTICE);
		return;
    }

    go_finish_game($gameid);   
}


function writeGameCount($game)
{
    global $db, $user;
    
    if ($game)
    {
		$game['data'] = implode("", $game['board']);
			
		$e = $db->query("UPDATE go_games
			 SET data='".$game['data']."', nextturn='".$game['nextturn'].
			"', countchanged='".$game['countchanged']."'
			 WHERE id='".$game['id']."'",
			__FILE__, __LINE__);

    }
    
}
