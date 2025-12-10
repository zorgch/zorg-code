<?php
/**
 * Chess game class
 *
 * @TODO x open chess games im header anzeigen
 * @TODO Game-Schluss-Meldungen wie "x hat gewonnen", "x hat aufgegeben", remis, patt, matt, usw.
 *
 * @package zorg\Games\Chess
 */

/**
 * File includes
 */
require_once __DIR__.'/config.inc.php';
require_once INCLUDES_DIR.'/usersystem.inc.php';

/** Constants */
define('CHESS_DWZ_BASE_POINTS', 1600);
define('CHESS_DWZ_MAX_POINTS_TRANSFERABLE', 32);

/**
 * @version 0.1
 * @since 0.1 `[z]biko` Class added
 */
class Chess
{
	function new_game ($white, $black=0) {
		global $db, $user;

		if (!$black) $black = $user->id;

		$e = $db->query('SELECT * FROM user WHERE id=?', __FILE__, __LINE__, __METHOD__, [$white]);
		$d = $db->fetch($e);
		if ($d['chess'] && $user->id) {
			$db->query('UPDATE user SET chess="1" WHERE id=?', __FILE__, __LINE__, __METHOD__, [$user->id]);
			return $db->query('INSERT INTO chess_games (start_date, white, black, next_turn) VALUES (?, ?, ?, ?)',
								__FILE__, __LINE__, __METHOD__, [timestamp(true), $white, $black, $white]);
		}else{
			return 0;
		}
	}

	function do_move ($game, $player, $from, $to) {
		global $db;

		// move notation
		// rochaden
		$roch_p = $player=='w' ? 1 : 8;
		if ($from == 'e'.$roch_p && $to == 'g'.$roch_p) $move = 'o-o';
		elseif ($from == 'e'.$roch_p && $to == 'c'.$roch_p) $move = 'o-o-o';
		// standard move
		else $move = "$from-$to";

		$e = $db->query('SELECT * FROM chess_games WHERE id=?', __FILE__, __LINE__, __METHOD__, [$game]);
		$g = $db->fetch($e);

		// move
		$e = $db->query('SELECT * FROM chess_history WHERE game=? ORDER BY nr DESC LIMIT 1', __FILE__, __LINE__, __METHOD__, [$game]);
		$d = $db->fetch($e);
		$board = $this->get_board($game);

		if ($g['state'] == 'running' && $g[$player=='w'?'white':'black']==$g['next_turn']
			&& $this->is_move_valid($board, $player, $move, $d[$player=='w' ? 'black' : 'white'])
		) {
			$board = $this->move($board, $player, $move);
			$move = $board['move'];

			// update db
			if ($player == 'w') {
				$nr = $db->fetch($db->query('SELECT COUNT(*) anz FROM chess_history WHERE game=?', __FILE__, __LINE__, __METHOD__, [$game]));
				$db->query('INSERT INTO chess_history (game, nr, white) VALUES (?, ?, ?)',
							__FILE__, __LINE__, __METHOD__, [$game, $nr['anz'], $move]);
			}else{
				$db->query('UPDATE chess_history SET black=? WHERE game=? AND nr=?', __FILE__, __LINE__, __METHOD__, [$move, $game, $d['nr']]);
			}

			// set lastturn and nextturn
			$other = $player=='w' ? $g['black'] : $g['white'];
			$db->query('UPDATE chess_games SET last_turn=?, next_turn=? WHERE id=?', __FILE__, __LINE__, __METHOD__, [timestamp(true), $other, $game]);

			// set state if game finished
			if ($move[strlen($move)-1] == '#') {
				$winner = $player=='w' ? $g['white'] : $g['black'];
				$db->query('UPDATE chess_games SET state="matt", winner=? WHERE id=?', __FILE__, __LINE__, __METHOD__, [$winner, $game]);
				$this->update_dwz($game);
			}elseif ($move[strlen($move)-1] == '=') {
				$db->query('UPDATE chess_games SET state="patt" WHERE id=?', __FILE__, __LINE__, __METHOD__, [$game]);
				$this->update_dwz($game);
			}

			return true;
		}else{
			return false;
		}
	}

	function do_offer_remis ($game) {
		global $db;

		$db->query("UPDATE chess_games SET offering_remis='1' WHERE id=?", __FILE__, __LINE__, __METHOD__, [$game]);
	}

