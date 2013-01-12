<?php

Class Quotes {

	function execActions() {

		global $db, $user;

		if($_POST['action'] == 'benoten' && $_POST['score'] != '') {

	  	$sql =
	  		"REPLACE INTO quotes_votes (quote_id, user_id, score) "
	  		." VALUES ("
	  		.$_POST['quote_id']
	  		.', '.$user->id
	  		.', '.$_POST['score']
	  		.")"
	  	;
	  	$db->query($sql, __FILE__, __LINE__);
			header("Location: ".base64_decode($_POST['url']));
		}
	}

	function formatQuote($rs) {

		global $user;

		$html .=
			'<table cellpadding="1" cellspacing="1" width="100%">'
			.'<tr><td align="center" width="100%">'
			.'<i>'.nl2br(htmlentities($rs["text"])).'</i>'
			.' - '
			.$user->id2user($rs["user_id"], 0)
			.'</td></tr>'
		;

		if($user->typ != USER_NICHTEINGELOGGT && Quotes::hasVoted($user->id, $rs['id'])) {
			$html .=
				'<tr><td align="center" valign="middle">'
				.'<small>(Note: '.round(Quotes::getScore($rs['id']), 1).')</small>'
				//.' (votes: '
				//.Quotes::getNumvotes($rs['id'])
				//.($user->typ != USER_NICHTEINGELOGGT ? ', deine Note: '.Quotes::getScorebyUser($rs['id'], $user->id) : '')
				//.')'

			;
		}

		if($user->typ != USER_NICHTEINGELOGGT && !Quotes::hasVoted($user->id, $rs['id'])) {
			$html .=
				'</table>'
				.'<table><tr><td align="center" valign="middle">'
				.'<form action="/quotes.php" name="quotevoteform'.$rs['id'].'" method="post">'
				.'<input name="action" type="hidden" value="benoten">'
				.'<input name="quote_id" type="hidden" value="'.$rs['id'].'">'
				.'<input name="url" type="hidden" value="'.base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">'
				.'<tr><td valign="middle">'
				.'<input name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" type="radio" value="1">'
				.'</td><td>1</td><td>'
				.'<input name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" type="radio" value="2">'
				.'</td><td>2</td><td>'
				.'<input name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" type="radio" value="3">'
				.'</td><td>3</td><td>'
				.'<input name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" type="radio" value="4">'
				.'</td><td>4</td><td>'
				.'<input name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" type="radio" value="5">'
				.'</td><td>5</td><td>'
				.'<input name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" type="radio" value="6">'
				.'</td><td>6</td><td>'
				.'<input class="button" type="submit" value="benoten">'
				.'</td></tr>'
				.'</form>'
			;
		}

		if($user->typ != USER_NICHTEINGELOGGT && $user->id == $rs['user_id']) {
  			$html .=
  				' <a href="'
  				.getChangedURL('do=delete&quote_id='.$rs['id'].'&site='.$site)
  				.'">[delete]</a>'
  			;
		}

		$html .= '</td></tr></table>';

		return $html;
	}

	function getScore($quote_id) {
		global $db;

		$sql =
			"SELECT AVG(score) as score"
			." FROM quotes_votes"
			." WHERE quote_id = ".$quote_id
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		$rs = $db->fetch($result, __FILE__, __LINE__);

		return $rs['score'];
	}



	function getDailyQuote() {
		global $db;

		//$sql = "SELECT quotes.*, TO_DAYS(p.date)-TO_DAYS(NOW()) upd"." FROM periodic p, quotes"." WHERE p.name='daily_quote' AND p.id=quotes.id";
		$sql =
			"SELECT quotes.*, TO_DAYS(p.date)-TO_DAYS(NOW()) upd"
			." FROM periodic p, quotes"
			." WHERE p.name='daily_quote' AND p.id=quotes.id";
		$result = $db->query($sql, __FILE__, __LINE__);

		$rs = $db->fetch($result);

		return Quotes::formatQuote($rs);
	}

	function getNumVotes($quote_id) {
		global $db;

		$sql =
			"SELECT *"
			." FROM quotes_votes"
			." WHERE quote_id = ".$quote_id
		;
		$result = $db->query($sql, __FILE__, __LINE__);

		return $db->num($result, __FILE__, __LINE__);
	}

	function getScorebyUser($quote_id, $user_id) {
		global $db;

		$sql =
			"SELECT score"
			." FROM quotes_votes"
			." WHERE quote_id = ".$quote_id." AND user_id =".$user_id
		;
		$result = $db->query($sql, __FILE__, __LINE__);
		$rs = $db->fetch($result, __FILE__, __LINE__);

		return $rs['score'];
	}

	function hasVoted($user_id, $quote_id) {
		global $db;

		$sql =
			"SELECT *"
			." FROM quotes_votes"
			." WHERE quote_id = '".$quote_id."' AND user_id =".$user_id
		;
		$result = $db->query($sql, __FILE__, __LINE__);

		return $db->num($result, __FILE__, __LINE__);
	}

	function isDailyQuote($id) {
		global $db;

		$sql =	"SELECT * FROM periodic
				WHERE date = NOW() AND name = 'daily_quote'";

		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__));

		return $rs['id'] == $id;
	}

	function newDailyQuote() {
		global $db;

			// anzahl quotes ermitteln
			$result = $db->query("SELECT * FROM quotes", __FILE__, __LINE__);
			$count = $db->num($result);

			// zufaellige quote-id holen
			$id = rand(0, $count-1); // Zufalls #
			$id = rand($id, $count-1); // die besten bevorzugen.

			// Quote fetchen
			$sql = "
				SELECT quotes.id, avg( score ) score
				FROM `quotes`
				LEFT JOIN quotes_votes ON ( quote_id = quotes.id )
				GROUP BY quotes.id
				ORDER BY score ASC
				LIMIT ".$id.", 1
			";
			$result = $db->query($sql, __FILE__, __LINE__);
			$rs = $db->fetch($result);

			// Quote in die daily tabelle tun
			$sql = "REPLACE INTO periodic (name, id, date) VALUES ('daily_quote', ".$rs['id'].", NOW())";
			$db->query($sql, __FILE__, __LINE__);
	}
}
?>
