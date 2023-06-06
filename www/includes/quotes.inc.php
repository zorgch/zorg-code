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

		if(isset($_POST['action']) && $_POST['action'] == 'benoten' &&
			isset($_POST['quote_id']) && !empty($_POST['quote_id']) && $_POST['quote_id'] > 0 &&
			isset($_POST['score']) && is_numeric($_POST['score']) && $_POST['score'] > 0)
		{
			$quote_id = $_POST['quote_id'];
			$votescore = $_POST['score'];
			if (!Quotes::hasVoted($user->id, $quote_id))
			{
				$sql = 'REPLACE INTO quotes_votes (quote_id, user_id, score) VALUES ('.$quote_id.', '.$user->id.', '.$votescore.')';
				$db->query($sql, __FILE__, __LINE__, __METHOD__);
			}
			header('Location: '.base64url_decode($_POST['url']));
		}
	}

	static function formatQuote($rs)
	{
		global $user;

		$site = (isset($_POST['site']) && !empty($_POST['site']) && $_POST['site'] > 0 ? $_POST['site'] : 0);
		$html = '<div class="quote">'
					.'<blockquote><i>'.nl2br(htmlentities($rs["text"])).'</i>'
					.' - '.$user->id2user($rs['user_id'], 0)
					.($user->is_loggedin() ? ($user->id === (int)$rs['user_id'] ? ' <a href="'.getChangedURL('do=delete&quote_id='.$rs['id'].'&site='.$site).'">[delete]</a>' : '') : '')
					.'</blockquote>';

		if ($user->is_loggedin() && !Quotes::hasVoted($user->id, $rs['id']))
		{
			$html .= '<form name="quotevoteform'.$rs['id'].'" method="post" action="/quotes.php" class="voteform left">'
						.'<input name="action" type="hidden" value="benoten">'
						.'<input name="quote_id" type="hidden" value="'.$rs['id'].'">'
						.'<input name="url" type="hidden" value="'.base64url_encode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']).'">'
						.'<span class="scoreinfo">Benoten:</span>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="6"></label>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="5"></label>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="4"></label>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="3"></label>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="2"></label>'
						.'<label class="scorevalue">
							<input type="radio" name="score" onClick="document.quotevoteform'.$rs['id'].'.submit();this.setAttribute(\'disabled\', \'disabled\');" value="1"></label>'
					.'</form>';
		}
		else {
			$totalvotescore = round(Quotes::getScore($rs['id']), 1);
			if ($totalvotescore > 0) $html .= '<small>(Note: '.$totalvotescore.')</small>';//+ Anzahl Votes: getNumVotes()
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

	/**
	 * Check if Quote ID is Daily Quote
	 *
	 * @author [z]milamber
	 * @author IneX
	 * @version 2.0
	 * @since 1.0 method added
	 * @since 2.0 `05.06.2023` `IneX` Removed date=NOW() comparison (not returning a result)
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @param int Quote ID to check
	 * @return bool True/False
	 */
	static function isDailyQuote($id) {
		global $db;

		$sql =	'SELECT id FROM periodic WHERE date = NOW() AND name = "daily_quote"';
		$rs = $db->fetch($db->query($sql, __FILE__, __LINE__, __METHOD__));

		return (int)$rs['id'] === (int)$id;
	}

	/**
	 * Quote of the Day
	 * Generates a new Daily Quote
	 *
	 * @author [z]milamber
	 * @author IneX
	 * @version 3.0
	 * @since 1.0
	 * @since 2.0 added Telegram Notification for new Daily Quote
	 * @since 2.1 changed to new Telegram Send-Method
	 * @since 3.0 `05.06.2023` `IneX` optimized and reduced SQL-query, code refactored
	 *
	 * @global object $db Globales Class-Object mit allen MySQL-Methoden
	 * @global object $user Globales Class-Object mit den User-Methoden & Variablen
	 * @global object $telegram Globales Class-Object mit den Telegram-Methoden
	 */
	static function newDailyQuote() {
		global $db, $user, $telegram;

		try {
			/** Alle Quotes holen */
			$quotes = $db->query('SELECT id, COALESCE((SELECT AVG(score) FROM quotes_votes WHERE quote_id=quotes.id GROUP BY quote_id), 0) score, text, user_id
								  FROM quotes GROUP BY id ORDER BY RAND()', __FILE__, __LINE__, __METHOD__);

			/** Zufällige Quote ID auswählen */
			$count = $db->num($quotes); // Anzahl Quotes
			if ($count <= 0) throw new Exception('No Quotes found');
			$id = rand(0, $count-1); // Zufalls #
			$id = rand($id, $count-1); // die besten bevorzugen.

			/** Ausgewählten Quote fetchen */
			$select_quote = $db->seek($quotes, $id);
			$rs = $db->fetch($quotes);

			if (!$rs || count($rs) === 0) throw new Exception('Quote data not fetched');

			/** Quote in die daily tabelle tun */
			$sql = 'REPLACE INTO periodic (name, id, date) VALUES ("daily_quote", '.$rs['id'].', NOW())';
			$db->query($sql, __FILE__, __LINE__, __METHOD__);

			/** Send new Daily Quote as Telegram Message */
			$telegram->send->message('group', sprintf('Daily [z]Quote: <b>%s</b><i> - %s</i>', $rs['text'], $user->id2user($rs['user_id'], TRUE)), ['disable_notification' => 'true']);

			return true;
		}
		catch (Exception $e) {
			if (DEVELOPMENT === true) echo $e->getMessage();
			user_error($e->getMessage(), E_USER_ERROR);

			return false;
		}
	}
}