	function do_remis ($game) {
		global $db;

		$db->query("UPDATE chess_games SET state='remis' WHERE id=?", __FILE__, __LINE__, __METHOD__, [$game]);
		$e = $db->query("SELECT * FROM chess_history WHERE game=? ORDER BY nr DESC LIMIT 1", __FILE__, __LINE__, __METHOD__, [$game]);
		$d = $db->fetch($e);
		if ($d['black']) {
			$db->query("UPDATE chess_history SET black=? WHERE game=? AND nr=?", __FILE__, __LINE__, __METHOD__, [$d['black'], $game, $d['nr']]);
		}else{
			$db->query('UPDATE chess_history SET white=? WHERE game=? AND nr=?', __FILE__, __LINE__, __METHOD__, [$d['white'], $game, $d['nr']]);
		}
		$this->update_dwz($game);
	}

	function aufgabe ($game) {
		global $user, $db;

// TODO: aufgabe testen
// TODO: aufgabe testen bei 1. zug

		$e = $db->query("SELECT g.*, count(h.nr) no_turns
						FROM chess_games g
						LEFT JOIN chess_history h ON h.game = g.id
						WHERE g.id=? AND g.next_turn=? AND g.state='running'
						GROUP BY g.id LIMIT 1",
						__FILE__, __LINE__, __METHOD__, [$game, $user->id]
			);
		$g = $db->fetch($e);
		if ($g) {
			if ($g['white'] == $user->id) {  // weiss gibt auf
				$db->query("INSERT INTO chess_history (game, nr, white) VALUES (?, ?, 'Resigns')", __FILE__, __LINE__, __METHOD__, [$game, $g['no_turns']]);
			}elseif ($g['black'] == $user->id) { // schwarz gibt auf
				$db->query("UPDATE chess_history SET white='Resigns' WHERE game=? AND nr=?", __FILE__, __LINE__, __METHOD__, [$game, $g['no_turns']-1]);
			}
		}else{
			user_error("Invalid game '$game'", E_USER_ERROR);
		}
	}

	function deny_remis ($game) {
		global $db;

		$db->query("UPDATE chess_games SET offering_remis='0' WHERE id=?", __FILE__, __LINE__, __METHOD__, [$game]);
	}

	function get_board ($game) {
		global $db;

		$board = array(
			'a' => array('', 'wR', 'wP', '-', '-', '-', '-', 'bP', 'bR'),
			'b' => array('', 'wN', 'wP', '-', '-', '-', '-', 'bP', 'bN'),
			'c' => array('', 'wB', 'wP', '-', '-', '-', '-', 'bP', 'bB'),
			'd' => array('', 'wQ', 'wP', '-', '-', '-', '-', 'bP', 'bQ'),
			'e' => array('', 'wK', 'wP', '-', '-', '-', '-', 'bP', 'bK'),
			'f' => array('', 'wB', 'wP', '-', '-', '-', '-', 'bP', 'bB'),
			'g' => array('', 'wN', 'wP', '-', '-', '-', '-', 'bP', 'bN'),
			'h' => array('', 'wR', 'wP', '-', '-', '-', '-', 'bP', 'bR'),
			'roch' => array('wL'=>1, 'wG'=>1, 'bL'=>1, 'bG'=>1),
			'taken' => array('w'=>array(), 'b'=>array()),
			'history' => array()
		);


		$e = $db->query("SELECT * FROM chess_history WHERE game=? ORDER BY nr ASC", __FILE__, __LINE__, __METHOD__, [$game]);
		while ($d = $db->fetch($e)) {
			$board = $this->move($board, 'w', $d['white'], 1);
			$figure = substr($this->figure($board, substr($d['white'], 3, 2)), 1, 1);
			if (isset($d['white'][0]) && $d['white'][0] != 'o' && $figure!='P') $d['white'] = $figure.$d['white'];
			$board = $this->move($board, 'b', $d['black'], 1);
			$figure = substr($this->figure($board, substr($d['black'], 3, 2)), 1, 1);
			if (isset($d['black'][0]) && $d['black'][0] != 'o' && $figure!='P') $d['black'] = $figure.$d['black'];

			$d['nr']++;
			$board['history'][] = $d;
		}

		return $board;
	}

