<?php
/**
 * Quotes Class
 *
 * In dieser Klasse befinden sich alle Funktionen zur Steuerung der Activities
 *
 * @author		[z]milamber
 * @author		IneX
 * @version		2.0
 * @since		1.0
 * @since		2.0 added Telegram Notification for new Daily Quote
 * @package		zorg
 * @subpackage	Quotes
 */
class Quotes
{
	static function execActions()
	{
		global $db, $user;

		if(isset($_POST['action']) && $_POST['action'] == 'benoten' && isset($_POST['score']) && is_numeric($_POST['score']))
		{
		  	$sql =
		  		"REPLACE INTO quotes_votes (quote_id, user_id, score) "
		  		." VALUES ("
		  		.$_POST['quote_id']
		  		.', '.$user->id
		  		.', '.$_POST['score']
		  		.")"
		  	;
		  	$db->query($sql, __FILE__, __LINE__, __METHOD__);
			header("Location: ".base64_decode($_POST['url']));
		}
	}

	static function formatQuote($rs)
	{
		global $user;

		$html = '<div class="quote">'
					.'<blockquote><i>'.nl2br(htmlentities($rs["text"])).'</i>'
					.' - '.$user->id2user($rs['user_id'], 0)
					.($user->is_loggedin() ? ($user->id === (int)$rs['user_id'] ? ' <a href="'.getChangedURL('do=delete&quote_id='.$rs['id'].'&site='.$site).'">[delete]</a>' : '') : '')
					.'</blockquote>';

		if ($user->is_loggedin())
		{
			if (Quotes::hasVoted($user->id, $rs['id'])) $html .= '<small>(Note: '.round(Quotes::getScore($rs['id']), 1).')</small>';
			if (!Quotes::hasVoted($user->id, $rs['id']))
			{
				$html .= '<form name="quotevoteform'.$rs['id'].'" method="post" action="/quotes.php" class="voteform" style="display: flex;">'
							.'<input name="action" type="hidden" value="benoten">'
							.'<input name="quote_id" type="hidden" value="'.$rs['id'].'">'
							.'<input name="url" type="hidden" value="'.base64_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">'
							.'<span>Benoten:</span>'
							.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
								.'<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" value="1"></label>'
								//.'1</label>'
							.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
								.'<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" value="2"></label>'
								//.'2</label>'
							.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
								.'<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" value="3"></label>'
								//.'3</label>'
							.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
								.'<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" value="4"></label>'
								//.'4</label>'
							.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
								.'<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" value="5"></label>'
								//.'5</label>'
							.'<label class="scorevalue" style="display: flex;margin-right: 1em;">'
								.'<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();" value="6"></label>'
								//.'6</label>'
							//.'<input class="button" type="submit" value="benoten">'
						.'</form>';
			}
		}

		$html .= '</div>';
		return $html;
	}

	static function getScore($quote_id) {
		global $db;

		$sql =
			"SELECT AVG(score) as score"
			." FROM quotes_votes"
			." WHERE quote_id = ".$quote_id
		;
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		$rs = $db->fetch($result, __FILE__, __LINE__, __METHOD__);

		return $rs['score'];
	}



	static function getDailyQuote() {
		global $db;

		//$sql = "SELECT quotes.*, TO_DAYS(p.date)-TO_DAYS(NOW()) upd"." FROM periodic p, quotes"." WHERE p.name='daily_quote' AND p.id=quotes.id";
		$sql =
			"SELECT quotes.*, TO_DAYS(p.date)-TO_DAYS(NOW()) upd"
			." FROM periodic p, quotes"
			." WHERE p.name='daily_quote' AND p.id=quotes.id";
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		$rs = $db->fetch($result);

		return Quotes::formatQuote($rs);
	}

	static function getNumVotes($quote_id) {
		global $db;

		$sql =
			"SELECT *"
			." FROM quotes_votes"
			." WHERE quote_id = ".$quote_id
		;
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		return $db->num($result, __FILE__, __LINE__, __METHOD__);
	}

	static function getScorebyUser($quote_id, $user_id) {
		global $db;

		$sql =
			"SELECT score"
			." FROM quotes_votes"
			." WHERE quote_id = ".$quote_id." AND user_id =".$user_id
		;
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
		$rs = $db->fetch($result, __FILE__, __LINE__, __METHOD__);

		return $rs['score'];
	}

	static function hasVoted($user_id, $quote_id) {
		global $db;

		$sql =
			"SELECT *"
			." FROM quotes_votes"
			." WHERE quote_id = '".$quote_id."' AND user_id =".$user_id
		;
		$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);

		return $db->num($result, __FILE__, __LINE__, __METHOD__);
	}

	static function isDailyQuote($id) {
		global $db;

		$sql =	"SELECT * FROM periodic
				WHERE date = NOW() AND name = 'daily_quote'";

		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

		return $rs['id'] == $id;
	}

	/**
	 * Quote of the Day
	 * Generates a new Daily Quote
	 *
	 * @author [z]milamber
	 * @author IneX
	 * @version 2.1
	 * @since 1.0
	 * @since 2.0 added Telegram Notification for new Daily Quote
	 * @since 2.1 changed to new Telegram Send-Method
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $telegram Globales Class-Object mit den Telegram-Methoden
	 */
	static function newDailyQuote() {
		global $db, $user, $telegram;

		try {
			// anzahl quotes ermitteln
			$result = $db->query("SELECT * FROM quotes", __FILE__, __LINE__, __METHOD__);
			$count = $db->num($result);

			// zufaellige quote-id holen
			$id = rand(0, $count-1); // Zufalls #
			$id = rand($id, $count-1); // die besten bevorzugen.

			// Quote fetchen
			$sql = "
				SELECT quotes.id, avg( score ) score, quotes.text, quotes.user_id
				FROM `quotes`
				LEFT JOIN quotes_votes ON ( quote_id = quotes.id )
				GROUP BY quotes.id
				ORDER BY score ASC
				LIMIT ".$id.", 1
			";
			$result = $db->query($sql, __FILE__, __LINE__, __METHOD__);
			$rs = $db->fetch($result);

			// Quote in die daily tabelle tun
			$sql = "REPLACE INTO periodic (name, id, date) VALUES ('daily_quote', ".$rs['id'].", NOW())";
			$db->query($sql, __FILE__, __LINE__, __METHOD__);

			/** Send new Daily Quote as Telegram Message */
			$telegram->send->message('group', sprintf('Daily [z]Quote: <b>%s</b><i> - %s</i>', $rs['text'], $user->id2user($rs['user_id'], TRUE)), ['disable_notification' => 'true']);

			return true;
		}
		catch (Exception $e) {
			user_error($e->getMessage(), E_USER_ERROR);

			return false;
		}
	}
}
