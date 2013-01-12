<?
	include_once($_SERVER['DOCUMENT_ROOT'].'includes/chess.inc.php');

	global $db, $user, $smarty;

	// my games
	$smarty->assign("my_games", Chess::my_games());
	
	
	// this game
	if (!$_GET['game']) {
		$e = $db->query("SELECT id FROM chess_games WHERE next_turn='$user->id' OR offering_remis='1' LIMIT 0,1", __FILE__, __LINE__);
		$d = $db->fetch($e);
		if ($d) $_GET['game'] = $d['id'];
	}
	
	if ($_GET['game']) {
		$game = $db->fetch($db->query(
			"SELECT 
				g.*, 
				w.rank wrank, w.score wscore, b.score bscore, b.rank brank, 
				concat(wu.clan_tag, wu.username) wuser, concat(bu.clan_tag, bu.username) buser
			FROM chess_games g, chess_dwz w, chess_dwz b, user wu, user bu
			WHERE g.id=$_GET[game] AND w.user=g.white AND b.user=g.black AND wu.id=g.white AND bu.id=g.black", 
			__FILE__, __LINE__
		));
		$smarty->assign("game", $game);
	
		if ($game['white'] == $user->id) $my_color = 'w';
		elseif ($game['black'] == $user->id) $my_color = 'b';
		else $my_color = '';

		$board = Chess::get_board($_GET['game']);		
		$smarty->assign("board", Chess::simplify_board($board));
		$smarty->assign("taken", $board['taken']);
		$smarty->assign("positions", Chess::positions());
		$smarty->assign("history", $board['history']);
		
		$d = $db->fetch($db->query("SELECT * FROM chess_history WHERE game=$_GET[game] ORDER BY nr DESC LIMIT 0,1", __FILE__, __LINE__));
		if (substr($d['white'], -1) == '+') {
			$smarty->assign("say_chess", 'w');
		}elseif (substr($d['black'], -1) == '+') {
			$smarty->assign("say_chess", 'b');
		}else{
			$smarty->assign("say_chess", '');
		}

		if ($game['state'] == 'running' && $game['next_turn'] == $user->id && !$game['offering_remis']) {
			$smarty->assign("my_positions", Chess::own_positions($board, $my_color));
			
			if ($_GET['from']) {
				$e = $db->query("SELECT * FROM chess_history WHERE game=$_GET[game] ORDER BY nr DESC LIMIT 0,1", __FILE__, __LINE__);
				$d = $db->fetch($e);
				$prev_move = $my_color=='w' ? $d['black'] : $d['white'];
				$smarty->assign("possible_moves", Chess::possible_moves($board, $my_color, $_GET['from'], $prev_move));
			}else{
				$smarty->assign("possible_moves", array());
			}
		}else{
			$smarty->assign("my_positions", array());
			$smarty->assign("possible_moves", array());
		}
		
	}
	
	
?>