	function move ($board, $player, $move, $no_move_apply=0) {
		// rochaden
		if ($player=='w') $roch_p = 1; else $roch_p = 8;
		if ($move == 'o-o') {
			$board['e'][$roch_p] = '-';
			$board['h'][$roch_p] = '-';
			$board['g'][$roch_p] = $player.'K';
			$board['f'][$roch_p] = $player.'R';
			$board['roch'][$player.'L'] = $board['roch'][$player.'G'] = 0;
		}elseif ($move == 'o-o-o') {
			$board['e'][$roch_p] = '-';
			$board['a'][$roch_p] = '-';
			$board['c'][$roch_p] = $player.'K';
			$board['d'][$roch_p] = $player.'R';
			$board['roch'][$player.'L'] = $board['roch'][$player.'G'] = 0;
		}else{
			if ($player=='w' && isset($move[1]) && $move[1]==7 && isset($move[4]) && $move[4]==8
				|| $player=='b' && isset($move[1]) && $move[1]==2 && isset($move[4]) && $move[4]==1
			) {
				// Pawn to Queen
				$board[$move[3]][$move[4]] = $player.'Q';
				if (!$no_move_apply) $move .= '=Q';
			}else{
				// take figure
				$dst_figure = $this->figure($board, substr($move, 3, 2));
				if ($dst_figure) {
					array_push($board['taken'][$dst_figure[0]], $dst_figure);
					if (!$no_move_apply) $move = substr($move,0,2).'x'.substr($move,3,2);
				}

				// standard move
				if (isset($move[0]) && isset($move[1])) $board[$move[3]][$move[4]] = $board[$move[0]][$move[1]];

				// verbiete künftige rochade
				if (in_array(substr($move, 0, 2), array('e1', 'e8')))
					$board['roch'][$player.'L'] = $board['roch'][$player.'G'] = 0;
				if (in_array(substr($move, 0, 2), array('a1', 'a8'))) $board['roch'][$player.'G'] = 0;
				if (in_array(substr($move, 0, 2), array('h1', 'h8'))) $board['roch'][$player.'L'] = 0;
			}
			if (isset($move[1])) $board[$move[0]][$move[1]] = '-';
		}

		// apply check or checkmate to move-string
		if (!$no_move_apply) {
			$other = $player=='w' ? 'b' : 'w';
			if ($this->is_check($board, $other, $this->position_of($board, $other.'K'))) {
				$move .= '+';
			}elseif ($this->is_checkmate($board, $other, $this->position_of($board, $other.'K'))) {
				$move .= '#';
			}elseif ($this->is_patt($board, $other)) {
				$move .= '=';
			}
		}

		$board['move'] = $move;

		return $board;
	}

	function is_move_valid ($board, $player, $move, $prev_move) {
		// rochaden
		if ($player == 'w') $roch_p = 1; else $roch_p = 8;
		if ($move == 'o-o') {
			if (in_array('g'.$roch_p, $this->possible_moves($board, $player, 'e'.$roch_p)))
				return true;
		}elseif ($move == 'o-o-o') {
			if (in_array('c'.$roch_p, $this->possible_moves($board, $player, 'e'.$roch_p))
			) return true;
		}else{
			// other moves
			if (in_array(substr($move, 3, 2), $this->possible_moves($board, $player, substr($move, 0, 2), $prev_move)))
				return true;
		}
		return false;
	}

	function possible_moves ($board, $player, $pos, $prev_move='', $no_check_check=0) {
		$x = substr($pos, 0, 1);
		$y = substr($pos, 1, 1);
		$figure = $board[$x][$y][1];

		$ret = array();

		if (!$board[$x][$y] || $board[$x][$y] == '-') {
			// no figure on posistion
			return array();
		}elseif ($board[$x][$y][0] != $player) {
			// figure of other player
			return array();
		}else{
			if ($figure == 'P') {
				// pawn
				$other_player = $player=='w' ? 'b' : 'w';
				if ($player == 'w') {
					// standard
					$p = $this->inc_y($pos);
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					// 2 steps in first move
					$p = $this->inc_y($pos, 2);
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					// schlagen rechts + en passant
					$p = $this->inc_y($this->inc_x($pos));
					$pass_mv = $this->inc_y($p).'-'.$this->dec_y($p);
					if ($p && ($this->player($board, $p)==$other_player || !$this->player($board, $p) && $prev_move==$pass_mv))
						array_push($ret, $p);
					// schlagen links + en passant
					$p = $this->inc_y($this->dec_x($pos));
					$pass_mv = $this->inc_y($p).'-'.$this->dec_y($p);
					if ($p && ($this->player($board, $p)==$other_player || !$this->player($board, $p) && $prev_move==$pass_mv))
						array_push($ret, $p);
				}else{
					// standard
					$p = $this->dec_y($pos);
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					// 2 steps in first move
					$p = $this->dec_y($pos, 2);
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					// schlagen rechts + en passant
					$p = $this->dec_y($this->inc_x($pos));
					$pass_mv = $this->dec_y($p).'-'.$this->inc_y($p);
					if ($p && ($this->player($board, $p)==$other_player || !$this->player($board, $p) && $prev_move==$pass_mv))
						array_push($ret, $p);
					// schlagen links + en passant
					$p = $this->dec_y($this->dec_x($pos));
					$pass_mv = $this->dec_y($p).'-'.$this->inc_y($p);
					if ($p && ($this->player($board, $p)==$other_player || !$this->player($board, $p) && $prev_move==$pass_mv))
						array_push($ret, $p);
				}
			}
			if ($figure == 'R' || $figure == 'Q') {
				// rook
				$p = $pos;
				while ($p = $this->inc_y($p)) {
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					elseif ($p && $this->player($board, $p)!=$player) {array_push($ret, $p); break;}
					else break;
				}
				$p = $pos;
				while ($p = $this->dec_y($p)) {
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					elseif ($p && $this->player($board, $p)!=$player) {array_push($ret, $p); break;}
					else break;
				}
				$p = $pos;
				while ($p = $this->inc_x($p)) {
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					elseif ($p && $this->player($board, $p)!=$player) {array_push($ret, $p); break;}
					else break;
				}
				$p = $pos;
				while ($p = $this->dec_x($p)) {
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					elseif ($p && $this->player($board, $p)!=$player) {array_push($ret, $p); break;}
					else break;
				}
			}
			if ($figure == 'B' || $figure == 'Q') {
				// bishop and queen
				$p = $pos;
				while ($p = $this->inc_x($this->inc_y($p))) {
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					elseif ($p && $this->player($board, $p)!=$player) {array_push($ret, $p); break;}
					else break;
				}
				$p = $pos;
				while ($p = $this->inc_x($this->dec_y($p))) {
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					elseif ($p && $this->player($board, $p)!=$player) {array_push($ret, $p); break;}
					else break;
				}
				$p = $pos;
				while ($p = $this->dec_x($this->inc_y($p))) {
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					elseif ($p && $this->player($board, $p)!=$player) {array_push($ret, $p); break;}
					else break;
				}
				$p = $pos;
				while ($p = $this->dec_x($this->dec_y($p))) {
					if ($p && !$this->figure($board, $p)) array_push($ret, $p);
					elseif ($p && $this->player($board, $p)!=$player) {array_push($ret, $p); break;}
					else break;
				}
			}
			if ($figure == 'N') {
				// knight
				$p = $this->inc_y($this->dec_x($pos, 2));
				if ($p && (!$this->figure($board, $p) || $this->player($board, $p)!=$player))
					array_push($ret, $p);
				$p = $this->dec_x($this->inc_y($pos, 2));
				if ($p && (!$this->figure($board, $p) || $this->player($board, $p)!=$player))
					array_push($ret, $p);
				$p = $this->inc_x($this->inc_y($pos, 2));
				if ($p && (!$this->figure($board, $p) || $this->player($board, $p)!=$player))
					array_push($ret, $p);
				$p = $this->inc_y($this->inc_x($pos, 2));
				if ($p && (!$this->figure($board, $p) || $this->player($board, $p)!=$player))
					array_push($ret, $p);
				$p = $this->dec_y($this->inc_x($pos, 2));
				if ($p && (!$this->figure($board, $p) || $this->player($board, $p)!=$player))
					array_push($ret, $p);
				$p = $this->inc_x($this->dec_y($pos, 2));
				if ($p && (!$this->figure($board, $p) || $this->player($board, $p)!=$player))
					array_push($ret, $p);
				$p = $this->dec_x($this->dec_y($pos, 2));
				if ($p && (!$this->figure($board, $p) || $this->player($board, $p)!=$player))
					array_push($ret, $p);
				$p = $this->dec_y($this->dec_x($pos, 2));
				if ($p && (!$this->figure($board, $p) || $this->player($board, $p)!=$player))
					array_push($ret, $p);
			}

			if ($figure == 'K') {
				// standard moves
				$p = $this->inc_y($pos);
				$tboard = $this->move($board, $player, "$pos-$p", 1);
				if ($p && $this->player($board, $p)!=$player && ($no_check_check || !$this->is_check($tboard, $player, $p)))
					array_push($ret, $p);
				$p = $this->inc_x($p);
				$tboard = $this->move($board, $player, "$pos-$p", 1);
				if ($p && $this->player($board, $p)!=$player && ($no_check_check || !$this->is_check($tboard, $player, $p)))
					array_push($ret, $p);
				$p = $this->dec_y($p);
				$tboard = $this->move($board, $player, "$pos-$p", 1);
				if ($p && $this->player($board, $p)!=$player && ($no_check_check || !$this->is_check($tboard, $player, $p)))
					array_push($ret, $p);
				$p = $this->dec_y($p);
				$tboard = $this->move($board, $player, "$pos-$p", 1);
				if ($p && $this->player($board, $p)!=$player && ($no_check_check || !$this->is_check($tboard, $player, $p)))
					array_push($ret, $p);
				$p = $this->dec_x($p);
				$tboard = $this->move($board, $player, "$pos-$p", 1);
				if ($p && $this->player($board, $p)!=$player && ($no_check_check || !$this->is_check($tboard, $player, $p)))
					array_push($ret, $p);
				$p = $this->dec_x($p);
				$tboard = $this->move($board, $player, "$pos-$p", 1);
				if ($p && $this->player($board, $p)!=$player && ($no_check_check || !$this->is_check($tboard, $player, $p)))
					array_push($ret, $p);
				$p = $this->inc_y($p);
				$tboard = $this->move($board, $player, "$pos-$p", 1);
				if ($p && $this->player($board, $p)!=$player && ($no_check_check || !$this->is_check($tboard, $player, $p)))
					array_push($ret, $p);
				$p = $this->inc_y($p);
				$tboard = $this->move($board, $player, "$pos-$p", 1);
				if ($p && $this->player($board, $p)!=$player && ($no_check_check || !$this->is_check($tboard, $player, $p)))
					array_push($ret, $p);

				// kleine rochade
				$p = $this->inc_x($pos, 2);
				$tboard = $this->move($board, $player, 'o-o', 1);
				if ($board['roch'][$player.'L']
					&& !$this->figure($board, $p) && !$this->figure($board, $this->inc_x($pos))
					&& ($no_check_check || !$this->is_check($tboard, $player, $p))
				) array_push($ret, $p);

				// grosse rochade
				$p = $this->dec_x($pos, 2);
				$tboard = $this->move($board, $player, 'o-o-o', 1);
				if ($board['roch'][$player.'G']
					&& !$this->figure($board, $p) && !$this->figure($board, $this->dec_x($pos)) && !$this->figure($board, $this->dec_x($pos, 2))
					&& ($no_check_check || !$this->is_check($tboard, $player, $p))
				) array_push($ret, $p);
			}


			// remove moves where player is checked after move
			$playerking = $this->position_of($board, $player.'K');
			$rem_one = false;
			for ($i=0; $i<sizeof($ret); $i++) {
				$tboard = $this->move($board, $player, $pos.'-'.$ret[$i], 1);
				if ($no_check_check || $this->is_check($tboard, $player, $playerking)) {
					$ret[$i] = '';
					$rem_one = true;
				}
			}
			// if something removed, create proper array
			if ($rem_one) {
				$t = $ret;
				$ret = array();
				for ($i=0; $i<sizeof($t); $i++) {
					if ($t[$i]) array_push($ret, $t[$i]);
				}
			}

			return $ret;
		}
	}

	function is_check ($board, $player, $pos) {
		$other = $player=='w' ? 'b' : 'w';
		for ($i=ord('a'); $i<=ord('h'); $i++) {
			for ($j=1; $j<=8; $j++) {
				if ($this->player($board, chr($i).$j) == $other
					&& in_array($pos, $this->possible_moves($board, $other, chr($i).$j, '', 1))
				) return true;
			}
		}
		return false;
	}

	function is_checkmate ($board, $player, $pos) {

		for ($i=ord('a'); $i<=ord('h'); $i++) {
			for ($j=0; $j<=8; $j++) {
				if ($this->player($board, chr($i).$j) == $player) {
					$poss_moves = $this->possible_moves($board, $player, chr($i).$j);
					foreach ($poss_moves as $it) {
						$tboard = $this->move($board, $player, chr($i).$j.'-'.$it, 1);
						if (!$this->is_check($tboard, $player, $board[chr($i)][$j]==$player.'K' ? $it : $pos))
							return false;
					}
				}
			}
		}
		if (!$this->is_check($board, $player, $this->position_of($board, $player.'K'))) return false;
		return true;
	}

	function is_patt ($board, $player) {
		for ($i=ord('a'); $i<=ord('h'); $i++) {
			for ($j=1; $j<=8; $j++) {
				if ($this->player($board, chr($i).$j) == $player) {
					if (sizeof($this->possible_moves($board, $player, chr($i).$j))>0) return false;
				}
			}
		}
		return true;
	}

	function figure ($board, $pos) {
		if (strlen($pos) !=2 ) return '';
		elseif ($board[$pos[0]][$pos[1]] == '-') return '';
		else return $board[$pos[0]][$pos[1]];
	}

	function player ($board, $pos) {
		if (strlen($pos) != 2) return '';
		else return $board[$pos[0]][$pos[1]][0];
	}

	function position_of ($board, $figure) {
		for ($i=ord('a'); $i<=ord('h'); $i++) {
			for ($j=1; $j<=8; $j++) {
				if ($board[chr($i)][$j] == $figure) return chr($i).$j;
			}
		}
		return '';
	}

	function own_positions ($board, $player) {
		$ret = array();
		for ($i=ord('a'); $i<=ord('h'); $i++) {
			for ($j=1; $j<=8; $j++) {
				if ($this->player($board, chr($i).$j) == $player) array_push($ret, chr($i).$j);
			}
		}
		return $ret;
	}

	function inc_x ($pos, $anz=1) {
		if (!$pos) return 0;
		if (!$anz) return $pos;

		$row = ord(substr($pos, 0, 1)) + 1;
		if ($row > ord('h')) return false;
		else return $this->inc_x(chr($row).substr($pos, 1, 1), $anz-1);
	}

	function dec_x ($pos, $anz=1) {
		if (!$pos) return 0;
		if (!$anz) return $pos;

		$row = ord(substr($pos, 0, 1)) - 1;
		if ($row < ord('a')) return false;
		else return $this->dec_x(chr($row).substr($pos, 1, 1), $anz-1);
	}

	function inc_y ($pos, $anz=1) {
		if (!$pos) return false;
		if (!$anz) return $pos;

		$row = substr($pos, 1, 1) + 1;
		if ($row > 8) return false;
		else return $this->inc_y(substr($pos, 0, 1).$row, $anz-1);
	}

	function dec_y ($pos, $anz=1) {
		if (!$pos) return false;
		if (!$anz) return $pos;

		$row = substr($pos, 1, 1) - 1;
		if ($row < 1) return false;
		else return $this->dec_y(substr($pos, 0, 1).$row, $anz-1);
	}

	function is_valid_position ($pos) {
		if (strlen($pos) != 2) return false;
		if (chr($pos[0]) < chr('a') || chr($pos[0]) > chr('h')) return false;
		if ($pos[1] < 1 || $pos[1] > 8) return false;
		return true;
	}

	function simplify_board ($board) {
		$b = array();
		for ($i=0; $i<8; $i++) $b[] = array();

		for ($i=ord('a'), $n=0; $i<=ord('h'); $i++, $n++) {
			for ($j=1, $m=7; $j<=8; $j++, $m--) {
				$b[$m][$n] = $board[chr($i)][$j];
			}
		}

		return $b;
	}

	function update_dwz ($game) {
	   global $db;

	   $prev_score_2 = $prev_score_1 = CHESS_DWZ_BASE_POINTS;

	   $e = $db->query("SELECT * FROM chess_games WHERE id=? AND state!='running'", __FILE__, __LINE__, __METHOD__, [$game]);
	   $d = $db->fetch($e);
	   if (!$d) user_error("Invalid Chess Game-ID", E_USER_ERROR);

	   if ($d['winner'] == $d['white']) $p1 = 1;
	   elseif ($d['winner'] == $d['black']) $p1 = 0;
	   else $p1 = 0.5;
	   $p2 = 1 - $p1;

	   $e = $db->query("SELECT * FROM chess_dwz WHERE user=?", __FILE__, __LINE__, __METHOD__, [$d['white']]);
	   $d1 = $db->fetch($e);
	   if ($d1) {
	   	$dwz1 = $d1['score'];
	   	$prev_score_1 = $dwz1;
	   }
	   else $dwz1 = CHESS_DWZ_BASE_POINTS;
	   $e = $db->query("SELECT * FROM chess_dwz WHERE user=?", __FILE__, __LINE__, __METHOD__, [$d['black']]);
	   $d2 = $db->fetch($e);
	   if ($d2) {
	   	$dwz2 = $d2['score'];
	   	$prev_score_2 = $dwz2;
	   }
	   else $dwz2 = CHESS_DWZ_BASE_POINTS;

	   $prob1 = 1 / (pow(10, (($dwz2 - $dwz1) / 400)) + 1) ;
	   $prob2 = 1 / (pow(10, (($dwz1 - $dwz2) / 400)) + 1) ;

	   $dif1 = round (CHESS_DWZ_MAX_POINTS_TRANSFERABLE * ($p1 - $prob1));
	   $dif2 = round (CHESS_DWZ_MAX_POINTS_TRANSFERABLE * ($p2 - $prob2));

	   $dwz1 += $dif1;
	   $dwz2 += $dif2;

	   if ($d1) $db->query("UPDATE chess_dwz SET score=?, prev_score=? WHERE user=?", __FILE__, __LINE__, __METHOD__, [$dwz1, $prev_score_1, $d['white']]);
	   else $db->query("INSERT INTO chess_dwz (user, score, prev_score) VALUES (?, ?, ?)", __FILE__, __LINE__, __METHOD__, [$d['white'], $dwz1, $prev_score_1]);
	   if ($d2) $db->query("UPDATE chess_dwz SET score=?, prev_score=? WHERE user=?", __FILE__, __LINE__, __METHOD__, [$dwz2, $prev_score_2, $d['black']]);
	   else $db->query("INSERT INTO chess_dwz (user, score, prev_score) VALUES (?, ?, ?)", __FILE__, __LINE__, __METHOD__, [$d['black'], $dwz2, $prev_score_2]);

	   // dwz_dif für game
	   $db->query("UPDATE chess_games SET dwz_dif=? WHERE id=? AND state!='running'", __FILE__, __LINE__, __METHOD__, [abs($dif1), $game]);

	   // rank update
	   $e = $db->query("SELECT * FROM chess_dwz ORDER BY score DESC", __FILE__, __LINE__, __METHOD__);
	   $i = 1;
	   $prev_score = 0;
	   $rank = 0;
	   while ($upd = $db->fetch($e)) {
	   	if ($upd['score'] != $prev_score) {
	   		$rank = $i;
	   	}

	   	if ($upd['user'] == $d['white'] || $upd['user'] == $d['black']) {
	   		$prev_rank = ", prev_rank=$upd[rank]";
	   	}else{
	   		$prev_rank = "";
	   	}

	   	$db->query("UPDATE chess_dwz SET rank=?, prev_rank=? WHERE user=?", __FILE__, __LINE__, __METHOD__, [$rank, $prev_rank, $upd['user']]);

	   	$prev_score = $upd['score'];
	   	++$i;
	   }
	}

	function running_games () {
		global $db, $user;

		$e = $db->query("SELECT count(*) anz FROM chess_games WHERE next_turn=?", __FILE__, __LINE__, __METHOD__, [$user->id]);
		$d = $db->fetch($e);
		return $d['anz'];
	}

	function my_games () {
		global $db, $user;

		if (!$user->id) return array();

		$e = $db->query(
			"SELECT IF(g.white=?, b.username, w.username) player, IF(g.next_turn=?, 1, 0) my_turn,
			concat('/?tpl=141&game=', g.id) link
			FROM chess_games g, user b, user w
			WHERE (g.black=? OR g.white=?) AND b.id=g.black AND w.id=g.white
			ORDER BY g.last_turn DESC",
			__FILE__, __LINE__, __METHOD__, [$user->id, $user->id, $user->id, $user->id]
		);
		$my_games = array();
		while ($d = $db->fetch($e)) {
			array_push($my_games, $d);
		}
		return $my_games;
	}

	function positions () {
		$ret = array();
		for ($i=0; $i<8; $i++) $ret[] = array();

		for ($i=0; $i<=7; $i++) {
			for ($j=0; $j<=7; $j++) {
				$ret[$j][$i] = chr($i+ord('a')).(8-$j);
			}
		}
		return $ret;
	}
}

/** Instantiate Class */
$chess = new Chess();
